<?php
include('../hmb/conn_pdo.php');

if (!isset($_REQUEST['itemID'])) {
    header("Location: salesmane_HomePage.php");
    die("يجب تحديد عنصر");
}

$itemID = (int)$_REQUEST['itemID'];
$salesmaneID = (int)$_REQUEST['salesmaneID'];

// هنا نحدث حالة الطلب في جدول requests
$stmt = $pdo->prepare("
    UPDATE requests 
    SET status = 'cancelled' 
    WHERE request_type = 'return_request' AND message LIKE :msg AND fromID = :fromID AND status = 'pending'
");
$stmt->execute([
    ':msg' => "%$itemID%",
    ':fromID' => $salesmaneID
]);

// لو حابب ترجع حالة العنصر في invoice_items
$stmt2 = $pdo->prepare("UPDATE invoice_items SET return_status = 0 WHERE id = :id");
$stmt2->execute([':id' => $itemID]);

// الرد للعميل
echo '<button id="requestReturnBtn' . $itemID . '" class="btn btn-success" onclick="reqrequestReturn(' . $itemID . ')">
        طلب ارجاع
      </button>';
