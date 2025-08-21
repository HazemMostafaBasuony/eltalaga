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



    .unavailable {
       cursor: not-allowed !important;
       opacity: 0.5 !important;
       pointer-events: none !important;
       background-color: #e9ecef !important;
       color: #6c757d !important;

    }


    .unavailable:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
        background-color: #e9ecef !important;
    }

    /* Contenedor de ítems con diseño de cuadrículا */
    #items {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 10px !important;
    }
</style>
<?php

// 1. التحقق من وجود 'subGroup' في الطلب
if (!isset($_REQUEST['subGroup'])) {
    echo "<script>alert('الرجاء اختيار المجموعة الفرعية.');</script>";
    die();
}

if (!isset($_REQUEST['userID'])) {
    echo "<script>alert('الرجاء اختيار العميل.');</script>";
    die();
} else {
     $userID = (int)$_REQUEST['userID']; // تأمين القيمة
    $tableName = 'itemscard' . $userID;
}

$subGroup = $_REQUEST['subGroup'];

if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
    echo '<tr><td colspan="6">اسم الجدول غير صالح</td></tr>';
    exit;
}


// الاتصال بقاعدة البيانات (PDO)
include("../hmb/conn_pdo.php"); // تأكد أن ملف الاتصال فيه متغير $pdo

if ($subGroup == "all") {
   

// نجمع الكميات لكل صنف، ونجيب آخر سعر للصنف من أحدث فاتورة لنفس المندوب

$sql = "SELECT * FROM `$tableName` ORDER BY `stock` DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
 $subTotal=0;
if ($items) {
    foreach ($items as $item) {
        $itemID    = htmlspecialchars($item['itemID']);
        $itemName  = htmlspecialchars($item['itemName']);
        $stock = (float)$item['stock'];      // مجموع الكمية
        $unitL  = htmlspecialchars($item['unitL']); // الوحدة


                if ($stock > 0) {
                    $class = "item-button";
                    $disabled = "";
                } else {
                    $class = "item-button unavailable";
                    $disabled = "disabled";
                }
                ?>
                <div onclick="showSendItemModal('<?= $itemID ?>', <?= $stock ?>);" 
                     class="<?= $class ?>" 
                     data-item-id="<?= $itemID ?>"
                     title="<?= $itemName ?> - <?= $itemID ?>" <?= $disabled ?>>
                     <small style="color:#666;">[<?= $itemID ?>]</small>
                    <strong><?= $itemName ?></strong><br>
                    <small class="text-muted">
                        <i class="fa fa-cubes me-1"></i><?= number_format($stock, 2) . " " . $unitL ?>
                    </small>
                </div>
                <?php
            }
        }

   
}   

else {
        echo "لا توجد منتجات للبيع";
    } 
?>
