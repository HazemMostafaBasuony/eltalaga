<?php
include('../hmb/conn.php');

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'add') {
    // إضافة صنف جديد
    $itemName = $_POST['itemName'];
    $unitL = $_POST['unitL'];
    $fL2M = $_POST['fL2M'];
    $unitM = $_POST['unitM'];
    $fM2S = $_POST['fM2S'];
    $unitS = $_POST['unitS'];
    $mainGroup = $_POST['mainGroup'];
    $subGroup = $_POST['subGroup'];
    $stok = $_POST['stok'];
    $profit = $_POST['profit'];
    
    // التحقق من وجود الصنف
    $checkSql = "SELECT * FROM itemsCard WHERE itemName = '$itemName'";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'اسم الصنف موجود بالفعل'
        ]);
        exit;
    }
    
    $sql = "INSERT INTO itemsCard (itemName, unitL, fL2M, unitM, fM2S, unitS, mainGroup, subGroup, stok, profit) 
            VALUES ('$itemName', '$unitL', '$fL2M', '$unitM', '$fM2S', '$unitS', '$mainGroup', '$subGroup', '$stok', '$profit')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            'success' => true,
            'message' => 'تم إضافة الصنف بنجاح'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'خطأ في إضافة الصنف: ' . $conn->error
        ]);
    }
    
} elseif ($action == 'update') {
    // تعديل صنف موجود
    $itemID = $_POST['itemID'];
    $itemName = $_POST['itemName'];
    $unitL = $_POST['unitL'];
    $fL2M = $_POST['fL2M'];
    $unitM = $_POST['unitM'];
    $fM2S = $_POST['fM2S'];
    $unitS = $_POST['unitS'];
    $mainGroup = $_POST['mainGroup'];
    $subGroup = $_POST['subGroup'];
    $stok = $_POST['stok'];
    $profit = $_POST['profit'];
    
    // التحقق من وجود الصنف بنفس الاسم (عدا الصنف الحالي)
    $checkSql = "SELECT * FROM itemsCard WHERE itemName = '$itemName' AND itemID != '$itemID'";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'اسم الصنف موجود بالفعل'
        ]);
        exit;
    }
    
    $sql = "UPDATE itemsCard SET 
            itemName = '$itemName',
            unitL = '$unitL',
            fL2M = '$fL2M',
            unitM = '$unitM',
            fM2S = '$fM2S',
            unitS = '$unitS',
            mainGroup = '$mainGroup',
            subGroup = '$subGroup',
            stok = '$stok',
            profit = '$profit'
            WHERE itemID = '$itemID'";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث الصنف بنجاح'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'خطأ في تحديث الصنف: ' . $conn->error
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'إجراء غير صحيح'
    ]);
}

$conn->close();
?>