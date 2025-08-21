<?php
include('../hmb/conn_pdo.php');

$invoiceID = $_POST['invoiceID'] ?? 0;
if(!$invoiceID) die(json_encode(['success'=>false,'message'=>'رقم الفاتورة غير صحيح']));

// تحديث حالة الفاتورة
$pdo->prepare("UPDATE invoices SET state = 3 WHERE invoiceID = ?")
    ->execute([$invoiceID]);

// تحديث الأصناف كـ delete
$pdo->prepare("UPDATE itemaction SET action = 'delete' WHERE invoiceID = ?")
    ->execute([$invoiceID]);

echo json_encode(['success'=>true]);
