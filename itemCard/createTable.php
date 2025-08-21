<?php
include('../hmb/conn.php');

// إنشاء جدول itemsCard
$sql = "CREATE TABLE IF NOT EXISTS `itemsCard` (
  `itemID` int(30) AUTO_INCREMENT PRIMARY KEY,
  `itemName` varchar(30) NOT NULL,
  `unitL` varchar(30) NOT NULL,
  `fL2M` float UNSIGNED NOT NULL,
  `unitM` varchar(30) NOT NULL,
  `fM2S` float UNSIGNED NOT NULL,
  `unitS` varchar(30) NOT NULL,
  `mainGroup` varchar(30) NOT NULL,
  `subGroup` varchar(30) NOT NULL,
  `stok` float UNSIGNED NOT NULL,
  `profit` float UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql) === TRUE) {
    echo "تم إنشاء الجدول بنجاح";
} else {
    echo "خطأ في إنشاء الجدول: " . $conn->error;
}

$conn->close();
?>