<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// الاتصال بقاعدة البيانات
include('../hmb/conn_pdo.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents('php://input');
    $invoiceData = json_decode($jsonData, true);

    file_put_contents('invoice_debug.log', "Received data:\n" . print_r([
        'raw_input' => $jsonData,
        'decoded_data' => $invoiceData
    ], true), FILE_APPEND);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'message' => 'خطأ في تنسيق البيانات المرسلة',
            'json_error' => json_last_error_msg()
        ]);
        exit;
    }

    $requiredFields = [
        'customerID', 'action', 'state', 'paymentMethod',
        'total', 'discount', 'totalDue', 'vat', 'generalTotal',
        'notes', 'paidAmount', 'remainingAmount', 'items'
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

    if (!is_numeric($invoiceData['customerID']) || $invoiceData['customerID'] <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'معرف العميل غير صالح'
        ]);
        exit;
    }

    if (!is_array($invoiceData['items']) || empty($invoiceData['items'])) {
        echo json_encode([
            'success' => false,
            'message' => 'قائمة العناصر فارغة أو غير صالحة'
        ]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. إدراج الفاتورة
        $sqlInvoice = "INSERT INTO invoices (
            fromID,ToID,fromType,toType, action, state, paymentMethod, 
            total, discount, totalDue, vat, generalTotal, 
            notes, paidAmount, remainingAmount
        ) VALUES (
            :userID,:customerID,'store','customer', :action, :state, :paymentMethod, 
            :total, :discount, :totalDue, :vat, :generalTotal, 
            :notes, :paidAmount, :remainingAmount
        )";
        $stmtInvoice = $pdo->prepare($sqlInvoice);
        $stmtInvoice->execute([
            ':userID' => $invoiceData['userID'],
            ':customerID' => $invoiceData['customerID'],
            ':action' => $invoiceData['action'],
            ':state' => $invoiceData['state'],
            ':paymentMethod' => $invoiceData['paymentMethod'],
            ':total' => $invoiceData['total'],
            ':discount' => $invoiceData['discount'],
            ':totalDue' => $invoiceData['totalDue'],
            ':vat' => $invoiceData['vat'],
            ':generalTotal' => $invoiceData['generalTotal'],
            ':notes' => $invoiceData['notes'],
            ':paidAmount' => $invoiceData['paidAmount'],
            ':remainingAmount' => $invoiceData['remainingAmount']
        ]);

        $invoiceID = $pdo->lastInsertId();

        // 2. تحضير استعلامات الأصناف
        $stmtItemSelect = $pdo->prepare("SELECT unitM, unitS, fl2M, fm2S, stock 
                                         FROM itemscard WHERE itemID = :itemID");

        $stmtItemInsert = $pdo->prepare("INSERT INTO itemAction 
            (itemID, action, count, price, invoiceID) 
            VALUES (:itemID, 'out', :count, :price, :invoiceID)");

        $stmtStockUpdate = $pdo->prepare("UPDATE itemscard SET stock = stock - :stock WHERE itemID = :itemID");

        // 3. معالجة العناصر
        foreach ($invoiceData['items'] as $item) {
            if (empty($item['itemID']) || !is_numeric($item['itemID'])) {
                throw new Exception("معرف الصنف غير صالح: " . print_r($item, true));
            }
            if (empty($item['price']) || !is_numeric($item['price'])) {
                throw new Exception("سعر الصنف غير صالح: " . print_r($item, true));
            }
            if (empty($item['count']) || !is_numeric($item['count'])) {
                throw new Exception("كمية الصنف غير صالحة: " . print_r($item, true));
            }

            // جلب بيانات الصنف
            $stmtItemSelect->execute([':itemID' => $item['itemID']]);
            $itemData = $stmtItemSelect->fetch(PDO::FETCH_ASSOC);
            if (!$itemData) {
                throw new Exception("الصنف غير موجود: {$item['itemID']}");
            }

            $currentStock = $itemData['stock'];
            if ($currentStock < $item['count']) {
                throw new Exception("لا يوجد مخزون كافي للصنف {$item['itemID']} (المتاح: $currentStock ، المطلوب: {$item['count']})");
            }

            // حساب الكميات
            $fL2M = $itemData['fl2M'] ?? 1;
            $fM2S = $itemData['fm2S'] ?? 1;
            $count = $item['count'];
            $price = $item['price'];

            if ($item['unit'] === ($itemData['unitM'] ?? '')) {
                $count = $count / $fL2M;
                $price = $price * $fL2M;
            } elseif ($item['unit'] === ($itemData['unitS'] ?? '')) {
                $count = ($count / $fM2S) / $fL2M;
                $price = $price * $fM2S * $fL2M;
            }

            // إدراج الحركة
            $stmtItemInsert->execute([
                ':itemID' => $item['itemID'],
                ':count' => $count,
                ':price' => $price,
                ':invoiceID' => $invoiceID
            ]);

            // تحديث المخزون
            $newStock = $currentStock - $count;
            $stmtStockUpdate->execute([
                ':stock' => $newStock,
                ':itemID' => $item['itemID']
            ]);
        }

        // 4. تحديث بيانات العميل
        $stmtSalesmanUpdate = $pdo->prepare("UPDATE customers 
            SET remainingAmount = remainingAmount + :total 
            WHERE customerID	 = :customerID");
        $stmtSalesmanUpdate->execute([
            ':total' => $invoiceData['generalTotal'],
            ':customerID' => $invoiceData['customerID']
        ]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'تم حفظ الفاتورة بنجاح',
            'invoiceID' => $invoiceID
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Invoice Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'حدث خطأ أثناء حفظ الفاتورة: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'طريقة الطلب غير مدعومة'
    ]);
}
