<?php
// Set proper JSON headers to prevent HTML error output
header('Content-Type: application/json; charset=utf-8');

// تضمين ملف الاتصال بقاعدة البيانات مرة واحدة فقط
include('../hmb/conn_pdo.php'); // هنا $pdo هو كائن PDO

// التأكد من أن قيمة 'area' موجودة في طلب الـ POST
if (isset($_POST['area'])) {
    $area = $_POST['area'];

    try {
        if ($area === 'الكل') {
            $sql = "SELECT * FROM `customers`";
            $stmt = $pdo->query($sql);
        } else {
            $sql = "SELECT * FROM `customers` WHERE `city` = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$area]);
        }

        // جلب النتائج كمصفوفة
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // إرسال البيانات كـ JSON
        echo json_encode($customers, JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        // في حالة حدوث خطأ في الاستعلام
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }

} else {
    // إرسال استجابة فارغة في حال عدم وجود بيانات
    echo json_encode([]);
}
?>
