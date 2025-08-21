document.addEventListener('DOMContentLoaded', () => {
    const excelFileInput = document.getElementById('excelFileInput');
    const dataTable = document.getElementById('dataTable');
    const saveDataBtn = document.getElementById('saveDataBtn');
    const downloadTemplateBtn = document.getElementById('downloadTemplateBtn');
    const statusMessage = document.getElementById('statusMessage');

    let importedExcelData = [];

    // وظيفة لعرض رسائل الحالة
    function showStatus(message, type, details = null) { // إضافة معلمة details
        let fullMessage = message;
        if (details) {
            if (details.saved && details.saved.length > 0) {
                fullMessage += `<br><strong>تم حفظ:</strong> ${details.saved.join(', ')}`;
            }
            // إضافة تفاصيل الأخطاء والتخطي هنا (كما كانت في الجزء المكرر)
            if (details.skipped && details.skipped.length > 0) {
                fullMessage += `<br><strong>لم يتم حفظ (مكرر/غير صالح):</strong><ul>`;
                details.skipped.forEach(item => {
                    fullMessage += `<li>${item.itemName || 'صنف غير معروف'} (${item.reason})</li>`;
                });
                fullMessage += `</ul>`;
            }
            if (details.technicalErrors && details.technicalErrors.length > 0) {
                fullMessage += `<br><strong>أخطاء فنية:</strong><ul>`;
                details.technicalErrors.forEach(error => {
                    fullMessage += `<li>${error}</li>`;
                });
                fullMessage += `</ul>`;
            }
        }

        statusMessage.innerHTML = fullMessage; // استخدام innerHTML للسماح بالـ HTML
        statusMessage.className = 'status-message';
        statusMessage.classList.add(`status-${type}`);
        statusMessage.style.display = 'block';
        setTimeout(() => {
            statusMessage.style.display = 'none';
        }, 10000); // زيادة وقت العرض لـ 10 ثوانٍ للرسائل التفصيلية
    }

    excelFileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = (e) => {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });

                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];

                importedExcelData = XLSX.utils.sheet_to_json(worksheet);

                console.log('بيانات Excel المستوردة:', importedExcelData);

                renderTable(importedExcelData);
                saveDataBtn.disabled = false;
                showStatus('تم تحميل ملف Excel بنجاح. يمكنك الآن حفظ البيانات.', 'success');
            };

            reader.onerror = (error) => {
                console.error("خطأ في قراءة الملف:", error);
                showStatus("حدث خطأ أثناء قراءة الملف.", 'error');
                saveDataBtn.disabled = true;
            };

            reader.readAsArrayBuffer(file);
        } else {
            showStatus("يرجى تحديد ملف Excel.", 'error');
            saveDataBtn.disabled = true;
        }
    });

    function renderTable(data) {
        dataTable.innerHTML = '';

        if (!data || data.length === 0) {
            dataTable.innerHTML = '<thead><tr><th>لا توجد بيانات لعرضها.</th></tr></thead>';
            saveDataBtn.disabled = true;
            return;
        }

        let tableHTML = '<thead><tr>';
        const headers = Object.keys(data[0]);

        headers.forEach(header => {
            tableHTML += `<th>${header}</th>`;
        });
        tableHTML += '</tr></thead><tbody>';

        data.forEach(row => {
            tableHTML += '<tr>';
            headers.forEach(header => {
                tableHTML += `<td>${row[header] !== undefined ? row[header] : ''}</td>`;
            });
            tableHTML += '</tr>';
        });

        tableHTML += '</tbody>';
        dataTable.innerHTML = tableHTML;
    }

    saveDataBtn.addEventListener('click', async () => {
        if (importedExcelData.length === 0) {
            showStatus('لا توجد بيانات لحفظها. يرجى تحميل ملف Excel أولاً.', 'error');
            return;
        }

        saveDataBtn.disabled = true;
        showStatus('جاري حفظ البيانات...', 'info');

        try {
            const response = await fetch('inputItemsFromExcl/saveExlc.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(importedExcelData)
            });

            const result = await response.json(); // افتراض أن الخادم سيعيد استجابة JSON

            if (result.success) {
                showStatus(result.message, 'success', result); // تمرير كائن result الكامل
                importedExcelData = [];
                renderTable([]);
                excelFileInput.value = '';
            } else {
                showStatus(result.message, 'error', result); // تمرير كائن result الكامل
            }
        } catch (error) {
            // الرسالة التفصيلية في حالة الخطأ
            console.error('خطأ في إرسال البيانات إلى الخادم:');
            showStatus('حدث خطأ في الاتصال بالخادم. يرجى المحاولة مرة أخرى.', 'error', {
                technicalErrors: [error.message || String(error)]
            });
        } finally {
            saveDataBtn.disabled = false;
        }
    });

    downloadTemplateBtn.addEventListener('click', () => {
        const headers = [
            'itemName', 'unitL', 'fL2M', 'unitM', 'fM2S',
            'unitS', 'mainGroup', 'subGroup', 'stock', 'profit', 'priceL', 'priceM', 'priceS'
        ];

        const ws = XLSX.utils.aoa_to_sheet([headers]);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "نموذج الأصناف");
        XLSX.writeFile(wb, "نموذج_الأصناف.xlsx");

        showStatus('تم تنزيل قالب Excel بنجاح!', 'success');
    });
});