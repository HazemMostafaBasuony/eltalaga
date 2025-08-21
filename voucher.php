<?php
include('headAndFooter/head.php');
include('hmb/conn_pdo.php'); // اتصال قاعدة البيانات

// قراءة القيم من GET
$customerID = isset($_GET['customerID']) ? intval($_GET['customerID']) : 0;
$salesmaneID = isset($_GET['userID']) ? intval($_GET['userID']) : 0;

if ($customerID <= 0 || $salesmaneID <= 0) {
    die("بيانات غير صحيحة");
}

// 1. بيانات العميل
$stmt = $pdo->prepare("SELECT customerID, type, numberRC, numberTax, customerName, street, area, city, country, bulding, postCode, phone, email, remainingAmount, notes FROM customers WHERE customerID = :customerID");
$stmt->execute(['customerID' => $customerID]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. جلب branchID واسم المندوب
$stmt = $pdo->prepare("SELECT branchID, salesmaneName FROM salesmane WHERE salesmaneID = :salesmaneID");
$stmt->execute(['salesmaneID' => $salesmaneID]);
$salesman = $stmt->fetch(PDO::FETCH_ASSOC);
$branchID = $salesman['branchID'] ?? 0;
$salesmaneName = $salesman['salesmaneName'] ?? "";

// 3. بيانات الفرع
$stmt = $pdo->prepare("SELECT branchID, numberRC, numberTax, branchName, street, area, city, country, bulding, postCode, phone, email, notes FROM branchs WHERE branchID = :branchID");
$stmt->execute(['branchID' => $branchID]);
$branch = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!-- مكتبات تحويل الأرقام -->
<script src="JS/written-number.min.js"></script>
<script src="JS/i18n/ar.json"></script>
<script src="JS/i18n/en.json"></script>
<script src="JS/currencyConverter.js"></script>

<style>
    /* تقليل الماسفة بين الاسطر فى <p> */
    p {
        margin: 0;
    }

    @media print {
        body::before {
            content: "";

            opacity: 0.1;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            margin: 0;
            padding: 0;
        }

        .form-control {
            border: none !important;
            border-radius: 30px !important;

        }

    }

    .printableContent {
        border: 2px solid #000;
    }

    @page {
        size: A4;
        margin: 0;
    }
</style>

<div id="printableContent" class=" my-4 w-100 border-3" dir="rtl">
    <div class="card p-4 shadow ">


        <!-- بيانات العميل والفرع -->
        <div class="row mb-1">
            <div class="col-4 border">
                <h5>بيانات العميل</h5>
                <p>الاسم: <?= htmlspecialchars($customer['customerName']) ?></p>
                <p>العنوان:
                    <?= htmlspecialchars($customer['street'] . ' ' . $customer['area'] . ' ' . $customer['city']) ?>
                </p>
                <p>الهاتف: <?= htmlspecialchars($customer['phone']) ?></p>
                <p>المديونية: <span id="remainingAmount"><?= number_format($customer['remainingAmount'], 2) ?></span>
                    ريال</p>
            </div>
            <div class="col-4">
                <h5 class="text-center mt-3">سنـــد صــــرف</h5>
                <!-- logo -->
                <img src="assets/images/logo7.png" alt="" style="width: 70%; height: auto;" class="img-fluid mt-3">
            </div>
            <div class="col-4 border">
                <h5>بيانات الفرع</h5>
                <p>الفرع: <?= htmlspecialchars($branch['branchName']) ?></p>
                <p>الهاتف: <?= htmlspecialchars($branch['phone']) ?></p>
                <p>المدينة: <?= htmlspecialchars($branch['city']) ?></p>
                <p>اسم المستلم: <?= htmlspecialchars($salesmaneName) ?></p>
            </div>
        </div>

        <hr class="border border-primary border-3 opacity-75">

        <!-- تفاصيل الدفع -->
        <div class="row mb-2">
            <div class="col-6 d-flex align-items-center">
                <label for="paidAmount" class="me-2">المبلغ المدفوع:</label>
                <input type="number" step="0.01" class="form-control w-auto" id="paidAmount">
            </div>
            <div class="col-6 d-flex align-items-center">
                <label for="paymentMethod" class="me-2">طريقة الدفع :</label>
                <select class="form-control w-auto" id="paymentMethod">
                    <option value="cash">كاش</option>
                    <option value="card">شبكة</option>
                    <option value="bank">تحويل بنكي</option>
                </select>
            </div>

        </div>

        <div class="mb-2">
            <label>المبلغ بالكلمات (عربي)</label>
            <input type="text" class="form-control" id="finalTotalAr" readonly>
        </div>
        <div class="mb-2">
            <label>Amount in Words (English)</label>
            <input type="text" class="form-control" id="finalTotalEn" readonly>
        </div>
        <div class="mb-2 d-flex align-items-center">
            <label class="me-2">المتبقي بعد الدفع :</label>
            <input type="text" class="form-control w-auto" id="newRemaining" readonly>
        </div>
        <div class="mb-2">
            <label>تفاصيل إضافية</label>
            <textarea class="form-control" id="paymentDetails"></textarea>
        </div>
        <hr class="border border-primary border-3 opacity-75">
        <!-- التوقيع -->
        <div class="row mt-5">
            <div class="col-6 text-center">
                <strong>توقيع /المستلم</strong>
                <div style="height: 50px; border-bottom: 1px solid #000;"></div>
            </div>
            <div class="col-6 text-center">
                <strong>توقيع العميل</strong>
                <div style="height: 50px; border-bottom: 1px solid #000;"></div>
            </div>
        </div>

        <div class="text-center mt-4">
            <button class="btn btn-primary w-100  d-print-none" onclick="closeVoucher()"> تم
            </button>
        </div>
    </div>
</div>

<script>
    function closeVoucher() {
        try {
            updateInfo();
            printSectionById('printableContent');
            // الانتقال الى الصفحه الرئيسيه مع مسح هذه الصفحه من الذاكرة
            window.location.href = "salesmane_HomePage.php";
        } catch (error) {
            console.error(error);
        }

    }

    //  تحديث بيانات العميل و المستخدم
    function updateInfo() {
        const customerID = <?php echo $customer['customerID']; ?>;
        const userID = <?php echo $_GET['userID']; ?>;
        const remainingAmount = document.getElementById('newRemaining').value;
        superagent
            .post('voucher/updateInfo.php')
            .type('form')
            .send({
                customerID: customerID,
                userID: userID,
                remainingAmount: remainingAmount
            })
            .end((err, res) => {
                if (err) {
                    console.error("📢 خطأ من السيرفر:", err);
                    return;
                }
                console.log("✅ تم التحديث:", res.body);
            })

    }
    // الطباعة
    function printSectionById(sectionId) {
        const section = document.getElementById(sectionId);
        if (!section) {
            alert("العنصر غير موجود!");
            return;
        }

        // نسخ المحتوى عشان نحافظ على القيم المحدثة
        const clonedSection = section.cloneNode(true);

        // تحديث قيم الحقول قبل الطباعة
        clonedSection.querySelectorAll('input, textarea').forEach(el => {
            el.setAttribute('value', el.value); // عشان input
            el.textContent = el.value; // عشان textarea
        });

        // جلب جميع الستايلات
        const styles = Array.from(document.styleSheets)
            .map(sheet => {
                try {
                    return Array.from(sheet.cssRules).map(rule => rule.cssText).join('\n');
                } catch (e) {
                    return '';
                }
            })
            .join('\n');

        // فتح نافذة الطباعة
        const printWindow = window.open('', '', 'width=800,height=600');
        printWindow.document.write(`
        <html>
        <head>
            <style>${styles}</style>
        </head>
        <body>
            ${clonedSection.outerHTML}
            <script>
                window.onload = function() {
                    window.print();
                    window.close();
                }
            <\/script>
        </body>
        </html>
    `);
        printWindow.document.close();
    }


    document.getElementById('paidAmount').addEventListener('input', function () {
        let paid = parseFloat(this.value) || 0;
        let remaining = parseFloat("<?= $customer['remainingAmount'] ?>");
        let newRemaining = remaining - paid;
        if (newRemaining < 0) newRemaining = 0;
        document.getElementById('newRemaining').value = newRemaining.toFixed(2);

        let paidAmount = convertAmountToWords(paid.toFixed(2));
        document.getElementById('finalTotalAr').value = paidAmount.ar;
        document.getElementById('finalTotalEn').value = paidAmount.en;
    });
</script>

<?php include('headAndFooter/footer.php'); ?>