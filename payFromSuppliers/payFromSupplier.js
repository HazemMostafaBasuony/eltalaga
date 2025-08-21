// متغيرات عامة
var supplierID;
var remainingAmount;
var selectedItemID = null;
var invoiceItemsCount = 0;
var searchDebounceTimer = null;
const VAT_RATE = 0.15;
var unitMain = "";

let totalAmount = 0;

let totalFormatted = 0;


// تهيئة الصفحة عند التحميل
document.addEventListener('DOMContentLoaded', function () {
  // تهيئة تاريخ الفاتورة
  var today = new Date();
  document.getElementById('invoiceDate').textContent = today.toLocaleDateString('ar-SA');

  // تهيئة عداد الفاتورة
  updateInvoiceCounter(0);

  document.getElementById('originalInvoiceUpload').addEventListener('change', handleOriginalInvoiceUpload);



  // تحميل المجموعات الرئيسية
  getMainGroup();

  // تحميل جميع الأصناف
  getItems('all');

  // تهيئة حقل البحث مع دالة debounce
  const searchInput = document.getElementById('searchItem');
  if (searchInput) {
    searchInput.addEventListener('input', debounce(handleSearch, 300));
    searchInput.addEventListener('keyup', function (e) {
      if (e.key === 'Escape') clearSearch();
    });
  }
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

// معالجة البحث مع تحسين الأداء
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

  // عرض رسالة إذا لم توجد نتائج
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

  // تحديث عداد النتائج
  if (searchResults) {
    searchResults.textContent = `${foundCount} نتيجة`;
    searchResults.className = foundCount > 0 ? 'text-success fw-bold' : 'text-danger fw-bold';
  }
}

// دالة مسح البحث
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

// حساب إجماليات الفاتورة
function calculateInvoiceTotals() {
  let subtotal = 0;
  let totalDiscount = 0;
  const discount = document.querySelectorAll('#invoiceItemsTable tr');
  const rows = document.querySelectorAll('#invoiceItemsTable tr');

  rows.forEach(row => {
    const totalCell = row.querySelector('td:nth-child(7)');
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


//التجهيز لـــ إضافة عنصر إلى الفاتورة
function addItemToInvoice(itemID) {
  if (!itemID) {
    console.error('لم يتم تحديد معرف الصنف');
    showToast('لم يتم تحديد صنف', 'danger');
  } else {
    selectedItemID = itemID;
    priceSelectValue = []; // إعادة تعيين مصفوفة الأسعار
    document.getElementById('discountItem').value = 0;
    // const itemName = clickedElement.querySelector('strong')?.textContent || 'عنصر جديد';
    // document.querySelector('#sendItem .modal-title').textContent = itemName;
    // fetchItemDetails(selectedItemID);
    fetch(`payFromSuppliers/getItemDetails.php?itemID=${selectedItemID}`)
      .then(response => response.json())
      .then(data => {
        try {
          if (data.success) {
            console.log('تم استقبال البيانات:', data.item);
            const item = data.item;

            document.querySelector('#sendItem .modal-title').textContent = item.itemName;
            document.getElementById('itemID').value = itemID;
            // عرضوحدات القياس في المودال
            const unitSelect = document.getElementById('unitSelect');
            unitSelect.innerHTML = ''; // مسح الخيارات السابقة
            // add data.item.unitL ,data.item.unitM ,data.item.unitS
            if (data.item.unitL) {
              const option = document.createElement('option');
              option.value = 'L';
              option.data = data.item.L2M;
              unitMain = data.item.unitL; // <-- This sets unitMain for later use
              option.textContent = `${data.item.unitL} `;
              unitSelect.appendChild(option).textContent;
            }
            if (data.item.unitM) {
              const option = document.createElement('option');
              option.value = 'M';
              option.data = data.item.M2S;
              option.textContent = `${data.item.unitM} `;
              unitSelect.appendChild(option);
            }
            if (data.item.unitS) {
              const option = document.createElement('option');
              option.value = 'S';
              funUnitS = data.item.S2L * data.item.M2S;
              option.data = funUnitS;
              option.textContent = `${data.item.unitS} `;
              unitSelect.appendChild(option).textContent;
            }
          } else {
            console.error('خطأ:', data.message);
          }
        } catch (e) {
          console.error('Response is not valid JSON:', text);
          throw e;
        }
      })

      .catch(error => console.error('حدث خطأ:', error));


    // ايجاد اخر سعر و متوسط الاسعار
    fetch(`payFromSuppliers/getitemAction.php?itemID=${selectedItemID}`)
      .then(response => response.text())
      .then(text => {
        try {
          const data = JSON.parse(text);
          if (data.success && Array.isArray(data.itemActions) && data.itemActions.length > 0) {
            const lastAction = data.itemActions[0]; // Use the first (latest) action
            var sumCount = data.sumCount;
            var lastPrice = Number(data.lastPrice).toFixed(2);
            var sumPrice = Number(data.sumPrice).toFixed(2) || 0; // Ensure sumPrice is defined
            var countSale = data.countSale || 0;
            var average = 0;
            var lastcount = lastAction.count || 1; // Default to 1 if count is not available
            console.log('lastcount:  ', lastcount)

            var lastdate = lastAction.formatted_date || '';
            const priceLabel1 = document.getElementById('priceLabel1');
            const priceLabel2 = document.getElementById('priceLabel2');

            priceLabel1.textContent = ` آخر سعر: ${lastPrice} ريال`;
            // priceLabel1.textContent += ` لال  ${unitMain}`;
            if (countSale > 0) {
              average = sumPrice / countSale;

              priceLabel2.textContent = `  متوسط السعر( ${(average).toFixed(2)} ريال )`;
              priceLabel2.textContent += ` عدد مرات الشراء: ${countSale}`;
            } else { priceLabel2.textContent = " لا توجد بيانات متاحة "; }
            priceLabel1.textContent += `   من يوم : ${lastdate}`;
            // priceLabel1.textContent += `لال  ${unitMain}`; // Uncomment if unitMain is defined
          } else {
            console.error('خطأ:', data.message);
            document.getElementById('priceLabel1').textContent = 'لا توجد بيانات سعرية متاحة لهذا العنصر';
            document.getElementById('priceLabel2').textContent = '';
          }
        } catch (e) {
          console.error('Response is not valid JSON:', text);
        }
      })

      .catch(error => console.error('حدث خطأ:', error));

  }
  // إعادة تعيين القيم

  document.getElementById('countItem').value = '';
  // معرفة الحدث هل هو input , mousemove او غيره الذى يحدث فى countItemو طباعته فى الكوسول


  document.getElementById('priceItem').value = '';
  document.getElementById('priceXcount').value = '';
  document.getElementById('price_vat').value = '';
  document.getElementById('price_vatXcount').value = '';
  document.getElementById('totalDisplay').value = '';



  // عرض المودال
  document.getElementById('sendItem').classList.add('show');

  document.getElementById('countItem').focus();
  document.getElementById('countItem').addEventListener('keypress', function (event) {
    if (event.key === 'Enter') { // مفتاح Enter
      // منع السلوك الافتراضي
      event.preventDefault(); // منع السلوك الافتراضي
      //عند الضغط على انتر انتقل الى السعر
      document.getElementById('priceItem').focus();
    }
  });

  document.getElementById('priceItem').addEventListener('keypress', function (event) {
    if (event.key === 'Enter') { // مفتاح Enter
      // منع السلوك الافتراضي
      event.preventDefault(); // منع السلوك الافتراضي
      //عند الضغط على انتر انتقل الى السعر
      document.getElementById('unitSelect').focus();

    }

  });


}


function hideSendItemModal() {
  // إغلاق المودال
  document.getElementById('sendItem').classList.remove('show');
  selectedItemID = null;
}



// edit item
// إضافة عنصر إلى الفاتورة
// تأكيد إضافة عنصر
// هام لبيانات العنصر
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
    count: count,
    unit: unit,
    price: price,
    discount: discount,
    priceWithVat: (price * (1 + VAT_RATE)).toFixed(2),
    total: (count * price).toFixed(2),
    totalWithVat: ((count * price * (1 + VAT_RATE)) - discount).toFixed(2),
    vatRate: VAT_RATE * 100,
    vat: (count * price * VAT_RATE).toFixed(2),
    finalTotal: (count * price * (1 + VAT_RATE)).toFixed(2)
  };

  addItemToInvoiceTable(itemData);
  hideSendItemModal();
  showToast(`تمت إضافة ${itemName} (${count} ${unit}) بنجاح`, 'success');
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

// إضافة عنصر إلى جدول الفاتورة
function addItemToInvoiceTable(itemData) {
  const tableBody = document.getElementById('invoiceItemsTable');

  // البحث عن الصف الموجود بنفس الـ ID ونفس الوحدة
  // هذا هو السطر الرئيسي الذي سيتم تعديله
  const existingRow = document.querySelector(
    `#invoiceItemsTable tr td[data-item-id="${itemData.id}"][data-col="unit"][data-value="${itemData.unit}"]`
  )?.closest('tr');
  // ملاحظة: ستحتاج إلى إضافة data-value="${itemData.unit}" إلى خلية الوحدة عند إنشاء الصف

  let rowToAddOrUpdate = null;
  let isNewRowAdded = false;

  if (existingRow) {
    // بما أن البحث الأولي أصبح يشمل الوحدة،
    // فإذا وجدنا existingRow، فهذا يعني أن الـ ID والوحدة متطابقان
    // لذلك، ننتقل مباشرة إلى تحديث الصف
    updateExistingInvoiceRow(existingRow, itemData);
    rowToAddOrUpdate = existingRow; // لتطبيق تأثير التحذير

  } else {
    // العنصر غير موجود بهذا الـ ID وهذه الوحدة، أضف صفًا جديدًا
    rowToAddOrUpdate = createNewInvoiceRow(itemData);
    isNewRowAdded = true;
  }

  // إذا تم إضافة صف جديد، ألحقه بالجدول وحدّث العداد
  if (isNewRowAdded) {
    tableBody.appendChild(rowToAddOrUpdate);
    updateInvoiceCounter(invoiceItemsCount + 1);
    rowToAddOrUpdate.classList.add('table-success');
    setTimeout(() => rowToAddOrUpdate.classList.remove('table-success'), 2000);
    console.log("تم إضافة العنصر إلى الفاتورة:", itemData);
  } else if (rowToAddOrUpdate) {
    // إذا تم تحديث صف موجود
    rowToAddOrUpdate.classList.add('table-warning');
    setTimeout(() => rowToAddOrUpdate.classList.remove('table-warning'), 2000);
  }

  // تحديث الإجماليات الكلية للفاتورة في النهاية دائمًا
  calculateInvoiceTotals();
}


/**
 * دالة مساعدة لإنشاء صف جديد للفاتورة
 * @param {object} itemData - بيانات العنصر
 * @returns {HTMLElement} - عنصر الصف الجديد
 */
function createNewInvoiceRow(itemData) {
  const row = document.createElement('tr');
  row.innerHTML = `
      <td>${invoiceItemsCount + 1}</td>
      <td class="d-none">${itemData.id}</td>
      <td data-col="name">${itemData.name}</td>
      <td data-col="count">${parseFloat(itemData.count).toFixed(2)}</td>
      <td data-item-id="${itemData.id}" data-col="unit" data-value="${itemData.unit}">${itemData.unit}</td> 
      <td data-col="price">${parseFloat(itemData.price).toFixed(2)}</td>
      <td data-col="total">${parseFloat(itemData.total).toFixed(2)}</td>
      <td data-col="discount">${parseFloat(itemData.discount).toFixed(2)}</td>
      <td data-col="vatRate" class="d-none">${itemData.vatRate}</td>
      <td data-col="vat">${parseFloat(itemData.vat).toFixed(2)}</td>
      <td data-col="finalTotal">${parseFloat(itemData.finalTotal).toFixed(2)}</td>
      <td>
          <button class="btn btn-sm btn-danger d-print-none" onclick="removeItemFromInvoice(this)">
              <i class="fa fa-trash"></i>
          </button>
      </td>
  `;
  return row;
}

/**
* دالة مساعدة لتحديث صف موجود في الفاتورة
* @param {HTMLElement} existingRow - الصف الموجود المراد تحديثه
* @param {object} itemData - بيانات العنصر الجديدة
*/
function updateExistingInvoiceRow(existingRow, itemData) {
  const countCell = existingRow.querySelector('td[data-col="count"]');
  const totalCell = existingRow.querySelector('td[data-col="total"]');
  const discountCell = existingRow.querySelector('td[data-col="discount"]');
  const vatCell = existingRow.querySelector('td[data-col="vat"]');
  const finalTotalCell = existingRow.querySelector('td[data-col="finalTotal"]');

  const currentCount = parseFloat(countCell.textContent) || 0;
  const newCount = currentCount + parseFloat(itemData.count);

  countCell.textContent = newCount.toFixed(2);

  const priceCell = existingRow.querySelector('td[data-col="price"]');
  const itemPrice = parseFloat(priceCell.textContent) || 0;
  const newTotal = (newCount * itemPrice);
  totalCell.textContent = newTotal.toFixed(2);

  const currentDiscount = parseFloat(discountCell.textContent) || 0;
  const newDiscount = currentDiscount + parseFloat(itemData.discount);
  discountCell.textContent = newDiscount.toFixed(2);

  const vatRateCell = existingRow.querySelector('td[data-col="vatRate"]');
  const rowVatRate = parseFloat(vatRateCell.textContent) / 100 || 0;
  const newVat = (newTotal * rowVatRate);
  vatCell.textContent = newVat.toFixed(2);

  const newFinalTotal = (newTotal + newVat - newDiscount);
  finalTotalCell.textContent = newFinalTotal.toFixed(2);

  console.log("تم تحديث العنصر في الفاتورة:", itemData);
}




// إزالة عنصر من الفاتورة
function removeItemFromInvoice(button) {
  if (confirm('هل أنت متأكد من حذف هذا العنصر من سلة المشتريات؟')) {
    const row = button.closest('tr');
    row.classList.add('table-danger');

    setTimeout(() => {
      row.remove();

      // إعادة ترقيم الصفوف
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





// تأكيد الشراء
function confirmPurchase() {
  const total = calculateInvoiceTotals();
  totalAmount = total; // Store the numeric value
  totalFormatted = total.toFixed(2);


  // sendDataInvoice();
  document.getElementById('paidInput').value = totalFormatted;

  // Calculate change (assuming remainingAmount is defined elsewhere)
  const change = (remainingAmount).toFixed(2);

  showInvoiceInfoModal(change, totalAmount);

}

document.getElementById('paidInput').addEventListener('input', function () {
  const paidValue = Number(this.value) || 0;

  if (paidValue < 0 || this.value === "" || this.value === null) {
    document.getElementById('add').disabled = true;
  } else {
    document.getElementById('add').disabled = false;
    // Calculate change (total - paid + remaining)
    const change = (totalAmount - paidValue + remainingAmount).toFixed(2);
    document.getElementById('changeInput').value = change;
  }
});
// إرسال بيانات الفاتورة إلى الخادم
// المرحله الاخيره لارسال البيانات الى قاعدة البيانات 
// و منها يمكن الطباعه
async function sendDataInvoice() {
  const loadingToast = showToast('جاري حفظ الفاتورة...', 'info', 0);

  try {
    // 1. جمع بيانات العناصر من الجدول
    const rows = document.querySelectorAll('#invoiceItemsTable tr');
    if (rows.length === 0) {
      showToast('لا توجد عناصر في الفاتورة!', 'danger');
      return;
    }

    // 2. إنشاء كائن الفاتورة مرة واحدة خارج الحلقة
    const parseField = (selector) => {
      const element = document.getElementById(selector);
      return element ? parseFloat(element.textContent.replace(/[^0-9.-]+/g, "")) || 0 : 0;
    };

    const invoiceData = {
      fromID: supplierID,
      toID: document.getElementById('branchID').value||500,
      fromType: "supplier",
      toType: "branch",
      action: "purchase",
      state: 1,
      date: new Date().toISOString(),
      paymentMethod: document.getElementById('typePay').value,
      total: parseField('subtotal'),
      discount: parseField('discount'),
      totalDue: parseField('finalTotal'),
      vat: parseField('taxAmount'),
      generalTotal: parseField('finalTotal'),
      notes: document.getElementById('invoiceNotes').value || 'فاتورة شراء من المورد',
      paidAmount: parseFloat(document.getElementById('paidInput').value) || 0,
      remainingAmount: parseFloat(document.getElementById('changeInput').value) || 0,
      dateRemainingAmount: document.getElementById('dateWantDebt').value || "update1",
      items: [], // سيتم ملء هذا المصفوفة في الحلقة
      branchID: document.getElementById('branchID').value||500,
      wantDebt: parseFloat(document.getElementById('changeInput').value) || 0,
    };

    // 3. تجميع العناصر
    rows.forEach(row => {
      try {
        const cells = row.querySelectorAll('td');
        if (cells.length < 7) return;

        const itemIdElement = row.querySelector('[data-item-id]');
        const nameElement = row.querySelector('[data-col="name"]');
        const countElement = row.querySelector('[data-col="count"]');
        const unitElement = row.querySelector('[data-col="unit"]');
        const priceElement = row.querySelector('[data-col="price"]');
        const totalElement = row.querySelector('[data-col="total"]');
        const discountElement = row.querySelector('[data-col="discount"]');

        if (!itemIdElement || !nameElement || !countElement ||
          !unitElement || !priceElement || !totalElement || !discountElement) {
          console.warn('Missing required elements in row:', row);
          return;
        }

        const itemID = itemIdElement.dataset?.itemId || '';
        const itemName = nameElement.textContent.trim();
        const count = parseFloat(countElement.textContent) || 0;
        const unit = unitElement.dataset?.value || unitElement.textContent.trim();
        const price = parseFloat(priceElement.textContent) || 0;
        const total = parseFloat(totalElement.textContent) || 0;
        const discount = parseFloat(discountElement.textContent) || 0;

        if (itemID && itemName && !isNaN(count) && !isNaN(price) && !isNaN(total)) {
          invoiceData.items.push({
            itemID: itemID,
            itemName: itemName,
            count: count,
            unit: unit,
            price: price,
            total: total,
            discount: discount
          });
        }
      } catch (error) {
        console.error('Error processing row:', error, row);
      }
    });

    // 4. التحقق من وجود عناصر قبل الإرسال
    if (invoiceData.items.length === 0) {
      showToast('لا توجد عناصر صالحة في الفاتورة!', 'danger');
      return;
    }else{
      console.log(invoiceData);
    } 

    // 5. إرسال البيانات
    try {
      const response = await fetch('payFromSuppliers/addItemToInvoice.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(invoiceData)
      });
  
      // التحقق من نوع المحتوى أولاً
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        const text = await response.text();
        throw new Error(`توقع JSON لكن تلقيت: ${text.substring(0, 100)}...`);
      }
  
      const data = await response.json();
      
      if (!response.ok || !data.success) {
        throw new Error(data.message || 'فشل في معالجة الفاتورة');
      }
  

    showToast('تم حفظ الفاتورة بنجاح', 'success');

    if (typeof printInvoice === 'function') {
      printInvoice(data.invoiceNumber, data.originalInvoicePath);
    }

    setTimeout(() => {
      window.location.href = 'index.php';
    }, 1500);

  } catch (error) {
    console.error('تفاصيل الخطأ:', error);
    showToast(`حدث خطأ: ${error.message}`, 'danger');
    
    // يمكنك عرض تفاصيل الخطأ الكاملة للتdebug
    console.log('استجابة الخادم الكاملة:', error.response);
  }


  } catch (error) {
    console.error('تفاصيل الخطأ:', error);
    showToast(`حدث خطأ: ${error.message}`, 'danger');
  } finally {
    if (loadingToast?.hide) {
      loadingToast.hide();
    }
  }
}
// عرض/إخفاء مودال الفاتورة
function showInvoiceModal() {
  document.getElementById('invoiceModal').classList.add('show');
  document.getElementById('invoiceModal').style.display = 'block';
}

function hideInvoiceModal() {
  document.getElementById('invoiceModal').classList.remove('show');
  document.getElementById('invoiceModal').style.display = 'none';
}



function showInvoiceInfoModal(change, totalAmount) {
  //  alert ("go" + change );
  //  alert ("go" + totalAmount );
  document.getElementById('paidInput').value = totalAmount.toFixed(2);
  document.getElementById('changeInput').value = change;
  document.getElementById('invoiceInfoModal').classList.add('show');
  document.getElementById('invoiceInfoModal').style.display = 'block';
}

function hideInvoiceInfoModal() {
  const paidInput = document.getElementById('paidInput');
  const changeInput = document.getElementById('changeInput');
  const modal = document.getElementById('invoiceInfoModal');

  if (paidInput) paidInput.value = 0;
  if (changeInput) changeInput.value = 0;
  if (modal) {
    modal.classList.remove('show');
    modal.style.display = 'none';
  }
}


// 99999999999999999999999999999999999999999
// طباعة الفاتورة
// لا يعمل originalInvoicePath

function printInvoice(invoiceNumber, originalInvoicePath) {

  try {
    alert(originalInvoicePath);
    const printContent = document.getElementById('printInvoice').innerHTML;
    const originalContent = document.body.innerHTML;

    document.body.innerHTML = printContent;

    const qrContainer = document.getElementById('qrcode');
    qrUrl = "http://192.168.8.128/elTalaga/uploads/invoices/" + originalInvoicePath;
    qrContainer.innerHTML = '';
    new QRCode(qrContainer, {
      text: qrUrl,
      width: 100,
      height: 100,
      colorDark: "#000000",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H
    });
  
    document.getElementById('invoiceNumber').textContent = invoiceNumber;
    // document.getElementById('invoiceDate').textContent = new Date().toLocaleDateString();
    window.print();
    document.body.innerHTML = originalContent;



  } catch (error) {
    console.error('Error:', error);
    showToast(`حدث خطأ: ${error.message}`, 'danger');
  }

}


// async function printInvoice(num) {
//   try {
//     const response = await fetch('http://localhost:8080/print-number', {//
//       method: 'POST',
//       headers: {
//         'Content-Type': 'application/json'
//       },
//       body: JSON.stringify({
//         number: parseInt(num) // تم التعديل هنا لاستخدام 'number' بدل 'num'
//       })
//     });

//     if (!response.ok) {
//       const errorText = await response.text();
//       throw new Error(errorText);
//     }

//     const result = await response.json();
//     console.log(result.message); // عرض رسالة التأكيد
//     return result;

//   } catch (error) {
//     console.error('Error:', error);
//     throw error;
//   }
// }








// وظائف AJAX لجلب البيانات
function getMainGroup() {
  fetch(`payFromSuppliers/getMainGroup.php?supplierID=${supplierID}`)
    .then(response => response.text())
    .then(data => document.getElementById('mainGroup').innerHTML = data);
}

function getSubGroup(mainGroup) {
  fetch(`payFromSuppliers/getSubGroup.php?mainGroup=${mainGroup}`)
    .then(response => response.text())
    .then(data => document.getElementById('supGroup').innerHTML = data);
}

function getItems(subGroup) {
  fetch(`payFromSuppliers/getItems.php?subGroup=${subGroup}`)
    .then(response => response.text())
    .then(data => {
      document.getElementById('items').innerHTML = data;
      clearSearch();
    });
}
