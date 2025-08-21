async function loadInvoice(invoiceID) {
    try {
        const response = await fetch(`invoiceIn/invoiceIn.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ invoiceID })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Unknown server error');
        }

        // التحقق من وجود بيانات الفاتورة
        if (!data.invoice) {
            throw new Error('invoice not found');
        }

        // التحقق من وجود بيانات المورد
        if (!data.supplier) {
            throw new Error('supplier not found');
        }

        // التحقق من وجود بيانات الفرع
        if (!data.branch) {
            throw new Error('branch not found');
        }



        // عرض بيانات الفاتورة الأساسية
        document.getElementById('invoiceTitle').textContent =
            `فاتورة شراء من ${data.supplier?.supplierName || 'غير معروف'} إلى فرع ${data.branch?.branchName || 'غير معروف'}`;

        document.getElementById('invoiceNumber').textContent = data.invoice.invoiceID || 'غير متوفر';
        document.getElementById('invoiceDate').textContent = data.invoice.date || 'غير متوفر';
        let qrUrl = "http://192.168.8.128/elTalaga/uploads/invoices/" + data.invoice.originalInvoicePath;
        document.getElementById('qrcode').innerHTML = '';
        new QRCode(document.getElementById('qrcode'), {
            text: qrUrl,
            width: 100,
            height: 100,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
        // إضافة العناصر إلى الجدول
        const tableBody = document.getElementById('invoiceItemsTable');
        tableBody.innerHTML = ''; // مسح المحتوى القديم

        let subtotal = 0;
        let totalVAT = 0;
        let totalDiscount = 0;
        let finalTotal = 0;

        data.itemActions.forEach((action, index) => {
            const item = data.items[action.itemID] || {};
            const price = parseFloat(action.price) || 0;
            const count = parseInt(action.count) || 0;
            const unit = action.unit || 'غير معروف';
            const discount = parseFloat(action.discount) || 0;
            const priceWithVat = price * 1.15;
            const total = (priceWithVat * count) - discount;

            subtotal += price * count;
            totalVAT += price * count * 0.15;
            totalDiscount += discount;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${action.itemID || ''}</td>
                <td>${item.itemName || 'غير معروف'}</td>
                <td>${count}</td>
                <td>${unit}</td>
                <td>${price.toFixed(2)}</td>
                <td>${priceWithVat.toFixed(2)}</td>
                <td>${discount.toFixed(2)}</td>
                <td>${total.toFixed(2)}</td>
                <td>
                    <div class="d-flex justify-content-center gap-2 d-print-none">
                        <button class="btn btn-danger btn-sm" onclick="deleteItem('${action.itemID}')">
                            <i class="fa fa-trash"></i>
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="editItem('${action.itemID}')">
                            <i class="fa fa-edit"></i>
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });
        finalTotal = (subtotal + totalVAT - totalDiscount).toFixed(2);
        
        // تحديث الإجماليات
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('vatAmount').textContent = totalVAT.toFixed(2);
        document.getElementById('discount').textContent = totalDiscount;
        document.getElementById('totalAmount').textContent = (subtotal + totalVAT).toFixed(2);
        document.getElementById('finalTotal').textContent = finalTotal;
        document.getElementById('finalTotalAr').textContent += convertAmountToWords(finalTotal).ar;
        document.getElementById('finalTotalEn').textContent += convertAmountToWords(finalTotal).en;
    } catch (error) {
        console.error('Failed to load invoice:', error);
        alert('فشل تحميل الفاتورة: ' + error.message);
    }
}

// دالة مساعدة لحذف العنصر
function deleteItem(itemID) {
    if (confirm('هل أنت متأكد من حذف هذا العنصر؟')) {
        // كود الحذف هنا
        console.log('Deleting item:', itemID);
    }
}

// دالة مساعدة لتعديل العنصر
function editItem(itemID) {
    // كود التعديل هنا
    console.log('Editing item:', itemID);
}