<?php

if (isset($_REQUEST['mainGroup'])) {
    $mainGroup = $_REQUEST['mainGroup'];
} else {
    echo "<script>alert('الرجاء اختيار المجموعة الرئيسية.');</script>";
    die();
}

include("../hmb/conn.php");
if (!$conn) {
    echo "<script>alert('خطأ في الاتصال بقاعدة البيانات.');</script>";
    die();
}

// استعلام SQL لجلب المجموعات الفرعية المميزة بناءً على المجموعة الرئيسية
// تم تصحيح اسم العمود إلى `subGroup`
$sql = "SELECT DISTINCT `subGroup` FROM `itemsCard` WHERE `mainGroup` = '$mainGroup'";
$result = mysqli_query($conn, $sql);

// التحقق من نجاح الاستعلام
if (!$result) {
    echo "<script>alert('خطأ في الاستعلام: " . mysqli_error($conn) . "');</script>";
    die();
}

// البدء في تجميع كود JavaScript لتمثيل المجموعات الفرعية
// يمكن استخدام هذا إذا كنت تريد تمريرها ككائن أو مصفوفة إلى دالة JavaScript
$subGroupsArray = [];

// المرور على النتائج وعرضها كأزرار
while ($row = mysqli_fetch_assoc($result)) { // استخدام mysqli_fetch_assoc للحصول على مفاتيح باسم العمود
    // التأكد من أن اسم العمود في الكود هو `subGroup`
    $currentSubGroup = htmlspecialchars($row['subGroup']); // استخدام htmlspecialchars للحماية من XSS
    ?>
    <button onclick="getItems('<?= $currentSubGroup ?>')" class="btn btn-success  mb-2 text-start">
        <i class="fa fa-tag me-2"></i> <?= $currentSubGroup ?>
    </button>
    <?php
    // إضافة المجموعة الفرعية إلى مصفوفة لتمريرها لاحقًا إلى JavaScript
    $subGroupsArray[] = $currentSubGroup;
    echo "<script>getItems(" . $currentSubGroup . ");</script>";
}

// عند الانتهاء من عرض الأزرار، يمكنك إرسال المجموعات الفرعية إلى دالة JavaScript
// (على افتراض أن لديك دالة JavaScript اسمها `updateSubGroupsList` مثلاً)
// هذا السطر يقوم بتحويل مصفوفة PHP إلى مصفوفة JSON صالحة في JavaScript
// echo "<script>updateSubGroupsList(" . json_encode($subGroupsArray) . ");</script>";

// إغلاق الاتصال بقاعدة البيانات
$conn->close();
?>


<?php
// if (isset($_REQUEST['mainGroup'])) {
//   $mainGroup = $_REQUEST['mainGroup'];
//   // echo $mainGroup;
// } else {
//   echo "<script>alert('Please select Main Group')</script>";
//   die;
// }


// include("../hmb/conn.php");

// $sql = "SELECT DISTINCT `subGroup` FROM `itemscard` WHERE 	`mainGroup` ='$mainGroup'";
// $result = mysqli_query($conn, $sql);
// $supGroup = array();
// while ($row = mysqli_fetch_array($result)): ?>
  <!-- <div  onclick="getItems(' <?php //$row['supGroup'] ?>')" class="btn btn-outline-danger" >
    <p><?php //$row['supGroup'] ?></p>
  </div> -->
  <?php
//   $mainGroup = $row['subGroup'];
// endwhile;

// echo "<script>getSubGroup(" . $mainGroup . ")</script>";
// $conn->close();
?>