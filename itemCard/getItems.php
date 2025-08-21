<?php
include('../hmb/conn.php');

$search = isset($_GET['search']) ? $_GET['search'] : '';

// إنشاء الاستعلام
$sql = "SELECT * FROM itemsCard";
if (!empty($search)) {
    $sql .= " WHERE itemName LIKE '%$search%' OR mainGroup LIKE '%$search%' OR subGroup LIKE '%$search%'";
}
$sql .= " ORDER BY itemID DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['itemID'] . "</td>";
        echo "<td>" . $row['itemName'] . "</td>";
        echo "<td>" . $row['unitL'] . "</td>";
        echo "<td>" . $row['fL2M'] . "</td>";
        echo "<td>" . $row['unitM'] . "</td>";
        echo "<td>" . $row['fM2S'] . "</td>";
        echo "<td>" . $row['unitS'] . "</td>";
        echo "<td>" . $row['mainGroup'] . "</td>";
        echo "<td>" . $row['subGroup'] . "</td>";
        echo "<td>" . $row['stock'] . "</td>";
        echo "<td>" . $row['profit'] . "</td>";
        echo "<td>";
        echo "<div class='action-buttons'>";
        echo "<button class='btn btn-edit' onclick='editItem(" . $row['itemID'] . ")'>";
        echo "<i class='fas fa-edit'></i> تعديل";
        echo "</button>";
        echo "<button class='btn btn-delete' onclick='deleteItem(" . $row['itemID'] . ")'>";
        echo "<i class='fas fa-trash'></i> حذف";
        echo "</button>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr>";
    echo "<td colspan='12' class='empty-state'>";
    echo "<i class='fas fa-box-open'></i>";
    echo "<h3>لا توجد أصناف</h3>";
    echo "<p>لم يتم العثور على أي أصناف في قاعدة البيانات</p>";
    echo "</td>";
    echo "</tr>";
}

$conn->close();
?>