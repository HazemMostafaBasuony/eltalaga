<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

include('../hmb/conn_pdo.php');

/**
 * تحضير لوج بسيط للتتبع (اختياري)
 */
function log_debug($msg) {
    file_put_contents(__DIR__ . '/invoice_debug.log', '[' . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

/**
 * دالة حساب أسعار الوحدات الثلاث (L/M/S) بناءً على سعر وحدة مُعطاة
 */
function calculatePrices($chosenUnit, $chosenPrice, $unitL, $unitM, $unitS, $fL2M, $fM2S): array {
    $priceL = $priceM = $priceS = 0.0;

    if ($chosenUnit === $unitL) {
        $priceL = $chosenPrice;
        $priceM = $fL2M > 0 ? ($priceL / $fL2M) : 0;
        $priceS = ($fM2S > 0) ? ($priceM / $fM2S) : 0;
    } elseif ($chosenUnit === $unitM) {
        $priceM = $chosenPrice;
        $priceL = $priceM * $fL2M;
        $priceS = ($fM2S > 0) ? ($priceM / $fM2S) : 0;
    } elseif ($chosenUnit === $unitS) {
        $priceS = $chosenPrice;
        $priceM = $priceS * $fM2S;
        $priceL = $priceM * $fL2M;
    }
    return [$priceL, $priceM, $priceS];
}

/**
 * إنشاء جدول المندوب إن لم يكن موجودًا.
 * نعمله قبل الـ Transaction لتجنب implicit commit.
 */
function ensureSalesmanTable(PDO $pdo, int $salesmanID): string {
    $tableName = 'itemscard' . $salesmanID; // نفس التسمية اللي بتستخدمها
    // تأكد أنه رقم فقط
    $salesmanID = intval($salesmanID);
    $stmt = $pdo->prepare("SHOW TABLES LIKE :t");
    $stmt->execute([':t' => $tableName]);

    if ($stmt->rowCount() === 0) {
        $sql = "
            CREATE TABLE `$tableName` (
                `itemID` INT(30) NOT NULL,
                `itemName` VARCHAR(255) NOT NULL,
                `unitL` VARCHAR(30) NOT NULL,
                `fL2M` FLOAT UNSIGNED NOT NULL,
                `unitM` VARCHAR(30) NOT NULL,
                `fM2S` FLOAT UNSIGNED NOT NULL,
                `unitS` VARCHAR(30) NOT NULL,
                `mainGroup` VARCHAR(255) NOT NULL DEFAULT 'ثلاجة',
                `subGroup` VARCHAR(255) NOT NULL,
                `stock` FLOAT UNSIGNED NOT NULL DEFAULT 0,
                `profit` FLOAT UNSIGNED NOT NULL DEFAULT 0,
                `priceL` FLOAT UNSIGNED NOT NULL DEFAULT 0,
                `priceM` FLOAT UNSIGNED NOT NULL DEFAULT 0,
                `priceS` FLOAT UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`itemID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $pdo->exec($sql);
        log_debug("Created salesman table: $tableName");
    }
    return $tableName;
}

/**
 * تحويل الكمية إلى وحدة L (الكبيرة) بناءً على وحدة الإدخال
 */
function toLargeUnitCount(float $count, string $unit, string $unitL, string $unitM, string $unitS, float $fL2M, float $fM2S): float {
    // الأساس: المخزون في itemscard محسوب بوحدة L
    if ($unit === $unitL) {
        return $count; // بالفعل L
    } elseif ($unit === $unitM) {
        // من M إلى L → القسمة على fL2M
        return $fL2M > 0 ? ($count / $fL2M) : $count;
    } elseif ($unit === $unitS) {
        // من S إلى L → (الكمية / fM2S) / fL2M
        if ($fM2S > 0 && $fL2M > 0) {
            return ($count / $fM2S) / $fL2M;
        }
        return $count;
    }
    return $count; // fallback
}

/**
 * إدراج/تحديث أصناف المندوب (داخل الـ Transaction)
 * - المخزون يُخزن بوحدة L
 * - الأسعار يتم حسابها من سعر ووحدة الفاتورة
 * - ON DUPLICATE KEY: يزود المخزون ويحدّث الأسعار بآخر قيمة
 */
function upsertSalesmanItems(PDO $pdo, string $salesmanTable, array $items) {
    // هنحتاج بيانات الصنف من itemscard
    $stmtGet = $pdo->prepare("
        SELECT
            itemID, itemName, unitL, unitM, unitS,
            COALESCE(fL2M, fl2M) AS fL2M,
            COALESCE(fM2S, fm2S) AS fM2S,
            mainGroup, subGroup, profit
        FROM itemscard
        WHERE itemID = :itemID
    ");

    $stmtUpsert = $pdo->prepare("
        INSERT INTO `$salesmanTable`
            (itemID, itemName, unitL, fL2M, unitM, fM2S, unitS, mainGroup, subGroup, stock, profit, priceL, priceM, priceS)
        VALUES
            (:itemID, :itemName, :unitL, :fL2M, :unitM, :fM2S, :unitS, :mainGroup, :subGroup, :stockL, :profit, :priceL, :priceM, :priceS)
        ON DUPLICATE KEY UPDATE
            stock  = stock + VALUES(stock),
            priceL = VALUES(priceL),
            priceM = VALUES(priceM),
            priceS = VALUES(priceS),
            profit = VALUES(profit),
            itemName = VALUES(itemName),
            unitL = VALUES(unitL),
            unitM = VALUES(unitM),
            unitS = VALUES(unitS),
            fL2M = VALUES(fL2M),
            fM2S = VALUES(fM2S),
            mainGroup = VALUES(mainGroup),
            subGroup  = VALUES(subGroup)
    ");

    foreach ($items as $it) {
        // لازم يكون عندنا: itemID, itemName, unit, price, count
        if (empty($it['itemID']) || !is_numeric($it['itemID'])) {
            throw new Exception("معرف صنف غير صالح داخل مزامنة المندوب");
        }
        if (!isset($it['unit'], $it['price'], $it['count'])) {
            throw new Exception("بيانات صنف غير كاملة داخل مزامنة المندوب");
        }

        $stmtGet->execute([':itemID' => $it['itemID']]);
        $info = $stmtGet->fetch(PDO::FETCH_ASSOC);
        if (!$info) {
            throw new Exception("الصنف {$it['itemID']} غير موجود في itemscard");
        }

        $unitL = $info['unitL'];
        $unitM = $info['unitM'];
        $unitS = $info['unitS'];
        $fL2M  = floatval($info['fL2M'] ?: 1);
        $fM2S  = floatval($info['fM2S'] ?: 1);

        // تحويل كمية الفاتورة لوحدة L
        $countL = toLargeUnitCount(floatval($it['count']), $it['unit'], $unitL, $unitM, $unitS, $fL2M, $fM2S);

        // حساب أسعار L/M/S من سعر و وحدة الفاتورة
        [$priceL, $priceM, $priceS] = calculatePrices($it['unit'], floatval($it['price']), $unitL, $unitM, $unitS, $fL2M, $fM2S);

        $stmtUpsert->execute([
            ':itemID'   => $info['itemID'],
            ':itemName' => $info['itemName'],
            ':unitL'    => $unitL,
            ':fL2M'     => $fL2M,
            ':unitM'    => $unitM,
            ':fM2S'     => $fM2S,
            ':unitS'    => $unitS,
            ':mainGroup'=> $info['mainGroup'],
            ':subGroup' => $info['subGroup'],
            ':stockL'   => $countL,
            ':profit'   => $info['profit'],
            ':priceL'   => $priceL,
            ':priceM'   => $priceM,
            ':priceS'   => $priceS
        ]);
    }
}

/* =======================
   نقطة الدخول الرئيسية
   ======================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مدعومة']);
    exit;
}

$raw = file_get_contents('php://input');
$invoiceData = json_decode($raw, true);

log_debug("Incoming: " . $raw);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'خطأ في تنسيق البيانات المرسلة', 'json_error' => json_last_error_msg()]);
    exit;
}

// التحقق من الحقول المطلوبة
$required = ['salesmaneID','userID','action','state','paymentMethod','total','discount','totalDue','vat','generalTotal','notes','paidAmount','remainingAmount','items'];
foreach ($required as $k) {
    if (!array_key_exists($k, $invoiceData)) {
        echo json_encode(['success' => false, 'message' => "حقل مطلوب مفقود: $k"]);
        exit;
    }
}
if (!is_array($invoiceData['items']) || empty($invoiceData['items'])) {
    echo json_encode(['success' => false, 'message' => 'قائمة العناصر فارغة أو غير صالحة']);
    exit;
}
if (!is_numeric($invoiceData['salesmaneID']) || $invoiceData['salesmaneID'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف المندوب غير صالح']);
    exit;
}

try {
    // 0) تأكد من وجود جدول المندوب (خارج الترانزكشن)
    $salesmanTable = ensureSalesmanTable($pdo, intval($invoiceData['salesmaneID']));

    // 1) ابدأ المعاملة
    $pdo->beginTransaction();

    // 2) إدراج الفاتورة
    $sqlInvoice = "INSERT INTO invoices (
        fromID, ToID, fromType, toType, action, state, paymentMethod,
        total, discount, totalDue, vat, generalTotal,
        notes, paidAmount, remainingAmount
    ) VALUES (
        :userID, :salesmaneID, 'store', 'customer', :action, :state, :paymentMethod,
        :total, :discount, :totalDue, :vat, :generalTotal,
        :notes, :paidAmount, :remainingAmount
    )";
    $stmtInvoice = $pdo->prepare($sqlInvoice);
    $stmtInvoice->execute([
        ':userID'          => $invoiceData['userID'],
        ':salesmaneID'     => $invoiceData['salesmaneID'],
        ':action'          => $invoiceData['action'],
        ':state'           => $invoiceData['state'],
        ':paymentMethod'   => $invoiceData['paymentMethod'],
        ':total'           => $invoiceData['total'],
        ':discount'        => $invoiceData['discount'],
        ':totalDue'        => $invoiceData['totalDue'],
        ':vat'             => $invoiceData['vat'],
        ':generalTotal'    => $invoiceData['generalTotal'],
        ':notes'           => $invoiceData['notes'],
        ':paidAmount'      => $invoiceData['paidAmount'],
        ':remainingAmount' => $invoiceData['remainingAmount']
    ]);
    $invoiceID = $pdo->lastInsertId();

    // 3) تحضير استعلامات حركة الأصناف + خصم مخزون المخزن
    $stmtItemSelect = $pdo->prepare("
        SELECT itemID, itemName, unitL, unitM, unitS,
               COALESCE(fL2M, fl2M) AS fL2M,
               COALESCE(fM2S, fm2S) AS fM2S,
               stock
        FROM itemscard
        WHERE itemID = :itemID
    ");
    $stmtItemAction = $pdo->prepare("
        INSERT INTO itemaction (itemID, action, count, price, invoiceID, itemName, unit)
        VALUES (:itemID, 'transfer', :countL, :priceL, :invoiceID, :itemName, :unit)
    ");
    $stmtStockUpdate = $pdo->prepare("UPDATE itemscard SET stock = stock - :countL WHERE itemID = :itemID");

    foreach ($invoiceData['items'] as $item) {
        // تحقق المينيموم
        if (empty($item['itemID']) || !is_numeric($item['itemID'])) {
            throw new Exception("معرف الصنف غير صالح: " . json_encode($item, JSON_UNESCAPED_UNICODE));
        }
        if (!isset($item['price']) || !is_numeric($item['price'])) {
            throw new Exception("سعر الصنف غير صالح: " . json_encode($item, JSON_UNESCAPED_UNICODE));
        }
        if (!isset($item['count']) || !is_numeric($item['count'])) {
            throw new Exception("كمية الصنف غير صالحة: " . json_encode($item, JSON_UNESCAPED_UNICODE));
        }
        if (empty($item['unit'])) {
            throw new Exception("وحدة الصنف غير محددة: " . json_encode($item, JSON_UNESCAPED_UNICODE));
        }

        // بيانات الصنف من الكرت الرئيسي
        $stmtItemSelect->execute([':itemID' => $item['itemID']]);
        $info = $stmtItemSelect->fetch(PDO::FETCH_ASSOC);
        if (!$info) {
            throw new Exception("الصنف غير موجود: {$item['itemID']}");
        }

        $unitL = $info['unitL'];
        $unitM = $info['unitM'];
        $unitS = $info['unitS'];
        $fL2M  = floatval($info['fL2M'] ?: 1);
        $fM2S  = floatval($info['fM2S'] ?: 1);

        // حساب الكمية بوحدة L لخصمها من مخزون المخزن
        $countL = toLargeUnitCount(floatval($item['count']), $item['unit'], $unitL, $unitM, $unitS, $fL2M, $fM2S);

        // تأكيد المخزون
        $currentStockL = floatval($info['stock']);
        if ($currentStockL < $countL) {
            throw new Exception("لا يوجد مخزون كافي للصنف {$item['itemID']} (المتاح L: $currentStockL، المطلوب L: $countL)");
        }

        // حساب سعر L (لتسجيله في حركة الصنف)، نحسب أسعار L/M/S ثم نأخذ priceL
        [$priceL, $priceM, $priceS] = calculatePrices($item['unit'], floatval($item['price']), $unitL, $unitM, $unitS, $fL2M, $fM2S);

        // 3.a) سجل الحركة في itemaction
        $stmtItemAction->execute([
            ':itemID'   => $item['itemID'],
            ':countL'   => $countL,
            ':priceL'   => $priceL,
            ':invoiceID'=> $invoiceID,
            ':itemName' => $info['itemName'],
            ':unit'     => $item['unit'] // ملاحظة: ده بيسجل وحدة البيع كما هي
        ]);

        // 3.b) خصم المخزون
        $stmtStockUpdate->execute([
            ':countL' => $countL,
            ':itemID' => $item['itemID']
        ]);
    }

    // 4) تحديث بيانات المندوب (إجمالي قيمة الجرد/التسليم)
    $stmtSalesmanUpdate = $pdo->prepare("
        UPDATE salesmane SET stockInventory = stockInventory + :total WHERE salesmaneID = :sid
    ");
    $stmtSalesmanUpdate->execute([
        ':total' => $invoiceData['generalTotal'],
        ':sid'   => $invoiceData['salesmaneID']
    ]);

    // 5) مزامنة أصناف المندوب (تُخزن الكميات بوحدة L)
    upsertSalesmanItems($pdo, $salesmanTable, $invoiceData['items']);

    // 6) COMMIT
    $pdo->commit();

    echo json_encode([
        'success'   => true,
        'message'   => 'تم حفظ الفاتورة ومزامنة أصناف المندوب بنجاح',
        'invoiceID' => $invoiceID
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    log_debug('ERROR: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ: ' . $e->getMessage()
    ]);
}
