<?php
header('Content-Type: application/json; charset=utf-8');
include('../hmb/conn_pdo.php'); // تأكد إن هذا الملف يُعدّ $pdo = new PDO(...)

if (!isset($_POST['userID'], $_POST['customerID'], $_POST['invoiceID'])) {
    echo json_encode(['success' => false, 'message' => 'البيانات غير مكتملة']);
    exit;
}

$userID     = (int) $_POST['userID'];
$customerID = (int) $_POST['customerID'];
$invoiceID  = (int) $_POST['invoiceID'];

try {
    // 1) جلب الفاتورة أولًا (نحتاج fromType/fromID وحقول الفاتورة)
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE invoiceID = ?");
    $stmt->execute([$invoiceID]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        echo json_encode(['success' => false, 'message' => 'لا توجد فاتورة مفتوحه لهذا العميل ']);
        exit;
    }

    // 2) بناء بيانات الفرع/المصدر استنادًا إلى fromType/fromID
    $branch = null;
    if (!empty($invoice['fromType']) && !empty($invoice['fromID'])) {
        $fromType = $invoice['fromType'];
        $fromID = (int)$invoice['fromID'];

        if (in_array(strtolower($fromType), ['store', 'branch'])) {
            // جدولك اسمه branchs
            
            $stmt->execute([$fromID]);
            $branch = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (strtolower($fromType) === 'salesmane') {
            // لو المصدر مندوب مبيعات، نجيب بياناته من جدول salesmane
            $stmt = $pdo->prepare("SELECT branchID FROM salesmane WHERE salesmaneID = ?");
            $stmt->execute([$fromID]);
            $sm = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sm) {
                $stmt = $pdo->prepare("
                SELECT branchID, branchName, street, area, city, country, bulding, postCode, phone, numberRC, numberTax
                FROM branchs
                WHERE branchID = ?
            ");
                $stmt->execute([$sm['branchID']]);
                $branch = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }

    // إذا مافيش فرع وعايز fallback: اختياري — نقدر نجيب أول فرع
    if (!$branch) {
        $stmt = $pdo->query("SELECT branchID, branchName, street, area, city, phone, numberRC, numberTax FROM branchs LIMIT 1");
        $branch = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // 3) جلب بيانات العميل بناءًا على customerID
    $stmt = $pdo->prepare("
        SELECT customerID, customerName, phone, street, area, city, bulding, postCode, email, remainingAmount
        FROM customers
        WHERE customerID = ?
    ");
    $stmt->execute([$customerID]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'العميل غير موجود']);
        exit;
    }

    // تركيب عنوان العميل من الحقول المتاحة
    $parts = [];
    foreach (['street','area','city','bulding','postCode'] as $f) {
        if (!empty($customer[$f])) $parts[] = $customer[$f];
    }
    $customer['address'] = implode(' - ', $parts);

    // 4) جلب الأصناف من itemaction — استخدم itemName الموجود هناك (لتفادي اختلاف أسماء الوحدات في itemscard)
    $stmt = $pdo->prepare("
        SELECT ia.actionID, ia.itemID, ia.itemName, ia.count, ia.unit, ia.price, ia.discount,
               (ia.count * ia.price) AS totalPrice
        FROM itemaction ia
        WHERE ia.invoiceID = ?
    ");
    $stmt->execute([$invoiceID]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5) تأكد من وجود حقول الفاتورة الأساسية (fallback إذا كانت null)
    $invoice_out = [
        'invoiceID' => $invoice['invoiceID'],
        'total' => isset($invoice['total']) ? (float)$invoice['total'] : 0,
        'discount' => isset($invoice['discount']) ? (float)$invoice['discount'] : 0,
        'paidAmount' => isset($invoice['paidAmount']) ? (float)$invoice['paidAmount'] : 0,
        'vat' => isset($invoice['vat']) ? (float)$invoice['vat'] : 0,
        'generalTotal' => isset($invoice['generalTotal']) ? (float)$invoice['generalTotal'] : 0,
        'remainingAmount' => isset($customer['remainingAmount']) ? (float)$customer['remainingAmount']:0 ,
        // ملاحظة: اسم العمود للتاريخ في الـ dump هو `date`
        'date' => isset($invoice['date']) ? $invoice['date'] : null,
        'fromType' => $invoice['fromType'] ?? null,
        'fromID' => $invoice['fromID'] ?? null
    ];

    // 6) رد JSON مرتب
    echo json_encode([
        'success'  => true,
        'customer' => $customer,
        'branch'   => $branch,
        'invoice'  => $invoice_out,
        'items'    => $items
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
