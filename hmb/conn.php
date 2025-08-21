<?php
// Suppress HTML errors when included via AJAX
if (!isset($suppress_errors)) {
    $suppress_errors = false;
}

$conn = new mysqli('localhost', 'root', '', 'talagadb');
$conn->set_charset('utf8');

if ($conn->connect_error) {
    $error_msg = 'لا يوجد اتصال بقاعدة البيانات: ' . $conn->connect_error;
    
    // If this is an AJAX request, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        die(json_encode([
            'success' => false,
            'message' => $error_msg,
            'error' => 'database_connection_failed'
        ]));
    } 
    // Otherwise, output HTML error
    elseif (!$suppress_errors) {
        die("<div class='desconnect'>$error_msg</div>");
    } else {
        throw new Exception($error_msg);
    }
}