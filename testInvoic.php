<?php include('headAndFooter/head.php'); ?>
<style>
    * {
        font-size: 12px;
    }

    body {
        font-size: 12px;

    }

    #printInvoice {
        background-color: white !important;
        border: 2px double #ccc;
        border-radius: 20px;
    }

    #printInvoice table {
        font-size: 12px;
    }

    #printInvoice table th {
        font-size: 12px;
    }

    #printInvoice table td {
        font-size: 12px;
    }



    #invoiceTitle {
        padding-bottom: 20px;
    }


    .body-print {
        border: 3px double #000;
        border-radius: 20px;
        padding: 20px;
        margin: 5px;
    }

    /* invoiceItemsTable */
</style>

<div id="printInvoice" class="modal-body ">
    <div class="body-print ">
        <div class="row" >
            <div class="col-md-4">
                <div class="card mb-1">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-user"></i> بيانات الشركة/company Data</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="text-start">
                                <div class="company-info">
                                    <p class="fw-bold"><i class="fa fa-map-marker"></i><span id="address"></span> العنوان/address </p>
                                    <p class="fw-bold"><i class="fa fa-phone"></i><span id="phone"></span> رقم الهاتف/phone </p>
                                    <p class="fw-bold"><i class="fa fa-id-card"></i><span id="taxNumber"></span> رقم الضريبة/ tax number </p>
                                    <p class="fw-bold"><i class="fa fa-id-card"></i><span id="crNumber"></span> سجل تجاري/ cr number </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <img src="assets/images/logo7.png" class="img-fluid mb-2 mt-4" alt="" style="width: 70%; height: auto;">
                <h5 class="mb-4 fw-bold text-center"> فاتـــــورة ضريــبـيــــة</h5>
                <p><i class="fa fa-check-square-o"></i> رقم الفاتورة/Invoice Number: <span id="invoiceNumber"></span></p>
                <p><i class="fa fa-calendar"></i> تاريخ الفاتورة/Invoice Date: <span id="invoiceDate"></span></p>
            </div>
            <div class="col-md-4 text-center">
                <div class="card mb-1">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-user"></i> بيانات العميل/customer Data</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="text-start">
                                <p><i class="fa fa-user"></i> اسم العميل/customer Name: <span id="customerName">customer Name</span></p>
                                <p><i class="fa fa-id-card"></i>السجل التجاري/customer cr number: <span id="customerCrNumber">customer Cr Number</span></p>
                                <p><i class="fa fa-id-card"></i> الرقم الضريبي/customer tax number: <span id="customerTaxNumber">customer Tax Number</span></p>
                                <p><i class="fa fa-phone"></i> رقم الهاتف/customer Phone: <span id="customerPhone">customer Phone</span></p>
                                <p><i class="fa fa-map-marker"></i> العنوان/customer Address: <span id="customerAddress">customer Address</span></p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="row">
            <div class="col-12">
                
            </div>
        </div> -->
        <div class="table-responsive mb-4">
            <table class="table table-striped table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th width="5%">م <span class="text-danger">Si</span></th>
                        <th width="22%">اسم الصنف <span class="text-danger">item name</span></th>
                        <th width="7%">الكمية <span class="text-danger">count</span></th>
                        <th width="10%">الوحدة <span class="text-danger">unit</span></th>
                        <th width="7%">السعر <span class="text-danger">price</span></th>
                        <th width="10%">الإجمالي <span class="text-danger">total</span></th>
                        <th width="5%">الخصم <span class="text-danger">discount</span></th>
                        <th width="9%"> الضريبة <span class="text-danger">vat</span></th>
                        <th width="15%"> الإجمالي النهائي <span class="text-danger">final total</span></th>
                        <th class="d-print-none" width="10%">إجراءات <span class="text-danger">actions</span></th>
                    </tr>
                </thead>
                <tbody id="invoiceItemsTable">
                    <!-- سيتم تعبئتها بالجافاسكريبت -->
                    <!-- بيانات وهمية -->
                    <tr>
                        <td>1</td>
                        <td>صنف 1 </td>
                        <td>1</td>
                        <td>وحدة</td>
                        <td>100</td>
                        <td>100</td>
                        <td>100</td>
                        <td>100</td>
                        <td>100</td>
                        <td class="d-print-none">100</td>
                    </tr>
                    
                </tbody>
            </table>
        </div>
        <div class="row">
            <div class="col-6 text-start">الاجمالى بالحروف:</div>
            <div class="col-6 text-end" id="generalTotalAmountInWordsAr">ريال سعودي</div>
        </div>
        <div class="row">
            <div class="col-6 text-start">Total in words:</div>
            <div class="col-6 text-end" id="generalTotalAmountInWordsEn"> Riyal Saudi</div>
        </div>
        <div class="row">
            <div class="col-4">
                <div class="border border-5 border-dark  p-2">
                    <div class="">
                        <p>
                            حالة الدفع: <span id="paymentStatus"> paymentStatus</span>
                            <br>
                            طريقة الدفع: <span id="paymentMethod"> paymentMethod</span>
                            <br>
                            موعد الدفع المتفق عليه: <span id="paymentDate"> paymentDate</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-2">
                <div class=" border border-5 border-dark p-2 text-center">
                    <div id="qrcode" class="text-center ">
                        <img src="assets/images/qrcode.png" alt="" style="width: 100%; height: auto;">
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="border border-5 border-dark  p-2">

                    <div class="row mb-2">
                        <div class="col-6 text-start">المجموع الفرعي:</div>
                        <div class="col-6 text-end" id="subtotal">0.00</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 text-start">الضريبة (15%):</div>
                        <div class="col-6 text-end" id="taxAmount">0.00</div>
                    </div>
                    <div class="row fw-bold">
                        <div class="col-6 text-start">الإجمالي:</div>
                        <div class="col-6 text-end" id="totalAmount">0.00</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 text-start">الخصم:</div>
                        <div class="col-6 text-end" id="discountAmount">0.00</div>
                    </div>
                    <hr class="border border-dark border-2 opacity-100">
                    <div class="row fw-bold">
                        <div class="col-6 text-start">الإجمالي النهائي:</div>
                        <div class="col-6 text-end" id="generalTotalAmount">0.00</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <p>شكراً لتعاملكم معنا</p>
            <p>هذه فاتورة ضريبية معتمدة</p>
        </div>

    </div>




    <div>
        <button class="btn btn-primary d-print-none" onclick="printInvoiceComplex('printInvoice')">طباعة الفاتورة</button>
    </div>


    <script>
        // function printInvoice(printArea) {
        //     const originalContent = document.body.innerHTML;
        //     const printContent = document.getElementById(printArea).innerHTML;
        //     document.body.innerHTML = printContent;
        //     window.onafterprint = function() {
        //     document.body.innerHTML = originalContent;
        // };
        // }


        function printInvoiceComplex() {
            const printContents = document.getElementById('printInvoice').cloneNode(true);
            const originalStyles = document.querySelectorAll('link[rel="stylesheet"], style');

            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Print Invoice</title>');

            // Copy all stylesheets from the original document
            originalStyles.forEach(styleNode => {
                printWindow.document.write(styleNode.outerHTML);
            });

            printWindow.document.write('</head><body>');
            printWindow.document.body.appendChild(printContents); // Append the cloned content
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        }
    </script>
    <?php include('headAndFooter/footer.php'); ?>