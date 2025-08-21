<?php
include('../hmb/conn_pdo.php'); // الاتصال بقاعدة البيانات

if (!isset($_REQUEST['itemID'])) {
    header("Location: salesmane_HomePage.php");
    die("يجب تحديد عنصر");
}

$itemID = (int)$_REQUEST['itemID'];
$salesmaneID = (int)$_REQUEST['salesmaneID'];

// هنا نحدد ID المخزن المسؤول (مثال: userID للمخزن)
$storeUserID = 2; // ممكن تجيبها ديناميكي من جدول المستخدمين أو إعداداتك

// تسجيل الطلب في جدول requests
$stmt = $pdo->prepare("
    INSERT INTO requests (fromID, toID, request_type, message, status,dart)
    VALUES (:fromID, :toID, :type, :msg, :status, :dart)
");
$stmt->execute([
    ':fromID' => $salesmaneID,
    ':toID'   => $storeUserID,
    ':type'   => 'return_request',
    ':msg'    => "طلب إرجاع البضاعة رقم #$itemID",
    ':status' => 'pending',
    ':dart'   => $itemID
]);

// // تحديث حالة العنصر في invoice_items لو حابب
// $stmt2 = $pdo->prepare("UPDATE invoice_items SET return_status = 1 WHERE id = :id");
// $stmt2->execute([':id' => $itemID]);

// الرد للعميل
echo "تم الارسال ✅ (Item: $itemID) فى انتظار الموافقة";
echo " <button class='btn btn-danger' onclick='cancelRequest($itemID)'>الغاء</button>";
