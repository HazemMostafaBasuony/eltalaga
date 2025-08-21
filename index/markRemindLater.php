<?php
include('../hmb/conn_pdo.php');
session_start();
$requestID = $_POST['requestID'] ?? null;
if($requestID){
    $stmt = $pdo->prepare("UPDATE requests SET status='remind_later' WHERE requestID=:requestID");
    $stmt->execute(['requestID'=>$requestID]);
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false]);
}
