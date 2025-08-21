<?php
$idShift = isset($_REQUEST['idShift']) ? intval($_REQUEST['idShift']) : 0;
$endCash = isset($_REQUEST['endCash']) ? doubleval($_REQUEST['endCash']) : 0;
include('../hmb/conn.php');

if ($idShift > 0) {
    $stmt = $conn->prepare("UPDATE `serialshift` SET `state`=2, `endCash`=? WHERE `id`=?");
    $stmt->bind_param("di", $endCash, $idShift);
    if ($stmt->execute()) {
        echo "تم إغلاق الدوام بنجاح.";
        // يمكنك إعادة التوجيه مثلاً:
        // header("Location: ../report.php?msg=closed");
        // exit;
    } else {
        echo "حدث خطأ أثناء إغلاق الدوام: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "معرف الدوام غير صحيح.";
}
?>