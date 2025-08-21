<?php
header('Content-Type: application/json; charset=utf-8');
require_once('../hmb/conn_pdo.php');
session_start(); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مدعومة']);
    exit;
}

$serialShift = $_SESSION['serialShift'] ?? null;
$userID = $_POST['userID'] ?? null;
$lat = $_POST['lat'] ?? null;
$lng = $_POST['lng'] ?? null;

if (!$serialShift || !$userID || !is_numeric($lat) || !is_numeric($lng)) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير كاملة أو غير صحيحة']);
    exit;
}

$tableName = "reportSalesman" . intval($userID);

try {
    // إنشاء الجدول إذا لم يكن موجود
    $sqlCreate = "CREATE TABLE IF NOT EXISTS `$tableName` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serialShift INT DEFAULT NULL,
        shiftStart DATETIME,
        shiftEnd DATETIME,
        action VARCHAR(255),
        invoiceID INT DEFAULT NULL,
        customerID INT DEFAULT NULL,
        amount DECIMAL(10,2) DEFAULT 0,
        lat DECIMAL(10,8) DEFAULT 0,
        lng DECIMAL(11,8) DEFAULT 0,
        notes TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sqlCreate);

    // تسجيل بداية الوردية
    $stmt = $pdo->prepare("INSERT INTO `$tableName` 
        (serialShift, shiftStart, action, lat, lng, notes) 
        VALUES (:serialShift, NOW(), 'start_shift', :lat, :lng, 'تسجيل دخول')");
    $stmt->execute([
        ':serialShift' => $serialShift,
        ':lat' => $lat,
        ':lng' => $lng
    ]);

    $shiftID = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'تم تسجيل بداية الوردية بنجاح',
        'shiftID' => $shiftID
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ أثناء تسجيل الوردية: ' . $e->getMessage()
    ]);
}
