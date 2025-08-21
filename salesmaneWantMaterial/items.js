
var customerID;
var searchDebounceTimer = null;


// تهيئة الصفحة عند التحميل
document.addEventListener('DOMContentLoaded', function () {
    // تهيئة تاريخ الفاتورة
    var today = new Date();
    document.getElementById('receiveDate').textContent = today.toLocaleDateString('ar-SA');

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


function getMainGroup() {
    fetch(`salesmaneWantMaterial/getMainGroup.php?customerID=${customerID}`)
        .then(response => response.text())
        .then(data => document.getElementById('mainGroup').innerHTML = data);
}
function getSubGroup(mainGroup) {
    fetch(`salesmaneWantMaterial/getSubGroup.php?mainGroup=${mainGroup}`)
        .then(response => response.text())
        .then(data => document.getElementById('supGroup').innerHTML = data);
}

function getItems(subGroup) {
    fetch(`salesmaneWantMaterial/getItems.php?subGroup=${subGroup}`)
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
    const invoiceIcon= document.getElementById('invoiceIcon');
    if (count === 0) {
       invoiceIcon.src='assets/images/sms2.png';
    }else{
        invoiceIcon.src='assets/images/sms.png';
    }
}




