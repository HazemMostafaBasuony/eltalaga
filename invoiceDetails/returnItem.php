<?php
include('../hmb/conn_pdo.php');

$actionID = $_POST['actionID'] ?? 0;
$returnCount = $_POST['count'] ?? 0;

if(!$actionID || !$returnCount){
    die(json_encode(['success'=>false,'message'=>'بيانات غير صحيحة']));
}

// جلب بيانات الصنف
$stmt = $pdo->prepare("SELECT * FROM itemaction WHERE actionID = ?");
$stmt->execute([$actionID]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$item) die(json_encode(['success'=>false,'message'=>'الصنف غير موجود']));

if($returnCount >= $item['count']){
    // إرجاع كامل
    $pdo->prepare("UPDATE itemaction SET action = 'return' WHERE actionID = ?")
        ->execute([$actionID]);
} else {
    // إرجاع جزئي
    $remaining = $item['count'] - $returnCount;
    $pdo->prepare("UPDATE itemaction SET count = ? WHERE actionID = ?")
        ->execute([$remaining, $actionID]);

    $pdo->prepare("INSERT INTO itemaction (itemID, date, action, count, price, discount, invoiceID, unit, itemName)
                   VALUES (:itemID, NOW(), 'return', :count, :price, :discount, :invoiceID, :unit, :itemName)")
        ->execute([
            'itemID'=>$item['itemID'],
            'count'=>$returnCount,
            'price'=>$item['price'],
            'discount'=>0,
            'invoiceID'=>$item['invoiceID'],
            'unit'=>$item['unit'],
            'itemName'=>$item['itemName']
        ]);
}

echo json_encode(['success'=>true]);
