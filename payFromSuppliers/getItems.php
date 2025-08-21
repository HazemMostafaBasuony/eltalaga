<style>
/* Estilos mejorados para los botones de ítems */
.item-button {
    display: inline-flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
    padding: 15px !important;
    margin: 10px !important;
    border-radius: 10px !important;
    transition: all 0.3s ease !important;
    background-color: #4376aa42 !important;
    border: 1px solid #dee2e6 !important;
    color: #212529 !important;
    text-align: center !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1) !important;
    width: 200px !important;
    height: 120px !important;
    cursor: pointer !important;
}

.item-button:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
    background-color: #e9ecef !important;
}

.item-button strong {
    font-size: 18px !important;
    font-weight: bold !important;
    margin-bottom: 5px !important;
    color: #212529 !important;
}

.item-button small {
    font-size: 14px !important;
    color: #6c757d !important;
}

/* Contenedor de ítems con diseño de cuadrícula */
#items {
    display: flex !important;
    flex-wrap: wrap !important;
    justify-content: center !important;
    gap: 10px !important;
}
</style>

<?php

// 1. التحقق من وجود 'subGroup' في الطلب
if (isset($_REQUEST['subGroup'])) {
    $subGroup = $_REQUEST['subGroup'];
    
} else {
    echo "<script>alert('الرجاء اختيار المجموعة الفرعية.');</script>";
    die(); // إيقاف التنفيذ إذا لم يتم تحديد المجموعة الفرعية
}

if ($subGroup=="all"){
//   اظهار كل الخامات
include("../hmb/conn.php");

// التأكد من أن الاتصال تم بنجاح
if (!$conn) {
    echo "<script>alert('خطأ في الاتصال بقاعدة البيانات: لا يمكن الاتصال.');</script>";
    die();
}

$subGroupEscaped = mysqli_real_escape_string($conn, $subGroup);
$sql = "SELECT * FROM `itemscard`";

// تنفيذ الاستعلام
$result = mysqli_query($conn, $sql); // استخدام mysqli_query للتناسق مع mysqli_error

// 4. التحقق من نجاح الاستعلام
if (!$result) {
    echo "<script>alert('خطأ في الاستعلام: " . mysqli_error($conn) . "');</script>";
    die();
}


// 6. المرور على النتائج وعرضها كأزرار
// استخدام mysqli_fetch_assoc للحصول على مفاتيح باسم العمود
while ($row = mysqli_fetch_assoc($result)) {
    $itemName = htmlspecialchars($row['itemName']); // حماية من XSS
    $stock = intval($row['stock']); // تحويل الكمية إلى عدد صحيح (إذا كانت كذلك)
    $unitL = htmlspecialchars($row['unitL']); // حماية من XSS
    $itemID = htmlspecialchars($row['itemID']); // حماية من XSS وتأكد من تمرير ID بشكل آمن

    ?>
      <div onclick="addItemToInvoice('<?= $itemID ?>');" class="item-button" data-item-id="<?= $itemID ?>" title="<?= $itemName ?> - <?= $itemID ?>">
          <strong><?= $itemName ?></strong>
          <small><i class="fa fa-cubes me-1"></i><?= $stock . " " . $unitL ?></small>
      </div>
    <?php

}



}else{
// 2. تضمين ملف الاتصال بقاعدة البيانات
include("../hmb/conn.php");

// التأكد من أن الاتصال تم بنجاح
if (!$conn) {
    echo "<script>alert('خطأ في الاتصال بقاعدة البيانات: لا يمكن الاتصال.');</script>";
    die();
}

// 3. استعلام SQL لجلب جميع العناصر بناءً على المجموعة الفرعية المحددة
// استخدام mysqli_real_escape_string للحماية من حقن SQL
$subGroupEscaped = mysqli_real_escape_string($conn, $subGroup);
$sql = "SELECT * FROM `itemscard` WHERE `subGroup` = '$subGroupEscaped'";

// تنفيذ الاستعلام
$result = mysqli_query($conn, $sql); // استخدام mysqli_query للتناسق مع mysqli_error

// 4. التحقق من نجاح الاستعلام
if (!$result) {
    echo "<script>alert('خطأ في الاستعلام: " . mysqli_error($conn) . "');</script>";
    die();
}


// 6. المرور على النتائج وعرضها كأزرار
// استخدام mysqli_fetch_assoc للحصول على مفاتيح باسم العمود
while ($row = mysqli_fetch_assoc($result)) {
    $itemName = htmlspecialchars($row['itemName']); // حماية من XSS
    $stock = intval($row['stock']); // تحويل الكمية إلى عدد صحيح (إذا كانت كذلك)
    $unitL = htmlspecialchars($row['unitL']); // حماية من XSS
    $itemID = htmlspecialchars($row['itemID']); // حماية من XSS وتأكد من تمرير ID بشكل آمن

    ?>
      <div onclick="addItemToInvoice('<?= $itemID ?>');" class="item-button" data-item-id="<?= $itemID ?>" title="<?= $itemName ?> - <?= $itemID ?>">
          <strong ><?= $itemName ?></strong>
          <small class="text-muted"><i class="fa fa-cubes me-1"></i><?= $stock . " " . $unitL ?></small>
      </div>
    <?php

}


// 9. إغلاق الاتصال بقاعدة البيانات
$conn->close();
}
?>