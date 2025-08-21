<?php
header('Content-Type: application/json; charset=utf-8');
include('../hmb/conn_pdo.php');

session_start();
if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'يرجى تسجيل الدخول']);
    exit();
}

$userID = $_SESSION['userId'];
$message = $_POST['message'] ?? '';

// 1- جلب الطلبيات الحالية
$sql = $pdo->prepare("SELECT * FROM `requests` 
                      WHERE `fromID` = :userID 
                        AND `request_type` = 'wantMaterial' 
                        AND `status` = 'preparing' 
                      ORDER BY `created_at` DESC");
$sql->execute(['userID' => $userID]);
$request = $sql->fetchAll(PDO::FETCH_ASSOC);

// 2- تحويل الطلبات الحالية إلى slave
$requestIDs = "";
foreach ($request as $row) {
    $requestID = $row['requestID'];
    $update = $pdo->prepare("UPDATE `requests` 
                             SET `status` = 'slave' 
                             WHERE `requestID` = :requestID");
    $update->execute(['requestID' => $requestID]);
    $requestIDs .= $requestID . ",";
}

// إزالة الفاصلة الأخيرة
$requestIDs = rtrim($requestIDs, ",");

// 3- إضافة الرسالة كطلب جديد
$sql = "INSERT INTO `requests`
        (`fromID`, `toID`, `request_type`, `message`, `status`, `created_at`, `updated_at`, `dart`)
        VALUES (:userID, 2, 'wantMaterial', :message, 'pending', NOW(), NOW(), :requestIDs)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'userID' => $userID,
    'message' => $message,
    'requestIDs' => $requestIDs
]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'تم ارسال الرسالة بنجاح']);
} else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء الإرسال']);
}
