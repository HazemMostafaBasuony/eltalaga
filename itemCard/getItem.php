<?php
include('../hmb/conn.php');

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف الصنف مطلوب']);
    exit;
}

$id = $_GET['id'];

$sql = "SELECT * FROM itemsCard WHERE itemID = '$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'data' => $row
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'الصنف غير موجود'
    ]);
}

$conn->close();
?>