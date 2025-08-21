<?php
include('../hmb/conn_pdo.php');

$itemID = $_GET['itemID'] ?? null;
$userID = $_GET['userID'] ?? null;
$response = ['success' => false];

if ($itemID && $userID) {
    // جلب بيانات العنصر
    $stmt = $pdo->prepare("SELECT * FROM itemsCard WHERE itemID = :itemID");
    $stmt->execute(['itemID' => $itemID]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // جلب الفواتير الخاصة بالمستخدم بترتيب الأحدث
        $stmtInvoices = $pdo->prepare('SELECT invoiceID FROM invoices WHERE toID = ? AND state = 1 ORDER BY invoiceID DESC');
        $stmtInvoices->execute([$userID]);
        $invoiceIDs = $stmtInvoices->fetchAll(PDO::FETCH_COLUMN);

        $maxRow = null;
        $usedInvoiceID = null;

        foreach ($invoiceIDs as $invID) {
            // استعلام itemaction
            $stmAction = $pdo->prepare("SELECT price, discount, unit 
                                        FROM itemaction 
                                        WHERE invoiceID = :invoiceID AND itemID = :itemID
                                        ORDER BY price DESC
                                        LIMIT 1");
            $stmAction->execute([
                'invoiceID' => $invID,
                'itemID'    => $itemID
            ]);
            $result = $stmAction->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $maxRow = $result;
                $usedInvoiceID = $invID;
                break; // وقف عند أول فاتورة فيها نتيجة
            }
        }

        if (!$maxRow) {
            // مفيش نتائج → نرجع قيم بصفر
            $maxRow = [
                'price'    => 0,
                'discount' => 0,
                'unit'     => null
            ];
        }

        // تسجيل الحالة المشبوهة لو السعر صفر أو أقل من 0
        if ($maxRow['price'] <= 0) {
            $logStmt = $pdo->prepare("INSERT INTO suspicious_logs (itemID, userID, invoiceID, price, discount, unit, log_date) 
                                      VALUES (:itemID, :userID, :invoiceID, :price, :discount, :unit, NOW())");
            $logStmt->execute([
                'itemID'    => $itemID,
                'userID'    => $userID,
                'invoiceID' => $usedInvoiceID ?? 0,
                'price'     => $maxRow['price'],
                'discount'  => $maxRow['discount']||0,
                'unit'      => $maxRow['unit']
            ]);
        }

        $response = [
            'success'  => true,
            'itemID'   => $row['itemID'],
            'itemName' => $row['itemName'],
            'priceL'   => $row['priceL'],
            'priceM'   => $row['priceM'],
            'priceS'   => $row['priceS'],
            'unitL'    => $row['unitL'],
            'fL2M'     => $row['fL2M'],
            'fM2S'=> $row['fM2S'],
            'unitM'=> $row['unitM'],
            'unitS'    => $row['unitS'],
            'm_price'  => $maxRow['price'],
            'discount' => $maxRow['discount'],
            'unit_s'   => $maxRow['unit']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit();