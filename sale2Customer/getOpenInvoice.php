<?php
header('Content-Type: application/json; charset=utf-8');
include('../hmb/conn_pdo.php');

if (!isset($_POST['salesmaneID'], $_POST['customerID'])) {
    echo json_encode(['success' => false, 'message' => 'البيانات ناقصة']);
    exit;
}

$salesmaneID = (int) $_POST['salesmaneID'];
$customerID  = (int) $_POST['customerID'];

try {
    $stmt = $pdo->prepare("
        SELECT invoiceID
        FROM invoices
        WHERE fromID = ? 
          AND toID = ? 
          AND state = 101
        LIMIT 1
    ");
    $stmt->execute([$salesmaneID, $customerID]);
    $invoiceID = $stmt->fetchColumn();

    if ($invoiceID) {
        echo json_encode(['success' => true, 'invoiceID' => $invoiceID]);
    } else {
        echo json_encode(['success' => false, 'message' => 'لا توجد فاتورة مفتوحة']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
