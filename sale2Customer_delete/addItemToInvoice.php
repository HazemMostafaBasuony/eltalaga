<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// استيراد ملف الاتصال بقاعدة البيانات
include('../hmb/conn.php');

// التحقق من أن الطلب هو POST وأنه يحتوي على بيانات JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // استلام البيانات من الطلب
    $jsonData = file_get_contents('php://input');
    $invoiceData = json_decode($jsonData, true);

    // تسجيل بيانات الإدخال للتحليل
    file_put_contents('invoice_debug.log', "Received data:\n" . print_r([
        'raw_input' => $jsonData,
        'decoded_data' => $invoiceData
    ], true), FILE_APPEND);

    // التحقق من صحة البيانات
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'message' => 'خطأ في تنسيق البيانات المرسلة',
            'json_error' => json_last_error_msg()
        ]);
        exit;
    }

    // التحقق من وجود البيانات الأساسية
    $requiredFields = [
        'salesmaneID', 'action', 'state', 'paymentMethod',
        'total', 'discount', 'totalDue', 'vat', 'generalTotal',
        'notes', 'paidAmount', 'items'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($invoiceData[$field])) {
            echo json_encode([
                'success' => false,
                'message' => "حقل مطلوب مفقود: $field",
                'received_data' => array_keys($invoiceData)
            ]);
            exit;
        }
    }

    // التحقق من صحة salesmaneID
    if (!is_numeric($invoiceData['salesmaneID']) || $invoiceData['salesmaneID'] <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'معرف المندوب غير صالح'
        ]);
        exit;
    }

    // التحقق من وجود العناصر وصحتها
    if (!is_array($invoiceData['items']) || empty($invoiceData['items'])) {
        echo json_encode([
            'success' => false,
            'message' => 'قائمة العناصر فارغة أو غير صالحة'
        ]);
        exit;
    }

    try {
        // بدء المعاملة
        $conn->autocommit(false);

        // 1. إدراج الفاتورة الرئيسية
        $insertInvoiceQuery = "INSERT INTO invoices (
            customerID, action, state, paymentMethod, 
            total, discount, totalDue, vat, generalTotal, 
            notes, paidAmount,remainnigAmount
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtInvoice = $conn->prepare($insertInvoiceQuery);
        if (!$stmtInvoice) {
            throw new Exception('فشل تحضير استعلام الفاتورة: ' . $conn->error);
        }

        $stmtInvoice->bind_param(
            'isisdddddsd',
            $invoiceData['salesmaneID'],
            $invoiceData['action'],
            $invoiceData['state'],
            $invoiceData['paymentMethod'],
            $invoiceData['total'],
            $invoiceData['discount'],
            $invoiceData['totalDue'],
            $invoiceData['vat'],
            $invoiceData['generalTotal'],
            $invoiceData['notes'],
            $invoiceData['paidAmount'],
            $invoiceData['remainnigAmount']
        );

        if (!$stmtInvoice->execute()) {
            throw new Exception('فشل إدراج الفاتورة: ' . $stmtInvoice->error);
        }

        $invoiceID = $conn->insert_id;

        // 2. تحضير الاستعلامات للعناصر
        $sqlItem = "SELECT unitM, unitS, fl2M, fm2S, stock FROM itemscard WHERE itemID = ?";
        $stmtItemSelect = $conn->prepare($sqlItem);
        if (!$stmtItemSelect) {
            throw new Exception('فشل تحضير استعلام الصنف: ' . $conn->error);
        }

        $insertItemQuery = "INSERT INTO itemAction (itemID, action, count, price, invoiceID) 
                           VALUES (?, 'out', ?, ?, ?)";
        $stmtItemInsert = $conn->prepare($insertItemQuery);
        if (!$stmtItemInsert) {
            throw new Exception('فشل تحضير استعلام إدراج الحركة: ' . $conn->error);
        }

        $updateStockQuery = "UPDATE itemscard SET stock = ? WHERE itemID = ?";
        $stmtStockUpdate = $conn->prepare($updateStockQuery);
        if (!$stmtStockUpdate) {
            throw new Exception('فشل تحضير استعلام تحديث المخزون: ' . $conn->error);
        }

        // 3. معالجة كل عنصر
        foreach ($invoiceData['items'] as $item) {
            // التحقق من صحة بيانات العنصر
            if (empty($item['itemID']) || !is_numeric($item['itemID'])) {
                throw new Exception("معرف الصنف غير صالح: " . print_r($item, true));
            }
            
            if (empty($item['price']) || !is_numeric($item['price'])) {
                throw new Exception("سعر الصنف غير صالح: " . print_r($item, true));
            }
            
            if (empty($item['quantity']) || !is_numeric($item['quantity'])) {
                throw new Exception("كمية الصنف غير صالحة: " . print_r($item, true));
            }

            // الحصول على بيانات الصنف
            $stmtItemSelect->bind_param('i', $item['itemID']);
            if (!$stmtItemSelect->execute()) {
                throw new Exception('فشل تنفيذ استعلام الصنف: ' . $stmtItemSelect->error);
            }

            $itemData = $stmtItemSelect->get_result()->fetch_assoc();
            if (!$itemData) {
                throw new Exception("الصنف غير موجود: {$item['itemID']}");
            }

            // التحقق من المخزون
            $currentStock = $itemData['stock'];
            if ($currentStock < $item['quantity']) {
                throw new Exception("لا يوجد مخزون كافي للصنف {$item['itemID']} (المتاح: $currentStock ، المطلوب: {$item['quantity']})");
            }

            // حساب الكمية والسعر حسب الوحدة
            $fL2M = !empty($itemData['fl2M']) ? $itemData['fl2M'] : 1;
            $fM2S = !empty($itemData['fm2S']) ? $itemData['fm2S'] : 1;

            $quantity = $item['quantity'];
            $price = $item['price'];

            if ($item['unit'] === ($itemData['unitM'] ?? '')) {
                $quantity = $quantity / $fL2M;
                $price = $price * $fL2M;
            } elseif ($item['unit'] === ($itemData['unitS'] ?? '')) {
                $quantity = ($quantity / $fM2S) / $fL2M;
                $price = $price * $fM2S * $fL2M;
            }

            // إدراج حركة المخزون
            $stmtItemInsert->bind_param('iddi', 
                $item['itemID'],
                $quantity,
                $price,
                $invoiceID
            );
            if (!$stmtItemInsert->execute()) {
                throw new Exception('فشل إدراج حركة المخزون: ' . $stmtItemInsert->error);
            }

            // تحديث المخزون
            $newStock = $currentStock - $quantity;
            $stmtStockUpdate->bind_param('di', $newStock, $item['itemID']);
            if (!$stmtStockUpdate->execute()) {
                throw new Exception('فشل تحديث المخزون: ' . $stmtStockUpdate->error);
            }
        }

        // 4. تحديث بيانات المندوب
        $updateSalesmanQuery = "UPDATE salesmane SET 
            stockInventory = stockInventory + ? 
            WHERE salesmaneID = ?"; 
        $stmtSalesmanUpdate = $conn->prepare($updateSalesmanQuery);
        if (!$stmtSalesmanUpdate) {
            throw new Exception('فشل تحضير استعلام تحديث المندوب: ' . $conn->error);
        }
        $stmtSalesmanUpdate->bind_param('di', $invoiceData['generalTotal'], $invoiceData['salesmaneID']);
        if (!$stmtSalesmanUpdate->execute()) {
            throw new Exception('فشل تحديث بيانات المندوب: ' . $stmtSalesmanUpdate->error);
        }

        // تأكيد جميع العمليات
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'تم حفظ الفاتورة بنجاح',
            'invoiceID' => $invoiceID
        ]);

    } catch (Exception $e) {
        // التراجع عن جميع العمليات في حالة الخطأ
        $conn->rollback();
        
        // تسجيل الخطأ
        error_log("Invoice Error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'حدث خطأ أثناء حفظ الفاتورة: ' . $e->getMessage()
        ]);
    } finally {
        // إغلاق جميع العبارات المحضرة
        $conn->autocommit(true);
        if (isset($stmtInvoice)) $stmtInvoice->close();
        if (isset($stmtItemSelect)) $stmtItemSelect->close();
        if (isset($stmtItemInsert)) $stmtItemInsert->close();
        if (isset($stmtStockUpdate)) $stmtStockUpdate->close();
        if (isset($stmtSalesmanUpdate)) $stmtSalesmanUpdate->close();
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'طريقة الطلب غير مدعومة'
    ]);
}
?>