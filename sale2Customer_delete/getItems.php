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
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1) !important;
        width: 200px !important;
        height: 120px !important;
        cursor: pointer !important;
    }

    .item-button:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
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


$salesmaneID = $_GET['salesmaneID'];
$customerID = $_GET['customerID'];
$itemsID = [];
$items = [];

// اظهار كل الخامات
include("../hmb/conn.php");

// التأكد من أن الاتصال تم بنجاح
if (!$conn) {
    echo "<script>alert('خطأ في الاتصال بقاعدة البيانات: لا يمكن الاتصال.');</script>";
    die();
    
}

// الخطأ الأول: كان هناك علامة ` زائدة قبل invoices وكان الشرط خاطئاً
$sql = "SELECT * FROM invoices WHERE customerID = $salesmaneID AND `remainingAmount` > 1";
$result = mysqli_query($conn, $sql);
if (!$result) {
    echo "<script>alert('خطأ في الاستعلام: " . mysqli_error($conn) . "');</script>";
 
    die();
}

while ($row = mysqli_fetch_assoc($result)) {
    $invoiceID = $row['invoiceID'];
    
    // الخطأ الثاني: كان اسم الجدول `itemaction` بدون علامات الاقتباس الصحيحة
    $sql2 = "SELECT * FROM itemaction WHERE invoiceID = $invoiceID";
    $result2 = mysqli_query($conn, $sql2);
    if (!$result2) {
        echo "<script>alert('خطأ في الاستعلام: " . mysqli_error($conn) . "');</script>";
        die();
    }
    

    
    while ($row2 = mysqli_fetch_assoc($result2)) {
        $itemsID[] = $row2['itemID'];
        $count[] = $row2['count'];
        
    }
    // تم إزالة exit; لأنها كانت توقف العملية بعد أول نتيجة
}

if (!empty($itemsID)) {
    // نستخدم array_unique لإزالة التكرارات من مصفوفة itemsID
    $uniqueItemsID = array_unique($itemsID);
    
    // نجهز الاستعلام لاسترجاع جميع العناصر المطلوبة دفعة واحدة
    $sql = "SELECT * FROM itemscard WHERE itemID IN (" . implode(',', array_map('intval', $uniqueItemsID)) . ")";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        echo "<script>alert('خطأ في الاستعلام: " . mysqli_error($conn) . "');</script>";
        die();
    }
    
    // نستخدم هذا المؤشر للوصول إلى قيم count
    $i = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $itemName = htmlspecialchars($row['itemName']);
        // نتحقق من وجود قيمة في مصفوفة count قبل استخدامها
        $stock = isset($count[$i]) ? intval($count[$i]) : 0;
        $unitL = htmlspecialchars($row['unitL']);
        $itemID = htmlspecialchars($row['itemID']);
        
        if ($stock > 0) {
            ?>
            <div onclick="addItemToInvoice('<?= $itemID ?>' ,<?= $stock ?>);" class="item-button" data-item-id="<?= $itemID ?>"
                title="<?= $itemName ?> - <?= $itemID ?>">
                <strong><?= $itemID ?>  <?= $itemName ?>  </strong>
                <small class="text-muted"><i class="fa fa-cubes me-1"></i><?= $stock . " " . $unitL ?></small>
            </div>
            <?php
        }
        $i++; // نزيد المؤشر بعد كل عنصر
    }
    
    // إذا لم نجد أي عناصر
    if ($i === 0) {
        echo "<p>لا توجد عناصر متاحة</p>";
    }
} else {
    echo "<p>لا توجد عناصر متاحة</p>";
}