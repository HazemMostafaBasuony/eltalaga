<!DOCTYPE html>
<html lang="ar" dir="rtl"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة ضريبية / TAX Invoice</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', 'Roboto', sans-serif; /* Prefer Tajawal for Arabic, then Roboto */
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .invoice-container {
            width: 850px; /* Slightly wider to accommodate content better */
            background-color: #fff;
            padding: 20px;
            border: 2px solid #000; /* Main border for the invoice */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
        }

        /* General box styling */
        .box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .qr-code-box {
            border: 1px solid #000;
            padding: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 120px; /* Adjust size */
            height: 120px; /* Adjust size */
            background-color: #f0f0f0; /* Placeholder for QR background */
            position: relative; /* For the QR image */
        }
        .qr-code-box img {
             width: 100%; /* Make QR code image fill its box */
             height: 100%;
             object-fit: contain; /* Ensure the image is contained within its box without cropping */
        }
        .company-details {
            flex-grow: 1;
            margin-right: 15px; /* Space between QR and company info */
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Two columns for pairs of info */
            gap: 5px 15px; /* Row and column gap */
            font-size: 0.85em;
        }
        .company-details .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 0;
        }
        .company-details .item span:first-child {
            font-weight: bold;
            color: #333;
        }
        .company-logo-info {
            display: flex;
            flex-direction: column;
            align-items: center; /* Center align logo and names */
            flex-shrink: 0; /* Prevent shrinking */
        }
        .company-logo-info img {
            max-width: 150px;
            height: auto;
            margin-bottom: 5px;
        }
        .company-logo-info p {
            margin: 0;
            line-height: 1.2;
            font-size: 0.9em;
            text-align: center;
        }
        .company-logo-info p.arabic {
            font-size: 1.1em;
            font-weight: bold;
        }
        .company-logo-info p.english {
            font-size: 0.9em;
            font-style: italic;
        }

        /* Invoice Type Section */
        .invoice-type-box {
            text-align: center;
            font-size: 1.6em;
            font-weight: bold;
            padding: 8px 0;
            margin-bottom: 10px;
            background-color: #f8f8f8;
        }
        .invoice-type-box span {
            display: inline-block;
            padding: 0 10px;
            line-height: 1;
        }

        /* Invoice and Customer Details Sections */
        .details-section {
            display: grid;
            grid-template-columns: 1fr; /* Single column for full width boxes */
            gap: 10px;
        }
        .details-box {
            border: 1px solid #000;
            padding: 8px 12px;
            box-sizing: border-box;
            background-color: #fdfdfd;
        }
        .details-box .title {
            font-weight: bold;
            font-size: 1em;
            margin-bottom: 5px;
            border-bottom: 1px solid #bbb;
            padding-bottom: 3px;
            color: #444;
            text-align: center;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Two columns for details */
            gap: 5px 15px; /* Row and column gap */
            font-size: 0.8em;
        }
        .details-grid .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 0;
        }
        .details-grid .item span:first-child {
            font-weight: bold;
            color: #666;
            white-space: nowrap; /* Prevent breaking of labels */
        }
        .details-grid .item span:last-child {
            text-align: left; /* Align value to the left */
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table th, table td {
            border: 1px solid #000; /* Borders for table cells */
            padding: 6px;
            text-align: center; /* Center align content in cells */
            font-size: 0.85em;
            line-height: 1.2;
            vertical-align: middle;
        }
        table thead th {
            background-color: #e0e0e0;
            font-weight: bold;
            color: #333;
            white-space: nowrap; /* Prevent header text from wrapping */
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table td:nth-child(2) { /* Item description column */
            text-align: right; /* Align description to the right */
        }
        table th:nth-child(2) { /* Item description column in header */
            text-align: right;
        }

        /* Total Section */
        .total-section-container {
            display: flex;
            justify-content: flex-end; /* Align to the right */
            margin-bottom: 10px;
        }
        .total-section {
            width: 50%; /* Adjust width as per invoice image */
            border: 1px solid #000;
            background-color: #fdfdfd;
            padding: 8px 12px;
            box-sizing: border-box;
        }
        .total-section .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 0.9em;
        }
        .total-section .total-row:not(:last-child) {
            border-bottom: 1px dotted #bbb;
        }
        .total-section .total-row span:first-child {
            font-weight: bold;
            color: #555;
            white-space: nowrap;
        }
        .total-section .grand-total {
            font-size: 1.1em;
            font-weight: bold;
            color: #000;
        }

        /* Amount in Words */
        .amount-in-words-box {
            border: 1px solid #000;
            padding: 8px 12px;
            margin-bottom: 10px;
            background-color: #fdfdfd;
        }
        .amount-in-words-box p {
            margin: 0;
            padding: 3px 0;
            font-size: 0.9em;
            line-height: 1.4;
        }
        .amount-in-words-box p span {
            font-weight: bold;
        }

        /* Bank Details */
        .bank-details-box {
            border: 1px solid #000;
            padding: 8px 12px;
            background-color: #fdfdfd;
        }
        .bank-details-box .title {
            font-weight: bold;
            font-size: 1em;
            margin-bottom: 5px;
            border-bottom: 1px solid #bbb;
            padding-bottom: 3px;
            color: #444;
            text-align: center;
        }
        .bank-details-box p {
            margin: 0;
            padding: 2px 0;
            font-size: 0.85em;
            line-height: 1.5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .bank-details-box p span:first-child {
            font-weight: bold;
            color: #666;
            white-space: nowrap;
        }

        /* Specific alignment for dual-language text */
        .dual-lang {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
        .dual-lang .arabic-text {
            flex-basis: 50%; /* Adjust as needed */
            text-align: right;
            padding-right: 5px;
            box-sizing: border-box;
        }
        .dual-lang .english-text {
            flex-basis: 50%; /* Adjust as needed */
            text-align: left;
            padding-left: 5px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="qr-code-box">
                <img src="assets/images/qr-code.png" alt="QR Code"> </div>
            <div class="company-logo-info">
                <img src="assets/images/logo4.png" alt="Company Logo">
                [cite_start]<p class="arabic">مؤسسة ثلاجة الياس [cite: 8, 9]</p>
                [cite_start]<p class="english">Elias's Refrigerator [cite: 9]</p>
            </div>
            <div class="company-details">
                <div class="item">
                    [cite_start]<span>CRNO: [cite: 1]</span>
                    [cite_start]<span id="companyCrno">5904618649 [cite: 1]</span>
                </div>
                <div class="item">
                    [cite_start]<span>السجل التجاري : [cite: 7]</span>
                    [cite_start]<span>٥٩٠٤٦١٨٦٤٩ [cite: 7]</span>
                </div>
                <div class="item">
                    [cite_start]<span>VAT NO: [cite: 2]</span>
                    [cite_start]<span id="companyVatNo">310483256200003 [cite: 2]</span>
                </div>
                <div class="item">
                    [cite_start]<span>الرقم الضريبي : [cite: 8]</span>
                    [cite_start]<span>٣١٠٤٨٣٢٥٦٢٠٠٠٠٣ [cite: 8]</span>
                </div>
                <div class="item">
                    [cite_start]<span>Mobile: [cite: 3]</span>
                    [cite_start]<span id="companyMobile">0537759757-053330744 [cite: 3]</span>
                </div>
                <div class="item">
                    [cite_start]<span>جوال : [cite: 20]</span>
                    [cite_start]<span>٠٥٣٧٧٥٩٧٥٧ - ٠٥٣٣٣٠٧٤٤ [cite: 20]</span>
                </div>
                <div class="item">
                    [cite_start]<span>City: [cite: 6]</span>
                    [cite_start]<span id="companyCity">أبو عريش [cite: 14]</span>
                </div>
                <div class="item">
                    [cite_start]<span>المدينة: [cite: 13]</span>
                    [cite_start]<span>أبو عريش [cite: 14]</span>
                </div>
                <div class="item">
                    [cite_start]<span>Postal Code: [cite: 25]</span>
                    [cite_start]<span id="companyPostalCode">٨٤٧١٧ [cite: 27]</span>
                </div>
                <div class="item">
                    <span>الرمز البريدي:</span>
                    [cite_start]<span>٨٤٧١٧ [cite: 27]</span>
                </div>
                <div class="item">
                    [cite_start]<span>Building NO: [cite: 18]</span>
                    [cite_start]<span id="companyBuildingNo">٦٥٣٦ [cite: 28]</span>
                </div>
                <div class="item">
                    [cite_start]<span>رقم المبني: [cite: 22]</span>
                    [cite_start]<span>٦٥٣٦ [cite: 28]</span>
                </div>
                <div class="item">
                    [cite_start]<span>Street NO: [cite: 18]</span>
                    [cite_start]<span id="companyStreetName">طريق الملك فهد [cite: 23]</span>
                </div>
                <div class="item">
                    [cite_start]<span>اسم الشارع : [cite: 23]</span>
                    [cite_start]<span>طريق الملك فهد [cite: 23]</span>
                </div>
                <div class="item">
                    [cite_start]<span>District: [cite: 18]</span>
                    [cite_start]<span id="companyDistrict">الحي الربيع [cite: 24]</span>
                </div>
                <div class="item">
                    [cite_start]<span>الحي: [cite: 24]</span>
                    [cite_start]<span>الحي الربيع [cite: 24]</span>
                </div>
                <div class="item">
                    [cite_start]<span>Additional No: [cite: 26]</span>
                    <span id="companyAdditionalNo"></span>
                </div>
                <div class="item">
                    <span>رقم إضافي:</span>
                    <span></span>
                </div>
            </div>
        </div>

        <div class="invoice-type-box box">
            [cite_start]<span>فاتورة ضريبية [cite: 10]</span>
            <span>/</span>
            [cite_start]<span>TAX invoice [cite: 15]</span>
        </div>

        <div class="details-box">
            <div class="title">Invoice Details / تفاصيل الفاتورة</div>
            <div class="details-grid">
                <div class="item">
                    [cite_start]<span>Invoice number: [cite: 12]</span>
                    [cite_start]<span>رقم الفاتورة: [cite: 21]</span>
                    <span id="invoiceNumber">INV-2025-001</span>
                </div>
                <div class="item">
                    [cite_start]<span>Invoice Date: [cite: 5]</span>
                    [cite_start]<span>تاريخ الفاتورة: [cite: 11]</span>
                    <span id="invoiceDate">2025-07-22</span>
                </div>
                <div class="item">
                    [cite_start]<span>Pay mode: [cite: 16]</span>
                    [cite_start]<span>نوع الحساب: [cite: 16]</span>
                    <span id="payMode">Bank Transfer</span>
                </div>
                <div class="item">
                    [cite_start]<span>P.O Number: [cite: 30]</span>
                    [cite_start]<span>رقم الطلب : [cite: 41]</span>
                    <span id="poNumber">PO-2025-045</span>
                </div>
            </div>
        </div>

        <div class="details-box">
            <div class="title">Customer Details / تفاصيل العميل</div>
            <div class="details-grid">
                <div class="item">
                    [cite_start]<span>Party Name: [cite: 29]</span>
                    [cite_start]<span>اسم العميل: [cite: 40]</span>
                    <span id="partyName">Ahmed Al-Saud</span>
                </div>
                <div class="item">
                    [cite_start]<span>VAT No: [cite: 36]</span>
                    [cite_start]<span>الرقم الضريبي : [cite: 42]</span>
                    <span id="customerVatNo">310123456789012</span>
                </div>
                <div class="item">
                    <span>C.R. [cite_start]NO: [cite: 36]</span>
                    [cite_start]<span>السجل التجاري : [cite: 43]</span>
                    <span id="customerCrNo">1234567890</span>
                </div>
                <div class="item">
                    [cite_start]<span>Building NO: [cite: 36]</span>
                    [cite_start]<span>رقم المينى : [cite: 44]</span>
                    <span id="customerBuildingNo">101</span>
                </div>
                <div class="item">
                    [cite_start]<span>Street Name: [cite: 36]</span>
                    [cite_start]<span>إسم الشارع: [cite: 45]</span>
                    <span id="customerStreetName">King Abdullah Rd</span>
                </div>
                <div class="item">
                    [cite_start]<span>District: [cite: 36]</span>
                    [cite_start]<span>الحي: [cite: 46]</span>
                    <span id="customerDistrict">Al Olaya</span>
                </div>
                <div class="item">
                    [cite_start]<span>City: [cite: 31]</span>
                    [cite_start]<span>المدينة: [cite: 35]</span>
                    <span id="customerCity">Riyadh</span>
                </div>
                <div class="item">
                    [cite_start]<span>Postal Code: [cite: 32]</span>
                    [cite_start]<span>الرمز البريدي: [cite: 35]</span>
                    <span id="customerPostalCode">12345</span>
                </div>
                <div class="item">
                    [cite_start]<span>Country: [cite: 33]</span>
                    [cite_start]<span>البلد: [cite: 35]</span>
                    <span id="customerCountry">Saudi Arabia</span>
                </div>
                <div class="item">
                    [cite_start]<span>Cuts ID: [cite: 36]</span>
                    [cite_start]<span>رقم العميل : [cite: 42]</span>
                    <span id="customerCutsId"></span>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    [cite_start]<th>رقم <br> SI [cite: 34]</th>
                    [cite_start]<th>البيان <br> Item description [cite: 34]</th>
                    [cite_start]<th>الكميه <br> qty [cite: 34]</th>
                    [cite_start]<th>الوحدة <br> unit [cite: 34]</th>
                    [cite_start]<th>سعر الوحدة <br> Unit price [cite: 34]</th>
                    [cite_start]<th>خصم البند <br> Item discount [cite: 34]</th>
                    [cite_start]<th>معدلات الضرائب <br> Tax rate [cite: 34]</th>
                    [cite_start]<th>ضريبه <br> VAT [cite: 34]</th>
                    [cite_start]<th>القيمه <br> Amount [cite: 34]</th>
                </tr>
            </thead>
            <tbody id="invoiceItems">
                </tbody>
        </table>

        <div class="total-section-container">
            <div class="total-section">
                <div class="total-row">
                    [cite_start]<span>Total gross[cite: 38]:</span>
                    [cite_start]<span>الإجمالي[cite: 38]:</span>
                    <span id="totalGross"></span>
                </div>
                <div class="total-row">
                    [cite_start]<span>Total discount[cite: 38]:</span>
                    [cite_start]<span>إجمالي الخصم[cite: 38]:</span>
                    <span id="totalDiscount"></span>
                </div>
                <div class="total-row">
                    [cite_start]<span>Total VAT 15%: [cite: 38]</span>
                    [cite_start]<span>ضريبة القيمة المضافة: [cite: 38]</span>
                    <span id="totalVat"></span>
                </div>
                <div class="total-row grand-total">
                    [cite_start]<span>Grand total[cite: 38]:</span>
                    [cite_start]<span>الصافي[cite: 38]:</span>
                    <span id="grandTotal"></span>
                </div>
            </div>
        </div>

        <div class="amount-in-words-box">
            <p>
                [cite_start]<span class="arabic-text">الإجمالي بالحروف : [cite: 47]</span>
                [cite_start]<span class="english-text">Amount in Words: [cite: 37]</span>
            </p>
            <p id="amountInWordsCombined">
                <span class="arabic-text" id="amountInWordsArabic"></span>
                <span class="english-text" id="amountInWordsEnglish"></span>
            </p>
        </div>

        <div class="bank-details-box">
            [cite_start]<div class="title">Bank Details / تفاصيل البنك [cite: 39]</div>
            <p>
                [cite_start]<span>Bank: [cite: 17]</span>
                <span>البنك:</span>
                <span id="bankName"></span>
            </p>
            <p>
                <span>Account Name:</span>
                <span>اسم الحساب:</span>
                <span id="accountName"></span>
            </p>
            <p>
                <span>Account Number:</span>
                <span>رقم الحساب:</span>
                <span id="accountNumber"></span>
            </p>
            <p>
                <span>IBAN:</span>
                <span>الآيبان:</span>
                <span id="iban"></span>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Function to populate invoice details (replace with your actual data)
            function populateInvoice() {
                // Invoice Details
                document.getElementById('invoiceNumber').textContent = 'INV-2025-001';
                document.getElementById('invoiceDate').textContent = '2025-07-22';
                document.getElementById('payMode').textContent = 'Bank Transfer';
                document.getElementById('poNumber').textContent = 'PO-2025-045';

                // Customer Details
                document.getElementById('partyName').textContent = 'Ahmed Al-Saud';
                document.getElementById('customerVatNo').textContent = '310123456789012';
                document.getElementById('customerCrNo').textContent = '1234567890';
                document.getElementById('customerBuildingNo').textContent = '101';
                document.getElementById('customerStreetName').textContent = 'King Abdullah Rd';
                document.getElementById('customerDistrict').textContent = 'Al Olaya';
                document.getElementById('customerCity').textContent = 'Riyadh';
                document.getElementById('customerPostalCode').textContent = '12345';
                document.getElementById('customerCountry').textContent = 'Saudi Arabia';
                document.getElementById('customerCutsId').textContent = 'CUST-007';


                // Invoice Items
                const items = [
                    { si: 1, description: 'Product A - جهاز تكييف سبليت', qty: 2, unit: 'pcs', unitPrice: 1000.00, discount: 0, taxRate: 15, vat: 300.00, amount: 2000.00 },
                    { si: 2, description: 'Service B - خدمة تركيب وصيانة', qty: 1, unit: 'hr', unitPrice: 500.00, discount: 50, taxRate: 15, vat: 67.50, amount: 450.00 },
                    { si: 3, description: 'Item C - قطع غيار (مروحة)', qty: 5, unit: 'kg', unitPrice: 20.00, discount: 0, taxRate: 15, vat: 15.00, amount: 100.00 }
                ];

                const invoiceItemsBody = document.getElementById('invoiceItems');
                let totalGross = 0;
                let totalDiscount = 0;
                let totalVat = 0;
                let grandTotal = 0;

                items.forEach(item => {
                    const row = invoiceItemsBody.insertRow();
                    row.insertCell().textContent = item.si;
                    row.insertCell().textContent = item.description;
                    row.insertCell().textContent = item.qty;
                    row.insertCell().textContent = item.unit;
                    row.insertCell().textContent = item.unitPrice.toFixed(2);
                    row.insertCell().textContent = item.discount.toFixed(2);
                    row.insertCell().textContent = item.taxRate + '%';
                    row.insertCell().textContent = item.vat.toFixed(2);
                    row.insertCell().textContent = item.amount.toFixed(2);

                    totalGross += (item.qty * item.unitPrice);
                    totalDiscount += item.discount;
                    totalVat += item.vat;
                    grandTotal += item.amount;
                });

                document.getElementById('totalGross').textContent = totalGross.toFixed(2);
                document.getElementById('totalDiscount').textContent = totalDiscount.toFixed(2);
                document.getElementById('totalVat').textContent = totalVat.toFixed(2);
                document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);

                // Amount in Words (placeholder - you'll need a library or custom function for this)
                document.getElementById('amountInWordsArabic').textContent = 'ألفان وثمانمائة وستون ريالاً سعودياً وخمسون هللة فقط'; // Example
                document.getElementById('amountInWordsEnglish').textContent = 'Two Thousand Eight Hundred Sixty Saudi Riyals and Fifty Halalas Only'; // Example

                // Bank Details
                document.getElementById('bankName').textContent = 'Al Rajhi Bank';
                document.getElementById('accountName').textContent = 'Elias\'s Refrigerator Est. / مؤسسة ثلاجة الياس';
                document.getElementById('accountNumber').textContent = '12345678901234567';
                document.getElementById('iban').textContent = 'SA1234567890123456789012';
            }

            populateInvoice();
        });
    </script>
</body>
</html>