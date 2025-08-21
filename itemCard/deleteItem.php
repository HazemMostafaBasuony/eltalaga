<?php
include('../hmb/conn.php');

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف الصنف مطلوب'
    ]);
    exit;
}

$id = $_GET['id'];

// التحقق من وجود الصنف
$checkSql = "SELECT * FROM itemsCard WHERE itemID = '$id'";
$checkResult = $conn->query($checkSql);

if ($checkResult->num_rows == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'الصنف غير موجود'
    ]);
    exit;
}

$sql = "DELETE FROM itemsCard WHERE itemID = '$id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        'success' => true,
        'message' => 'تم حذف الصنف بنجاح'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في حذف الصنف: ' . $conn->error
    ]);
}

$conn->close();
?>