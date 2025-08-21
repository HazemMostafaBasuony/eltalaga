<?php

header('Content-Type: application/json; charset=utf-8');
error_reporting(0); // For production, log errors instead of suppressing them


// التأكد من وجود معرف العنصر
if (!isset($_GET['itemID']) || empty($_GET['itemID'])) {
    // إرجاع رسالة خطأ إذا لم يتم تحديد معرف العنصر
    echo json_encode([
        'success' => false,
        'message' => 'لم يتم تحديد معرف العنصر'. mysqli_connect_error()
    ]);
    exit;
}

// الحصول على معرف العنصر
$itemID = intval($_GET['itemID']);

// الاتصال بقاعدة البيانات
include('../hmb/conn.php');

// التحقق من الاتصال
if (!$conn) { 
    echo json_encode([
        'success' => false,
        'message' => 'فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error()
    ]);
    exit;
}

// استعلام للحصول على معلومات العنصر وتفاصيل الوحدات
$sql = "SELECT 
            i.itemID,
            i.itemName,
            i.unitL,
            i.unitM,
            i.unitS,
            i.fL2M,
            i.fM2S,
            i.mainGroup,
            i.subGroup,
            i.stock,
            i.profit,
            i.priceL,
            i.priceM,
            i.priceS
        FROM 
            itemsCard i
        WHERE 
            i.itemID = $itemID";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في الاستعلام: ' . mysqli_error($conn)
    ]);
    exit;
}

// التحقق من وجود العنصر
if (mysqli_num_rows($result) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'العنصر غير موجود'. mysqli_connect_error() 
    ]);
    exit;
}

// الحصول على بيانات العنصر  
$item = mysqli_fetch_assoc($result);

// التحقق من وجود وحدات للعنصر
if (empty($item['unitL'])) {
    $item['unitL'] = 'كرتون';  // قيمة افتراضية للوحدة الكبرى
}
if (empty($item['unitM'])) {
    $item['unitM'] = 'كيس';    // قيمة افتراضية للوحدة المتوسطة
}
if (empty($item['unitS'])) {
    $item['unitS'] = 'قطعة';    // قيمة افتراضية للوحدة الصغرى
}

// إرجاع البيانات بتنسيق JSON
echo json_encode([
    'success' => true,
    'item' => $item
]);

// إغلاق الاتصال
mysqli_close($conn);
?>