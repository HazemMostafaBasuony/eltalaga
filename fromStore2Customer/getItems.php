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
$i = 0;

// 1. التحقق من وجود 'subGroup' في الطلب
if (isset($_REQUEST['subGroup'])) {
    $subGroup = $_REQUEST['subGroup'];
} else {
    echo "<script>alert('الرجاء اختيار المجموعة الفرعية.');</script>";
    die();
}

// 2. تضمين ملف الاتصال بقاعدة البيانات (PDO)
require_once("../hmb/conn_pdo.php"); // يحتوي على $pdo

try {
    if ($subGroup == "all") {
        // استعلام لكل الخامات
        $stmt = $pdo->query("SELECT * FROM `itemscard` ORDER BY `stock` DESC");
    } else {
        // استعلام للعناصر الخاصة بـ subGroup
        $stmt = $pdo->prepare("SELECT * FROM `itemscard` WHERE `subGroup` = :subGroup ORDER BY `stock` DESC");
        $stmt->execute(['subGroup' => $subGroup]);
    }

    // 3. المرور على النتائج
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $itemName = htmlspecialchars($row['itemName']);
        $stock    = intval($row['stock']);
        $unitL    = htmlspecialchars($row['unitL']);
        $itemID   = htmlspecialchars($row['itemID']);

        if ($stock > 0) {
            $class = "item-button";
            $disabled = "";
        } else {
            $class = "item-button unavailable";
            $disabled = "disabled";
        }
        ?>
        <div onclick="addItemToInvoice('<?= $itemID ?>', <?= $stock ?>);" 
             class="<?= $class ?>" 
             data-item-id="<?= $itemID ?>" 
             title="<?= $itemName ?> - <?= $itemID ?>" <?= $disabled ?>>
            <strong><?= $itemID ?> <?= $itemName ?> </strong>
            <small class="text-muted">
                <i class="fa fa-cubes me-1"></i><?= $stock . " " . $unitL ?>
            </small>
        </div>
        <?php
        $i++;
    }

    // إذا لم نجد أي عناصر
    if ($i === 0) {
        echo "<p>لا توجد عناصر متاحة</p>";
    }

} catch (PDOException $e) {
    echo "<script>alert('خطأ في قاعدة البيانات: " . addslashes($e->getMessage()) . "');</script>";
    die();
}
