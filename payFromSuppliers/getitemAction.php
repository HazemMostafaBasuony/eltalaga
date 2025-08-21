<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

// التأكد من وجود معرف العنصر
if (!isset($_GET['itemID']) || empty($_GET['itemID'])) {
    echo json_encode([
        'success' => false,
        'message' => 'لم يتم تحديد معرف العنصر'
    ]);
    exit;
}

$itemID = intval($_GET['itemID']);
include('../hmb/conn.php');

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error()
    ]);
    exit;
}

// استعلام للحصول على جميع حركات العنصر
$sql = "SELECT 
            i.itemID,
            i.actionID,
            DATE_FORMAT(i.date, '%Y-%m-%d') as formatted_date,
            i.action,
            i.count,
            i.price,
            i.invoiceID
        FROM 
            itemAction i
        WHERE 
            i.itemID = $itemID
        ORDER BY i.date DESC, i.invoiceID DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في الاستعلام: ' . mysqli_error($conn)
    ]);
    exit;
}

// جلب جميع الصفوف
$itemActions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $itemActions[] = $row;
}


// إذا لم توجد حركات
if (empty($itemActions)) {

    $response = [
    'success' => true,
    'itemActions' => "لا توجد بيانات سابقة",
    'sumCount' => 0.00,
    'sumPrice' => 0.00,
    'countSale' => 0
];
exit(json_encode($response, JSON_UNESCAPED_UNICODE));
}


// حساب المجاميع
$sqlCount = "SELECT SUM(count) AS sumCount, price AS lastPrice , SUM(price) as sumPrice FROM itemAction WHERE itemID = $itemID";
$resultCount = mysqli_query($conn, $sqlCount);

if (!$resultCount) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في استعلام المجموع: ' . mysqli_error($conn)
    ]);
    exit;
}

$rowCount = mysqli_fetch_assoc($resultCount);
$sumCount = $rowCount['sumCount'] ?? 0;
$lastPrice = $rowCount['lastPrice'] ?? 0;
$sumPrice = $rowCount['sumPrice'] ?? 0;


// حساب عدد مرات الشراء
$itemID = (int)$itemID;

// 2. استخدام استعلام معدّ (prepared statement) لمنع ثغرات SQL injection
$countSaleQuery = "SELECT COUNT(*) as sale_count FROM itemAction WHERE itemID = ? AND action = 'in'";
$stmt = mysqli_prepare($conn, $countSaleQuery);
mysqli_stmt_bind_param($stmt, 'i', $itemID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$countSaleRow = mysqli_fetch_assoc($result);
$countSale = $countSaleRow['sale_count'] ?? 0;
if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في استعلام عدد مرات الشراء: ' . mysqli_error($conn)
    ]);
    exit;
}


$response = [
    'success' => true,
    'itemActions' => $itemActions,
    'sumCount' => (float)$sumCount,
    'lastPrice' => (float)$lastPrice,
    'sumPrice' => (float)$sumPrice,
    'countSale' => (int)$countSale
];



echo json_encode($response, JSON_UNESCAPED_UNICODE);
mysqli_close($conn);
?>