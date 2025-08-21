<?php
include('../hmb/conn_pdo.php');

$action = $_GET['action'] ?? '';
$requestID = (int)($_GET['requestID'] ?? 0);

if($action && $requestID){
    $status = $action === 'approve' ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE requests SET status = :status WHERE requestID = :id");
    $stmt->execute([':status'=>$status, ':id'=>$requestID]);
    echo "ok";
}
