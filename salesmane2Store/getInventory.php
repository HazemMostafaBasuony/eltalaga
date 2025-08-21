<?php
include('../hmb/conn_pdo.php');

if (!isset($_REQUEST['userID'])) {
    echo '<tr><td colspan="6">معرف المستخدم غير موجود</td></tr>';
    exit;
} else {
    $userID = (int)$_REQUEST['userID']; // تأمين القيمة
    $tableName = 'itemscard' . $userID;
}

// التحقق من أن اسم الجدول يحتوي فقط على الحروف والأرقام (أمان)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
    echo '<tr><td colspan="6">اسم الجدول غير صالح</td></tr>';
    exit;
}

$sql = "SELECT * FROM `$tableName` ORDER BY `stock` DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
 $subTotal=0;
if ($items) {
    foreach ($items as $item) {
        $id    = htmlspecialchars($item['itemID']);
        $name  = htmlspecialchars($item['itemName']);
        $count = (float)$item['stock'];      // مجموع الكمية
        $unit  = htmlspecialchars($item['unitL']); // الوحدة
        $price = (float)($item['priceL'] ?? 0); // آخر سعر
        $total = $count * $price;
        $subTotal += $total;
?>

<tr id="row<?php echo $id; ?>">
    <td><input type="checkbox" id="check<?php echo $id; ?>"></td>
    <td><?php echo $name; ?></td>
    <td id="qty<?php echo $id; ?>"><?php echo number_format($count,2); ?></td>
    <td><?php echo $unit; ?></td>
    <td><?php echo number_format($price,2); ?></td>
    <td><?php echo number_format($total,2); ?></td>
    <td>
        <button id="requestReturnBtn<?php echo $id; ?>" class="btn btn-success" onclick="reqrequestReturn(<?php echo $id; ?>)">
            طلب ارجاع
        </button>
        <span id="returnBtn<?php echo $id; ?>" style="display:none;">✅ تم الإرسال</span>
        <span id="returnStatus<?php echo $id; ?>" style="display:none;">✅ تم الإرجاع</span>
        <div id="requestReturnStatus<?php echo $id; ?>"></div>
    </td>
</tr>


<?php
    }
echo '<tr> 
        <td colspan="5">المجموع</td>
        <td>' . number_format($subTotal, 2) . '</td>
      </tr>';

echo '<tr> 
        <td colspan="5">المجموع مع الضريبة</td>
        <td>' . number_format($subTotal * 1.15, 2) . '</td>
            <td>
            <button class="btn btn-outline-danger d-flex align-items-center gap-2"
                    onclick="cleanZero('.$userID.')">
                <i class="fa fa-trash"></i> تنظيف المخزون
            </button>
            </td>
      </tr>';


 

} else {
    echo '<tr><td colspan="6">لا توجد أصناف في فواتير هذا المندوب.</td></tr>';
}
