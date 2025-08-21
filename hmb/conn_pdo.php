<?php
// Suppress HTML errors when included via AJAX
if (!isset($suppress_errors)) {
    $suppress_errors = false;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=talagadb;charset=utf8", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // إظهار الأخطاء كمُستثنيات (Exceptions)
} catch (PDOException $e) {
    $error_msg = 'لا يوجد اتصال بقاعدة البيانات: ' . $e->getMessage();

    // إذا كان الطلب من نوع AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        die(json_encode([
            'success' => false,
            'message' => $error_msg,
            'error' => 'database_connection_failed'
        ]));
    } 
    // طلب عادي من المتصفح
    elseif (!$suppress_errors) {
        die("<div class='desconnect'>$error_msg</div>");
    } else {
        throw new Exception($error_msg);
    }
}
?>
