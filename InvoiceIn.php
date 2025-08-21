<?php
include('headAndFooter/head.php');

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     // معالجة البيانات المرسلة سواء كانت JSON أو FormData
//     $invoice = [];
//     $originalInvoice = null;
// }


if (isset($_GET['invoiceID'])) {
    // تأمين المدخلات
    $invoiceID = mysqli_real_escape_string($conn, $_GET['invoiceID']);
} else {
    header('Location: index.php');
    exit();
}
?>

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

    .body-print {
        border: 3px double #000;
        border-radius: 20px;
        padding: 20px;
        margin: 10px;
    }

    #invoiceTitle {
        padding-bottom: 20px;
    }

    #qrcode {
        
        border: 1px solid #000;
        border-radius: 20px;
        padding: 10px;
        width: 125px;
    }

    /* invoiceItemsTable */
</style>
<div id="printInvoice" class="w-100 m-3 ">
    <div class="body-print">


        <div class="header">


            <div class="row w-100">
                <div class="col-3  text-start">
                    <div id="qrcode" class="text-center m-2  "></div>
                    <p class="m-2"><i class="fa fa-camera  m-2"></i>الفاتورة الأصلية</p>
                </div>
                <div class="col-6   text-center">
                    <h6 id="invoiceTitle" class="mb-0 text-center fw-bold w-70 ">
                        <!-- فاتورة شراء من شركة الإمدادات الغذائية السعود إلى فرع غير معروف-->
                    </h6>
                    <img src="assets/images/logo7.png" alt="" style="width: 50%; height: auto;">
                </div>
                <div class="col-3 ">
                    <p class="text-end fw-bold">رقم الفاتورة: <span id="invoiceNumber"></span></p>
                    <p class="text-end fw-bold">التاريخ: <span id="invoiceDate"></span></p>
                </div>
            </div>
        </div>

        <div class="table-responsive ">

            <table class="table table-striped table-bordered">
                <thead class="table-primary">
                    <tr class="text-center">
                        <th width="5%">م</th>
                        <th width="5%">كود الصنف</th>
                        <th width="25%">اسم الصنف</th>
                        <th width="10%">الكمية</th>
                        <th width="15%">الوحدة</th>
                        <th width="10%">السعر</th>
                        <th width="10%">السعر بعد الضريبة</th>
                        <th width="10%">الخصم</th>
                        <th width="10%">الإجمالي</th>
                        <th class="d-print-none" width="5%">إجراءات</th>
                    </tr>
                </thead>
                <tbody id="invoiceItemsTable">
                    <!-- سيتم تعبئتها بالجافاسكريبت -->
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6 text-start">المجموع الفرعي:</div>
                    <div class="col-6 text-end" id="subtotal">0.00</div>
                </div>
                <div class="row mb-2">
                    <div class="col-6 text-start">الخصم:</div>
                    <div class="col-6 text-end" id="discount">0.00</div>
                </div>
                <div class="row mb-2">
                    <div class="col-6 text-start">الضريبة (15%):</div>
                    <div class="col-6 text-end" id="vatAmount">0.00</div>
                </div>
                <div class="row fw-bold">
                    <div class="col-6 text-start">الإجمالي:</div>
                    <div class="col-6 text-end" id="totalAmount">0.00</div>
                </div>
                <div class="row fw-bold">
                    <div class="col-6 text-start">الإجمالي بعد الخصم:</div>
                    <div class="col-6 text-end" id="finalTotal">0.00</div>
                </div>
                <div class="row fw-bold">
                    <div class="col-12 text-end" id="finalTotalAr">فقط ريال</div>
                </div>
                <div class="row fw-bold">
                    <div class="col-12 text-end" id="finalTotalEn">Only Riyal</div>
                </div>
            </div>
        </div>

        <div class="text-end mt-4">
            <p class="text-end">............................/  توقيع المستلم</p><br>
            <p class="text-end">............................/  اسم المستلم</p>
        </div>
    </div>
</div>

<div id="printInvoice card w-100 m-3 d-print-none">
    <div class="card-body">
        <button class="btn btn-primary" onclick="printInvoice('printInvoice')">طباعة الفاتورة</button>       
    </div>
</div>

<!-- استدعاء المكتبات -->
<script src="JS/written-number.min.js"></script>
<script src="JS/i18n/ar.json"></script>
<script src="JS/i18n/en.json"></script>
<script src="JS/currencyConverter.js"></script>
<script src="invoiceIn/invoiceIn.js"></script>
<script>
    invoiceID = <?php echo $invoiceID ?>;

    loadInvoice(invoiceID);
    // alert(convertAmountToWords(invoiceID).ar);


    function printInvoice(printArea) {
        const originalContent = document.body.innerHTML;
        const printContent = document.getElementById(printArea).innerHTML;
        document.body.innerHTML = printContent;
        window.onafterprint = function() {
        document.body.innerHTML = originalContent;
    };
    }
</script>


<?php
include('headAndFooter/footer.php');
?>