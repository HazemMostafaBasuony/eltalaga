<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../hmb/conn_pdo.php');
require_once('../hmb/convertToUnitL.php'); // دالة التحويل للوحدة الكبرى

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مدعومة']);
    exit;
}

// قراءة وفك ترميز العناصر
$items = json_decode($_POST['items'], true);
if (!is_array($items)) {
    echo json_encode(['success' => false, 'message' => 'البيانات المرسلة $items فارغة أو غير صالحة']);
    exit;
}

// التحقق من الحقول المطلوبة
$requiredFields = [
    'invoiceID','userID','customerID','paidAmount','totalDue',
    'today','paymentMethod','grandTotal','discount','vat',
    'remainingAmount','latitude','longitude','total'
];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "الحقل مطلوب مفقود: $field"]);
        exit;
    }
}

// جلب الحقول
$total = (float) $_POST['total'];
$invoiceID = (int) $_POST['invoiceID'];
$userID = (int) $_POST['userID'];
$customerID = (int) $_POST['customerID'];
$paidAmount = (float) $_POST['paidAmount'];
$totalDue = (float) $_POST['totalDue'];
$grandTotal = (float) $_POST['grandTotal'];
$discount = (float) $_POST['discount'];
$vat = (float) $_POST['vat'];
$remainingAmount = (float) $_POST['remainingAmount'];
$today = $_POST['today'];
$paymentMethod = $_POST['paymentMethod'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$notes = "بيع من مندوب الى عميل";

$tableUserStock = "itemscard$userID";
$reportTable = "reportSalesman$userID";
foreach ($items as $item) {
$discount+=$item['discount'];
}
if ($remainingAmount < 0 || $discount < 0 || $discount > $grandTotal ) {
    echo json_encode(['success' => false, 'message' => $discount.'خطاء في البيانات المدخلة']);
    exit;
}
try {
    $pdo->beginTransaction();

    // تحديث حالة الفاتورة
    try {
        $stmt = $pdo->prepare("
            UPDATE invoices 
            SET state = 1,
                date = :dateInvoice,
                paymentMethod = :paymentMethod,
                total = :total,
                discount = :discount,
                totalDue = :totalDue,
                vat = :vat,
                generalTotal = :grandTotal,
                notes = :notes,
                paidAmount = :paidAmount,
                paidDate = :paidDate,
                remainingAmount = :remainingAmount
            WHERE invoiceID = :invoiceID
        ");
        $stmt->execute([
            ':dateInvoice' => $today,
            ':paidDate' => $today,
            ':paymentMethod' => $paymentMethod,
            ':total' => $total,
            ':totalDue' => $totalDue,
            ':discount' => $discount,
            ':vat' => $vat,
            ':grandTotal' => $grandTotal,
            ':notes' => $notes,
            ':paidAmount' => $paidAmount,
            ':remainingAmount' => $remainingAmount,
            ':invoiceID' => $invoiceID
        ]);
    } catch (Exception $e) {
        throw new Exception("خطأ أثناء تحديث الفاتورة: " . $e->getMessage());
    }

    // خصم الأصناف من مخزون المندوب
    foreach ($items as $item) {
        try {
            $itemID = (int)$item['itemID'];
            $count = (float)$item['count'];
            $price = (float)$item['price'];
            $unit = $item['unit'];
            $discountItem = (float)$item['discount'];

            $stmtItemSelect = $pdo->prepare("SELECT * FROM itemscard WHERE itemID = :itemID");
            $stmtItemSelect->execute([':itemID' => $itemID]);
            $itemData = $stmtItemSelect->fetch(PDO::FETCH_ASSOC);
            if (!$itemData) throw new Exception("الصنف غير موجود: $itemID");

            $countInL = convertToUnitL($count, $unit, $itemData['unitL'], $itemData['unitM'], $itemData['unitS']);

            $stmtCheckStock = $pdo->prepare("SELECT stock FROM $tableUserStock WHERE itemID = :itemID");
            $stmtCheckStock->execute([':itemID' => $itemID]);
            $userStock = $stmtCheckStock->fetchColumn();
            if ($userStock < $countInL) throw new Exception("لا يوجد مخزون كافي للصنف $itemID");

            $stmtItemUpdate = $pdo->prepare("UPDATE $tableUserStock SET stock = stock - :stock WHERE itemID = :itemID");
            $stmtItemUpdate->execute([':stock' => $countInL, ':itemID' => $itemID]);

            $stmtItemActionInsert = $pdo->prepare("INSERT INTO itemAction (itemID, action, count, price, invoiceID) VALUES (:itemID, 'out', :count, :price, :invoiceID)");
            $stmtItemActionInsert->execute([
                ':itemID' => $itemID,
                ':count' => $countInL,
                ':price' => $price,
                ':invoiceID' => $invoiceID
            ]);
        } catch (Exception $e) {
            throw new Exception("خطأ في معالجة الصنف $itemID: " . $e->getMessage());
        }
    }

    // تحديث رصيد المندوب المالي
    try {
        $stmt = $pdo->prepare("UPDATE salesmane SET stockInventory = stockInventory - :amount WHERE salesmaneID = :userID");
        $stmt->execute([':amount' => $paidAmount, ':userID' => $userID]);
    } catch (Exception $e) {
        throw new Exception("خطأ أثناء تحديث رصيد المندوب: " . $e->getMessage());
    }

    // تحديث رصيد العميل
    try {
        $stmt = $pdo->prepare("
            UPDATE customers 
            SET remainingAmount = :remainingAmount,
                notes = CONCAT(IFNULL(notes,''), '\nآخر معاملة بتاريخ ', NOW(), ' بمبلغ ', :totalDue, ' ريال')
            WHERE customerID = :customerID
        ");
        $stmt->execute([':remainingAmount' => $remainingAmount, ':totalDue' => $totalDue, ':customerID' => $customerID]);
    } catch (Exception $e) {
        throw new Exception("خطأ أثناء تحديث رصيد العميل: " . $e->getMessage());
    }

    // تسجيل حركة البيع في تقرير المندوب
    try {
        $stmtName = $pdo->prepare("SELECT customerName FROM customers WHERE customerID = :customerID");
        $stmtName->execute([':customerID' => $customerID]);
        $customerName = $stmtName->fetchColumn();
// `serialShift`, `shiftStart`, `shiftEnd`, `action`, `invoiceID`, `customerID`, `amount`, `lat`, `lng`, `notes`
        $stmtReport = $pdo->prepare("
            INSERT INTO $reportTable (invoiceID, customerID, customerName, amount, lat, lng, notes)
            VALUES (:invoiceID, :customerID, :customerName, :totalAmount, :latitude, :longitude, 'بيع')
        ");
        $stmtReport->execute([
            ':invoiceID' => $invoiceID,
            ':customerID' => $customerID,
            ':customerName' => $customerName,
            ':totalAmount' => $grandTotal,
            ':latitude' => $latitude,
            ':longitude' => $longitude
        ]);
    } catch (Exception $e) {
        throw new Exception("خطأ أثناء تسجيل التقرير للمندوب: " . $e->getMessage());
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'تم إغلاق الفاتورة وتحديث المخزون والتقارير بنجاح']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'خطأ أثناء إغلاق الفاتورة: ' . $e->getMessage()]);
    exit;
}
