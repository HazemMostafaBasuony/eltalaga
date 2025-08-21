<?php
header('Content-Type: application/json; charset=utf-8');
include('../hmb/conn_pdo.php');

session_start();
if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'يرجى تسجيل الدخول']);
    exit();
}

if (!isset($_POST['requestID'])) {
    echo json_encode(['success' => false, 'message' => 'معرف الطلبية غير موجود']);
    exit();
}

$requestID = intval($_POST['requestID']);
$userID = $_SESSION['userId'];

// حذف السطر مع التحقق من أنه يخص نفس المستخدم
$stmt = $pdo->prepare("DELETE FROM requests WHERE requestID = :requestID AND fromID = :userID AND status = 'preparing'");
$stmt->execute(['requestID' => $requestID, 'userID' => $userID]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'تم حذف الصنف من الطلبية']);
} else {
    echo json_encode(['success' => false, 'message' => 'تعذر حذف الصنف أو غير موجود']);
}
exit;
