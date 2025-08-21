const sendItemModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('sendItem'));
const invoiceModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('invoiceModal'));
const closeInvoiceModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('closeInvoiceModal'));
var date = new Date();
var year = date.getFullYear();
var month = date.getMonth() + 1;
var day = date.getDate();
if (month < 10) {
  month = '0' + month;
}
if (day < 10) {
  day = '0' + day;
}
var today = year + '-' + month + '-' + day;

document.addEventListener('DOMContentLoaded', () => {
  reloadInvoice(); // أولاً

});


function reloadInvoice() {
  const salesmaneID = document.getElementById('salesmaneID').value;
  const customerID = document.getElementById('customerID').value;

  superagent
    .post('sale2Customer/getOpenInvoice.php')
    .type('form')
    .send({ salesmaneID, customerID })
    .then(res => {
      const data = JSON.parse(res.text);
      if (data.success) {
        document.getElementById('invoiceID').value = data.invoiceID;
        fetchInvoiceData(false); // تحديث العداد مباشرة
      }
    })
    .catch(err => {
      console.error('خطأ في جلب الفاتورة المفتوحة:', err);
    });
}



// الانتقال الى السعر عند الضغط انتر
document.getElementById('countItem').addEventListener('keyup', function (e) {
  if (e.key === 'Enter') {
    document.getElementById('priceItem').focus();
  }
});
// الانتقال الى زر اضافة العنصر عند الضغط انتر
document.getElementById('priceItem').addEventListener('keyup', function (e) {
  if (e.key === 'Enter') {
    document.getElementById('add').focus();
  }
});

var priceSelectValue = [];
var discountSelectValue = [];
var discounL = 0;
var discounM = 0;
var discounS = 0;
var priceL = 0;
var priceM = 0;
var priceS = 0;

// اظاهر مودل اضافة العنصر 
//عند الضغط على زر الايتم
function showSendItemModal(itemID, stockCount) {
  const userID = document.getElementById('salesmaneID').value;
  document.getElementById('commintCount').textContent = "";
  const xhr = new XMLHttpRequest();

  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      try {
        const response = JSON.parse(xhr.responseText);
        console.log(response);
        if (response.success) {
          // تعيين بيانات المودال
          document.getElementById('itemName_modal').textContent = "اضافة " + response.itemName;
          document.getElementById('commintCount').textContent += stockCount + " " + response.unitL + " فقط";
          document.getElementById('itemID').value = response.itemID;

          if (response.unit_s == response.unitL) {
            discounL = parseFloat(response.discount) || 0;
            priceL = parseFloat(response.m_price);
            discounM = 0;
            discounS = 0;
            priceM = parseFloat(response.m_price) / parseFloat(response.fL2M);
            priceS = parseFloat(response.m_price) / (parseFloat(response.fL2M) * parseFloat(response.fM2S));
          } else if (response.unit_s == response.unitM) {
            discounL = 0;
            discounM = parseFloat(response.discount) || 0;
            discounS = 0;
            priceL = parseFloat(response.m_price) * parseFloat(response.fL2M);
            priceM = parseFloat(response.m_price);
            priceS = parseFloat(response.m_price) / parseFloat(response.fM2S);
          } else if (response.unit_s == response.unitS) {
            discounL = 0;
            discounM = 0;
            discounS = parseFloat(response.discount) || 0;
            priceL = parseFloat(response.m_price) * parseFloat(response.fL2M) * parseFloat(response.fM2S);
            priceM = parseFloat(response.m_price) * parseFloat(response.fM2S);
            priceS = parseFloat(response.m_price);
          }

          document.getElementById('priceItem').value = priceL.toFixed(2);
          document.getElementById('unitL').innerText = response.unitL;
          document.getElementById('unitM').innerText = response.unitM;
          document.getElementById('unitS').innerText = response.unitS;
          document.getElementById('discountItem').value = discounL.toFixed(2);

          priceSelectValue = [priceL, priceM, priceS];
          discountSelectValue = [discounL, discounM, discounS];
          console.log(priceSelectValue);
          console.log(discountSelectValue);
          // set couuntItem.value > 0 and < stockCount
          document.getElementById('countItem').value = 1;
          document.getElementById('countItem').max = stockCount;
          // إظهار المودال
          sendItemModal.show();

        } else {
          console.error('Item not found');
        }
      } catch (e) {
        console.error('Error parsing response', e);
      }
    }
  };

  xhr.open('GET', 'sale2Customer/modal_sendItem.php?itemID=' + itemID + '&userID=' + userID, true);
  xhr.send();
}

//  set couuntItem.value > 0 and < stockCount
document.getElementById('countItem').addEventListener('change', function () {
  const stockCount = document.getElementById('countItem').max;
  unitSelect = document.getElementById('unitSelect');
  if (this.value > stockCount && unitSelect.value === 'L') {
    this.value = stockCount;
    alert('لا يمكن ان يكون الكمية اكبر من المخزون');
  } else if (this.value < 0) {
    this.value = 0;
  }
});



// تغيير السعر على حسب الوحده 

document.getElementById('unitSelect').addEventListener('change', function () {
  const unitSelect = document.getElementById('unitSelect');
  if (unitSelect.value === 'L') {
    document.getElementById('priceItem').value = priceSelectValue[0].toFixed(2);
    document.getElementById('discountItem').value = discountSelectValue[0].toFixed(2);
  } else if (unitSelect.value === 'M') {
    document.getElementById('priceItem').value = priceSelectValue[1].toFixed(2);
    document.getElementById('discountItem').value = discountSelectValue[1].toFixed(2);
  } else if (unitSelect.value === 'S') {
    document.getElementById('priceItem').value = priceSelectValue[2].toFixed(2);
    document.getElementById('discountItem').value = discountSelectValue[2].toFixed(2);
  }
})




// اغلاق مودل اضافة العنصر
function hideSendItemModal() {
  sendItemModal.hide();
}

// من زر الاضافة الى الفاتورة
function confirmAddItem() {
  const val = id => parseFloat(document.getElementById(id).value).toFixed(2) || 0;
  const count = val('countItem'), price = val('priceItem'), discount = val('discountItem');
  const itemID = document.getElementById('itemID').value;
  const unitSelect = document.getElementById('unitSelect');
  const unit = unitSelect.selectedIndex !== -1 ? unitSelect.options[unitSelect.selectedIndex].textContent.trim() : '';
  if (count <= 0) return showToast('يرجى إدخال عدد صحيح أكبر من الصفر', 'danger');
  if (price <= 0) return showToast('يرجى إدخال سعر صحيح أكبر من الصفر', 'danger');
  if (discount < 0) return showToast('يرجى إدخال خصم صحيح (صفر أو أكبر)', 'danger');

  addItemToInvoice(itemID, count, price, unit, discount);
}



// --------------------------------------------------------------
// -------------------------------------------------------------
// ----------------------الفاتورة -------------------------------
// -------------------------------------------------------------
// --------------------------------------------------------------

let invoiceData = null; // لتخزين البيانات مؤقتًا
// استدعاء بيانات الفاتورة
function fetchInvoiceData(showAfter = false) {
  const userID = document.getElementById('salesmaneID').value;
  const customerID = document.getElementById('customerID').value;
  const invoiceID = document.getElementById('invoiceID').value;

  superagent
    .post('sale2Customer/getItemsInInvoice.php')
    .type('form')
    .send({ userID, customerID, invoiceID })
    .then(res => {
      const data = JSON.parse(res.text);

      if (!data.success) {
        showToast(data.message || 'خطأ في تحميل الفاتورة', 'danger');
        return;
      }

      invoiceData = data;

      // تحديث عداد الفاتورة
      updateInvoiceCounter(invoiceData.items.length);

      if (showAfter) {
        showInvoiceModal();
      }
    })
    .catch(err => {
      console.error(err);
      showToast('خطأ في الاتصال بالسيرفر', 'danger');
    });
}
// اظهار الفاتورة مع تحميل ببيانات المنتجات
// داله تابعه للدالة fetchInvoiceData
function showInvoiceModal() {
  if (!invoiceData) {
    showToast('لا توجد بيانات فاتورة للعرض', 'warning');
    return;
  }

  // تعبئة بيانات المودال من invoiceData
  document.getElementById('companyName').textContent = invoiceData.branch.branchName;
  document.getElementById('numberRC').textContent = invoiceData.branch.numberRC;
  document.getElementById('numberTax').textContent = invoiceData.branch.numberTax;
  document.getElementById('companyAddress').textContent =
    `${invoiceData.branch.street || ''} ${invoiceData.branch.city || ''}`;

  document.getElementById('customerName').textContent = invoiceData.customer.customerName;
  document.getElementById('customerPhone').textContent = invoiceData.customer.phone;
  document.getElementById('customerAddress').textContent = invoiceData.customer.address;

  document.getElementById('invoiceNumber').textContent = invoiceData.invoice.invoiceID;
  document.getElementById('invoiceDate').textContent = invoiceData.invoice.date;
  document.getElementById('totalAmount').textContent = invoiceData.invoice.total.toFixed(2);
  document.getElementById('discountAmount').textContent = invoiceData.invoice.discount.toFixed(2);
  document.getElementById('paidAmount').textContent = invoiceData.invoice.paidAmount.toFixed(2);
  document.getElementById('remainingAmount').textContent = invoiceData.invoice.remainingAmount.toFixed(2);

  const tbody = document.getElementById('invoiceItemsTableBody');
  tbody.innerHTML = '';
  invoiceData.items.forEach(item => {
    const price = parseFloat(item.price) || 0;
    const totalPrice = parseFloat(item.totalPrice) || 0;
    const discountItem = parseFloat(item.discount) || 0;
    const vat = totalPrice * 0.15;
    let finalTotal = totalPrice + vat - discountItem;

    finalTotal = (+item.totalPrice) + (+vat) - (discountItem);

    // إنشاء صف جديد
    const tr = document.createElement('tr');
    tr.setAttribute('data-action-id', item.actionID);

    // محتوى الصف
    tr.innerHTML = `
              <td style="width:5%">${item.itemID}</td>
              <td style="width:40%">${item.itemName}</td>
              <td style="width:7%">${item.count}</td>
              <td style="width:7%">${item.unit}</td>
              <td style="width:7%">${(+item.price).toFixed(2)}</td>
              <td style="width:7%">${(+item.totalPrice).toFixed(2)}</td>
              <td style="width:7%">${(+item.discount).toFixed(2)}</td>
              <td style="width:7%">${(+vat).toFixed(2)}</td>
              <td style="width:12%">${(+finalTotal).toFixed(2)}</td>
              <td style="width:6%">
            <button class="btn btn-sm btn-danger d-print-none" 
                    onclick="removeItemFromInvoice(${item.actionID})">
                <i class="fa fa-trash"></i>
            </button>
        </td>
      `;

    // إضافة الصف للجدول
    tbody.appendChild(tr);

    // بعد ما تملي جدول الأصناف
    let totalAmount = 0;
    let totalDiscountItem = 0;
    let vatAmount = 0;
    let totalWithVat = 0;
    let grandTotal = 0;
    invoiceData.items.forEach(item => {
      const itemTotal = parseFloat(item.price) * parseFloat(item.count);
      totalAmount += itemTotal;
      totalDiscountItem += parseFloat(item.discount);
    });

    // حساب المبالغ
    const discount = parseFloat(invoiceData.invoice.discount) || 0;
    const paidAmount = parseFloat(invoiceData.invoice.paidAmount) || 0;
    let remainingAmount = invoiceData.customer.remainingAmount || 0;

    vatAmount = totalAmount * 0.15;
    totalWithVat = totalAmount + vatAmount;
    grandTotal = totalWithVat - totalDiscountItem - discount;
    remainingAmount = parseFloat(remainingAmount) + parseFloat(grandTotal);
    // تحديث العناصر في الفاتورة
    document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
    document.getElementById('vatAmount').textContent = vatAmount.toFixed(2);
    document.getElementById('discountAmount').textContent = (totalDiscountItem + discount).toFixed(2);
    document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
    document.getElementById('paidAmount').textContent = paidAmount.toFixed(2);
    document.getElementById('remainingAmount').textContent = parseFloat(remainingAmount).toFixed(2);
    // كتابة المجموع بالعربي
    let grandTotalAR = convertAmountToWords(grandTotal.toFixed(2));
    let grandTotalEn = convertAmountToWords(grandTotal.toFixed(2));
    document.getElementById('totalInWordsAR').textContent = grandTotalAR.ar;
    document.getElementById('totalInWordsEN').textContent = grandTotalEn.en;
  });

  generateVatQrCode(
    invoiceData.branch.branchName,
    invoiceData.branch.numberTax,
    invoiceData.invoice.date,
    invoiceData.invoice.total,
    invoiceData.invoice.vat || (invoiceData.invoice.total - (invoiceData.invoice.total / 0.15)).toFixed(2)
  );

  bootstrap.Modal.getOrCreateInstance(document.getElementById('invoiceModal')).show();
}


// حزف العنصر من الفاتورة
function removeItemFromInvoice(actionID) {
  const xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      try {
        const response = JSON.parse(xhr.responseText);

        if (response.success) {
          // إزالة الصف من الجدول مباشرة
          const row = document.querySelector(`#invoiceItemsTableBody tr[data-action-id="${actionID}"]`);
          if (row) {
            row.remove();
          }
         reloadInvoice();
          // تحديث عداد الفاتورة
          updateInvoiceCounter(document.querySelectorAll('#invoiceItemsTableBody tr').length);

          // إعادة حساب الإجماليات بدون إعادة تحميل من السيرفر
          recalculateInvoiceTotals();

          showToast('تم حذف العنصر بنجاح', 'success');
        } else {
          showToast(response.message || 'خطأ أثناء حذف العنصر', 'danger');
        }
      } catch (e) {
        console.error("خطأ في تحليل الرد:", e, xhr.responseText);
        showToast('رد غير صحيح من السيرفر', 'danger');
      }
    }
  };
  xhr.open("GET", "sale2Customer/removeItemFromInvoice.php?actionID=" + actionID, true);
  xhr.send();
}

/************* ✨recalculateInvoiceTotals⭐ *************/
/**
* تعيد حساب إجمالي الفاتورة بناءً على الحالة الحالية لنموذج DOM.
*
* تتكرر هذه الدالة على جميع الصفوف في جدول عناصر الفاتورة، وتستخرج البيانات اللازمة،
* وتُحدّث القيم في نموذج DOM لتعكس الإجماليات الجديدة.
*
* لا تتفاعل هذه الدالة مع الخادم ولا تُرجع أي قيم.
*/
// ***** d13a0a28-d2e4-4833-975a-2ff9fb49df94 *******/

function recalculateInvoiceTotals() {
  let subTotal = 0;
  let totalDiscount = 0;
  let totalVat = 0;
  let grandTotal = 0;
  let totalWithVat = 0;
  let remainingAmount = 0;
  remainingAmount = parseFloat(document.getElementById("remainingAmount").textContent) || 0;
  const rows = document.querySelectorAll("#invoiceItemsTableBody tr");

  rows.forEach(row => {
    const count = parseFloat(row.querySelector("td:nth-child(3)").textContent) || 0;
    const price = parseFloat(row.querySelector("td:nth-child(5)").textContent) || 0;
    const discount = parseFloat(row.querySelector("td:nth-child(7)").textContent) || 0;
    const vat = parseFloat(row.querySelector("td:nth-child(8)").textContent) || 0;
    const finalTotal = parseFloat(row.querySelector("td:nth-child(9)").textContent) || 0;

    subTotal += count * price;
    totalDiscount += discount;
    totalVat += vat;
    grandTotal += finalTotal;
  });
totalWithVat=subTotal+totalVat;
remainingAmount = grandTotal - remainingAmount;
  // تحديث الحقول أو النصوص في الفاتورة
  document.getElementById("totalAmount").textContent = subTotal.toFixed(2) + " ريال";
  document.getElementById("discountAmount").textContent = totalDiscount.toFixed(2) + " ريال";
  document.getElementById("totalWithVat").textContent = totalWithVat.toFixed(2) + " ريال";
  document.getElementById("vatAmount").textContent = totalVat.toFixed(2)  + " ريال";
  document.getElementById("grandTotal").textContent = grandTotal.toFixed(2)   + " ريال";
  document.getElementById("remainingAmount").textContent = remainingAmount.toFixed(2) + " ريال";
}


function hideInvoiceModal() {
  invoiceModal.hide();
}

// تهيئة عداد الفاتورة
updateInvoiceCounter(0);

// تحديث عداد الفاتورة
function updateInvoiceCounter(count) {
  invoiceItemsCount = count;
  const counter = document.getElementById('invoiceCounter');
  counter.textContent = count;
  counter.style.display = count > 0 ? 'flex' : 'none';
}


// اضافة العنصر للفاتورة
// بعد الضغط على الزر الععنصر و ظهور المديول و الضغط على زر اضافة للفاتورة
// ارسال البيانات الى addItemToInvoice.php
// http://localhost/your_project/sale2Customer/addItemToInvoice.php?itemID=1&count=2&price=100&unit=قطعة&userID=5&customerID=10
function addItemToInvoice(selectedItemID, count, price, unit, discountItem) {
  const userID = document.getElementById('salesmaneID').value;
  const customerID = document.getElementById('customerID').value;

  superagent
    .post('sale2Customer/addItemToInvoice.php')
    .type('form')
    .send({
      itemID: selectedItemID,
      count: count,
      price: price,
      unit: unit,
      userID: userID,
      customerID: customerID,
      discount: discountItem
    })
    .then(res => {
      const raw = res.text.trim();
      const parts = raw.split('|');

      if (parts[0] === "OK") {
        const invoiceID = parts[1];
        const countInvoice = parts[2];
        const total = parts[3];

        updateInvoiceCounter(countInvoice);
        document.getElementById('totalInvoice').value = total;
        document.getElementById('invoiceID').value = invoiceID;
        showToast('تمت إضافة العنصر بنجاح', 'success');
        hideSendItemModal();
        reloadInvoice();
      } else if (parts[0] === "ERROR") {
        showToast(parts[1], 'danger');
        console.error("📢 خطأ من السيرفر:", parts[1]);
      } else {
        showToast("رد غير متوقع من السيرفر: " + raw, 'danger');
        console.error("رد غير متوقع:", raw);
      }
    })
    .catch(err => {
      console.error('❌ خطأ في الاتصال:', err);
      showToast('حدث خطأ أثناء الاتصال بالسيرفر', 'danger');
    });
}


document.getElementById('btnFinalize').addEventListener('click', () => {
  const grandTotal = document.getElementById('grandTotal').textContent;
  let remainingAmount = document.getElementById('remainingAmount').textContent;
  remainingAmount = remainingAmount - grandTotal;
  openCloseInvoiceModal(grandTotal, remainingAmount);
});
// زرار جوه انهاء الشراء
document.getElementById('btnConfirmFinalize').addEventListener('click', finalizeInvoice);

// فتح مودال إنهاء الفاتورة
function openCloseInvoiceModal(totalInvoice, oldBalance) {
  document.getElementById('totalInvoiceAmount').value = parseFloat(totalInvoice).toFixed(2);
  document.getElementById('oldCustomerBalance').value = parseFloat(oldBalance).toFixed(2);
  let remainingAmount = parseFloat(document.getElementById('remainingAmount').textContent).toFixed(2) ;
  remainingAmount = remainingAmount - totalInvoice;
  document.getElementById('remainingAmountClose').value = remainingAmount.toFixed(2);
  document.getElementById('paidAmountClose').value = '';
  document.getElementById('paymentMethod').value = 'cash';
  document.getElementById('dueDateGroup').style.display = 'none';
  closeInvoiceModal.show();
  document.getElementById('paidAmountClose').focus();
}

// إظهار/إخفاء تاريخ الدفع عند اختيار الدفع بالأجل
document.getElementById('paymentMethod').addEventListener('change', function () {
  if (this.value === 'credit') {
    document.getElementById('paidAmountClose').value = 0;
    document.getElementById('dueDateGroup').style.display = 'block';
  } else {
    document.getElementById('dueDateGroup').style.display = 'none';
  }
});
document.getElementById('paidAmountClose').addEventListener('input', function () {
  let paidAmount = parseFloat(this.value) || 0;
  const total = parseFloat(document.getElementById('totalInvoiceAmount').value) || 0;
  const oldBalance = parseFloat(document.getElementById('oldCustomerBalance').value) || 0;
  const remainingAmount = total + oldBalance - paidAmount;
  document.getElementById('remainingAmountClose').textContent = remainingAmount.toFixed(2);
});

// 3️⃣ JavaScript — حفظ التغييرات في قاعدة البيانات + الطباعة

function finalizeInvoice() {
  const invoiceID = document.getElementById('invoiceID').value;
  const total = parseFloat(document.getElementById('totalInvoiceAmount').value) || 0;
  const subTotal = parseFloat(document.getElementById('totalAmount').value) || 0;
  const oldBalance = parseFloat(document.getElementById('oldCustomerBalance').value) || 0;
  const paidAmount = parseFloat(document.getElementById('paidAmountClose').value) || 0;
  const grandTotal = parseFloat(document.getElementById('grandTotal').textContent) || 0;
  const discount = parseFloat(document.getElementById('discountAmount').textContent) || 0;
  const vat = parseFloat(document.getElementById('vatAmount').textContent) || 0;
  const remainingAmount = parseFloat(document.getElementById('remainingAmountClose').value) || 0;
  const paymentMethod = document.getElementById('paymentMethod').value;
  const dueDate = document.getElementById('dueDate').value || null;
  const customerID = document.getElementById('customerID').value;
  const userID = document.getElementById('salesmaneID').value;
  const today = new Date().toISOString().slice(0, 10);

  // جمع عناصر الفاتورة
  const tbody = document.getElementById('invoiceItemsTableBody');
  const rows = tbody.querySelectorAll('tr');
  let items = [];
  rows.forEach(row => {
    const itemID = parseFloat(row.querySelector('td:nth-child(1)').textContent) || 0;
    const count = parseFloat(row.querySelector('td:nth-child(3)').textContent) || 0;
    const price = parseFloat(row.querySelector('td:nth-child(5)').textContent) || 0;
    const discountItem = parseFloat(row.querySelector('td:nth-child(7)').textContent) || 0;
    const unit = row.querySelector('td:nth-child(4)').textContent || null;


    items.push({ itemID, count, price, discount: discountItem, unit });
  });
  console.log(items);
  if (paidAmount < 0) {
    showToast('المبلغ المدفوع لا يمكن أن يكون سالباً', 'danger');
    return;
  }
  if (paymentMethod === 'credit' && !dueDate) {
    showToast('يرجى تحديد تاريخ السداد', 'danger');
    return;
  }

  // استدعاء تحديد الموقع أولاً
  getLocation(function (result, error) {
    let latitude = null, longitude = null;
    if (!error) {
      latitude = result.lat;
      longitude = result.lng;
    }

    // إرسال البيانات إلى السيرفر
    superagent
      .post('sale2Customer/closeInvoice.php')
      .type('form')
      .send({
        invoiceID,
        total,
        totalDue: grandTotal + vat,
        paidAmount,
        grandTotal,
        discount,
        vat,
        remainingAmount,
        paymentMethod,
        today,
        userID,
        customerID,
        latitude,
        longitude,
        items: JSON.stringify(items) // إرسال العناصر كـ JSON
      })
      .then(res => {
        const data = JSON.parse(res.text);
        if (data.success) {
          showToast('تم إنهاء الفاتورة بنجاح', 'success');
          closeInvoiceModal.hide();
          setTimeout(() => { printInvoiceFromModal(); }, 300);
          setTimeout(() => { window.location.href = 'salesmane_HomePage.php'; }, 2000);
        } else {
          showToast(data.message || 'حدث خطأ أثناء حفظ الفاتورة', 'danger');
        }
      })
      .catch(err => {
        console.error(err);
        showToast('خطأ في الاتصال بالسيرفر', 'danger');
      });
  });
}







// 'طباعة الفاتورة'
function printInvoiceFromModal() {
  // إخفاء العناصر الغير ضرورية للطباعة
  document.querySelectorAll('.d-print-none').forEach(el => el.style.display = 'none');

  // تنفيذ أمر الطباعة
  printSectionById("printInvoiceArea");

  // إعادة إظهار العناصر بعد الطباعة
  setTimeout(() => {
    document.querySelectorAll('.d-print-none').forEach(el => el.style.display = '');
  }, 500);
}



function printSectionById(sectionId) {

  // الحصول على العنصر المطلوب
  const section = document.getElementById(sectionId);
  if (!section) {
    alert("العنصر غير موجود!");
    return;
  }

  // فتح نافذة طباعة جديدة
  const printWindow = window.open('', '', 'width=800,height=600');

  // نسخ الـ CSS الخاص بالصفحة
  const styles = Array.from(document.styleSheets)
    .map(sheet => {
      try {
        return Array.from(sheet.cssRules)
          .map(rule => rule.cssText)
          .join('\n');
      } catch (e) {
        return ''; // تجاهل ملفات CSS من دومين خارجي
      }
    })
    .join('\n');

  // كتابة المحتوى في النافذة الجديدة مع الـ CSS
  printWindow.document.write(`
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>طباعة</title>
            <style>${styles}</style>
        </head>
        <body>
            ${section.outerHTML}
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


