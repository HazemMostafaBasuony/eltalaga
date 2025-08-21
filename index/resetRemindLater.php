<?php
include('../hmb/conn_pdo.php');

// اختيار كل الطلبات remind_later
$stmt = $pdo->prepare("UPDATE requests SET status='pending' WHERE status='remind_later'");
$stmt->execute();

echo json_encode(['success'=>true, 'updated'=>$stmt->rowCount()]);
