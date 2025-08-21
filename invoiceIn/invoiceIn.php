<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

include('../hmb/conn.php');

// الحصول على invoiceID من POST request
$data = json_decode(file_get_contents('php://input'), true);
$invoiceID = isset($data['invoiceID']) ? mysqli_real_escape_string($conn, $data['invoiceID']) : null;

if (!$invoiceID) {
    echo json_encode([
        'success' => false,
        'message' => 'لم يتم توفير رقم الفاتورة'
    ]);
    exit;
}

// استعلام الفاتورة الأساسية
$sql = "SELECT * FROM `invoices` WHERE `invoiceID` = '$invoiceID'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في استعلام الفاتورة: ' . mysqli_error($conn)
    ]);
    exit;
}

$invoice = mysqli_fetch_assoc($result);

if (!$invoice) {
    echo json_encode([
        'success' => false,
        'message' => 'لا توجد فاتورة بهذا الرقم'
    ]);
    exit;
}

// استعلام العناصر المرتبطة
$itemActions = [];
$sql = "SELECT * FROM itemaction WHERE invoiceID = '$invoiceID'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في جلب عناصر الفاتورة: ' . mysqli_error($conn)
    ]);
    exit;
}

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $itemActions[] = $row;
    }
}

// استعلام بيانات الأصناف
$items = [];
if (!empty($itemActions)) {
    $itemIds = array_column($itemActions, 'itemID');
    $idsList = "'" . implode("','", $itemIds) . "'";

    $sql = "SELECT * FROM `itemscard` WHERE itemID IN ($idsList)";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => 'خطأ في جلب بيانات الأصناف: ' . mysqli_error($conn)
        ]);
        exit;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $items[$row['itemID']] = $row;
    }
}

// استعلام بيانات الفرع
$branch = [];
if (isset($invoice['branchName'])) {
    $branchName = mysqli_real_escape_string($conn, $invoice['branchName']);
    $sql = "SELECT * FROM branch WHERE branchName='$branchName'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $branch = mysqli_fetch_assoc($result);
    }
}

// استعلام بيانات المورد (supplier) بدلاً من customer
$supplier = [];
if (isset($invoice['fromID'])) {
    $fromID = mysqli_real_escape_string($conn, $invoice['fromID']);
    $sql = "SELECT * FROM suppliers WHERE supplierID='$fromID'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $supplier = mysqli_fetch_assoc($result);
    }
}

mysqli_close($conn);

// إعداد البيانات للإرجاع
$response = [
    'success' => true,
    'invoice' => $invoice,
    'supplier' => $supplier,
    'branch' => $branch,
    'itemActions' => $itemActions,
    'items' => $items
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>