<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نموذج فاتورة ضريبية</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f5f5f5;
        }
        .invoice-wrapper {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 15mm;
            position: relative;
        }
        .invoice-border {
            border: 2px solid #000;
            padding: 10mm;
            height: 100%;
            position: relative;
        }
        .invoice-container {
            width: 100%;
            height: 100%;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            align-items: flex-start;
        }
        .logo {
            width: 120px;
            height: auto;
            margin-left: 20px;
        }
        .company-info {
            text-align: right;
            flex-grow: 1;
        }
        .invoice-info {
            text-align: left;
        }
        .invoice-title {
            text-align: center;
            margin: 20px 0;
            font-size: 24px;
            font-weight: bold;
            border: 1px solid #000;
            padding: 10px;
            background-color: #f0f0f0;
        }
        .client-info, .bank-info {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .info-section {
            width: 48%;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #000;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .total-box {
            width: 300px;
            border: 1px solid #000;
            padding: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .watermark {
            position: absolute;
            opacity: 0.1;
            font-size: 80px;
            color: #000;
            transform: rotate(-45deg);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            z-index: -1;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .invoice-wrapper {
                padding: 0;
                margin: 0;
                width: 100%;
                height: 100%;
            }
            .no-print {
                display: none;
            }
        }
        .print-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px auto;
            display: block;
        }
        .print-btn:hover {
            background-color: #45a049;
        }
        .bordered-section {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center;">
        <button class="print-btn" onclick="window.print()">طباعة الفاتورة</button>
    </div>

    <div class="invoice-wrapper">
        <div class="invoice-border">
            <div class="watermark">فاتورة ضريبية</div>
            
            <div class="invoice-container">
                <div class="header">
                    <div class="company-info">
                        <div><strong>: السجل التجاري</strong> <span id="cr-no">5904618649</span></div>
                        <div><strong>: الرقم الضريبي</strong> <span id="vat-no">310483256200003</span></div>
                        <div><strong>: جوال</strong> <span id="mobile">0537759757 - 053330744</span></div>
                    </div>
                    <img src="assets/images/logo4.png" alt="Company Logo" class="logo">
                </div>

                <div class="invoice-title">
                    <h2>فاتورة ضريبية</h2>
                    <h3>TAX INVOICE</h3>
                </div>

                <div class="bordered-section">
                    <div class="client-info">
                        <div class="info-section">
                            <div class="info-label">اسم العميل:</div>
                            <div id="client-name">_______________</div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">الرقم الضريبي:</div>
                            <div id="client-vat">_______________</div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">رقم العميل:</div>
                            <div id="client-id">_______________</div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">رقم الطلب:</div>
                            <div id="po-number">_______________</div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">المدينة:</div>
                            <div id="client-city">_______________</div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">البلد:</div>
                            <div id="client-country">_______________</div>
                        </div>
                    </div>
                </div>

                <div class="bordered-section">
                    <table>
                        <thead>
                            <tr>
                                <th>رقم</th>
                                <th>البيان</th>
                                <th>الكمية</th>
                                <th>الوحدة</th>
                                <th>سعر الوحدة</th>
                                <th>خصم البند</th>
                                <th>معدل الضريبة</th>
                                <th>الضريبة</th>
                                <th>القيمة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>_____________</td>
                                <td>_____</td>
                                <td>_____</td>
                                <td>_____</td>
                                <td>_____</td>
                                <td>15%</td>
                                <td>_____</td>
                                <td>_____</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>_____________</td>
                                <td>_____</td>
                                <td>_____</td>
                                <td>_____</td>
                                <td>_____</td>
                                <td>15%</td>
                                <td>_____</td>
                                <td>_____</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>_____________</td>
                                <td>_____</td>
                                <td>_____</td>
                                <td>_____</td>
                                <td>_____</td>
                                <td>15%</td>
                                <td>_____</td>
                                <td>_____</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="totals">
                    <div class="total-box">
                        <div class="total-row">
                            <span>الإجمالي:</span>
                            <span id="total-gross">0.00 ر.س</span>
                        </div>
                        <div class="total-row">
                            <span>إجمالي الخصم:</span>
                            <span id="total-discount">0.00 ر.س</span>
                        </div>
                        <div class="total-row">
                            <span>ضريبة القيمة المضافة 15%:</span>
                            <span id="total-vat">0.00 ر.س</span>
                        </div>
                        <div class="total-row" style="font-weight: bold;">
                            <span>الصافي:</span>
                            <span id="grand-total">0.00 ر.س</span>
                        </div>
                    </div>
                </div>

                <div class="bordered-section">
                    <div class="bank-info">
                        <div class="info-section">
                            <div class="info-label">المدينة:</div>
                            <div>أبو عريش</div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">الرمز البريدي:</div>
                            <div>84717</div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">الرقم الإضافي:</div>
                            <div>6536</div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">رقم المبنى:</div>
                            <div>56036</div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">اسم الشارع:</div>
                            <div>طريق الملك فهد</div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">الحي:</div>
                            <div>الربيع</div>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <p>شكراً لتعاملكم معنا</p>
                    <p>للاستفسار: 0537759757 - 053330744</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // يمكنك إضافة JavaScript لملء البيانات ديناميكياً
        document.getElementById('invoice-date').textContent = new Date().toLocaleDateString('ar-SA');
        document.getElementById('invoice-number').textContent = 'INV-' + Math.floor(Math.random() * 10000);
        
        // مثال لحساب المجموع
        function calculateTotals() {
            // هنا يمكنك إضافة منطق لحساب المجاميع
            document.getElementById('total-gross').textContent = '1,000.00 ر.س';
            document.getElementById('total-discount').textContent = '100.00 ر.س';
            document.getElementById('total-vat').textContent = '135.00 ر.س';
            document.getElementById('grand-total').textContent = '1,035.00 ر.س';
        }
        
        calculateTotals();
    </script>
</body>
</html>