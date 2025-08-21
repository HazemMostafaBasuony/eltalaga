<?php
include('../hmb/conn.php');

// إدراج بيانات تجريبية
$sampleData = [
    ['أرز بسمتي', 'كيس', 25, 'كيلو', 1, 'كيلو', 'أرز', 'حبوب', 100, 5],
    ['سكر أبيض', 'كيس', 50, 'كيلو', 1, 'كيلو', 'سكر', 'تحلية', 200, 3],
    ['شاي أحمر', 'كرتون', 12, 'علبة', 1, 'علبة', 'شاي', 'مشروبات', 50, 10],
    ['قهوة عربية', 'كرتون', 24, 'علبة', 1, 'علبة', 'قهوة', 'مشروبات', 30, 15],
    ['زيت طبخ', 'كرتون', 12, 'علبة', 1, 'علبة', 'زيت', 'طبخ', 75, 8]
];

foreach ($sampleData as $item) {
    $sql = "INSERT INTO itemsCard (itemName, unitL, fL2M, unitM, fM2S, unitS, mainGroup, subGroup, stok, profit) 
            VALUES ('$item[0]', '$item[1]', '$item[2]', '$item[3]', '$item[4]', '$item[5]', '$item[6]', '$item[7]', '$item[8]', '$item[9]')";
    
    if ($conn->query($sql) === TRUE) {
        echo "تم إدراج $item[0] بنجاح<br>";
    } else {
        echo "خطأ في إدراج $item[0]: " . $conn->error . "<br>";
    }
}

$conn->close();
?>