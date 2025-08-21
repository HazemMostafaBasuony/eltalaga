<?php include('headAndFooter/headMop.php');

use Dom\Document; ?>



<div class="container-fluid mt-2">


  <?php
  $customerID = $_GET['customerID'];
  $salesmaneID = $_GET['salesmaneID'];
  if (isset($_GET['remainingAmount'])) {
    $remainingAmount = $_GET['remainingAmount'];
    if ($remainingAmount > 0) {
      $class_remaining = "available";
      $state_remaining = "";
    } else {
      $class_remaining = "unavailable";
      $state_remaining = "hidden";
    }
  }
  //  print($customerID);
//  print($salesmaneID); 
  
  ?>

  <input type="hidden" id="salesmaneID" value="<?php echo $salesmaneID; ?>">
  <input type="hidden" id="customerID" value="<?php echo $customerID; ?>">
  <input type="hidden" id="invoiceID" value="-1">
  <input type="hidden" id="totalInvoice" value="-10">

  <!-- شريط البحث المحسن -->
  <div class="card mb-3 shadow-sm <?php echo $class_remaining; ?>" <?php echo $state_remaining; ?>>
    <div class="card-header bg-info text-white py-2">
      <h6>يوجد مبلغ : <span id="remainingAmountWant"><?php echo $remainingAmount; ?></span> </h6>
      <a href="voucher.php?userID=<?php echo $salesmaneID; ?>&customerID=<?php echo $customerID; ?>">
        <button class="btn btn-primary" id="btnCollectAmount">تحصيل مبلغ</button>
      </a>
    </div>
  </div>
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

  <!-- قسم الأصناف المحسن -->
  <div class="card shadow-lg border-0 ">
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
<div id="sendItem" class="modal" tabindex="-1" dir="rtl">
  <!-- getItemDetails -->

  <!-- // المطلوب -->
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header text-center">
        <button type="button" class="btn-close" onclick="hideSendItemModal()"></button>
        <h5 class="modal-title " id="itemName_modal">إضافة عنصر </h5>
        <input type="hidden" id="itemID">
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label for="countItem" class="form-label">كمية: <span class="text-danger ms-1" id="commintCount">
              <p class="text-danger">*</p> لديك
            </span></label>
          <input id="countItem" type="number" class="form-control" placeholder="أدخل العدد" min="1" step="1" value="1">
        </div>
        <div class="mb-3">
          <div class="row">
            <div class="col-6">
              <label for="priceItem" class="form-label">السعر:</label>
              <input id="priceItem" type="number" class="form-control" placeholder="أدخل السعر" min="0" step="0.01"
                value="">
            </div>
            <div class="col-6">
              <label for="discountItem" class="form-label">خصم:</label>
              <input id="discountItem" type="number" class="form-control" placeholder="أدخل الخصم" min="0" step="1"
                value="0">
            </div>
          </div>
        </div>
        <div class="mb-3">
          <label for="unitSelect" class="form-label">الوحدة:</label>
          <select id="unitSelect" class="form-select">
            <option id="unitL" value="L"></option>
            <option id="unitM" value="M"></option>
            <option id="unitS" value="S"></option>
          </select>
        </div>

      </div>

      <div class="modal-footer justify-content-center">
        <button class="btn btn-primary btn-sm w-50" id="add" onclick="confirmAddItem()">إضافة للفاتورة</button>
        <button onclick="hideSendItemModal()" class="btn btn-secondary btn-sm ">إلغاء</button>
      </div>
    </div>
  </div>


</div>

<!-- أيقونة الفاتورة -->
<div class="invoice-icon-container">
  <img src="assets/images/invoice.png" alt="فاتورة" class="invoice-icon" onclick="showInvoiceModal()">
  <div class="invoice-counter" id="invoiceCounter">0</div>
</div>

<!-- مودال الفاتورة -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
  <div id="printInvoiceArea" class="modal-dialog modal-xl">
    <div class="modal-content p-3">

      <!-- الهيدر -->
      <div class="modal-header border-bottom pb-2 d-flex justify-content-between align-items-center">

        <!-- يمين: اللوجو -->
        <div>
          <img id="companyLogo" src="assets/images/logo7.png" alt="Company Logo" style="max-height:60px;">
        </div>

        <!-- شمال: عنوان وتاريخ ورقم فاتورة -->
        <div class="text-end">
          <h5 class="small" id="invoiceModalLabel">فاتورة ضريبية</h5>
          <div class="small">تاريخ الفاتورة: <span id="invoiceDate"></span></div>
          <div class="fw-bold">رقم الفاتورة: <span id="invoiceNumber"></span></div>
        </div>

      </div>

      <div class="modal-body">
        <!-- بيانات المؤسسة والعميل -->
        <div class="row mb-2">
          <!-- بيانات المؤسسة -->
          <div class="col-6 text-start">
            <h6 class="fw-bold" id="companyName">اسم الشركة</h6>
            <div>السجل التجاري: <span id="numberTax"></span></div>
            <div>الرقم الضريبي: <span id="numberRC"></span></div>
            <div>العنوان: <span id="companyAddress"></span></div>
          </div>
          <!-- بيانات العميل -->
          <div class="col-6 text-end">
            <h6 class="fw-bold">بيانات العميل</h6>
            <div>الاسم: <span id="customerName"></span></div>
            <div>رقم الهاتف: <span id="customerPhone"></span></div>
            <div>العنوان: <span id="customerAddress"></span></div>
          </div>
        </div>

        <!-- جدول الأصناف -->
        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle text-center">
            <thead class="table-light">
              <tr>
                <th style="width:5%">رقم</th>
                <th style="width:40%">الصنف</th>
                <th style="width:7%">الكمية</th>
                <th style="width:7%">الوحدة</th>
                <th style="width:7%">السعر</th>
                <th style="width:7%">الإجمالي</th>
                <th style="width:7%">الخصم</th>
                <th style="width:7%">الضريبة</th>
                <th style="width:12%">الإجمالي النهائي</th>
                <th class="d-print-none" style="width:6%">الإجراءات</th>
              </tr>
            </thead>
            <tbody id="invoiceItemsTableBody"></tbody>
          </table>
        </div>

        <!-- الإجماليات و QR -->
        <div class="row mt-3">
          <div class="col-4 text-end">
            <div id="vatQrCode"></div>
          </div>
          <div class="col-8 text-end">
            <div>الإجمالي: <span id="totalAmount"></span></div>
            <div>الضريبة: <span id="vatAmount"></span></div>
            <div>الإجمالي مع الضريبة: <span id="totalWithVat"></span></div>
            <div>الخصم: <span id="discountAmount"></span></div>
            <div class="fw-bold mb-1">المجموع الكلي: <span id="grandTotal"></span></div>
            <div>الإجمالي بالحروف: <span id="totalInWordsAR"></span></div>
            <div>total in words: <span id="totalInWordsEN"></span></div>
            <div>المدفوع: <span id="paidAmount"></span></div>
            <div>الحساب القديم + حساب الفاتورة: <span id="remainingAmount"></span></div>
          </div>
        </div>
      </div>

      <!-- فوتر المودال -->
      <div class="modal-footer border-top pt-2 d-print-none">
        <button id="btnFinalize" class="btn btn-success">إنهاء الشراء</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>



<!-- تفاصيل الفاتورة -->
<!-- مودال إغلاق الفاتورة -->
<div id="closeInvoiceModal" class="modal" tabindex="-1" dir="rtl">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        <h5 class="modal-title">إنهاء الشراء</h5>

      </div>

      <div class="modal-body">
        <!-- الإجماليات -->
        <div class="mb-3">
          <label class="form-label">إجمالي الفاتورة</label>
          <input type="text" id="totalInvoiceAmount" class="form-control" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">حساب العميل القديم</label>
          <input type="text" id="oldCustomerBalance" class="form-control" readonly>
        </div>

        <!-- المبلغ المدفوع -->
        <div class="mb-3">
          <label class="form-label">المبلغ المدفوع</label>
          <input type="number" id="paidAmountClose" class="form-control" min="0" step="0.01">
        </div>
        <!-- المبلغ المتبقى -->
        <div class="mb-3">
          <label class="form-label">المبلغ المتبقي</label>
          <input type="number" id="remainingAmountClose" class="form-control" min="0" step="0.01" readonly>
        </div>

        <!-- طريقة الدفع -->
        <div class="mb-3">
          <label class="form-label">طريقة الدفع</label>
          <select id="paymentMethod" class="form-select">
            <option value="cash">كاش</option>
            <option value="card">شبكة</option>
            <option value="transfer">تحويل</option>
            <option value="credit">أجل</option>
          </select>
        </div>

        <!-- تاريخ السداد (يظهر فقط عند الدفع بالأجل) -->
        <div class="mb-3" id="dueDateGroup" style="display:none;">
          <label class="form-label">تاريخ السداد</label>
          <input type="date" id="dueDate" class="form-control">
        </div>
      </div>

      <div class="modal-footer justify-content-center">
        <button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button id="btnConfirmFinalize" class="btn btn-primary">إنهاء</button>
      </div>
    </div>
  </div>
</div>


<style>
  .invoice-icon-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
    width: 60px;
    height: 60px;
  }

  .invoice-icon {
    width: 60px;
    height: 60px;
    cursor: pointer;
    border-radius: 50%;
    transition: transform 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  }




  .invoice-icon:hover {
    transform: scale(1.1);
  }


  .invoice-counter {
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    position: absolute;
    top: -8px;
    right: -8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  }


  .item-card {
    transition: all 0.3s ease;
    cursor: pointer;
    height: 100%;
  }

  .item-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }

  #preview-container {
    position: relative;
    margin-top: 15px;
    display: none;
  }

  #preview {
    width: 100%;
    height: auto;
    border-radius: 8px;
  }

  .center-text {
    text-align: center;
    margin-top: 10px;
  }

  @media (max-width: 768px) {
    .item-img {
      height: 60px;
      object-fit: contain;
    }

    .card-header h4 {
      font-size: 1.2rem;
    }
  }
</style>

<style>
  /* تنسيقات الطباعة */
  @media print {
    body * {
      visibility: hidden;
    }

    #printInvoiceArea,
    #printInvoiceArea * {
      visibility: visible;
    }

    #printInvoiceArea {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
    }

    .d-print-none {
      display: none !important;
    }
  }
</style>


<script>

  // البحث
  document.getElementById('searchItem').addEventListener('input', function () {
    var searchValue = document.getElementById('searchItem').value.toLowerCase();
    var items = document.querySelectorAll('#items .item-button');
    items.forEach(function (item) {
      var itemName = item.textContent.toLowerCase();
      if (itemName.includes(searchValue)) {
        item.style.display = 'block';
      } else {
        item.style.display = 'none';
      }
    });
  });






</script>

<script src="js/instascan.min.js"></script>
<!-- <script src="salesmane/salesmane.js"></script> -->
<script src="sale2Customer/items.js"></script>

<script src="sale2Customer/modals.js"></script>
<script src="js/qrcode.min.js"></script>
<script src="sale2Customer/qr.js"></script>
<!--تحويل الرقم إلى كلمات باللغتين العربية والإنجليزية -->
<script src="JS/written-number.min.js"></script>
<script src="JS/i18n/ar.json"></script>
<script src="JS/i18n/en.json"></script>
<script src="JS/currencyConverter.js"></script>
<script src="js/getLocation.js"></script>
<!--  
let finalTotalAr = convertAmountToWords(finalTotal.toFixed(2));
let finalTotalEn = convertAmountToWords(finalTotal.toFixed(2));
document.getElementById('finalTotalAr').textContent = finalTotalAr.ar;
document.getElementById('finalTotalEn').textContent = finalTotalEn.en;
-->



<?php include('headAndFooter/footer.php'); ?>