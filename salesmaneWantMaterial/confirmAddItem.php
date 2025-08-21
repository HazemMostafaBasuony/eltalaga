<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_REQUEST['itemID']) || !isset($_REQUEST['unit']) || !isset($_REQUEST['countItem'])) {
    echo json_encode(['success' => false, 'message' => 'الرجاء اختيار الصنف.']);
    exit();
}
session_start();
if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'يرجى تسجيل الدخول']);
    exit();
}
$userID = $_SESSION['userId'];
$itemID = $_REQUEST['itemID'];
$unit   = $_REQUEST['unit'];
$count  = $_REQUEST['countItem'];

include('../hmb/conn_pdo.php');
// تحقق من وجود الصنف في قاعدة البيانات
$stmt = $pdo->prepare("SELECT * FROM `itemscard` WHERE `itemID` = :itemID");
$stmt->execute(['itemID' => $itemID]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'الصنف غير موجود']);
    exit();
}
// اضافة الصنف الى الطلبيه

// message
$message = $count . '  -  ' . $unit . ' - ' . $item['itemName'];

    // // INSERT INTO `requests`(`requestID`, `fromID`, `toID`, `request_type`, `message`, `status`, `created_at`, `updated_at`, `dart`)

    $stmt = $pdo->prepare("INSERT INTO `requests`(`fromID`, `toID`, `request_type`, `message`, `status`, `dart`,created_at)
                                                 VALUES (:userID, 2, 'wantMaterial', :message, 'preparing', :itemID,NOW())");
    $stmt->execute(['userID' => $userID, 'itemID' => $itemID, 'message' => $message]);

if ($stmt->rowCount() > 0) {
    echo json_encode([
        'success' => true,
         'message' => 'تم اضافة الصنف بنجاح']);
         exit();
} else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ']);
    exit();
}