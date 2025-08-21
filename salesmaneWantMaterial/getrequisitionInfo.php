<?php
header('Content-Type: application/json; charset=utf-8');

// Error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to the user
ini_set('log_errors', 1);

include('../hmb/conn_pdo.php');


session_start();
if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'يرجى تسجيل الدخول']);
    exit();
}
$userID = $_SESSION['userId'];


// ايجاد الرسائل المرسله
// INSERT INTO `requests`(`requestID`, `fromID`, `toID`, `request_type`, `message`, `status`, `created_at`, `updated_at`, `dart`)
$sql =$pdo->prepare("SELECT * FROM `requests` WHERE `fromID` = :userID AND `request_type` = 'wantMaterial' AND `status` = 'preparing' ORDER BY `created_at` DESC");
// exucute the query
$sql->execute(['userID' => $userID]);
// fetch the results
$result = $sql->fetchAll(PDO::FETCH_ASSOC);
// echo the results
echo json_encode(
    [
        'success' => true,
        'requests' => $result,
        'countREQ' => count($result)
    ]
);
exit;