<?php 
$invoiceId = $_GET['invoiceId'] ?? null;
$ceNumber = isset($_SESSION['ceNumber']) ? $_SESSION['ceNumber'] : '310483256200003';
$taxNumber = isset($_SESSION['taxNumber']) ? $_SESSION['taxNumber']: '4031269136';


if (!$invoiceId) {
    die("معرف الفاتورة غير موجود.");
}

include '../hmb/conn.php';

// تحسين الأمان باستخدام prepared statement
$stmt = $conn->prepare("SELECT * FROM `invoices` WHERE `id` = ?");
$stmt->bind_param("i", $invoiceId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("الفاتورة غير موجودة.");
}

$row = $result->fetch_assoc();
$invoiceNumber = $row['invoiceNumber'];
$invoiceType = $row['invoiceType'];
$state = $row['state'];
$customerName = $row['customerName'];
$branch = $row['branch'];
$footerText = $row['footerText'];
$date = $row['date'];
$totalPrice = $row['totalPrice'] - $row['totalVat'];
$totalVat = $row['totalVat'];
$finalPrice = $totalPrice + $totalVat;
$shiftId = $row['shiftId'];

// جلب معلومات الوردية
$shiftStmt = $conn->prepare("SELECT * FROM `serialshift` WHERE `id` = ?");
$shiftStmt->bind_param("i", $shiftId);
$shiftStmt->execute();
$shiftResult = $shiftStmt->get_result();
$shiftData = $shiftResult->fetch_assoc();
$shiftSerial = $shiftData['shiftSerial'] ?? '';

// تحديد حالة الفاتورة
$stateText = '';
switch($state) {
    case 1: $stateText = 'فاتورة مفتوحة'; break;
    case 2: $stateText = 'فاتورة تمت'; break;
    case 3: $stateText = 'فاتورة مرتجعة'; break;
    default: $stateText = 'حالة غير معروفة';
}

// جلب بيانات المنتجات المرتبطة بالفاتورة
$itemsStmt = $conn->prepare("SELECT * FROM `invoice_items` WHERE `invoiceId` = ?");
$itemsStmt->bind_param("i", $invoiceId);
$itemsStmt->execute();
$resultItems = $itemsStmt->get_result();

$invoiceItems = [];
if ($resultItems->num_rows > 0) {
    while ($itemRow = $resultItems->fetch_assoc()) {
        $invoiceItems[] = [
            'itemName' => $itemRow['itemName'],
            'count' => $itemRow['count'],
            'price' => $itemRow['price'],
            'vat' => $itemRow['vat'],
            'totalPrice' => $itemRow['totalPrice'],
            'sectionPrint' => $itemRow['sectionPrint']
        ];
    }
} else {
    die("لا توجد منتجات مرتبطة بهذه الفاتورة.");
}

// جلب معلومات التوصيل إن وجدت
$deliveryInfo = null;
if ($invoiceType === 'توصيل') {
    $deliveryStmt = $conn->prepare("SELECT * FROM `customername` WHERE `customerName` = ?");
    $deliveryStmt->bind_param("s", $customerName);
    $deliveryStmt->execute();
    $deliveryResult = $deliveryStmt->get_result();
    
    if ($deliveryResult->num_rows > 0) {
        $deliveryInfo = $deliveryResult->fetch_assoc();
    }
}

// إعداد رسوم التوصيل
$deliveryFee = $deliveryInfo['priceDelivery'] ?? 0;
$deliveryTotal = $finalPrice + $deliveryFee;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm, initial-scale=1.0">
    <title>طباعة الفاتورة - <?php echo $invoiceNumber; ?></title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        
        body {
            font-family: Tahoma, Arial, sans-serif;
            width: 80mm;
            margin: 0;
            padding: 5mm;
            font-size: 13px;
            background: #fff;
            position: relative;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 40px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.1);
            z-index: -1;
            white-space: nowrap;
            pointer-events: none;
        }
        
        .company-header { 
            margin-bottom: 10px;
            text-align: center;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 8px;
        }
        
        .logo {
            max-width: 60px;
            height: auto;
        }
        
        .invoice-info { 
            margin-bottom: 10px; 
            font-size: 12px;
            padding: 8px;
            background: #f8f8f8;
            border-radius: 4px;
        }
        
        .invoice-info-row {
            display: flex;
            margin-bottom: 4px;
        }
        
        .invoice-info-label {
            font-weight: bold;
            width: 35%;
        }
        
        .invoice-info-value {
            width: 65%;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        th {
            background: #f2f2f2;
            padding: 6px 4px;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        
        td {
            padding: 4px;
            border: 1px solid #ddd;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px dashed #ddd;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            font-weight: bold;
        }
        
        .summary-value {
            text-align: left;
        }
        
        .total-row {
            font-weight: bold;
            font-size: 14px;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 2px solid #333;
        }
        
        .qr-container {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            border: 1px dashed #ccc;
            border-radius: 4px;
            background: #f9f9f9;
        }
        
        .qr-image {
            max-width: 100px;
            height: auto;
            margin: 0 auto;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #333;
            font-size: 11px;
        }
        
        .state-indicator {
            position: absolute;
            top: 5px;
            left: 5px;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .state-1 { background: #ffc107; color: #333; } /* مفتوحة */
        .state-2 { background: #28a745; color: white; } /* تمت */
        .state-3 { background: #dc3545; color: white; } /* مرتجعة */
        
        @media print {
            body { 
                width: 80mm; 
                margin: 0;
                padding: 5mm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- علامة مائية للحالة -->
    <div class="watermark"><?php echo $stateText; ?></div>
    
    <!-- مؤشر الحالة -->
    <div class="state-indicator state-<?php echo $state; ?>">
        <?php echo $stateText; ?>
    </div>
    
    <div class="company-header">
        <div class="logo-container">
            <img src="../assets/images/logo.png" alt="الشعار" class="logo">
        </div>
        <strong><?php echo htmlspecialchars($branch); ?></strong><br>
        رقم السجل: <?php echo $ceNumber; ?><br>
        الرقم الضريبي: <?php echo $taxNumber; ?><br>
        العنوان: مكة المكرمة - الجعرانة<br>
        الهاتف: 0538808408<br>
    </div> 
    
    <div class="invoice-info">
        <div class="invoice-info-row">
            <div class="invoice-info-label">رقم الفاتورة:</div>
            <div class="invoice-info-value"><?php echo htmlspecialchars($invoiceNumber); ?></div>
        </div>
        <div class="invoice-info-row">
            <div class="invoice-info-label">نوع الفاتورة:</div>
            <div class="invoice-info-value"><?php echo htmlspecialchars($invoiceType); ?></div>
        </div>
        <div class="invoice-info-row">
            <div class="invoice-info-label">الفرع:</div>
            <div class="invoice-info-value"><?php echo htmlspecialchars($branch); ?></div>
        </div>
        <div class="invoice-info-row">
            <div class="invoice-info-label">التاريخ:</div>
            <div class="invoice-info-value"><?php echo htmlspecialchars($date); ?></div>
        </div>
        <div class="invoice-info-row">
            <div class="invoice-info-label">العميل:</div>
            <div class="invoice-info-value"><?php echo htmlspecialchars($customerName); ?></div>
        </div>
        <div class="invoice-info-row">
            <div class="invoice-info-label">رقم الوردية:</div>
            <div class="invoice-info-value"><?php echo htmlspecialchars($shiftSerial); ?></div>
        </div>
    </div>
    
    <?php if ($invoiceType === 'توصيل' && $deliveryInfo): ?>
    <div class="qr-container">
        <img src="https://api.qrserver.com/v1/create-qr-code/?data=<?php 
            echo urlencode($deliveryInfo['qrAddress'] ?? 'العنوان غير مسجل'); 
            ?>&size=100x100" alt="QR Code" class="qr-image">
        <p>قم بمسح الرمز الضوئي للوصول إلى العنوان</p>
        <p>العنوان: <?php echo htmlspecialchars($deliveryInfo['address'] ?? 'العنوان غير مسجل'); ?></p>
    </div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th width="40%">المنتج</th>
                <th width="15%">الكمية</th>
                <th width="20%">السعر</th>
                <th width="25%">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($invoiceItems as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['itemName']); ?></td>
                <td style="text-align:center"><?php echo $item['count']; ?></td>
                <td style="text-align:left"><?php echo number_format($item['price'], 2); ?></td>
                <td style="text-align:left"><?php echo number_format($item['totalPrice'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="invoice-summary">
        <div class="summary-row">
            <div class="summary-label">الإجمالي:</div>
            <div class="summary-value"><?php echo number_format($totalPrice, 2); ?> ر.س</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">الضريبة:</div>
            <div class="summary-value"><?php echo number_format($totalVat, 2); ?> ر.س</div>
        </div>
        <div class="summary-row total-row">
            <div class="summary-label">المجموع النهائي:</div>
            <div class="summary-value"><?php echo number_format($finalPrice, 2); ?> ر.س</div>
        </div>
        
        <?php if ($invoiceType === 'توصيل' && $deliveryInfo): ?>
        <div class="summary-row">
            <div class="summary-label">رسوم التوصيل:</div>
            <div class="summary-value"><?php echo number_format($deliveryFee, 2); ?> ر.س</div>
        </div>
        <div class="summary-row total-row">
            <div class="summary-label">الإجمالي مع التوصيل:</div>
            <div class="summary-value"><?php echo number_format($deliveryTotal, 2); ?> ر.س</div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="footer-text">
        <?php echo nl2br(htmlspecialchars($footerText)); ?>
    </div>
    
    <div class="no-print" style="text-align:center; margin-top:20px;">
        <button onclick="window.print()" style="padding:10px 20px; background:#2196F3; color:white; border:none; border-radius:4px; cursor:pointer;">
            طباعة الفاتورة
        </button>
    </div>

    <script>
        window.onload = function() {
            // طباعة تلقائية عند التحميل
            setTimeout(function() {
                window.print();
            }, 500);
            
            // العودة بعد الطباعة
            window.onafterprint = function() {
                setTimeout(function() {
                    window.close();
                }, 1000);
            };
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>