<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../hmb/conn_pdo.php';

// تحقق من البراميتر والجلسة
$userID = isset($_GET['userID']) ? (int)$_GET['userID'] : 0;
if ($userID <= 0) {
    echo json_encode(['requests' => [], 'error' => 'missing_userID']);
    exit;
}

if (!isset($_SESSION['serialShift'])) {
    echo json_encode(['requests' => [], 'error' => 'missing_serialShift']);
    exit;
}
$serialShift = (int)$_SESSION['serialShift'];

// أسماء الجداول الديناميكية (مع تحصين)
$itemscardUser = 'itemscard' . $userID;
$reportTable   = 'reportsalesman' . $userID;

if (!preg_match('/^[a-zA-Z0-9_]+$/', $itemscardUser) || !preg_match('/^[a-zA-Z0-9_]+$/', $reportTable)) {
    echo json_encode(['requests' => [], 'error' => 'invalid_table_name']);
    exit;
}

try {
    // هات الطلبات الموافق عليها للمندوب
    $stmt = $pdo->prepare("
        SELECT requestID, dart AS itemID
        FROM requests
        WHERE fromID = :userID
          AND request_type = 'return_request'
          AND status = 'approved'
        ORDER BY created_at ASC
    ");
    $stmt->execute([':userID' => $userID]);
    $approved = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$approved) {
        echo json_encode(['requests' => []]); // لا جديد
        exit;
    }

    $pdo->beginTransaction();

    $processed = [];

    foreach ($approved as $row) {
        $requestID = (int)$row['requestID'];
        $itemID    = (int)$row['itemID'];

        // هات بيانات الصنف
        $sel = $pdo->prepare("SELECT * FROM `$itemscardUser` WHERE itemID = :id LIMIT 1");
        $sel->execute([':id' => $itemID]);
        $item = $sel->fetch(PDO::FETCH_ASSOC);

        // لو الصنف مش موجود خلاص علّم الطلب كمُعالَج عشان ما يفضلش يرجع كل مرة
        if (!$item) {
            $mark = $pdo->prepare("UPDATE requests SET status = 'done', updated_at = NOW() WHERE requestID = :rid");
            $mark->execute([':rid' => $requestID]);
            continue;
        }

        $amount = (float)($item['stock'] * ($item['priceL'] ?? 0));
        $lat    = isset($item['lat']) ? (float)$item['lat'] : 0;
        $lng    = isset($item['lng']) ? (float)$item['lng'] : 0;
        $itemName = $item['itemName'] ?? ('Item#' . $itemID);

        // احذف الصنف من مخزون المندوب
        $del = $pdo->prepare("DELETE FROM `$itemscardUser` WHERE itemID = :id");
        $del->execute([':id' => $itemID]);

        // سجّل في تقرير المندوب
        $log = $pdo->prepare("
            INSERT INTO `$reportTable`
                (`serialShift`, `action`, `amount`, `lat`, `lng`, `notes`)
            VALUES
                (:serialShift, 'returnMaterial', :amount, :lat, :lng, :notes)
        ");
        $log->execute([
            ':serialShift' => $serialShift,
            ':amount'      => $amount,
            ':lat'         => $lat,
            ':lng'         => $lng,
            ':notes'       => 'ارجاع بضاعة صنف ' . $itemName
        ]);

        // علّم الطلب كمُعالَج عشان ما يتكرر
        $done = $pdo->prepare("UPDATE requests SET status = 'done', updated_at = NOW() WHERE requestID = :rid");
        $done->execute([':rid' => $requestID]);

        // رجّع للفرونت إ IDs اللي اتعالجت
        $processed[] = ['itemID' => $itemID, 'status' => 'approved'];
    }

    $pdo->commit();

    echo json_encode(['requests' => $processed]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    // مهم: لا تطبع رسائل خام تفسد JSON
    echo json_encode(['requests' => [], 'error' => 'server_error']);
}
