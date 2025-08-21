<?php
include('../hmb/conn_pdo.php');

$actionID = $_POST['actionID'] ?? 0;
$newPrice = $_POST['price'] ?? 0;

if(!$actionID || !$newPrice){
    die(json_encode(['success'=>false,'message'=>'بيانات غير صحيحة']));
}

$pdo->prepare("UPDATE itemaction SET price = ?, action='edit' WHERE actionID = ?")
    ->execute([$newPrice, $actionID]);

echo json_encode(['success'=>true]);
