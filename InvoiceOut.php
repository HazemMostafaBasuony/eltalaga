<?php
include('headAndFooter/head.php');

if (isset($_GET['invoiceID'])) {
    // تأمين المدخلات
    $invoiceID = mysqli_real_escape_string($conn, $_GET['invoiceID']);
    include('hmb/conn.php');

    // استعلام الفاتورة الأساسية
    $sql = "SELECT * FROM `invoices` WHERE `invoiceID` = '$invoiceID'";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die("خطأ في استعلام الفاتورة: " . mysqli_error($conn));
    }

    $invoice = mysqli_fetch_assoc($result);

    if (!$invoice) {
        die("لا توجد فاتورة بهذا الرقم");
    }

    // استعلام العناصر المرتبطة
    $itemeAction = [];
    $sql = "SELECT * FROM itemeAction WHERE invoiceID = '$invoiceID'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $itemeAction[] = $row;
        }
    }

    // استعلام بيانات الأصناف
    $items = [];
    if (!empty($itemeAction)) {
        $itemIds = array_column($itemeAction, 'itemID');
        $idsList = "'" . implode("','", $itemIds) . "'";
        
        $sql = "SELECT * FROM `itemscard` WHERE itemID IN ($idsList)";
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $items[$row['itemID']] = $row;
            }
        }
    }

    // استعلام بيانات الفرع
    $branch = [];
    if (isset($invoice['branchName'])) {
        $branchName = mysqli_real_escape_string($conn, $invoice['branchName']);
        $sql = "SELECT * FROM branch WHERE branchName='$branchName'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $branch = mysqli_fetch_assoc($result);
        }
    }

    // استعلام بيانات العميل
    $customer = [];
    if (isset($invoice['toID'])) {
        $toID = mysqli_real_escape_string($conn, $invoice['toID']);
        $sql = "SELECT * FROM customers WHERE customerID='$toID'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $customer = mysqli_fetch_assoc($result);
        }
    }

    mysqli_close($conn);

    // تحويل البيانات لـ JavaScript
    echo '<script>';
    echo 'const invoiceData = ' . json_encode([
        'invoice' => $invoice,
        'items' => $items,
        'itemActions' => $itemeAction,
        'branch' => $branch,
        'customer' => $customer
    ], JSON_UNESCAPED_UNICODE) . ';';
    echo '</script>';
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة ضريبية</title>
    <style>
        @media print {
            .no-print, .print-btn {
                display: none !important;
            }
            body {
                padding: 20px;
                font-size: 14px;
            }
            .card {
                border: none;
                box-shadow: none;
            }
            .table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5" dir="rtl">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <!-- الترويسة -->
                    <div class="card-header bg-success text-white">
                        <div class="row">
                            <div class="col-md-6">
                                <img src="assets/images/logo5.png" alt="شعار المؤسسة" class="img-fluid" style="max-height: 80px;" id="companyLogo">
                            </div>
                            <div class="col-md-6 text-end">
                                <h4 class="mt-3">فاتورة ضريبية</h4>
                                <p class="mb-0 fw-bold text-black" id="invoiceDetails">رقم: <span id="invoiceNumber"></span> | التاريخ: <span id="invoiceDate"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- بيانات المؤسسة والعميل -->
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="fw-bold">معلومات المؤسسة:</h5>
                                <p id="companyInfo">
                                    <i class="fas fa-building"></i> <span id="companyName"></span><br>
                                    <i class="fas fa-id-card"></i> السجل التجاري: <span id="commercialRegister"></span><br>
                                    <i class="fas fa-barcode"></i> الرقم الضريبي: <span id="vatNumber"></span><br>
                                    <i class="fas fa-map-marker-alt"></i> <span id="companyAddress"></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5 class="fw-bold">معلومات العميل:</h5>
                                <p id="clientInfo">
                                    <i class="fas fa-user"></i> الاسم: <span id="clientName"></span><br>
                                    <i class="fas fa-id-card"></i> الرقم الضريبي: <span id="clientVAT"></span><br>
                                    <i class="fas fa-map-marker-alt"></i> العنوان: <span id="clientAddress"></span><br>
                                    <i class="fas fa-phone"></i> الهاتف: <span id="clientPhone"></span>
                                </p>
                            </div>
                        </div>

                        <!-- جدول العناصر -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="30%">الوصف</th>
                                        <th width="7%">الكمية</th>
                                        <th width="10%">الوحدة</th>
                                        <th width="7%">السعر (ريال)</th>
                                        <th width="7%">الاجمالي (ريال)</th>
                                        <th width="7%">الخصم (ريال)</th>
                                        <th width="7%">الضريبة (15%)</th>
                                        <th width="10%">الإجمالي النهائي (ريال)</th>

                                    </tr>
                                </thead>
                                <tbody id="invoiceItems"></tbody>
                            </table>
                        </div>

                        <!-- الإجماليات -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="border p-3">
                                    <h6>ملاحظات:</h6>
                                    <p id="notes"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>الإجمالي قبل الضريبة:</th>
                                            <td id="subtotal">0.00 ريال</td>
                                        </tr>
                                        <tr>
                                            <th>ضريبة القيمة المضافة (15%):</th>
                                            <td id="vatAmount">0.00 ريال</td>
                                        </tr>
                                        <tr class="table-success fw-bold">
                                            <th>المبلغ المستحق:</th>
                                            <td id="grandTotal">0.00 ريال</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- التذييل مع QR Code -->
                    <div class="card-footer no-print">
                        <div class="row">
                            <div class="col-md-6 text-center">
                                <img src="" alt="QR Code" id="qrCode" class="img-fluid" style="max-width: 150px;">
                                <p class="mt-2">QR Code للتحقق من الفاتورة</p>
                            </div>
                            <div class="col-md-6 text-center">
                                <button class="btn btn-primary print-btn" onclick="window.print()">
                                    <i class="fas fa-print"></i> طباعة الفاتورة
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Script لملء البيانات -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof invoiceData !== 'undefined') {
                // ملء بيانات المؤسسة
                if (invoiceData.branch) {
                    document.getElementById('companyName').textContent = invoiceData.branch.branchName || 'غير متوفر';
                    document.getElementById('commercialRegister').textContent = invoiceData.branch.numberRC || 'غير متوفر';
                    document.getElementById('vatNumber').textContent = invoiceData.branch.numberTax || 'غير متوفر';
                    
                    const branchAddress = [
                        invoiceData.branch.street,
                        invoiceData.branch.area,
                        invoiceData.branch.city,
                        invoiceData.branch.country
                    ].filter(Boolean).join('، ');
                    
                    document.getElementById('companyAddress').textContent = branchAddress || 'غير متوفر';
                }

                // ملء بيانات العميل
                if (invoiceData.customer) {
                    document.getElementById('clientName').textContent = invoiceData.customer.customerName || 'غير متوفر';
                    document.getElementById('clientVAT').textContent = invoiceData.customer.numberTax || 'غير متوفر';
                    
                    const customerAddress = [
                        invoiceData.customer.street,
                        invoiceData.customer.area,
                        invoiceData.customer.city,
                        invoiceData.customer.country
                    ].filter(Boolean).join('، ');
                    
                    document.getElementById('clientAddress').textContent = customerAddress || 'غير متوفر';
                    document.getElementById('clientPhone').textContent = invoiceData.customer.phone || 'غير متوفر';
                }

                // ملء بيانات الفاتورة
                document.getElementById('invoiceNumber').textContent = invoiceData.invoice.invoiceID || 'غير متوفر';
                document.getElementById('invoiceDate').textContent = invoiceData.invoice.date || 'غير متوفر';
                document.getElementById('notes').textContent = invoiceData.invoice.notes || 'لا توجد ملاحظات';

                // ملء جدول العناصر وحساب الإجماليات
                const itemsTable = document.getElementById('invoiceItems');
                let subtotal = 0;

                invoiceData.itemActions.forEach(action => {
                    const item = invoiceData.items[action.itemID];
                    const quantity = action.unit || 1;
                    const price = action.price || 0;
                    const itemTotal = quantity * price;
                    const itemTax = itemTotal * 0.15;
                    subtotal += itemTotal;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${action.itemID || ''}</td>
                        <td>${item ? item.itemName : 'صنف غير معروف'}</td>
                        <td>${quantity}</td>
                        <td>${price.toFixed(2)}</td>
                        <td>${itemTax.toFixed(2)}</td>
                        <td>${(itemTotal + itemTax).toFixed(2)}</td>
                    `;
                    itemsTable.appendChild(row);
                });

                // حساب الإجماليات
                const vatAmount = subtotal * 0.15;
                const grandTotal = subtotal + vatAmount;

                document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' ريال';
                document.getElementById('vatAmount').textContent = vatAmount.toFixed(2) + ' ريال';
                document.getElementById('grandTotal').textContent = grandTotal.toFixed(2) + ' ريال';

                // توليد QR Code
                if (invoiceData.branch && invoiceData.invoice) {
                    const qrText = [
                        `فاتورة ضريبية`,
                        `رقم: ${invoiceData.invoice.invoiceID || ''}`,
                        `التاريخ: ${invoiceData.invoice.date || ''}`,
                        `الإجمالي: ${grandTotal.toFixed(2)} ريال`,
                        `الضريبة: ${vatAmount.toFixed(2)} ريال`,
                        `الرقم الضريبي: ${invoiceData.branch.numberTax || ''}`
                    ].join('\n');

                    new QRCode(document.getElementById("qrCode"), {
                        text: qrText,
                        width: 150,
                        height: 150
                    });
                }
            }
        });
    </script>

<?php
include('headAndFooter/footer.php');
?>