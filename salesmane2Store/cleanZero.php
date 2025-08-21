<?php
include('../hmb/conn_pdo.php');

if (!isset($_REQUEST['userID'])) {
    echo json_encode(['status' => 'error', 'message' => 'معرف المستخدم غير موجود']);
    exit;
}
$userID = (int)$_REQUEST['userID'];
$tableName = 'itemscard' . $userID;

if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
    echo json_encode(['status' => 'error', 'message' => 'اسم الجدول غير صالح']);
    exit;
}

// هات IDs الأول
$sqlSelect = "SELECT itemID FROM `$tableName` WHERE stock <= 0";
$stmtSelect = $pdo->prepare($sqlSelect);
$stmtSelect->execute();
$ids = $stmtSelect->fetchAll(PDO::FETCH_COLUMN);

if (!$ids) {
    echo json_encode(['status' => 'success', 'deleted' => 0, 'ids' => []]);
    exit;
}

// احذف العناصر
$sqlDelete = "DELETE FROM `$tableName` WHERE stock <= 0";
$stmtDelete = $pdo->prepare($sqlDelete);
$stmtDelete->execute();

echo json_encode([
    'status' => 'success',
    'deleted' => $stmtDelete->rowCount(),
    'ids' => $ids
]);
