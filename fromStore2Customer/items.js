
var customerID;
var searchDebounceTimer = null;


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

    // تهيئة الماسح الضوئي للباركود
    initBarcodeScanner();
});


function getMainGroup() {
    fetch(`fromStore2Customer/getMainGroup.php?customerID=${customerID}`)
        .then(response => response.text())
        .then(data => document.getElementById('mainGroup').innerHTML = data);
}
function getSubGroup(mainGroup) {
    fetch(`fromStore2Customer/getSubGroup.php?mainGroup=${mainGroup}`)
        .then(response => response.text())
        .then(data => document.getElementById('supGroup').innerHTML = data);
}

function getItems(subGroup) {
    fetch(`fromStore2Customer/getItems.php?subGroup=${subGroup}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('items').innerHTML = data;
            clearSearch();
        });
}



// ---------------------------------------------------------------
// search
// -----------------------------------------------------------

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


// تحديث عداد الفاتورة
function updateInvoiceCounter(count) {
    invoiceItemsCount = count;
    const counter = document.getElementById('invoiceCounter');
    counter.textContent = count;
    counter.style.display = count > 0 ? 'flex' : 'none';
}




// ----------------------------------------------------------------
// scan Came -----------------------------------------------
// -------------------------------------------------------------

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
                addItemToInvoice(content);
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


//   ------------------------------------------------------------------

