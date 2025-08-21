// متغيرات عامة
var salesmaneID;
var customerID;
var selectedItemID = null;
var invoiceItemsCount = 0;
var searchDebounceTimer = null;
const VAT_RATE = 0.15;  
var unitMain = "";
var priceSelectValue = [];

// تهيئة الصفحة عند التحميل
document.addEventListener('DOMContentLoaded', function () {
  // تهيئة تاريخ الفاتورة
  var today = new Date();
  document.getElementById('invoiceDate').textContent = today.toLocaleDateString('ar-SA');

  // تهيئة عداد الفاتورة
  updateInvoiceCounter(0);


  // تحميل جميع الأصناف
  getItems();

  // تهيئة حقل البحث مع دالة debounce
  const searchInput = document.getElementById('searchItem');
  if (searchInput) {
    searchInput.addEventListener('input', debounce(handleSearch, 300));
    searchInput.addEventListener('keyup', function (e) {
      if (e.key === 'Escape') clearSearch();
    });
  }

  // تهيئة الماسح الضوئي للباركود
  initBarcodeScanner();
});

// دالة debounce لتحسين أداء البحث
function debounce(func, wait) {
  return function () {
    const context = this;
    const args = arguments;
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
      func.apply(context, args);
    }, wait);
  };
}

// معالجة البحث
function handleSearch() {
  const searchTerm = this.value.toLowerCase().trim();
  const searchResults = document.getElementById('searchResults');
  const noResultsMsg = document.getElementById('noResultsMessage');

  if (searchTerm === '') {
    clearSearch();
    return;
  }

  const items = document.querySelectorAll('#items .item-button');
  let foundCount = 0;

  items.forEach(item => {
    const itemText = item.textContent.toLowerCase();
    const itemID = item.getAttribute('data-item-id') || '';

    if (itemText.includes(searchTerm) || itemID.includes(searchTerm)) {
      item.style.display = 'inline-flex';
      foundCount++;
    } else {
      item.style.display = 'none';
    }
  });

  if (foundCount === 0) {
    if (!noResultsMsg) {
      const itemsContainer = document.getElementById('items');
      const msg = document.createElement('div');
      msg.id = 'noResultsMessage';
      msg.className = 'alert alert-warning text-center mt-3';
      msg.innerHTML = 'لم يتم العثور على نتائج';
      itemsContainer.appendChild(msg);
    } else {
      noResultsMsg.style.display = 'block';
    }
  } else if (noResultsMsg) {
    noResultsMsg.style.display = 'none';
  }

  if (searchResults) {
    searchResults.textContent = `${foundCount} نتيجة`;
    searchResults.className = foundCount > 0 ? 'text-success fw-bold' : 'text-danger fw-bold';
  }
}

// مسح البحث
function clearSearch() {
  const searchInput = document.getElementById('searchItem');
  if (searchInput) {
    searchInput.value = '';
    const items = document.querySelectorAll('#items .item-button');
    items.forEach(item => item.style.display = 'inline-flex');

    const noResultsMsg = document.getElementById('noResultsMessage');
    if (noResultsMsg) noResultsMsg.style.display = 'none';

    const searchResults = document.getElementById('searchResults');
    if (searchResults) searchResults.textContent = '';
  }
}

// 21-7-2025
// دالة إضافة عنصر إلى الفاتورة
// تم إضافة stock لعرض كمية المتاحة
function addItemToInvoice(itemID , stock) {
  console.log("تم استدعاء addItemToInvoice مع itemID:", itemID);
  
  if (!itemID) {
    console.error('لم يتم تحديد معرف الصنف');
    showToast('لم يتم تحديد صنف', 'danger');
    return;
  }

  selectedItemID = itemID;
  priceSelectValue = []; // إعادة تعيين مصفوفة الأسعار

  // جلب تفاصيل الصنف
  fetch(`sale2Customer/getItemDetails.php?itemID=${itemID}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        console.log('تفاصيل الصنف:', data.item);
        const item = data.item;
        
        document.querySelector('#sendItem .modal-title').textContent = item.itemName;
        document.getElementById('itemID').value = itemID;
        
        const unitSelect = document.getElementById('unitSelect');
        unitSelect.innerHTML = '';
        
        if (item.unitL) {
          const option = document.createElement('option');
          option.value = 'L';
          option.dataset.factor = item.L2M || 1;
          priceSelectValue.push(item.priceL);
          option.textContent = item.unitL;
          unitSelect.appendChild(option);
          document.getElementById('priceItem').value = item.priceL || 0;
        }
        
        if (item.unitM) {
          const option = document.createElement('option');
          option.value = 'M';
          option.dataset.factor = item.M2S || 1;
          priceSelectValue.push(item.priceM);
          option.textContent = item.unitM;
          unitSelect.appendChild(option);
        }
        
        if (item.unitS) {
          const option = document.createElement('option');
          option.value = 'S';
          option.dataset.factor = (item.S2L || 1) * (item.M2S || 1);
          priceSelectValue.push(item.priceS);
          option.textContent = item.unitS;
          unitSelect.appendChild(option);
        }
        
        showSendItemModal();
        document.getElementById('countItem').focus();
        document.getElementById('priceItem').value = item.priceL || 0;
        document.getElementById('priceLabel1').textContent ="الكمية المتاحة:  " + stock + "  " + item.unitL;
       
        
      } else {
        throw new Error(data.message || 'فشل جلب بيانات الصنف');
      }
    })
    .catch(error => {
      console.error('حدث خطأ:', error);
      showToast('حدث خطأ في جلب بيانات الصنف', 'danger');
    });



  

  // إعادة تعيين القيم
  document.getElementById('countItem').value = '';
  document.getElementById('priceItem').value = '';
  document.getElementById('priceXcount').value = '';
  document.getElementById('price_vat').value = '';
  document.getElementById('price_vatXcount').value = '';
  document.getElementById('totalDisplay').value = '';

  // إضافة مستمعي الأحداث
  document.getElementById('countItem').addEventListener('input', calculateTotals);
  document.getElementById('priceItem').addEventListener('input', calculateTotals);
}

// تغيير الوحدة
function unitSelectChange() {
  const unitSelect = document.getElementById('unitSelect');
  if (unitSelect.value === 'L') {
    document.getElementById('priceItem').value = priceSelectValue[0];
  } else if (unitSelect.value === 'M') {
    document.getElementById('priceItem').value = priceSelectValue[1];
  } else if (unitSelect.value === 'S') {
    document.getElementById('priceItem').value = priceSelectValue[2];
  }
  calculateTotals();
}

// إرفاق حدث تغيير الوحدة
document.getElementById('unitSelect').addEventListener('change', unitSelectChange);


// إظهار مودال إضافة العنصر
function showSendItemModal() {
  document.getElementById('sendItem').classList.add('show');
  document.getElementById('sendItem').style.display = 'block';
}

// إخفاء مودال إضافة العنصر
function hideSendItemModal() {
  document.getElementById('countItem').removeEventListener('input', calculateTotals);
  document.getElementById('priceItem').removeEventListener('input', calculateTotals);
  document.getElementById('sendItem').classList.remove('show');
  document.getElementById('sendItem').style.display = 'none';
  selectedItemID = null;
}

// تأكيد إضافة عنصر
function confirmAddItem() {
  const count = document.getElementById('countItem').value;
  const price = parseFloat(document.getElementById('priceItem').value);
  const discount = parseFloat(document.getElementById('discountItem').value);
  const selectedItemID = document.getElementById('itemID').value;
  const unitSelect = document.getElementById('unitSelect');
  let unit = '';

  if (unitSelect.selectedIndex !== -1) {
    unit = unitSelect.options[unitSelect.selectedIndex].textContent.trim();
  }

  if (isNaN(count) || count <= 0) {
    showToast('يرجى إدخال عدد صحيح أكبر من الصفر', 'danger');
    document.getElementById('countItem').focus();
    return;
  }

  if (isNaN(price) || price <= 0) {
    showToast('يرجى إدخال سعر صحيح أكبر من الصفر', 'danger');
    document.getElementById('priceItem').focus();
    return;
  }

  if (!unit) {
    showToast('يرجى اختيار وحدة القياس', 'danger');
    document.getElementById('unitSelect').focus();
    return;
  }

  const itemName = document.querySelector('#sendItem .modal-title').textContent || 'عنصر';
  const itemData = {
    id: selectedItemID,
    name: itemName,
    quantity: count,
    unit: unit,
    price: price,
    discount: discount,
    priceWithVat: (price * (1 + VAT_RATE)).toFixed(2),
    total: (count * price).toFixed(2),
    totalWithVat: ((count * price * (1 + VAT_RATE)) - discount).toFixed(2)
  };

  addItemToInvoiceTable(itemData);
  hideSendItemModal();
  showToast(`تمت إضافة ${itemName} (${count} ${unit}) بنجاح`, 'success');
}

// إضافة عنصر إلى جدول الفاتورة
function addItemToInvoiceTable(itemData) {
  const tableBody = document.getElementById('invoiceItemsTable');
  const existingRow = document.querySelector(`#invoiceItemsTable tr td:nth-child(2)[data-item-id="${itemData.id}"]`)?.closest('tr');

  if (existingRow) {
    const quantityCell = existingRow.querySelector('td:nth-child(4)');
    const totalCell = existingRow.querySelector('td:nth-child(7)');

    const newQuantity = (parseFloat(quantityCell.textContent) + parseFloat(itemData.quantity)).toFixed(2);
    const newTotal = (newQuantity * itemData.price).toFixed(2);

    quantityCell.textContent = newQuantity;
    totalCell.textContent = newTotal;

    existingRow.classList.add('table-warning');
    setTimeout(() => existingRow.classList.remove('table-warning'), 2000);
  } else {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${invoiceItemsCount + 1}</td>
      <td data-item-id="${itemData.id}">${itemData.id}</td>
      <td>${itemData.name}</td>
      <td>${parseFloat(itemData.quantity).toFixed(2)}</td>
      <td>${itemData.unit}</td>
      <td>${parseFloat(itemData.price).toFixed(2)}</td>
      <td>${parseFloat(itemData.priceWithVat).toFixed(2)}</td>
      <td>${parseFloat(itemData.discount).toFixed(2)}</td>
      <td>${parseFloat(itemData.total).toFixed(2)}</td>
      <td>
        <button class="btn btn-sm btn-danger" onclick="removeItemFromInvoice(this)">
          <i class="fa fa-trash"></i>
        </button>
      </td>
    `;

    tableBody.appendChild(row);
    invoiceItemsCount++;
    updateInvoiceCounter(invoiceItemsCount);
    
    row.classList.add('table-success');
    setTimeout(() => row.classList.remove('table-success'), 2000);
  }

  calculateInvoiceTotals();
}

// إزالة عنصر من الفاتورة
function removeItemFromInvoice(button) {
  if (confirm('هل أنت متأكد من حذف هذا العنصر من سلة المشتريات؟')) {
    const row = button.closest('tr');
    row.classList.add('table-danger');

    setTimeout(() => {
      row.remove();
      document.querySelectorAll('#invoiceItemsTable tr').forEach((tr, index) => {
        tr.querySelector('td:first-child').textContent = index + 1;
      });

      updateInvoiceCounter(document.querySelectorAll('#invoiceItemsTable tr').length);
      calculateInvoiceTotals();
    }, 500);
  }
}

// تحديث عداد الفاتورة
function updateInvoiceCounter(count) {
  invoiceItemsCount = count;
  const counter = document.getElementById('invoiceCounter');
  counter.textContent = count;
  counter.style.display = count > 0 ? 'flex' : 'none';
}

// حساب الإجماليات
function calculateTotals() {
  const count = parseFloat(document.getElementById('countItem').value) || 0;
  const price = parseFloat(document.getElementById('priceItem').value) || 0;
  const discount = parseFloat(document.getElementById('discountItem').value) || 0;

  const totalBeforeVat = (count * price).toFixed(2);
  const priceWithVat = (price * (1 + VAT_RATE)).toFixed(2);
  const totalWithVat = (totalBeforeVat * (1 + VAT_RATE)).toFixed(2);

  document.getElementById('priceXcount').value = totalBeforeVat;
  document.getElementById('discountItem').value = discount;
  document.getElementById('price_vat').value = priceWithVat;
  document.getElementById('price_vatXcount').value = totalWithVat;
  document.getElementById('totalDisplay').value = totalWithVat;
}

// حساب إجماليات الفاتورة
function calculateInvoiceTotals() {
  let subtotal = 0;
  let totalDiscount = 0;
  const discount = document.querySelectorAll('#invoiceItemsTable tr');
  const rows = document.querySelectorAll('#invoiceItemsTable tr');

  rows.forEach(row => {
    const totalCell = row.querySelector('td:nth-child(9)');
    if (totalCell) {
      const totalValue = parseFloat(totalCell.textContent.replace(/[^0-9.-]+/g, "")) || 0;
      subtotal += totalValue;
    }
  });
// حساب الخصم
  discount.forEach(row => {
    const discountCell = row.querySelector('td:nth-child(8)');
    if (discountCell) {
      const discountValue = parseFloat(discountCell.textContent.replace(/[^0-9.-]+/g, "")) || 0;
      totalDiscount += discountValue;
    }
  });
  let taxAmount = subtotal * VAT_RATE;
  let total = subtotal + taxAmount;
  let finalTotal = total - totalDiscount;

  // writtenNumber(1234, {lang: 'ar'});
  let finalTotalAr = convertAmountToWords(finalTotal.toFixed(2));
  let finalTotalEn = convertAmountToWords(finalTotal.toFixed(2));
// تحديث العناصر

  document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' ريال';
  document.getElementById('discount').textContent = totalDiscount.toFixed(2) + ' ريال';
  document.getElementById('taxAmount').textContent = taxAmount.toFixed(2) + ' ريال';
  document.getElementById('totalAmount').textContent = total.toFixed(2) + ' ريال';
  document.getElementById('finalTotal').textContent = finalTotal.toFixed(2) + ' ريال';
  document.getElementById('finalTotalAr').textContent = finalTotalAr.ar;
  document.getElementById('finalTotalEn').textContent = finalTotalEn.en;

  return finalTotal;
}

// تأكيد الشراء
function confirmPurchase() {
  const rows = document.querySelectorAll('#invoiceItemsTable tr');
  if (rows.length === 0) {
    showToast('لا توجد عناصر في سلة المشتريات', 'warning');
    return;
  }

  const total = calculateInvoiceTotals();
  const totalFormatted = total.toFixed(2) + ' ريال';

  if (confirm(`هل أنت متأكد من تأكيد الشراء بمبلغ ${totalFormatted}؟`)) {
    // sendDataInvoice();
    document.getElementById('paidInput').value = total.toFixed(2);
    document.getElementById('changeInput').value = 0;
    showInvoiceInfoModal();
  }
}

// إرسال بيانات الفاتورة
function sendDataInvoice() {
  const loadingToast = showToast('جاري حفظ الفاتورة...', 'info', 0);
  
  const taxElement = document.getElementById('taxAmount');
  const discountElement = document.getElementById('discount');
  const totalElement = document.getElementById('totalAmount');
  const paymentMethod = document.getElementById('typePay');
  const vatValue = taxElement ? parseFloat(taxElement.textContent.replace(/[^0-9.-]+/g, "")) || 0 : 0;
  const generalTotalValue = totalElement ? parseFloat(totalElement.textContent.replace(/[^0-9.-]+/g, "")) || 0 : 0;
  const discountValue = discountElement ? parseFloat(discountElement.textContent.replace(/[^0-9.-]+/g, "")) || 0 : 0;
  const dateRemainingAmount = document.getElementById('dateWantDebt').value;
  const paidAmount = document.getElementById('paidInput').value;
  const changeAmount = document.getElementById('changeInput').value;

  const invoiceData = {
    salesmaneID: salesmaneID,
    customerID: customerID,
    state: 1,
    action: "out",
    paidDate: new Date().toISOString(),
    paymentMethod:paymentMethod.value,
    discount: discountValue,
    totalDue: generalTotalValue,
    vat: vatValue,
    generalTotal: generalTotalValue,
    paidAmount: paidAmount ?? 0,
    changeAmount: changeAmount ?? 0,
    dateRemainingAmount: dateRemainingAmount??null,
    notes: 'فاتورة ضريبية من المندوب رقم  ' + salesmaneID + '  الى العميل رقم ' + customerID,
    items: [],
    total: calculateInvoiceTotals()
  };

  // جمع بيانات العناصر
  const rows = document.querySelectorAll('#invoiceItemsTable tr');
  if (rows.length === 0) {
    showToast('لا توجد عناصر في الفاتورة!', 'danger');
    return;
  }

  rows.forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length >= 7) {
      const itemID = parseInt(cells[1].textContent);
      const quantity = parseFloat(cells[3].textContent);
      const price = parseFloat(cells[5].textContent.replace(/[^0-9.-]+/g, ""));
      const total = parseFloat(cells[6].textContent.replace(/[^0-9.-]+/g, ""));

      if (!isNaN(itemID) && !isNaN(quantity) && !isNaN(price) && !isNaN(total)) {
        invoiceData.items.push({
          itemID: itemID,
          itemName: cells[2].textContent.trim(),
          quantity: quantity,
          unit: cells[4].textContent.trim(),
          price: price,
          total: total
        });
      }
    }
  });

  if (invoiceData.items.length === 0) {
    showToast('لا توجد عناصر صالحة في الفاتورة!', 'danger');
    return;
  }

  // إرسال البيانات
  fetch('sale2Customer/addItemToInvoice.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(invoiceData)
  })
  .then(async response => {
    const text = await response.text();
    try {
      const data = text ? JSON.parse(text) : {};
      if (!response.ok) {
        throw new Error(data.message || 'خطأ في الخادم');
      }
      return data;
    } catch (e) {
      console.error('فشل تحليل JSON:', text);
      throw new Error('استجابة غير صالحة من الخادم');
    }
  })
  .then(data => {
    if (data.success) {
      showToast('تم حفظ الفاتورة بنجاح', 'success');
      setTimeout(() => {
        window.location.href = 'index.php';
      }, 1500);
    } else {
      throw new Error(data.message || 'فشل حفظ الفاتورة');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast(`حدث خطأ: ${error.message}`, 'danger');
  })
  .finally(() => {
    if (loadingToast && loadingToast.hide) {
      loadingToast.hide();
    }
  });
}

// عرض رسالة toast
function showToast(message, type = 'success') {
  const toastContainer = document.createElement('div');
  toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
  toastContainer.style.zIndex = '1050';

  toastContainer.innerHTML = `
    <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  `;

  document.body.appendChild(toastContainer);
  const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
  toast.show();

  toastContainer.querySelector('.toast').addEventListener('hidden.bs.toast', function () {
    document.body.removeChild(toastContainer);
  });
}

// تهيئة الماسح الضوئي للباركود
function initBarcodeScanner() {
  const toggleScannerBtn = document.getElementById('toggleScannerBtn');
  const previewContainer = document.getElementById('preview-container');
  const previewVideo = document.getElementById('preview');
  const qrtextInput = document.getElementById('qrtext');
  const statusMessageDiv = document.getElementById('statusMessage');

  let scanner = null;
  let isScannerRunning = false;

  function showStatusMessage(message, type = 'error') {
    statusMessageDiv.textContent = message;
    statusMessageDiv.className = 'message ' + type;
    statusMessageDiv.style.display = 'block';
    setTimeout(() => {
      statusMessageDiv.style.display = 'none';
    }, 5000);
  }

  async function startScanner() {
    toggleScannerBtn.disabled = true;
    toggleScannerBtn.textContent = 'جاري التشغيل...';
    previewContainer.style.display = 'block';
    qrtextInput.value = '';
    qrtextInput.placeholder = 'جاري البحث عن باركود...';
    showStatusMessage('جاري تشغيل الكاميرا...', 'info');

    if (!scanner) {
      scanner = new Instascan.Scanner({
        video: previewVideo,
        scanPeriod: 5,
        mirror: false
      });

      scanner.addListener('scan', function (content) {
        qrtextInput.value = content;
        addItemToInvoice(content , 1000);
        stopScanner(true);
        showStatusMessage('تم مسح الباركود بنجاح!', 'success');
      });

      scanner.addListener('active', function () {
        showStatusMessage('الكاميرا قيد التشغيل. وجّه الكاميرا نحو الباركود.', 'success');
        toggleScannerBtn.textContent = 'إيقاف المسح';
        toggleScannerBtn.disabled = false;
        isScannerRunning = true;
      });
    }

    try {
      const cameras = await Instascan.Camera.getCameras();
      if (cameras.length > 0) {
        let selectedCamera = cameras[0];
        for (let i = 0; i < cameras.length; i++) {
          if (cameras[i].name && (cameras[i].name.toLowerCase().includes('back') || cameras[i].name.toLowerCase().includes('environment'))) {
            selectedCamera = cameras[i];
            break;
          }
          if (i === 0 && cameras.length > 1) {
            selectedCamera = cameras[1];
          }
        }
        await scanner.start(selectedCamera);
      } else {
        showStatusMessage('لم يتم العثور على أي كاميرات في جهازك.', 'error');
        stopScanner(false);
      }
    } catch (err) {
      let errorMessage = 'لم يتمكن من الوصول إلى الكاميرا. يرجى التأكد من منح الإذن.';
      if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
        errorMessage = 'تم رفض الوصول إلى الكاميرا. يرجى السماح بالوصول في إعدادات المتصفح/الجهاز.';
      } else if (err.name === 'NotFoundError') {
        errorMessage = 'لم يتم العثور على كاميرا في جهازك.';
      } else if (err.name === 'NotReadableError') {
        errorMessage = 'الكاميرا قيد الاستخدام بواسطة تطبيق آخر.';
      }
      showStatusMessage(errorMessage, 'error');
      stopScanner(false);
    }
  }

  function stopScanner(resetMessage = true) {
    if (scanner && isScannerRunning) {
      scanner.stop();
    }
    isScannerRunning = false;
    previewContainer.style.display = 'none';
    toggleScannerBtn.textContent = 'بدء المسح';
    toggleScannerBtn.disabled = false;
    if (resetMessage) {
      qrtextInput.placeholder = 'الباركود الممسوح سيظهر هنا';
    }
  }

  toggleScannerBtn.addEventListener('click', function () {
    if (isScannerRunning) {
      stopScanner();
    } else {
      startScanner();
    }
  });

  window.addEventListener('beforeunload', function () {
    if (scanner && isScannerRunning) {
      scanner.stop();
    }
  });
}



function getItems() {
  fetch(`sale2Customer/getItems.php?salesmaneID=${salesmaneID} +&customerID=${customerID}`)
    .then(response => response.text())
    .then(data => {
      document.getElementById('items').innerHTML = data;
      clearSearch();
    });
}

// عرض/إخفاء المودالات
function showInvoiceModal() {
  document.getElementById('invoiceModal').classList.add('show');
  document.getElementById('invoiceModal').style.display = 'block';
}

function hideInvoiceModal() {
  document.getElementById('invoiceModal').classList.remove('show');
  document.getElementById('invoiceModal').style.display = 'none';
}

function showInvoiceInfoModal() {
  document.getElementById('invoiceInfoModal').classList.add('show');
  document.getElementById('invoiceInfoModal').style.display = 'block';
}

function hideInvoiceInfoModal() {
  document.getElementById('invoiceInfoModal').classList.remove('show');
  document.getElementById('invoiceInfoModal').style.display = 'none';
}

// طباعة الفاتورة
function printInvoice() {
  window.print();
}

// دالة مساعدة لتنسيق الأرقام
Number.prototype.format = function() {
  return this.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
};