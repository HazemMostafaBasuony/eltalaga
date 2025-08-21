<?php
header('Content-Type: application/json; charset=utf-8');
include('../hmb/conn_pdo.php');

$ids = $_POST['ids'] ?? '';
if(empty($ids)){
    echo json_encode(['success'=>false, 'items'=>[], 'message'=>'لا توجد بيانات']);
    exit();
}

// تحويل النص إلى مصفوفة
$idArray = explode(',', $ids);
$placeholders = implode(',', array_fill(0, count($idArray), '?'));

$sql = $pdo->prepare("
    SELECT r.*, i.stock 
    FROM requests r
    LEFT JOIN itemscard i ON r.dart = i.itemID
    WHERE r.requestID IN ($placeholders)
");
$sql->execute($idArray);
$items = $sql->fetchAll(PDO::FETCH_ASSOC);



echo json_encode(['success'=>true, 'items'=>$items]);
