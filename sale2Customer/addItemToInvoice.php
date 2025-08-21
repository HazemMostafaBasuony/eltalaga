<?php
include('../hmb/conn_pdo.php');

$itemID = $_REQUEST['itemID'] ?? null;
$count = $_REQUEST['count'] ?? null;
$price = $_REQUEST['price'] ?? null;
$unit = $_REQUEST['unit'] ?? null;
$userID = $_REQUEST['userID'] ?? null;
$customerID = $_REQUEST['customerID'] ?? null;
$discount = $_POST['discount'] ?? null;

if (!$itemID || !$count || !$price || !$unit || !$userID || !$customerID ) {
    echo "ERROR|⚠️ بيانات ناقصة: تأكد من إرسال كل الحقول المطلوبة";
    exit;
}

try {
    // ايجاد بيانات الصنف
    $stmt = $pdo->prepare("SELECT * FROM itemscard WHERE itemID = :itemID LIMIT 1");
    $stmt->execute(['itemID' => $itemID]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo "ERROR|الصنف غير موجود";
        exit;
    }

    $itemName = $item['itemName'];

    // البحث عن فاتورة مفتوحة
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE state = 101 AND fromID = :userID AND toID = :customerID LIMIT 1");
    $stmt->execute([
        'userID' => $userID,
        'customerID' => $customerID
    ]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($invoice) {
        $invoiceID = $invoice['invoiceID'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO invoices (fromID, toID, fromType, toType, action, state) VALUES (:userID, :customerID, 'salesmane', 'customer', 'sale', 101)");
        $stmt->execute([
            'userID' => $userID,
            'customerID' => $customerID
        ]);
        $invoiceID = $pdo->lastInsertId();
    }

    // تحقق من وجود العنصر
    $stmt = $pdo->prepare("SELECT * FROM itemaction WHERE invoiceID = :invoiceID AND itemID = :itemID AND unit = :unit LIMIT 1");
    $stmt->execute([
        'invoiceID' => $invoiceID,
        'itemID' => $itemID,
        'unit' => $unit
    ]);
    $action = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($action) {
        $stmt = $pdo->prepare("UPDATE itemaction SET count = count + :count ,discount = discount +:discount WHERE actionID = :actionID");
        $stmt->execute([
            'count' => $count,
            'actionID' => $action['actionID'],
            'discount' => $discount,
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO itemaction (invoiceID, itemID, itemName, count, price, unit, discount, action) VALUES (:invoiceID, :itemID, :itemName, :count, :price, :unit ,:discount,'sale')");
        $stmt->execute([
            'invoiceID' => $invoiceID,
            'itemID' => $itemID,
            'itemName' => $itemName,
            'count' => $count,
            'price' => $price,
            'unit' => $unit,
            'discount' => $discount,
        ]);
    }

    // حساب الإجماليات
    $stmt = $pdo->prepare("SELECT COUNT(*) AS countInvoice, SUM(price) AS total FROM itemaction WHERE invoiceID = :invoiceID");
    $stmt->execute(['invoiceID' => $invoiceID]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // فيما بعد يتم عمل رقم بجانب ايقونة الفاتورة بالاجمالى
    echo "OK|{$invoiceID}|{$row['countInvoice']}|{$row['total']}";

} catch (Exception $e) {
    echo "ERROR|❌ خطأ في قاعدة البيانات: " . $e->getMessage();
}
