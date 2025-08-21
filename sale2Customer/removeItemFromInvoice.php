<?php
header('Content-Type: application/json; charset=utf-8');
include('../hmb/conn_pdo.php');

if (isset($_REQUEST['actionID'])) {
    try {
        $actionID = $_REQUEST['actionID'];
        $stmt = $pdo->prepare("DELETE FROM itemaction WHERE actionID = :actionID");
        $stmt->bindParam(':actionID', $actionID, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'تم حذف الصنف من الفاتورة بنجاح.'
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطأ أثناء حذف الصنف: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'لم يتم تمرير رقم الحركة (actionID).'
    ], JSON_UNESCAPED_UNICODE);
}
