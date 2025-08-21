<?php
include('headAndFooter/head.php');

// بدء جلسة إذا لم تكن بدأت
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// التحقق من صلاحيات المستخدم
if (!isset($_SESSION['userId'])) {
  header('Location: signIn.php');
  exit();
}

$userId = $_SESSION['userId'];
?>

<div class="container-fluid mt-4">
  <?php
  $supplierName = '';
  $branchName = '';

  // التحقق من وجود معرف المورد
  if (isset($_REQUEST['supplierID']) && is_numeric($_REQUEST['supplierID'])) {
    $supplierID = intval($_REQUEST['supplierID']);


    include('hmb/conn.php');

    // استعلام أكثر أمانًا باستخدام prepared statements
    $stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplierID = ?");
    $stmt->bind_param("i", $supplierID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $supplierName = htmlspecialchars($row['supplierName']);
      $supplierAddress = htmlspecialchars($row['street']);
      $supplierPhone = htmlspecialchars($row['phone']);
      $supplierTaxNumber = htmlspecialchars($row['numberTax']);
      $supplierRC = htmlspecialchars($row['numberRC']);
      $remainingAmount = $row['wantDebt'];
    } else {
      echo '<div class="alert alert-danger text-center mb-4" role="alert">
                    المورد غير موجود في النظام
                  </div>';
      include('headAndFooter/footer.php');
      exit();
    }

    $stmt->close();
    $conn->close();
  } else {
    echo '<div class="alert alert-warning text-center mb-4" role="alert">
                لم يتم تحديد مورد.
              </div>';
    include('headAndFooter/footer.php');
    exit();
  }

  // جلب بيانات الفرع
  include('hmb/conn.php');
  if ($branch === 'جيزان' || $branch === 'مكة') {
    $stmt = $conn->prepare("SELECT * FROM branchs WHERE area = ?");
    $stmt->bind_param("s", $branch);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $branchName = htmlspecialchars($row['branchName']);
      $branchAddress = htmlspecialchars($row['street']);
      $branchPhone = htmlspecialchars($row['phone']);
      $branchTaxNumber = htmlspecialchars($row['numberTax']);
      $branshRC = htmlspecialchars($row['numberRC']);
      $branchID = $row['branchID'];
    }
    $stmt->close();
  } else {
    header('Location: signIn.php');
    exit();
  }
  $conn->close();
  ?>
<input type="number" id="branchID" value="<?php echo $branchID ?>">
  <div class="alert alert-info text-center mb-4" role="alert">
    <h4 class="mb-0">
      أمر شراء من <?php echo $supplierName ?>
      إلى <?php echo $branchName ?>
      فرع <?php echo $branch ?>
    </h4>
  </div>

  <div class="row g-2 mb-3">
    <!-- المجموعات الرئيسية -->
    <div class="col-lg-6 col-md-12">
      <div class="card h-100 shadow-sm">
        <div class="card-header bg-primary text-white py-2">
          <h6 class="mb-0"><i class="fa fa-folder me-1"></i>المجموعات الرئيسية</h6>
        </div>
        <div class="card-body py-2">
          <div id="mainGroup" class="d-flex flex-wrap gap-1">
            <!-- يتم تعبئتها بالجافاسكريبت -->
          </div>
        </div>
      </div>
    </div>

    <!-- المجموعات الفرعية -->
    <div class="col-lg-6 col-md-12">
      <div class="card h-100 shadow-sm">
        <div class="card-header bg-success text-white py-2">
          <h6 class="mb-0"><i class="fa fa-folder-open me-1"></i>المجموعات الفرعية</h6>
        </div>
        <div class="card-body py-2">
          <button onclick="getItems('all')" class="btn btn-outline-primary btn-sm mb-2 w-100">
            <i class="fa fa-list-ul me-1"></i> عرض جميع الأصناف
          </button>
          <div id="supGroup" class="d-flex flex-wrap gap-1">
            <!-- يتم تعبئتها بالجافاسكريبت -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- شريط البحث -->
  <div class="card mb-3 shadow-sm">
    <div class="card-header bg-info text-white py-2">
      <h6 class="mb-0"><i class="fa fa-search me-2"></i>البحث والمسح</h6>
    </div>
    <div class="card-body py-2">
      <!-- البحث النصي -->
      <div class="row g-2 mb-2">
        <div class="col-md-8">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input type="text" id="searchItem" class="form-control" placeholder="ابحث عن الصنف بالاسم أو الرقم..."
              autocomplete="off">
            <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()" title="مسح البحث">
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>
        <div class="col-md-4">
          <button class="btn btn-primary btn-sm w-100" id="toggleScannerBtn">
            <i class="fa fa-camera me-1"></i>مسح الباركود
          </button>
        </div>
      </div>

      <!-- نتائج البحث -->
      <div class="d-flex justify-content-between align-items-center">
        <small id="searchResults" class="text-info fw-bold"></small>
      </div>

      <!-- منطقة الكاميرا -->
      <div id="statusMessage" class="message"></div>
      <div id="preview-container">
        <video id="preview"></video>
        <div class="center-text">
          <div class="row green">
            <input id="qrtext" type="text" name="" readonly placeholder="الباركود الممسوح سيظهر هنا">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- قسم الأصناف -->
  <div class="card shadow-lg border-0">
    <div class="card-header bg-gradient"
      style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
      <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="fa fa-shopping-bag me-2"></i>الأصناف</h4>
        <span class="badge bg-light text-dark" id="itemsCount">0 صنف</span>
      </div>
    </div>
    <div class="card-body p-0">
      <div id="items" class="p-2" style="max-height:500px; overflow-y: auto;">
        <div class="text-center text-muted py-5">
          <i class="fa fa-box-open fa-3x mb-3"></i>
          <p>اختر مجموعة لعرض الأصناف</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- مودال إضافة العنصر -->
<div id="sendItem" class="modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">إضافة عنصر جديد</h5>
        <input type="hidden" id="itemID">
        <button type="button" class="btn-close" onclick="hideSendItemModal()"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="countItem" class="form-label">العدد:</label>
          <input id="countItem" type="number" class="form-control" placeholder="أدخل العدد" min="0.5" step="0.5">
        </div>

        <div class="mb-3">
          <div class="row">
            <label class="w-100" id="priceLabel1"></label>
            <label class="w-100" id="priceLabel2"></label>
            <label for="priceItem" class="form-label">السعر:</label>
            <input class="col-8 m-2" id="priceItem" type="number" class="form-control" placeholder="أدخل السعر" min="0"
              step="0.01">
            <button class="col-3" onclick="calculateTotals()" class="btn btn-primary btn-sm w-100">
              <i class="fa fa-calculator me-1"></i> حساب
            </button>
          </div>
        </div>

        <div class="mb-3">
          <label for="discountItem" class="form-label">الخصم:</label>
          <input id="discountItem" type="number" class="form-control" placeholder="أدخل الخصم" min="0" step="0.01">
        </div>

        <div class="mb-3">
          <label for="priceXcount" class="form-label">الإجمالي:</label>
          <input id="priceXcount" type="number" class="form-control" readonly>
        </div>

        <div class="mb-3">
          <label for="price_vat" class="form-label">السعر بعد الضريبة:</label>
          <input id="price_vat" type="number" class="form-control" readonly>
        </div>

        <div class="mb-3">
          <div class="row">
            <label for="price_vatXcount" class="form-label">الإجمالي بعد الضريبة:</label>
            <input class="col-8 m-2" id="price_vatXcount" type="number" class="form-control">
            <button class="col-3" onclick="calculatePriceItem()" class="btn btn-primary btn-sm w-100">
              <i class="fa fa-calculator me-1"></i> حساب
            </button>
          </div>
        </div>

        <div class="mb-3">
          <label for="unitSelect" class="form-label">الوحدة:</label>
          <select id="unitSelect" class="form-select">
            <option value="" disabled selected>اختر الوحدة</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="totalDisplay" class="form-label">الإجمالي النهائي:</label>
          <input id="totalDisplay" type="number" class="form-control bg-warning fw-bold" readonly>
        </div>
      </div>
      <div class="modal-footer">
        <button id="add" onclick="confirmAddItem()" class="btn btn-primary">إضافة للفاتورة</button>
        <button onclick="hideSendItemModal()" class="btn btn-secondary">إلغاء</button>
      </div>
    </div>
  </div>
</div>
<!-- أيقونة الفاتورة -->
<div class="invoice-icon-container">
  <img src="assets/images/invoice.png" alt="فاتورة" class="invoice-icon" onclick="showInvoiceModal()">
  <div class="invoice-counter" id="invoiceCounter">0</div>
</div>

<!-- مودال الفاتورة المعدل -->
<div id="invoiceModal" class="modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success">
        <h5 class="modal-title">
          <i class="fa fa-shopping-cart me-2"></i> فاتورة شراء
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="hideInvoiceModal()"></button>
      </div>
      <div class="modal-body">
        <div class="invoice-container">
          <!-- إضافة قسم لرفع الفاتورة الأصلية -->
          <div class="card mb-4">
            <div class="card-header">
              <h5 class="mb-0">الفاتورة الأصلية من المورد</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="originalInvoiceUpload" class="form-label">رفع الفاتورة الأصلية</label>
                    <input type="file" class="form-control" id="originalInvoiceUpload" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">يجب أن تكون الصورة أو PDF بحجم أقل من 5MB</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div id="originalInvoicePreview" class="text-center" style="display:none;">
                    <!-- 888888888888888888888888888888888888888888888 -->
                    <a id="viewOriginalInvoice" href="#" target="_blank" class="btn btn-outline-primary mb-2">
                      <i class="fa fa-eye me-1"></i> عرض الفاتورة الأصلية
                    </a>
                    <button onclick="removeOriginalInvoice()" class="btn btn-outline-danger">
                      <i class="fa fa-trash me-1"></i> حذف
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- بقية محتوى الفاتورة -->
          <div id="printInvoice" class="row w-100 m-2 " style="border: 2px double #ccc; border-radius: 20px;">
            <div class="col-12 card-header">
              <h6 class="mb-0 text-center fw-bold w-70">
                أمر شراء من <?php echo $supplierName ?>
                إلى <?php echo $branchName ?>
                فرع <?php echo $branch ?>
              </h6>

              <div class="table-responsive mb-4">
                <h5 class="text-center">فاتورة شراء</h5>

                <div class="row">
                  <div class="col-4 border m-2 text-center">
                    <div id="qrcode" class="text-center" style="margin-bottom:10px;"></div>
                    <p class="text-center"><i class="fa fa-camera me-1"></i>نسخة الفاتورة الأصلية</p>
                  </div>
                  <div class="col-2  m-2 text-center">
                    <img src="assets/images/logo5.png" alt="" style="width: 100px; height: 100px;">
                  </div>
                  <div class="col-4 border m-2">
                    <p class="text-end">رقم الفاتورة: <span id="invoiceNumber"></span></p>
                    <p class="text-end">التاريخ: <span id="invoiceDate"></span></p>
                  </div>
                </div>

                <table class="table table-striped table-bordered">
                  <thead class="table-primary">
                    <tr class="text-center" style="font-size: 12px;">
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
                  <tbody id="invoiceItemsTable" style="font-size: 10px; ">
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
                    <div class="col-6 text-end" id="taxAmount">0.00</div>
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

              <div class="text-center mt-4">
                <p class="text-right">توقيع المستلم</p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" onclick="confirmPurchase()">
            <i class="fa fa-check-circle me-1"></i> تأكيد الشراء
          </button>
          <button class="btn btn-secondary" onclick="hideInvoiceModal()">إغلاق</button>
        </div>
      </div>
    </div>
  </div>

  <!-- مودال تفاصيل الفاتورة المعدل -->
  <div id="invoiceInfoModal" class="modal" tabindex="-2">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">تفاصيل إضافية للفاتورة</h5>
          <button type="button" class="btn-close" onclick="hideInvoiceInfoModal()"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="paidInput" class="form-label">المبلغ المدفوع</label>
            <input id="paidInput" type="number" class="form-control" placeholder="أدخل المبلغ المدفوع" min="0"
              step=".01">
          </div>
          <div class="mb-3">
            <label for="changeInput" class="form-label">المبلغ المتبقي</label>
            <input id="changeInput" type="number" class="form-control" placeholder="سيتم حسابه تلقائياً" readonly>
          </div>
          <div class="mb-3">
            <label for="discountInput" class="form-label">خصم إضافي</label>
            <input id="discountInput" type="number" class="form-control" placeholder="أدخل الخصم" min="0" step=".01">
          </div>
          <div class="mb-3">
            <label for="typePay" class="form-label">طريقة الدفع</label>
            <select id="typePay" class="form-select">
              <option value="transfer">تحويل بنكي</option>
              <option value="card">بطاقة ائتمان</option>
              <option value="cash">نقدي</option>
              <option value="wait">آجل</option>
            </select>
          </div>
          <div class="mb-3" id="dateWantDebtDiv" style="display:none;">
            <label for="dateWantDebt" class="form-label">موعد الدفع المتفق عليه</label>
            <input id="dateWantDebt" type="date" class="form-control">
          </div>
          <div class="mb-3">
            <label for="invoiceNotes" class="form-label">ملاحظات</label>
            <textarea id="invoiceNotes" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" onclick="sendDataInvoice()">
            <i class="fa fa-check-circle me-1"></i> حفظ الفاتورة
          </button>
          <button class="btn btn-secondary" onclick="hideInvoiceInfoModal()">إغلاق</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
  <script>
    // دالة لإنشاء QR Code عند رفع الفاتورة الأصلية
    document.getElementById('originalInvoiceUpload').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        // هنا يمكنك رفع الملف إلى الخادم والحصول على رابط دائم
        // لأغراض العرض، سنستخدم رابط مؤقت
        const tempUrl = URL.createObjectURL(file);


        // عرض رابط الفاتورة الأصلية
        // ../uploads/invoices/invoice_1753598984_2.jpg
        document.getElementById('viewOriginalInvoice').href = tempUrl;
        document.getElementById('originalInvoicePreview').style.display = 'block';

        // إنشاء QR Code للرابط
        // const qrContainer = document.getElementById('qrcode');
        // qrContainer.innerHTML = ''; // مسح أي QR Code موجود مسبقاً
        // new QRCode(qrContainer, {
        //   text: tempUrl,
        //   width: 100,
        //   height: 100,
        //   colorDark: "#000000",
        //   colorLight: "#ffffff",
        //   correctLevel: QRCode.CorrectLevel.H
        // });

        // تخزين رابط الفاتورة الأصلية في متغير للاستخدام لاحقاً
        window.originalInvoiceUrl = tempUrl;
        console.log('رابط الفاتورة الأصلية:', window.originalInvoiceUrl);
      }
    });

    // دالة لحذف الفاتورة الأصلية
    function removeOriginalInvoice() {
      document.getElementById('originalInvoiceUpload').value = '';
      document.getElementById('originalInvoicePreview').style.display = 'none';
      document.getElementById('qrcode').innerHTML = '';
      window.originalInvoiceUrl = null;
    }

    // عند طباعة الفاتورة، تأكد من تضمين QR Code
    // function printInvoice() {
    //   // يمكنك إضافة منطق الطباعة هنا
    //   // تأكد من أن QR Code مضمن في نسخة الطباعة
    // }
  </script>
  <script>
    // متغيرات عامة
    var supplierID = <?php echo $supplierID; ?>;
    var userId = <?php echo $userId; ?>;
    var originalInvoiceFile = null;
    var remainingAmount = <?php echo $remainingAmount; ?>;


    // معالجة رفع الفاتورة الأصلية
    function handleOriginalInvoiceUpload(event) {
      const file = event.target.files[0];
      if (!file) return;

      // التحقق من نوع الملف
      const validTypes = ['application/pdf', 'image/jpeg', 'image/png'];
      if (!validTypes.includes(file.type)) {
        alert('يجب أن يكون الملف من نوع PDF أو JPG أو PNG');
        return;
      }

      // التحقق من حجم الملف (5MB كحد أقصى)
      if (file.size > 5 * 1024 * 1024) {
        alert('حجم الملف يجب أن يكون أقل من 5MB');
        return;
      }

      originalInvoiceFile = file;

      // عرض معاينة الفاتورة
      document.getElementById('originalInvoicePreview').style.display = 'block';
      document.getElementById('viewOriginalInvoice').href = URL.createObjectURL(file);
    }

    // حذف الفاتورة الأصلية
    function removeOriginalInvoice() {
      originalInvoiceFile = null;
      document.getElementById('originalInvoiceUpload').value = '';
      document.getElementById('originalInvoicePreview').style.display = 'none';
    }

    document.getElementById('typePay').addEventListener('change', function() {
      if (this.value === 'wait') {
        // document.getElementById('dateWantDebtDiv').removeAttribute('hidden');
        document.getElementById('dateWantDebtDiv').classList.add('show');
        document.getElementById('dateWantDebtDiv').style.display = 'block';
      } else {
        // document.getElementById('dateWantDebtDiv').setAttribute('hidden', true);
        document.getElementById('dateWantDebtDiv').classList.remove('show');
        document.getElementById('dateWantDebtDiv').style.display = 'none';
      }
    });
  </script>

  <!-- استدعاء المكتبات -->
  <script src="JS/written-number.min.js"></script>
  <script src="JS/i18n/ar.json"></script>
  <script src="JS/i18n/en.json"></script>
  <script src="JS/currencyConverter.js"></script>
  <script src="payFromSuppliers/payFromSupplier.js"></script>
  <script src="payFromSuppliers/calculatSendItem.js"></script>

  <?php include('headAndFooter/footer.php'); ?>