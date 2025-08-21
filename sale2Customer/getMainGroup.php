<?php
include("../hmb/conn.php");

// Verificar si se recibió el ID del proveedor
$salesmaneID = isset($_REQUEST['salesmaneID']) ? intval($_REQUEST['salesmaneID']) : 0;




$sql = "SELECT DISTINCT mainGroup FROM itemscard";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "<div class='alert alert-danger'>Error al cargar los grupos principales: " . mysqli_error($conn) . "</div>";
} else {
    $mainGroup = "";
    $count = 0;
    
    while ($row = mysqli_fetch_array($result)) {
        $count++;
        $mainGroup = $row['mainGroup'];
        ?>
        <button class="btn btn-primary w-auto mb-2 text-start" 
                onclick="getSubGroup('<?= $mainGroup ?>')" 
                type="button" 
                name="mainGroup">
            <i class="fa fa-folder-open me-2"></i> <?= $mainGroup ?>
        </button>
        <?php
    }
    
    if ($count > 0) {
        echo "<script>getSubGroup('" . $mainGroup . "');</script>";
    } else {
        echo "<div class='alert alert-warning'>لا توجد مجموعات رئيسية متاحة</div>";
    }
}

$conn->close();
?>

