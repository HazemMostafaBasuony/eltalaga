<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مسموح بالوصول']);
    exit;
}

include('hmb/conn.php');

$data = json_decode(file_get_contents('php://input'), true);

$actionType = $data['actionType'] ?? '';
$entityType = $data['entityType'] ?? '';
$entityId = intval($data['entityId'] ?? 0);
$oldValue = $data['oldValue'] ?? null;
$newValue = $data['newValue'] ?? null;
$userId = intval($data['userId'] ?? 0);
$ipAddress = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

try {
    $stmt = $conn->prepare("INSERT INTO audit_log 
        (user_id, action_type, entity_type, entity_id, old_value, new_value, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("issiisss", 
        $userId,
        $actionType,
        $entityType,
        $entityId,
        $oldValue,
        $newValue,
        $ipAddress,
        $userAgent
    );
    
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>