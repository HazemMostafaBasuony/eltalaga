<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "conn.php";
$name = $_POST['customerName'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$priceDelivery = isset($_POST['priceDelivery']) && $_POST['priceDelivery'] !== '' ? floatval($_POST['priceDelivery']) : 0;
$qrAddress = $_POST['qrAddress'] ?? '';


if ($name) {
    $stmt = $conn->prepare("INSERT INTO customername (customerName, phone, address, qrAddress, priceDelivery) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssd", $name, $phone, $address, $qrAddress, $priceDelivery);
    $insert_success = $stmt->execute();

    if ($insert_success) {
        echo 'success';
    } else {
        echo 'error: ' . $stmt->error;
    }
    $stmt->close();
    $conn->close();
    exit;
} else {
    echo 'error: missing name';
    $conn->close();
    exit;
}
?>