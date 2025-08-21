<?php
session_start();

$currentShiftId = isset($_GET['idShift']) ? $_GET['idShift'] : 0;

if ($currentShiftId == 0) {
    header("Location: hmb/signIn.php");
    exit;
}
include('hmb/conn.php');

$sql = $conn->query("SELECT * FROM serialshift ORDER BY id");
$idShifts = [];
if ($sql && $sql->num_rows > 0) {
    while ($idRow = $sql->fetch_assoc()) {
        $idShifts[] = [
            'id' => $idRow['id'],
        ];
    }
}
// جلب رقم الوردية الحالية (افتراضي)
// $currentShiftId = 1; // يمكن تغيير هذا لاستقبال القيمة من الجلسة أو URL

// جلب معلومات الوردية
$shiftQuery = $conn->query("SELECT * FROM serialshift WHERE id = $currentShiftId");
$shiftData = $shiftQuery->fetch_assoc();
$shiftSerial = $shiftData['id'];
$startDate = $shiftData['date'];

$startCash=$shiftData['startCash'];

$realCash=doubleval($shiftData['realCash']);
$defrecedCash=doubleval($shiftData['defrecedCash']);


// جلب إجمالي المبيعات للوردية الحالية (حالة 2 = تامة البيع)
$totalSalesQuery = $conn->query("
    SELECT SUM(totalFinalPrice) as total_sales 
    FROM invoices 
    WHERE shiftId = $currentShiftId AND state = 2
");
$totalSales = $totalSalesQuery->fetch_assoc()['total_sales'];
$endCash= $totalSales +  $startCash;
// جلب المبيعات حسب الأقسام
$sectionsSalesQuery = $conn->query("
    SELECT i.sectionPrint, SUM(ii.finalPrice) as section_total, COUNT(ii.id) as item_count
    FROM invoice_items ii
    JOIN invoices inv ON ii.invoiceId = inv.id
    JOIN items i ON ii.itemId = i.idi
    WHERE inv.shiftId = $currentShiftId AND inv.state = 2
    GROUP BY i.sectionPrint
    ORDER BY section_total DESC
");

// جلب المبيعات حسب المنتجات
$productsSalesQuery = $conn->query("
    SELECT ii.itemName, SUM(ii.finalPrice) as product_total, SUM(ii.count) as total_count
    FROM invoice_items ii
    JOIN invoices inv ON ii.invoiceId = inv.id
    WHERE inv.shiftId = $currentShiftId AND inv.state = 2
    GROUP BY ii.itemName
    ORDER BY product_total DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير المبيعات - الوردية الحالية</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }
        
        :root {
            --primary: #2c7744;
            --primary-light: #5aaf70;
            --secondary: #f8f9fa;
            --text: #333;
            --border: #ddd;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
        }
        
        body {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);
            color: #333;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        header {
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, transparent 20%, rgba(255,255,255,0.1) 20%);
            background-size: 30px 30px;
            transform: rotate(30deg);
            z-index: 0;
        }
        
        .header-content {
            position: relative;
            z-index: 1;
        }
        
        h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .shift-info {
            background: rgba(255, 255, 255, 0.2);
            display: inline-block;
            padding: 10px 25px;
            border-radius: 30px;
            margin-top: 15px;
            font-size: 1.1rem;
        }
        
        .print-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
        }
        
        .print-btn {
            padding: 12px 25px;
            background: var(--info);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .print-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .print-btn.total {
            background: var(--success);
        }
        
        .print-btn.sections {
            background: var(--warning);
            color: #333;
        }
        
        .print-btn.products {
            background: var(--danger);
        }
        
        .content {
            padding: 30px;
        }
        
        .report-section {
            margin-bottom: 40px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        
        .section-header {
            background: linear-gradient(to right, #2c3e50, #4a6582);
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-title {
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .total-amount {
            font-size: 1.8rem;
            font-weight: bold;
            color: #FFD700;
        }
        
        .report-content {
            padding: 25px;
            background: white;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .summary-card h3 {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .summary-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        .summary-card .sub-value {
            font-size: 1rem;
            color: #777;
            margin-top: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 1rem;
        }
        
        th {
            background: #2c3e50;
            color: white;
            padding: 12px 15px;
            text-align: right;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f1f8ff;
        }
        
        .progress-container {
            height: 20px;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 8px;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            border-radius: 10px;
            text-align: center;
            color: white;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .chart-container {
            margin-top: 30px;
            height: 300px;
            position: relative;
        }
        
        .product-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            background: #e0e0e0;
            font-size: 0.9rem;
            margin-left: 5px;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #666;
            border-top: 1px solid #eee;
        }
        
        @media print {
            .print-controls {
                display: none;
            }
            
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                border-radius: 0;
            }
        }
        
        @media (max-width: 768px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .print-btn {
                width: 100%;
                justify-content: center;
            }
            
            table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1><i class="fas fa-chart-bar"></i> تقرير المبيعات - الوردية الحالية</h1>
                <div class="shift-info">
    
                    <i class="fas fa-calendar-alt"></i> رقم الوردية: <?php echo $shiftSerial; ?> | 
                    تاريخ البدء: <?php echo $startDate; ?>
                </div>
            </div>
        </header>
                  
        <div class="print-controls">
                  <input type="number" name="idShift" id="idShift">
                  <button onclick="getIdshift()" type="submit">عرض الورديه</button>
        </div>

        <div class="print-controls">        
              <button class="print-btn w3-blue" 
              onclick="closeShift(<?php echo intval($currentShiftId); ?>,
                                   <?php echo doubleval($endCash); ?>)">
                <i class="fas fa-inbox"></i> اغلاق الدوام
            </button>
        </div>


        
        <div class="print-controls">
            <button class="print-btn total" onclick="window.print()">
                <i class="fas fa-print"></i> طباعة التقرير الكامل
            </button>
            <button class="print-btn sections" onclick="printSection('sections-report')">
                <i class="fas fa-print"></i> طباعة تقرير الأقسام
            </button>
            <button class="print-btn products" onclick="printSection('products-report')">
                <i class="fas fa-print"></i> طباعة تقرير المنتجات
            </button>
        </div>

        <div class="content">
          <div class="summary-cards">
            
              <h3><i class="fas fa-money-bill-wave"></i> العهدة</h3>
              <div class="value"><?php echo number_format($startCash, 2); ?> ر.س</div>
              <div class="sub-value">للوردية الحالية</div>

          </div>
            <div class="summary-cards">
              
              <h3><i class="fas fa-money-bill-wave"></i> المصاريف</h3>
              <div class="value">0.00 ر.س</div>
              <div class="sub-value">للوردية الحالية</div>

          </div>
            <div class="summary-cards">
                
                <h3><i class="fas fa-money-bill-wave"></i> اجمالى الخازينه</h3>
                <div class="value"><?php echo number_format($endCash, 2); ?> ر.س</div>
                <div class="sub-value">للوردية الحالية</div>

             </div>
        </div>
        
        <div class="content">
            <!-- ملخص المبيعات -->
            <div class="summary-cards">
                <div class="summary-card">
                    <h3><i class="fas fa-money-bill-wave"></i> إجمالي المبيعات</h3>
                    <div class="value"><?php echo number_format($totalSales, 2); ?> ر.س</div>
                    <div class="sub-value">للوردية الحالية</div>
                </div>
                
                <div class="summary-card">
                    <h3><i class="fas fa-layer-group"></i> عدد الأقسام</h3>
                    <div class="value"><?php echo $sectionsSalesQuery->num_rows; ?></div>
                    <div class="sub-value">التي تم البيع فيها</div>
                </div>
                
                <div class="summary-card">
                    <h3><i class="fas fa-boxes"></i> المنتجات المباعة</h3>
                    <div class="value"><?php echo $productsSalesQuery->num_rows; ?></div>
                    <div class="sub-value">المنتجات الأكثر مبيعاً</div>
                </div>
            </div>
            
            <!-- تقرير المبيعات حسب الأقسام -->
            <div class="report-section" id="sections-report">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-th-large"></i> المبيعات حسب الأقسام
                    </div>
                    <div class="total-amount"><?php echo number_format($totalSales, 2); ?> ر.س</div>
                </div>
                
                <div class="report-content">
                    <table>
                        <thead>
                            <tr>
                                <th width="40%">القسم</th>
                                <th width="20%">عدد العناصر</th>
                                <th width="20%">إجمالي المبيعات</th>
                                <th width="20%">النسبة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sectionsSales = [];
                            $maxSectionSales = 0;
                            
                            if ($sectionsSalesQuery->num_rows > 0) {
                                while($section = $sectionsSalesQuery->fetch_assoc()) {
                                    $sectionsSales[] = $section;
                                    if ($section['section_total'] > $maxSectionSales) {
                                        $maxSectionSales = $section['section_total'];
                                    }
                                }
                                
                                foreach($sectionsSales as $section): 
                                    $percentage = ($maxSectionSales > 0) ? ($section['section_total'] / $maxSectionSales) * 100 : 0;
                            ?>
                            <tr>
                                <td><?php echo $section['sectionPrint']; ?></td>
                                <td><?php echo $section['item_count']; ?></td>
                                <td><?php echo number_format($section['section_total'], 2); ?> ر.س</td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%">
                                            <?php echo round($percentage, 1); ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; 
                            } else { ?>
                            <tr>
                                <td colspan="4" class="text-center">لا توجد بيانات</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    
                    <div class="chart-container">
                        <canvas id="sectionsChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- تقرير المبيعات حسب المنتجات -->
            <div class="report-section" id="products-report">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-box"></i> المنتجات الأكثر مبيعاً
                    </div>
                    <div class="total-amount">الـ 10 الأوائل</div>
                </div>
                
                <div class="report-content">
                    <table>
                        <thead>
                            <tr>
                                <th width="40%">اسم المنتج</th>
                                <th width="20%">الكمية المباعة</th>
                                <th width="20%">إجمالي المبيعات</th>
                                <th width="20%">متوسط السعر</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $productsSales = [];
                            
                            if ($productsSalesQuery->num_rows > 0) {
                                while($product = $productsSalesQuery->fetch_assoc()) {
                                    $productsSales[] = $product;
                                }
                                
                                foreach($productsSales as $product): 
                                    $avgPrice = ($product['total_count'] > 0) ? 
                                        $product['product_total'] / $product['total_count'] : 0;
                            ?>
                            <tr>
                                <td><?php echo $product['itemName']; ?></td>
                                <td><?php echo $product['total_count']; ?> <span class="product-badge">قطعة</span></td>
                                <td><?php echo number_format($product['product_total'], 2); ?> ر.س</td>
                                <td><?php echo number_format($avgPrice, 2); ?> ر.س</td>
                            </tr>
                            <?php endforeach; 
                            } else { ?>
                            <tr>
                                <td colspan="4" class="text-center">لا توجد بيانات</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    
                    <div class="chart-container">
                        <canvas id="productsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>تم إنشاء التقرير في <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // طباعة قسم معين
        function printSection(sectionId) {
            const printContent = document.getElementById(sectionId).innerHTML;
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = printContent;
            window.print();// إجمالي المبيعات
            document.body.innerHTML = originalContent;
            location.reload();
        }
        
        // مخطط الأقسام
        document.addEventListener('DOMContentLoaded', function() {
            // بيانات الأقسام
            const sectionsData = {
                labels: [<?php 
                    if (!empty($sectionsSales)) {
                        echo "'" . implode("','", array_column($sectionsSales, 'sectionPrint')) . "'";
                    }
                ?>],
                datasets: [{
                    label: 'المبيعات حسب الأقسام',
                    data: [<?php 
                        if (!empty($sectionsSales)) {
                            echo implode(',', array_column($sectionsSales, 'section_total'));
                        }
                    ?>],
                    backgroundColor: [
                        'rgba(44, 119, 68, 0.7)',
                        'rgba(41, 128, 185, 0.7)',
                        'rgba(142, 68, 173, 0.7)',
                        'rgba(230, 126, 34, 0.7)',
                        'rgba(231, 76, 60, 0.7)',
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(155, 89, 182, 0.7)',
                        'rgba(241, 196, 15, 0.7)',
                        'rgba(26, 188, 156, 0.7)',
                        'rgba(149, 165, 166, 0.7)'
                    ],
                    borderColor: [
                        'rgba(44, 119, 68, 1)',
                        'rgba(41, 128, 185, 1)',
                        'rgba(142, 68, 173, 1)',
                        'rgba(230, 126, 34, 1)',
                        'rgba(231, 76, 60, 1)',
                        'rgba(52, 152, 219, 1)',
                        'rgba(155, 89, 182, 1)',
                        'rgba(241, 196, 15, 1)',
                        'rgba(26, 188, 156, 1)',
                        'rgba(149, 165, 166, 1)'
                    ],
                    borderWidth: 1
                }]
            };
            
            // مخطط الأقسام
            const sectionsCtx = document.getElementById('sectionsChart').getContext('2d');
            const sectionsChart = new Chart(sectionsCtx, {
                type: 'bar',
                data: sectionsData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    family: 'Tajawal',
                                    size: 14
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'توزيع المبيعات حسب الأقسام',
                            font: {
                                family: 'Tajawal',
                                size: 16,
                                weight: 'bold'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `المبيعات: ${context.parsed.y.toFixed(2)} ر.س`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(2) + ' ر.س';
                                }
                            }
                        }
                    }
                }
            });
            
            // بيانات المنتجات
            const productsData = {
                labels: [<?php 
                    if (!empty($productsSales)) {
                        echo "'" . implode("','", array_column($productsSales, 'itemName')) . "'";
                    }
                ?>],
                datasets: [{
                    label: 'الكمية المباعة',
                    data: [<?php 
                        if (!empty($productsSales)) {
                            echo implode(',', array_column($productsSales, 'total_count'));
                        }
                    ?>],
                    backgroundColor: 'rgba(44, 119, 68, 0.7)',
                    borderColor: 'rgba(44, 119, 68, 1)',
                    borderWidth: 1
                }]
            };
            
            // مخطط المنتجات
            const productsCtx = document.getElementById('productsChart').getContext('2d');
            const productsChart = new Chart(productsCtx, {
                type: 'bar',
                data: productsData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    family: 'Tajawal',
                                    size: 14
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'المنتجات الأكثر مبيعاً',
                            font: {
                                family: 'Tajawal',
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });


        function getIdshift(){
          var idShift = document.getElementById('idShift').value;
          window.location.replace("report.php?idShift=" +idShift);
        }



        function closeShift(idShift,endCash){   
              var xhr = new XMLHttpRequest();

              xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    window.print()                  
                    
                }
              }
                xhr.open("GET", "report/closeShift.php?idShift=" + idShift + "&endCash=" + endCash, true);
              xhr.send()

        }

    </script>
</body>
</html>
<?php
$conn->close();
?>