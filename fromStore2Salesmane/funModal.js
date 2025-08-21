const VAT_RATE = 0.15;
var selectedItemID = null;
var priceSelectValue = [];

var discount;

// عرض/إخفاء المودالات
// عرض مودال الفاتورة
function showInvoiceModal() {
    new bootstrap.Modal(document.getElementById('invoiceModal')).show();
}

function hideInvoiceModal() {
    bootstrap.Modal.getInstance(document.getElementById('invoiceModal')).hide();
}

function showInvoiceInfoModal() {
    new bootstrap.Modal(document.getElementById('invoiceInfoModal')).show();
}

function hideInvoiceInfoModal() {
    bootstrap.Modal.getInstance(document.getElementById('invoiceInfoModal')).hide();
}


// دالة إضافة عنصر إلى الفاتورة
function addItemToInvoice(itemID) {
    console.log("تم استدعاء addItemToInvoice مع itemID:", itemID);

    if (!itemID) {
        console.error('لم يتم تحديد معرف الصنف');
        showToast('لم يتم تحديد صنف', 'danger');
        return;
    }

    selectedItemID = itemID;
    priceSelectValue = []; // إعادة تعيين مصفوفة الأسعار

    // جلب تفاصيل الصنف
    fetch(`fromStore2Salesmane/getItemDetails.php?itemID=${itemID}`)
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

                document.getElementById('sendItem').classList.add('show');
                document.getElementById('countItem').focus();

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
    document.getElementById('priceXcount').value = '0.00';
    document.getElementById('discountItem').value = '0.00';
    document.getElementById('price_vat').value = '0.00';
    document.getElementById('price_vatXcount').value = '0.00';
    document.getElementById('totalDisplay').value = '0.00';

    // إضافة مستمعي الأحداث
    document.getElementById('countItem').addEventListener('input', calculateTotals);
    document.getElementById('priceItem').addEventListener('input', calculateTotals);
    // عمل خصم الايتم
    document.getElementById('discountItem').addEventListener('input', calculateTotals);
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

// إخفاء مودال إضافة العنصر
function hideSendItemModal() {
    document.getElementById('countItem').removeEventListener('input', calculateTotals);
    document.getElementById('priceItem').removeEventListener('input', calculateTotals);
    document.getElementById('sendItem').classList.remove('show');
    selectedItemID = null;
}



// تأكيد إضافة عنصر
function confirmAddItem() {
    const count = document.getElementById('countItem').value;
    const price = parseFloat(document.getElementById('priceItem').value);
    const selectedItemID = document.getElementById('itemID').value;
    const unitSelect = document.getElementById('unitSelect');
    const discount = document.getElementById('discountItem').value ?? 0;
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
        priceWithVat: (price * (1 + VAT_RATE)).toFixed(2),
        discount: discount,
        total: (count * price).toFixed(2),
        totalWithVat: (count * price * (1 + VAT_RATE)).toFixed(2)
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
        const countCell = existingRow.querySelector('td:nth-child(4)');
        const totalCell = existingRow.querySelector('td:nth-child(7)');
        const discountCell = existingRow.querySelector('td:nth-child(8)');
        const newCount = (parseFloat(countCell.textContent) + parseFloat(itemData.count)).toFixed(2);
        const newTotal = (newCount * itemData.price).toFixed(2);
       

        countCell.textContent = newCount;
        totalCell.textContent = newTotal;
        discountCell.textContent = itemData.discount;

        existingRow.classList.add('table-warning');
        setTimeout(() => existingRow.classList.remove('table-warning'), 2000);
    } else {
        const row = document.createElement('tr');
        row.innerHTML = `
        <td>${invoiceItemsCount + 1}</td>
        <td data-item-id="${itemData.id}">${itemData.id}</td>
        <td>${itemData.name}</td>
        <td>${parseFloat(itemData.count).toFixed(2)}</td>
        <td>${itemData.unit}</td>
        <td>${parseFloat(itemData.price).toFixed(2)}</td>
        <td>${parseFloat(itemData.total).toFixed(2)}</td>
        <td>${parseFloat(itemData.discount).toFixed(2)}</td>
        <td>
        <button class="btn btn-outline-danger btn-sm d-print-none" onclick="removeItemFromInvoice(this)">
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




// حساب الإجماليات
function calculateTotals() {
    const count = parseFloat(document.getElementById('countItem').value) || 0;
    const price = parseFloat(document.getElementById('priceItem').value) || 0;
    const discount = parseFloat(document.getElementById('discountItem').value) || 0;

    const totalBeforeVat = (count * price).toFixed(2);
    const priceWithVat = (price * (1 + VAT_RATE)).toFixed(2);
    const totalWithVat = (totalBeforeVat * (1 + VAT_RATE)).toFixed(2);
    const totalAfterDiscount = (totalWithVat - discount).toFixed(2);

    document.getElementById('priceXcount').value = totalBeforeVat;
    document.getElementById('price_vat').value = priceWithVat;
    document.getElementById('price_vatXcount').value = totalWithVat;
    document.getElementById('totalDisplay').value = totalAfterDiscount;
}

// حساب إجماليات الفاتورة
function calculateInvoiceTotals() {
    let subtotal = 0;
    let discount = 0;
    const rows = document.querySelectorAll('#invoiceItemsTable tr');

    rows.forEach(row => {
        const totalCell = row.querySelector('td:nth-child(7)');
        if (totalCell) {
            const totalValue = parseFloat(totalCell.textContent.replace(/[^0-9.-]+/g, "")) || 0;
            subtotal += totalValue;
            const discountCell = row.querySelector('td:nth-child(8)');
            discount += parseFloat(discountCell.textContent.replace(/[^0-9.-]+/g, "")) || 0;
        }
    });

    const taxAmount = subtotal * VAT_RATE;
    const total = subtotal + taxAmount;

    document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' ريال';
    document.getElementById('taxAmount').textContent = taxAmount.toFixed(2) + ' ريال';
    document.getElementById('totalAmount').textContent = total.toFixed(2) + ' ريال';
    document.getElementById('discountAmount').textContent = discount.toFixed(2) + ' ريال';
    document.getElementById('generalTotalAmount').textContent = (total - discount).toFixed(2) + ' ريال';
    
    return total;
}

// تأكيد التحويل
function confirmPurchase() {
    const rows = document.querySelectorAll('#invoiceItemsTable tr');
    if (rows.length === 0) {
        showToast('لا توجد عناصر في سلة المشتريات', 'warning');
        return;
    }

    const total = calculateInvoiceTotals();
    const totalFormatted = total.toFixed(2) + ' ريال';

    if (confirm(`هل أنت متأكد من تأكيد التحويل بمبلغ ${totalFormatted}؟`)) {
        sendDataInvoice();
    }
}

// إرسال بيانات الفاتورة
function sendDataInvoice() {
    const loadingToast = showToast('جاري حفظ الفاتورة...', 'info', 0);

    const taxElement = document.getElementById('taxAmount');
    const totalElement = document.getElementById('totalAmount');
    const vatValue = taxElement ? parseFloat(taxElement.textContent.replace(/[^0-9.-]+/g, "")) || 0 : 0;
    const generalTotalValue = totalElement ? parseFloat(totalElement.textContent.replace(/[^0-9.-]+/g, "")) || 0 : 0;
    const addDiscount = document.getElementById('discountInput').value;
    const salesmaneID = document.getElementById('salesmaneID').value;
    const userID = document.getElementById('userID').value;
    const invoiceData = {
        salesmaneID: salesmaneID,
        userID: userID,
        state: 1,
        action: "transfer",
        paymentMethod: "wait",
        discount: addDiscount ,
        totalDue: generalTotalValue,
        vat: vatValue,
        generalTotal: generalTotalValue,
        paidAmount: generalTotalValue,
        notes: 'امر تسليم خامات للمندوب',
        items: [],
        total: calculateInvoiceTotals(),
        remainingAmount: generalTotalValue - addDiscount
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
            const count = parseFloat(cells[3].textContent);
            const price = parseFloat(cells[5].textContent.replace(/[^0-9.-]+/g, ""));
            const total = parseFloat(cells[6].textContent.replace(/[^0-9.-]+/g, ""));

            if (!isNaN(itemID) && !isNaN(count) && !isNaN(price) && !isNaN(total)) {
                invoiceData.items.push({
                    itemID: itemID,
                    itemName: cells[2].textContent.trim(),
                    count: count,
                    unit: cells[4].textContent.trim(),
                    price: price,
                    total: total,
                    discount: discount || 0
                });
            }
        }
    });

    if (invoiceData.items.length === 0) {
        showToast('لا توجد عناصر صالحة في الفاتورة!', 'danger');
        return;
    }

    // إرسال البيانات
    fetch('fromStore2Salesmane/addItemToInvoice.php', {
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

