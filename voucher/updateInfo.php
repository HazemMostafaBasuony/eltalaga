<?php
header('Content-Type: application/json; charset=utf-8');
include('../hmb/conn_pdo.php'); // تأكد إن هذا الملف يُعدّ $pdo = new PDO(...)

if (!isset($_POST['userID'], $_POST['customerID'], $_POST['remainingAmount'])) {
    echo json_encode(['success' => false, 'message' => 'البيانات غير مكتملة']);
    exit;
}

$userID     = (int) $_POST['userID'];
$customerID = (int) $_POST['customerID'];
$remainingAmount = (floatval($_POST['remainingAmount']));

try {
    $rst=$pdo->prepare("UPDATE customers SET remainingAmount=:remainingAmount WHERE customerID=:customerID");
    $rst->execute([
        "customerID"=> $customerID,
        "remainingAmount"=> $remainingAmount
        ]);

    echo json_encode(['OK'=> true,'message'=> 'تم التحديث بنجاح']);
    exit;



} catch (Exception $e) {
    echo json_encode(['OK'=> false,'message'=> $e->getMessage()]);
    exit;
}