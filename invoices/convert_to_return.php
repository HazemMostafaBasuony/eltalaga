<?php
include('../hmb/conn.php');

// جلب معرّف الفاتورة
$invoiceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($invoiceId > 0) {
    // تحديث حالة الفاتورة إلى مرتجع (3)
    $sql = "UPDATE invoices SET state = 3 WHERE id = $invoiceId";
    
    if($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'تم التحويل بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'خطأ في تحديث الفاتورة: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'معرّف فاتورة غير صالح']);
}

$conn->close();
?>