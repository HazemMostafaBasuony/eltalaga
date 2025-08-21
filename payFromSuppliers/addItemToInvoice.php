<?php
// Set headers first to ensure no output before them
header('Content-Type: application/json; charset=utf-8');

// Error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to the user
ini_set('log_errors', 1);

// Function to send JSON error response
function sendJsonError($message, $code = 500, $details = null) {
    http_response_code($code);
    $response = [
        'success' => false,
        'message' => $message
    ];
    if ($details !== null) {
        $response['details'] = $details;
    }
    echo json_encode($response);
    exit;
}

// Start output buffering
ob_start();

try {
    // Include database connection with suppressed HTML errors
    $suppress_errors = true;
    include('../hmb/conn.php');
    
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception('فشل الاتصال بقاعدة البيانات');
    }
} catch (Exception $e) {
    ob_end_clean();
    sendJsonError($e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // استقبال البيانات كـ JSON
    $jsonData = file_get_contents('php://input');
    $invoiceData = json_decode($jsonData, true);

    // التحقق من صحة JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'message' => 'خطأ في تنسيق البيانات المرسلة',
            'json_error' => json_last_error_msg()
        ]);
        exit;
    }

    // التحقق من البيانات الأساسية مع رسائل أكثر وصفية
    $requiredFields = [
        'fromID' => 'معرف المورد',
        'items' => 'الأصناف',
        'total' => 'الإجمالي',
        'discount' => 'الخصم',
        'totalDue' => 'المبلغ المستحق',
        'vat' => 'ضريبة القيمة المضافة',
        'generalTotal' => 'الإجمالي العام'
    ];

    foreach ($requiredFields as $field => $fieldName) {
        if (!isset($invoiceData[$field])) {
            echo json_encode(['success' => false, 'message' => 'بيانات ناقصة: ' . $fieldName]);
            exit;
        }
        
        // تحقق إضافي إذا كان الحقل مصفوفة
        if (is_array($invoiceData[$field])) {
            if (empty($invoiceData[$field])) {
                echo json_encode(['success' => false, 'message' => 'بيانات ناقصة: ' . $fieldName]);
                exit;
            }
            
            // تحقق خاص لمصفوفة الأصناف
            if ($field === 'items') {
                foreach ($invoiceData['items'] as $item) {
                    if (empty($item['itemID'])) {
                        echo json_encode(['success' => false, 'message' => 'يوجد صنف بدون معرف']);
                        exit;
                    }
                }
            }
        }
    }

    try {
        // بدء المعاملة
        mysqli_begin_transaction($conn);

        // 1. إعداد بيانات الفاتورة
        $invoiceValues = [
            'fromID' => (int) $invoiceData['fromID'],
            'toID' => isset($invoiceData['toID']) ? (int) $invoiceData['toID'] : null,
            'fromType' => $invoiceData['fromType'] ?? 'supplier',
            'toType' => $invoiceData['toType'] ?? 'branch',
            'action' => $invoiceData['action'] ?? 'purchase',
            'state' => $invoiceData['state'] ?? 1,
            'date' => $invoiceData['date'] ?? date('Y-m-d H:i:s'),
            'paymentMethod' => $invoiceData['paymentMethod'] ?? 'cash',
            'total' => (float) $invoiceData['total'],
            'discount' => (float) $invoiceData['discount'],
            'totalDue' => (float) $invoiceData['totalDue'],
            'vat' => (float) $invoiceData['vat'],
            'generalTotal' => (float) $invoiceData['generalTotal'],
            'notes' => $invoiceData['notes'] ?? 'فاتورة شراء من المورد',
            'paidAmount' => (float) ($invoiceData['paidAmount'] ?? 0),
            'paidDate' => $invoiceData['paidDate'] ?? null,
            'remainingAmount' => (float) ($invoiceData['remainingAmount'] ?? ($invoiceData['generalTotal'] - ($invoiceData['paidAmount'] ?? 0))),
            'branchID' => $invoiceData['branchID'] ?? null,
            'wantDebt' => (float) ($invoiceData['wantDebt'] ?? 0),
            'dateWantedDebt' => $invoiceData['dateRemainingAmount'] ?? null
        ];

        // معالجة ملف الفاتورة الأصلية إذا تم رفعه
        if (isset($_FILES['originalInvoice'])) {
            $originalInvoice = $_FILES['originalInvoice'];
            if ($originalInvoice['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/invoices/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExt = pathinfo($originalInvoice['name'], PATHINFO_EXTENSION);
                $fileName = 'invoice_' . time() . '_' . $invoiceValues['fromID'] . '.' . $fileExt;
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($originalInvoice['tmp_name'], $filePath)) {
                    $invoiceValues['originalInvoicePath'] = $fileName;   
                } else {
                    throw new Exception('فشل في حفظ ملف الفاتورة الأصلية');
                }
            }
        }


        // INSERT INTO `invoices`(`fromID`, `toID`, `fromType`, `toType`,
        // `action`, `state`, `date`, `paymentMethod`, `total`,
        // `discount`, `totalDue`, `vat`, `generalTotal`, `notes`,
        // `paidAmount`, `paidDate`, `remainingAmount`, `dateRemainingAmount`, `originalInvoicePath`)
        // إضافة الفاتورة
        
        
        $sql = "INSERT INTO invoices (
            fromID, toID, fromType, toType, action,
            state, date, paymentMethod, total, discount, 
            totalDue, vat, generalTotal, notes, paidAmount,
            paidDate, remainingAmount,originalInvoicePath
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
        
        $stmtInvoice = $conn->prepare($sql);
        
        if ($stmtInvoice === false) {
            throw new Exception('فشل تحضير استعلام الفاتورة: INSERT invoices ' . $conn->error);
        }

        // Log the values being bound for debugging
        error_log('Binding values to invoice statement:');
        error_log('fromID: ' . $invoiceValues['fromID']);
        error_log('toID: ' . ($invoiceValues['toID'] ?? 'NULL'));
        error_log('fromType: ' . $invoiceValues['fromType']);
        error_log('toType: ' . $invoiceValues['toType']);
        error_log('action: ' . $invoiceValues['action']);
        
        $bindResult = $stmtInvoice->bind_param(
            "iisssissdddddsdsds",
            $invoiceValues['fromID'],//i
            $invoiceValues['toID'],//i
            $invoiceValues['fromType'],//s
            $invoiceValues['toType'],//s
            $invoiceValues['action'],//s
            $invoiceValues['state'],//i
            $invoiceValues['date'],//s
            $invoiceValues['paymentMethod'],//s
            $invoiceValues['total'],//d
            $invoiceValues['discount'],//d
            $invoiceValues['totalDue'],//d
            $invoiceValues['vat'],//d
            $invoiceValues['generalTotal'],//d
            $invoiceValues['notes'],//s
            $invoiceValues['paidAmount'],//d
            $invoiceValues['paidDate'],//s
            $invoiceValues['remainingAmount'],//d
            $invoiceValues['originalInvoicePath']//s
        );

        if (!$stmtInvoice->execute()) {
            throw new Exception('فشل إضافة الفاتورة: ' . $stmtInvoice->error);
        }

        $invoiceID = $conn->insert_id;

        // 2. معالجة الأصناف
        foreach ($invoiceData['items'] as $item) {
            // تحقق من البيانات الأساسية للصنف
            if (empty($item['itemID'])) {
                throw new Exception('يوجد صنف بدون معرف');
            }

            $itemValues = [
                'itemID' => (int) $item['itemID'],
                'date' => $invoiceValues['date'],
                'action' => $invoiceValues['action'],
                'count' => (float) ($item['count'] ?? 0),
                'price' => (float) ($item['price'] ?? 0),
                'discount' => (float) ($item['discount'] ?? 0),
                'invoiceID' => $invoiceID, 
                'unit' => $item['unit'] ?? "L",
                'itemName' => $item['itemName'] ?? ""
            ];

            // إضافة حركة الصنف
            $stmtItem = $conn->prepare("INSERT INTO itemaction (
                itemID, date, action, count, price, discount, invoiceID, unit, itemName
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmtItem) {
                throw new Exception('تحضير استعلام حركة الصنف فشل: ' . $conn->error);
            }
            
            $stmtItem->bind_param(
                "issdddiss",
                $itemValues['itemID'],//i
                $itemValues['date'],//s
                $itemValues['action'],//s
                $itemValues['count'],//d
                $itemValues['price'],//d
                $itemValues['discount'],//d
                $itemValues['invoiceID'],//i
                $itemValues['unit'],//s
                $itemValues['itemName']//s
            );

            if (!$stmtItem->execute()) {
                throw new Exception('فشل إضافة حركة الصنف: ' . $stmtItem->error);
            }

            // تحديث المخزون مع التحقق من وجود الصنف
            $sqlCheckItem = "SELECT itemID FROM itemscard WHERE itemID = ?";
            $stmtCheckItem = $conn->prepare($sqlCheckItem);
            if ($stmtCheckItem === false) {
                throw new Exception('فشل تحضير استعلام التحقق من الصنف: itemscard' . $conn->error);
            }
            
            $bindResult = $stmtCheckItem->bind_param("i", $itemValues['itemID']);
            if ($bindResult === false) {
                throw new Exception('فشل ربط معاملات استعلام التحقق من الصنف: itemscard2' . $stmtCheckItem->error);
            }
            
            if (!$stmtCheckItem->execute()) {
                throw new Exception('فشل تنفيذ استعلام التحقق من الصنف: ' . $stmtCheckItem->error);
            }

            if ($stmtCheckItem->get_result()->num_rows === 0) {
                throw new Exception('الصنف غير موجود في المخزون: ' . $itemValues['itemID']);
            }

            $sqlUpdateStock = "UPDATE itemscard SET stock = stock + ? WHERE itemID = ?";
            $stmtUpdateStock = $conn->prepare($sqlUpdateStock);
            if ($stmtUpdateStock === false) {
                throw new Exception('فشل تحضير استعلام تحديث المخزون:UPDATE itemscard3' . $conn->error);
            }
            
            $bindResult = $stmtUpdateStock->bind_param("di", $itemValues['count'], $itemValues['itemID']);
            if ($bindResult === false) {
                throw new Exception('فشل ربط معاملات تحديث المخزون:UPDATE itemscard4' . $stmtUpdateStock->error);
            }

            if (!$stmtUpdateStock->execute()) {
                throw new Exception('فشل تحديث المخزون: ' . $stmtUpdateStock->error);
            }
            
            // هنا يمكن تقسيم المخزون إلى الفروع إذا لزم الأمر
            // [يمكنك إضافة الكود الخاص بتوزيع المخزون هنا]
        }

        // 3. تحديث المورد مع التحقق من وجوده
        $sqlCheckSupplier = "SELECT supplierID FROM suppliers WHERE supplierID = ?";
        $stmtCheckSupplier = $conn->prepare($sqlCheckSupplier);
        if ($stmtCheckSupplier === false) {
            throw new Exception('فشل تحضير استعلام التحقق من المورد: supplierID' . $conn->error);
        }
        
        $bindResult = $stmtCheckSupplier->bind_param("i", $invoiceValues['fromID']);
        if ($bindResult === false) {
            throw new Exception('فشل ربط معاملات استعلام التحقق من المورد: ' . $stmtCheckSupplier->error);
        }
        
        if (!$stmtCheckSupplier->execute()) {
            throw new Exception('فشل تنفيذ استعلام التحقق من المورد: ' . $stmtCheckSupplier->error);
        }

        if ($stmtCheckSupplier->get_result()->num_rows === 0) {
            throw new Exception('المورد غير موجود: ' . $invoiceValues['fromID']);
        }

        $sqlUpdateSupplier = "UPDATE suppliers SET 
            lastInvoiceDate = ?,
            totalDebt = totalDebt + ?,
            wantDebt = ?,
            dateWantedDebt = ?
            WHERE supplierID = ?";

        $stmtSupplier = $conn->prepare($sqlUpdateSupplier);
        if ($stmtSupplier === false) {
            throw new Exception('فشل تحضير استعلام تحديث المورد: UPDATE suppliers ' . $conn->error);
        }

        $bindResult = $stmtSupplier->bind_param(
            "sddsi",
            $invoiceValues['date'],//s
            $invoiceValues['generalTotal'],//d
            $invoiceValues['wantDebt'],//d
            $invoiceValues['dateWantedDebt'],//s
            $invoiceValues['fromID']//i
        );
        
        if ($bindResult === false) {
            throw new Exception('فشل ربط معاملات تحديث المورد: UPDATE suppliers2 ' . $stmtSupplier->error);
        }

        if (!$stmtSupplier->execute()) {
            throw new Exception('فشل تحديث بيانات المورد: UPDATE suppliers3 ' . $stmtSupplier->error);
        }

        // إتمام المعاملة
        mysqli_commit($conn);

        // إرجاع الاستجابة الناجحة
        $output = ob_get_clean();
        if (!empty($output)) {
            error_log('إخراج غير متوقع: ' . $output);
        }

        echo json_encode([
            'success' => true,
            'message' => 'تم حفظ الفاتورة بنجاح',
            'invoiceID' => $invoiceID,
            'invoiceNumber' => $invoiceID, // تم التصحيح هنا لاستخدام invoiceID بدلاً من itemValues
            'originalInvoicePath' => $invoiceValues['originalInvoicePath'] ?? "default.gif"
        ]);

    } catch (Exception $e) {
        // التراجع عن المعاملة في حالة الخطأ
        if ($conn) {
            mysqli_rollback($conn);
        }

        $errorOutput = ob_get_clean();
        error_log('Invoice Error: ' . $e->getMessage() . "\nOutput: " . $errorOutput);

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'حدث خطأ: ' . $e->getMessage(),
            'errorDetails' => $conn ? $conn->error : 'No DB connection',
            'debugOutput' => $errorOutput
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'message' => 'طريقة الطلب غير صحيحة'
    ]);
}