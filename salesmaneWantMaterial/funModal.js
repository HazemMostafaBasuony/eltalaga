const sendItem = bootstrap.Modal.getOrCreateInstance(document.getElementById('sendItem'));
const invoiceModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('invoiceModal'));
const invoiceInfoModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('invoiceInfoModal'));

function showSendItemModal() { sendItem.show(); }
function hideSendItemModal() { sendItem.hide(); }
function showInvoiceModal() { invoiceModal.show(); }
function hideInvoiceModal() { invoiceModal.hide(); }
function showInvoiceInfoModal() { invoiceInfoModal.show(); }
function hideInvoiceInfoModal() { invoiceInfoModal.hide(); }


// استدعاء بيانات الصنف و اظهارها فى المودل
function addItemToInvoice(itemID, stock) {
    console.log("تم استدعاء addItemToInvoice مع itemID:", itemID);

    if (!itemID) {
        console.error('لم يتم تحديد معرف الصنف');
        showToast('لم يتم تحديد صنف', 'danger');
        return;
    }
    // جلب تفاصيل الصنف
    fetch(`salesmaneWantMaterial/getItemDetails.php?itemID=${itemID}`)
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
                    option.textContent = item.unitL;
                    unitSelect.appendChild(option);
                }

                if (item.unitM) {
                    const option = document.createElement('option');
                    option.value = 'M';
                    option.textContent = item.unitM;
                    unitSelect.appendChild(option);
                }

                if (item.unitS) {
                    const option = document.createElement('option');
                    option.value = 'S';
                    option.textContent = item.unitS;
                    unitSelect.appendChild(option);
                }
                document.getElementById('countInfo').textContent = "* لديك فى المخزن الرئيسي " + stock + " " + item.unitL;

                showSendItemModal();
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
}


// اضافة الصنف الى الطلبية
function confirmAddItem() {
    const itemID = document.getElementById('itemID').value || -1;
    const countItem = document.getElementById('countItem').value || 0;
    const unitSelect = document.getElementById('unitSelect');
    let unit = '';

    if (unitSelect.selectedIndex !== -1) {
        unit = unitSelect.options[unitSelect.selectedIndex].textContent.trim();
    }

    if (isNaN(countItem) || countItem <= 0) {
        showToast('يرجى إدخال عدد صحيح أكبر من الصفر', 'danger');
        document.getElementById('countItem').focus();
        return;
    }


    if (!unit) {
        showToast('يرجى اختيار وحدة القياس', 'danger');
        document.getElementById('unitSelect').focus();
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                hideSendItemModal();
                getrequisitionInfo();
                showToast(res.message, 'success');
                // document.getElementById("loader").innerHTML = xhr.responseText;
            } else {
                showToast(res.message, 'danger');
            }
        }
    }
    xhr.open("GET", "salesmaneWantMaterial/confirmAddItem.php?itemID=" + encodeURIComponent(itemID) + "&countItem=" + encodeURIComponent(countItem) + "&unit=" + encodeURIComponent(unit), true);
    xhr.send();
}


function confirmPurchase() {
    invoiceInfoModal.show();

}

function sendMessage() {
    invoiceInfoModal.hide();
    invoiceModal.hide();
    showToast('تمت الارسال بنجاح', 'success');
}

// تحديث الطلبيه
function getItemsInvoice() {

}

getrequisitionInfo();
// ايجاد بيانات الرسائل المرسله
function getrequisitionInfo() {
    fetch('salesmaneWantMaterial/getrequisitionInfo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('عدد الطلبيات:', data.countREQ);
                updateInvoiceCounter(data.countREQ);

                // 🟢 تعبئة رأس الطلبية
                document.getElementById("requestNumber").textContent = "INV-" + String(data.countREQ).padStart(3, '0');
                document.getElementById("receiveDate").textContent = new Date().toLocaleString();

                // 🟢 تعبئة الجدول
                const tableBody = document.getElementById("invoiceItemsTable");
                tableBody.innerHTML = ""; // فضي الجدول

                data.requests.forEach(req => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${req.dart}</td>
                        <td>${req.message}</td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="removeItem(${req.requestID})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });

                // 🟢 رسالة إضافية
                document.getElementById("extraSmS").textContent =
                    "إجمالي عدد الأصناف بالطلبية: " + data.countREQ;
            }
        })
        .catch(error => {
            console.error('حدث خطأ:', error);
            showToast('حدث خطأ في جلب بيانات الطلبية', 'danger');
        });
}



// حزف عنصر من الطلبية
function removeItem(requestID) {
    if (!confirm("هل أنت متأكد من حذف هذا الصنف من الطلبية؟")) {
        return;
    }

    fetch("salesmaneWantMaterial/removeItem.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "requestID=" + encodeURIComponent(requestID)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, "success");
            getrequisitionInfo(); // إعادة تحميل الطلبية بعد الحذف
        } else {
            showToast(data.message, "danger");
        }
    })
    .catch(error => {
        console.error("خطأ في حذف الصنف:", error);
        showToast("تعذر حذف الصنف", "danger");
    });
}



// مرحلة ارسال الرساله الى المخزن
function sendMessageToStore() {
    const message = document.getElementById("message").value; 
    // if(massge.lenth>200){
    //     showToast('يرجى ادخال رسالة اقل من 200 حرف', 'danger');
    //     return;
    // }
    fetch('salesmaneWantMaterial/sendMessageToStore.php', {
        method: 'POST',
        body: 'message=' + encodeURIComponent(message),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            hideInvoiceInfoModal();
            // الانتقال الى صفحة salesmane_HomePage.php
            window.location.href = 'salesmane_HomePage.php';
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('حدث خطاء في الاتصال بالخادم:', error);
        showToast('حدث خطاء في الاتصال بالخادم', 'danger');
    });
}
