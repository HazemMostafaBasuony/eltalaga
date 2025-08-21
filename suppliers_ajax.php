<?php
// AJAX handler for suppliers operations
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

include('hmb/conn.php');
session_start();

// Helper function to return JSON response
function jsonResponse($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    try {
        switch ($_POST['action']) {
            case 'add_supplier':
                addSupplier();
                break;
            case 'update_supplier':
                updateSupplier();
                break;
            case 'delete_supplier':
                deleteSupplier();
                break;
            case 'get_supplier':
                getSupplier();
                break;
            case 'get_items':
                getItems();
                break;
            case 'save_supplier_items':
                saveSupplierItems();
                break;
            default:
                jsonResponse(['success' => false, 'message' => 'عملية غير صالحة']);
        }
    } catch (Exception $e) {
        error_log("Suppliers AJAX Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'حدث خطأ في النظام']);
    }
    exit;
}

// If not POST request or no action, return error
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    jsonResponse(['success' => false, 'message' => 'طلب غير صالح']);
    exit;
}

function addSupplier() {
    global $conn;
    
    try {
        $type = $_POST['type'] ?? '';
        $numberRC = $_POST['numberRC'] ?? '';
        $numberTax = $_POST['numberTax'] ?? '';
        $supplierName = $_POST['supplierName'] ?? '';
        $street = $_POST['street'] ?? '';
        $area = $_POST['area'] ?? '';
        $city = $_POST['city'] ?? '';
        $country = $_POST['country'] ?? '';
        $building = $_POST['building'] ?? '';
        $postCode = $_POST['postCode'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $startDate = $_POST['startDate'] ?? date('Y-m-d');
        $lastInvoiceDate = $_POST['lastInvoiceDate'] ?? date('Y-m-d');
        $totalDebt = (float)($_POST['totalDebt'] ?? 0);
        $wantDebt = (float)($_POST['wantDebt'] ?? 0);
        $dateWantedDebt = $_POST['dateWantedDebt'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        
        // Validate required fields (only name, type, and phone are required)
        if (empty($supplierName) || empty($type) || empty($phone)) {
            jsonResponse(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة (الاسم، النوع، الهاتف)']);
            return;
        }
        
        $sql = "INSERT INTO suppliers (type, numberRC, numberTax, supplierName, street, area, city, country, bulding, postCode, phone, email, startDate, lastInvoiceDate, totalDebt, wantDebt, dateWantedDebt, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssddss", $type, $numberRC, $numberTax, $supplierName, $street, $area, $city, $country, $building, $postCode, $phone, $email, $startDate, $lastInvoiceDate, $totalDebt, $wantDebt, $dateWantedDebt, $notes);
        
        if ($stmt->execute()) {
            jsonResponse(['success' => true, 'message' => 'تم إضافة المورد بنجاح']);
        } else {
            jsonResponse(['success' => false, 'message' => 'خطأ في إضافة المورد: ' . $conn->error]);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
    }
}

function updateSupplier() {
    global $conn;
    
    try {
        $supplierID = (int)($_POST['supplierID'] ?? 0);
        $type = $_POST['type'] ?? '';
        $numberRC = $_POST['numberRC'] ?? '';
        $numberTax = $_POST['numberTax'] ?? '';
        $supplierName = $_POST['supplierName'] ?? '';
        $street = $_POST['street'] ?? '';
        $area = $_POST['area'] ?? '';
        $city = $_POST['city'] ?? '';
        $country = $_POST['country'] ?? '';
        $building = $_POST['building'] ?? '';
        $postCode = $_POST['postCode'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $startDate = $_POST['startDate'] ?? date('Y-m-d');
        $lastInvoiceDate = $_POST['lastInvoiceDate'] ?? date('Y-m-d');
        $totalDebt = (float)($_POST['totalDebt'] ?? 0);
        $wantDebt = (float)($_POST['wantDebt'] ?? 0);
        $dateWantedDebt = $_POST['dateWantedDebt'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        
        // Validate required fields (only name, type, and phone are required)
        if ($supplierID <= 0 || empty($supplierName) || empty($type) || empty($phone)) {
            jsonResponse(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة (الاسم، النوع، الهاتف)']);
            return;
        }
        
        $sql = "UPDATE suppliers SET type=?, numberRC=?, numberTax=?, supplierName=?, street=?, area=?, city=?, country=?, bulding=?, postCode=?, phone=?, email=?, startDate=?, lastInvoiceDate=?, totalDebt=?, wantDebt=?, dateWantedDebt=?, notes=? WHERE supplierID=?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssddssi", $type, $numberRC, $numberTax, $supplierName, $street, $area, $city, $country, $building, $postCode, $phone, $email, $startDate, $lastInvoiceDate, $totalDebt, $wantDebt, $dateWantedDebt, $notes, $supplierID);
        
        if ($stmt->execute()) {
            jsonResponse(['success' => true, 'message' => 'تم تحديث المورد بنجاح']);
        } else {
            jsonResponse(['success' => false, 'message' => 'خطأ في تحديث المورد: ' . $conn->error]);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
    }
}

function deleteSupplier() {
    global $conn;
    
    try {
        $supplierID = (int)($_POST['supplierID'] ?? 0);
        
        if ($supplierID <= 0) {
            jsonResponse(['success' => false, 'message' => 'معرف المورد غير صحيح']);
            return;
        }
        
        // Delete supplier items first
        $sql1 = "DELETE FROM suppliersItems WHERE supplierID = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("i", $supplierID);
        $stmt1->execute();
        
        // Delete supplier
        $sql2 = "DELETE FROM suppliers WHERE supplierID = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $supplierID);
        
        if ($stmt2->execute()) {
            jsonResponse(['success' => true, 'message' => 'تم حذف المورد بنجاح']);
        } else {
            jsonResponse(['success' => false, 'message' => 'خطأ في حذف المورد: ' . $conn->error]);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
    }
}

function getSupplier() {
    global $conn;
    
    try {
        $supplierID = (int)($_POST['supplierID'] ?? 0);
        
        if ($supplierID <= 0) {
            jsonResponse(['success' => false, 'message' => 'معرف المورد غير صحيح']);
            return;
        }
        
        $sql = "SELECT * FROM suppliers WHERE supplierID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $supplierID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            jsonResponse(['success' => true, 'supplier' => $row]);
        } else {
            jsonResponse(['success' => false, 'message' => 'المورد غير موجود']);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
    }
}

function getItems() {
    global $conn;
    
    try {
        $supplierID = isset($_POST['supplierID']) ? (int)$_POST['supplierID'] : null;
        
        // Get all items from itemsCard
        $sql = "SELECT * FROM itemsCard ORDER BY mainGroup, subGroup, itemName";
        $result = $conn->query($sql);
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        // Get selected items for supplier if supplierID is provided
        $selectedItems = [];
        if ($supplierID && $supplierID > 0) {
            $sql2 = "SELECT * FROM suppliersItems WHERE supplierID = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $supplierID);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            
            while ($row = $result2->fetch_assoc()) {
                $selectedItems[] = $row;
            }
        }
        
        jsonResponse(['success' => true, 'items' => $items, 'selectedItems' => $selectedItems]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
    }
}

function saveSupplierItems() {
    global $conn;
    
    try {
        $supplierID = (int)($_POST['supplierID'] ?? 0);
        $items = json_decode($_POST['items'] ?? '[]', true);
        
        if ($supplierID <= 0) {
            jsonResponse(['success' => false, 'message' => 'معرف المورد غير صحيح']);
            return;
        }
        
        // Delete existing items for this supplier
        $sql1 = "DELETE FROM suppliersItems WHERE supplierID = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("i", $supplierID);
        $stmt1->execute();
        
        // Insert new items
        if (!empty($items)) {
            $sql2 = "INSERT INTO suppliersItems (itemID, supplierID, itemName, unitL, fL2M, unitM, fM2S, unitS, mainGroup, subGroup, stok, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt2 = $conn->prepare($sql2);
            
            foreach ($items as $item) {
                $itemID = (int)($item['itemID'] ?? 0);
                $itemName = $item['itemName'] ?? '';
                $unitL = $item['unitL'] ?? '';
                $fL2M = (float)($item['fL2M'] ?? 0);
                $unitM = $item['unitM'] ?? '';
                $fM2S = (float)($item['fM2S'] ?? 0);
                $unitS = $item['unitS'] ?? '';
                $mainGroup = $item['mainGroup'] ?? '';
                $subGroup = $item['subGroup'] ?? '';
                $stok = (float)($item['stok'] ?? 0);
                $profit = (float)($item['profit'] ?? 0);
                
                $stmt2->bind_param("iissdssssdd", $itemID, $supplierID, $itemName, $unitL, $fL2M, $unitM, $fM2S, $unitS, $mainGroup, $subGroup, $stok, $profit);
                $stmt2->execute();
            }
        }
        
        jsonResponse(['success' => true, 'message' => 'تم حفظ المواد بنجاح']);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
    }
}

$conn->close();
?>