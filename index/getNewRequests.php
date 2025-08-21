<?php
session_start();
include('../hmb/conn_pdo.php');

$currentUserID = $_SESSION['userId'] ?? 0;

// جلب كل الطلبات المعلقة
$stmt = $pdo->prepare("SELECT r.requestID, r.message, r.fromID, u.userName as fromName , r.request_type, r.dart
                       FROM requests r 
                       JOIN users u ON r.fromID = u.id
                       WHERE r.toID = :user AND r.status = 'pending'
                       ORDER BY r.created_at DESC");
$stmt->execute([':user' => $currentUserID]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// عدد الطلبات الجديدة
$newRequests = count($requests);

header('Content-Type: application/json');
echo json_encode([
    'newRequests' => $newRequests,
    'requests' => $requests
]);
exit;
?>