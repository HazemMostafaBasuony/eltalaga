<?php
// اختبار سريع لتجربة النظام
echo "<h1>اختبار نظام إدارة الأصناف</h1>";

// اختبار الاتصال بقاعدة البيانات
echo "<h2>1. اختبار الاتصال بقاعدة البيانات:</h2>";
include('../hmb/conn.php');
if ($conn) {
    echo "✅ تم الاتصال بقاعدة البيانات بنجاح<br>";
} else {
    echo "❌ فشل الاتصال بقاعدة البيانات<br>";
}

// اختبار وجود الجدول
echo "<h2>2. اختبار وجود الجدول:</h2>";
$sql = "SELECT COUNT(*) as count FROM itemsCard";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    echo "✅ الجدول موجود ويحتوي على " . $row['count'] . " صنف<br>";
} else {
    echo "❌ الجدول غير موجود<br>";
}

// عرض البيانات
echo "<h2>3. عرض البيانات:</h2>";
$sql = "SELECT * FROM itemsCard LIMIT 5";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>رقم الصنف</th><th>اسم الصنف</th><th>الوحدة الكبيرة</th><th>المجموعة الرئيسية</th><th>المخزون</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['itemID'] . "</td>";
        echo "<td>" . $row['itemName'] . "</td>";
        echo "<td>" . $row['unitL'] . "</td>";
        echo "<td>" . $row['mainGroup'] . "</td>";
        echo "<td>" . $row['stok'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ لا توجد بيانات<br>";
}

echo "<h2>4. روابط للتجربة:</h2>";
echo "<a href='index.php' style='padding: 10px; background: #2c3e50; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>الصفحة الرئيسية</a><br><br>";
echo "<a href='getItems.php' style='padding: 10px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>عرض الأصناف (JSON)</a><br><br>";
echo "<a href='getItem.php?id=1' style='padding: 10px; background: #f39c12; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>عرض صنف محدد</a><br><br>";

$conn->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    direction: rtl;
    padding: 20px;
    background: #f5f5f5;
}

h1 {
    color: #2c3e50;
    text-align: center;
}

h2 {
    color: #34495e;
    border-bottom: 2px solid #3498db;
    padding-bottom: 5px;
}

table {
    background: white;
    margin: 10px 0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

th {
    background: #3498db;
    color: white;
    padding: 10px;
}

td {
    padding: 8px;
    text-align: center;
}

tr:nth-child(even) {
    background: #f9f9f9;
}
</style>