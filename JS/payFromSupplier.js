// متغيرات عامة
var supplierID;
var selectedItemID = null;
var invoiceItemsCount = 0;
var searchDebounceTimer = null;
const VAT_RATE = 0.15;
var unitMain = "";


// تهيئة الصفحة عند التحميل
document.addEventListener('DOMContentLoaded', function () {
  // تهيئة تاريخ الفاتورة
  var today = new Date();
  document.getElementById('invoiceDate').textContent = today.toLocaleDateString('ar-SA');

  // تهيئة عداد الفاتورة
  updateInvoiceCounter(0);

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


// دالة الحسابات المعدلة
function calculateTotals() {
  const count = parseFloat(document.getElementById('countItem').value) || 0;
  const price = parseFloat(document.getElementById('priceItem').value) || 0;

  // الحسابات باستخدام toFixed
  const totalBeforeVat = (count * price).toFixed(2);
  const priceWithVat = ((price) * (1 + VAT_RATE)).toFixed(2);
  const totalWithVat = ((totalBeforeVat) * (1 + VAT_RATE)).toFixed(2);

  // تحديث القيم (استخدم القيمة الرقمية فقط في value والعرض المنسق في placeholder)
  document.getElementById('priceXcount').value = totalBeforeVat;
  document.getElementById('price_vat').value = priceWithVat;
  document.getElementById('price_vatXcount').value = totalWithVat;
  document.getElementById('totalDisplay').value = totalWithVat;

}



// حساب الإجمالي للفاتورة
function calculateInvoiceTotals() {
  let subtotal = 0;
  // تحديد جميع صفوف الجدول ضمن #invoiceItemsTable
  const rows = document.querySelectorAll('#invoiceItemsTable tr');

  rows.forEach(row => {
    // تحديد الخلية السادسة (td) في كل صف (المفترض أنها تمثل الإجمالي)
    const totalCell = row.querySelector('td:nth-child(5)');

    if (totalCell) {

      const rawValue = totalCell.textContent.replace(/[^0-9.-]+/g, "");
      const totalValue = (rawValue).toFixed(2);

      subtotal = parseFloat(subtotal) + parseFloat(totalValue);

      console.log("تتم الإضافة إلى المجموع الفرعي:", totalValue); // سجل الرقم الفعلي الذي يتم إضافته
      console.log("المجموع الفرعي الحالي:", subtotal); // سجل المجموع الفرعي الجاري
    }
  });




  const taxAmount = subtotal * (0.14);
  const total = subtotal + (taxAmount);

  document.getElementById('subtotal').textContent = subtotal.format() + ' ريال';
  document.getElementById('taxAmount').textContent = taxAmount.format() + ' ريال';
  document.getElementById('totalAmount').textContent = total.format() + ' ريال';
}

//التجهيز لـــ إضافة عنصر إلى الفاتورة
function addItemToInvoice() {
  const clickedElement = event.target.closest('.item-button');
  if (clickedElement) {
    selectedItemID = clickedElement.getAttribute('data-item-id');
    const itemName = clickedElement.querySelector('strong')?.textContent || 'عنصر جديد';
    document.querySelector('#sendItem .modal-title').textContent = 'إضافة: ' + itemName;
    // fetchItemDetails(selectedItemID);
    fetch(`payFromSuppliers/getItemDetails.php?itemID=${selectedItemID}`)
      .then(response => response.json())
      .then(data => {
        try {
          if (data.success) {
            console.log('تم استقبال البيانات:', data.item);
            // عرضوحدات القياس في المودال
            const unitSelect = document.getElementById('unitSelect');
            unitSelect.innerHTML = ''; // مسح الخيارات السابقة
            // add data.item.unitL ,data.item.unitM ,data.item.unitS
            if (data.item.unitL) {
              const option = document.createElement('option');
              option.value = 'L';
              option.data=data.item.L2M;
              unitMain = data.item.unitL; // <-- This sets unitMain for later use
              option.textContent = `${data.item.unitL} `;
              unitSelect.appendChild(option).textContent;
            }
            if (data.item.unitM) {
              const option = document.createElement('option');
              option.value = 'M';
              option.data=data.item.M2S;
              option.textContent = `${data.item.unitM} `;
              unitSelect.appendChild(option);
            }
            if (data.item.unitS) {
              const option = document.createElement('option');
              option.value = 'S';
              funUnitS= data.item.S2L * data.item.M2S;
              option.data=funUnitS;
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
            console.log('lastcount:  ' , lastcount)
           
            var lastdate = lastAction.formatted_date || '';
            const priceLabel1 = document.getElementById('priceLabel1');
            const priceLabel2 = document.getElementById('priceLabel2');
            
            priceLabel1.textContent = ` آخر سعر: ${lastPrice} ريال`;
            // priceLabel1.textContent += ` لال  ${unitMain}`;
            if (countSale > 0) {
              average = sumPrice / countSale;
              
              priceLabel2.textContent = `  متوسط السعر( ${(average).toFixed(2)} ريال )`;
              priceLabel2.textContent += ` عدد مرات الشراء: ${countSale}`;
            }else{ priceLabel2.textContent=" لا توجد بيانات متاحة "; }
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

  // إضافة event listeners
  document.getElementById('countItem').addEventListener('input', calculateTotals);
  document.getElementById('priceItem').addEventListener('input', calculateTotals);

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
  // إزالة event listeners
  document.getElementById('countItem').removeEventListener('input', calculateTotals);
  document.getElementById('priceItem').removeEventListener('input', calculateTotals);

  // إغلاق المودال
  document.getElementById('sendItem').classList.remove('show');
  selectedItemID = null;
}




// إضافة عنصر إلى الفاتورة
// تأكيد إضافة عنصر
// هام لبيانات العنصر
function confirmAddItem() {
  const count = document.getElementById('countItem').value;
  const price = parseFloat(document.getElementById('priceItem').value);
 
  let unit= "" ; 
  const funUnit= document.getElementById('unitSelect').dataset.value;

  const unitSelect = document.getElementById('unitSelect');
  const unitVal = unitSelect.value; // 'L', 'M', or 'S' from the selected option's value attribute

    // Get the text content of the currently selected option dynamically
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

  console.log("unit:", unit);
  
  console.log("funUnit:", funUnit);
  const itemName = document.querySelector(`.item-button[data-item-id="${selectedItemID}"] strong`)?.textContent || 'عنصر';

  const itemData = {
    id: selectedItemID,
    name: itemName,
    quantity: count,
    unit: unit,
    
    price: price,
    priceWithVat: ((price) * (1 + VAT_RATE)).toFixed(2),
    total: ((count) * (price)).toFixed(2),
    totalWithVat: ((count) * (price) * (1 + VAT_RATE)).toFixed(2)
  };
  // alert(`تم إضافة ${itemName} (${count} ${unit}) بنجاح`);
    addItemToInvoiceTable(itemData);
    hideSendItemModal();
  // عرض رسالة toast
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

  // التحقق مما إذا كان العنصر موجودًا بالفعل
  const existingRow = document.querySelector(`#invoiceItemsTable tr td[data-item-id="${itemData.id}"]`)?.closest('tr');

  if (existingRow) {
    // تحديث الكمية والإجمالي للعنصر الموجود
    const quantityCell = existingRow.querySelector('td:nth-child(2)');
    const totalCell = existingRow.querySelector('td:nth-child(5)');

    const newQuantity = ((parseFloat(quantityCell.textContent) || 0) + itemData.quantity).toFixed(2);
    const newTotal = (newQuantity * itemData.price).toFixed(2);

    quantityCell.textContent = newQuantity;

    totalCell.textContent = (newTotal).toFixed(2);

    existingRow.classList.add('table-warning');
    setTimeout(() => existingRow.classList.remove('table-warning'), 2000);
  } else {
    // إضافة صف جديد
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${invoiceItemsCount + 1}</td>
        <td data-item-id="${itemData.id}">${itemData.name}</td>
        <td>${itemData.quantity}</td>
        <td>${itemData.unit}</td>
        <td>${Number(itemData.price).toFixed(2)}</td>
        <td>${Number(itemData.total).toFixed(2)}</td>
        <td>
          <button class="btn btn-sm btn-danger" onclick="removeItemFromInvoice(this)">
            <i class="fa fa-trash"></i>
          </button>
        </td>
      `;

    tableBody.appendChild(row);
    updateInvoiceCounter(invoiceItemsCount + 1);
    //   calculateInvoiceTotals();
    row.classList.add('table-success');
    setTimeout(() => row.classList.remove('table-success'), 2000);
    calculateInvoiceTotals();
    console.log("تم إضافة العنصر إلى الفاتورة:", itemData);
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

// حساب إجماليات الفاتورة
function calculateInvoiceTotals() {
  let subtotal = 0;
  const rows = document.querySelectorAll('#invoiceItemsTable tr');

  rows.forEach(row => {
    const totalCell = row.querySelector('td:nth-child(6)');
    if (totalCell) {
      const totalValue = parseFloat(totalCell.textContent.replace(/[^0-9.-]+/g, ""));
      tot = (totalValue).toFixed(2);
      subtotal = subtotal + tot;
    }
  });

  const taxAmount = subtotal * (0.14); // ضريبة 14%
  const total = parseFloat(subtotal) + parseFloat(taxAmount);

  document.getElementById('subtotal').textContent = Number(subtotal).toFixed(2) + ' ريال';
  document.getElementById('taxAmount').textContent = Number(taxAmount).toFixed(2) + ' ريال';
  document.getElementById('totalAmount').textContent = Number(total).toFixed(2) + ' ريال';
  console.log("المجموع الفرعي:", subtotal);
  console.log("المجموع الضريبي:", taxAmount);
  console.log("المجموع الإجمالي:", total);
  return total;
}

// تأكيد الشراء
function confirmPurchase() {
  const rows = document.querySelectorAll('#invoiceItemsTable tr');
  if (rows.length === 0) {
    alert('لا توجد عناصر في سلة المشتريات', 'warning');
    return;
  }

  const total = calculateInvoiceTotals();
  const totalFormatted = (total).toFixed(2) + ' ريال';

  if (confirm(`هل أنت متأكد من تأكيد الشراء بمبلغ ${totalFormatted}؟`)) {
    sendDataInvoice();
  }
}

// إرسال بيانات الفاتورة إلى الخادم
// المرحله الاخيره لارسال البيانات الى قاعدة البيانات 
// و منها يمكن الطباعه
function sendDataInvoice() {
  const loadingToast = showToast('جاري حفظ الفاتورة...', 'info', 0);
  showToast('سيتم تحويلك إلى صفحة جديدة', 'info');

  const taxElement = document.getElementById('taxAmount');
  const totalElement = document.getElementById('totalAmount');
  const vatValue = taxElement ? parseFloat(taxElement.textContent.replace(/[^0-9.-]+/g, "")) || 0 : 0;
  const generalTotalValue = totalElement ? parseFloat(totalElement.textContent.replace(/[^0-9.-]+/g, "")) || 0 : 0;
  countTr = document.querySelectorAll('#invoiceItemsTable tr').length;
  console.log(countTr);
  const invoiceData = {
    supplierID: supplierID,
    state: 1, //1 open  , 2 closed , 3 canceled , 4 waiting
    action: "in",  //('in','out','ret')
    paymentMethod: "cash",  //('cash','card','transfer')
    discount: 0.00,
    totalDue: generalTotalValue,
    vat: vatValue,
    generalTotal: generalTotalValue,
    paidAmount: generalTotalValue,
    notes: 'فاتورة شراء جديدة',
    items: [],
    total: calculateInvoiceTotals()
  };
  //----------------------------------------------------------------------------------14-7-2025 
  // جمع بيانات العناصر
  document.querySelectorAll('#invoiceItemsTable tr').forEach(row => {
    const cells = row.getElementsByTagName('td');
    if (cells.length > 0) {
      invoiceData.items.push({
        itemID: cells[1].getAttribute('data-item-id'),
        itemName: cells[1].textContent,
        quantity: parseFloat(cells[2].textContent),
        unit: cells[3].textContent,
        price: parseFloat(cells[4].textContent.replace(/[^0-9.-]+/g, "")),
        total: parseFloat(cells[5].textContent.replace(/[^0-9.-]+/g, "")),
        

      });
      console.log(invoiceData);
    }
  });

  // إرسال البيانات
  fetch('payFromSuppliers/addItemToInvoice.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(invoiceData)
})
  .then(response => response.text())
  .then(text => {
    try {
      const data = JSON.parse(text);
      if (data.success) {
        console.log('تم حفظ الفاتورة بنجاح', 'success');
      } 
      else {
        console.log('حدث خطأ: ' + (data.message || 'غير معروف'), 'danger');
      }
    } catch (e) {
      console.error('Response is not valid JSON:', text);
      // Optionally show a toast or alert here
    }
  })
  .catch(error => {
    console.log('حدث خطأ في الاتصال بالخادم', 'danger');
    console.error('Error:', error);
  })
  .finally(() => { 
    alert('تم حفظ الفاتورة بنجاح', 'success');
    hideInvoiceModal();
    // االانتقال الى الصفحه الرئيسيه مع مسح ه\ه الصفحه من الذاكره
    window.location.href = 'index.php';
    // if (loadingToast) {
    //   loadingToast.hide();
    //   document.body.removeChild(loadingToast._element);
       
    // }
  });

}

// عرض/إخفاء مودال الفاتورة
function showInvoiceModal() {
  document.getElementById('invoiceModal').classList.add('show');
}

function hideInvoiceModal() {
  document.getElementById('invoiceModal').classList.remove('show');
}

// طباعة الفاتورة
function printInvoice() {
  window.print();
}

// مودل الخصم و معلومات الفاتورة
function showInvoiceInfoModal() {
  document.getElementById('invoiceInfoModal').classList.add('show');
}

function hideInvoiceInfoModal() {
  document.getElementById('invoiceInfoModal').classList.remove('show');
}


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


