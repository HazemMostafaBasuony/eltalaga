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

// التحقق من معاملات التحويل
if (empty($item['fL2M']) || $item['fL2M'] <= 0) {
    $item['fL2M'] = 12;  // قيمة افتراضية لمعامل التحويل من الكبرى إلى المتوسطة
}
if (empty($item['fM2S']) || $item['fM2S'] <= 0) {
    $item['fM2S'] = 100;  // قيمة افتراضية لمعامل التحويل من المتوسطة إلى الصغرى
}
// التحقق من وجود اسعار للعنصر
if (empty($item['priceL'])) {
    $item['priceL'] = 0;  // قيمة افتراضية للسعر الكبرى
}
if (empty($item['priceM'])) {
    $item['priceM'] = 0;  // قيمة افتراضية للسعر المتوسط
}
if (empty($item['priceS'])) {
    $item['priceS'] = 0;  // قيمة افتراضية للسعر الصغير
}

// إرجاع البيانات بتنسيق JSON
echo json_encode([
    'success' => true,
    'item' => $item
]);

// إغلاق الاتصال
mysqli_close($conn);
?>