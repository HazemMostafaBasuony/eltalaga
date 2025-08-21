<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0); // For production, log errors instead of suppressing them

// التأكد من وجود معرف العنصر
if (!isset($_GET['itemID']) || empty($_GET['itemID'])) {
    // إرجاع رسالة خطأ إذا لم يتم تحديد معرف العنصر
    echo json_encode([
        'success' => false,
        'message' => 'لم يتم تحديد معرف العنصر'
    ]);
    exit;
}

$itemID = $_GET['itemID']; // Keep as string for preparation, intval for validation if needed

include('../hmb/conn.php'); // Ensure this path is correct and conn.php is secure

if (!$conn) {
    // Log the actual error on the server-side, provide a generic message to the client
    error_log('Database connection failed: ' . mysqli_connect_error());
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ غير متوقع في الاتصال بقاعدة البيانات. الرجاء المحاولة لاحقًا.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate itemID more thoroughly if necessary (e.g., check if it's a positive integer)
if (!filter_var($itemID, FILTER_VALIDATE_INT) || $itemID <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف العنصر غير صالح.' . mysqli_connect_error()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Prepare the SQL statement to get profit from itemscard
$sqlProfit = "SELECT profit FROM itemscard WHERE itemID = ?";
$stmtProfit = mysqli_prepare($conn, $sqlProfit);

if (!$stmtProfit) {
    error_log('Failed to prepare profit statement: ' . mysqli_error($conn));
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ داخلي في الخادم. الرجاء المحاولة لاحقًا.'. mysqli_error($conn)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Bind parameters for profit query
mysqli_stmt_bind_param($stmtProfit, "i", $itemID);

// Execute the profit statement
mysqli_stmt_execute($stmtProfit);

// Get the profit result
$resultProfit = mysqli_stmt_get_result($stmtProfit);

if (!$resultProfit) {
    error_log('Error executing profit statement: ' . mysqli_error($conn));
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ أثناء استرداد بيانات العنصر. الرجاء المحاولة لاحقًا.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$rowProfit = mysqli_fetch_assoc($resultProfit);
$profit = $rowProfit['profit'] ?? 0;

// Close the profit statement
mysqli_stmt_close($stmtProfit);

// Prepare the SQL statement to get last purchase price from itemaction
$sqlLastPrice = "SELECT price FROM itemaction WHERE itemID = ? AND action = 'in' ORDER BY date DESC LIMIT 1";
$stmtLastPrice = mysqli_prepare($conn, $sqlLastPrice);

if (!$stmtLastPrice) {
    error_log('Failed to prepare last price statement: ' . mysqli_error($conn));
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ داخلي في الخادم. الرجاء المحاولة لاحقًا.'. mysqli_error($conn)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Bind parameters for last price query
mysqli_stmt_bind_param($stmtLastPrice, "i", $itemID);

// Execute the last price statement
mysqli_stmt_execute($stmtLastPrice);

// Get the last price result
$resultLastPrice = mysqli_stmt_get_result($stmtLastPrice);

if (!$resultLastPrice) {
    error_log('Error executing last price statement: ' . mysqli_error($conn));
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ أثناء استرداد سعر الشراء الأخير. الرجاء المحاولة لاحقًا.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$rowLastPrice = mysqli_fetch_assoc($resultLastPrice);
$lastPurchasePrice = $rowLastPrice['price'] ?? 0;

// Close the last price statement
mysqli_stmt_close($stmtLastPrice);

// Calculate good price based on last purchase price and profit
$goodPrice = $lastPurchasePrice * (($profit / 100) + 1);

$response = [
    'success' => true,
    'profit' => (float)$profit,
    'lastPurchasePrice' => (float)$lastPurchasePrice,
    'goodPrice' => (float)$goodPrice
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);

mysqli_close($conn);
?>