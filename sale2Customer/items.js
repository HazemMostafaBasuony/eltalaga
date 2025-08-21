 

// تحميل جميع الأصناف

getItems('all');
// تحميل الأصناف حسب فاتورة المورد
function getItems(subGroup) {
    var userID = document.getElementById('salesmaneID').value;
    
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('items').innerHTML = xhr.responseText;
            clearSearch();
        }
    }
    xhr.open('GET', 'sale2Customer/getItems.php?subGroup=' + subGroup + "&userID=" + userID, true);
    xhr.send();
}



// مسح البحث
function clearSearch() {
    document.getElementById('searchItem').value = '';
    var items = document.querySelectorAll('#items .item-button');
    items.forEach(function (item) {
        item.style.display = 'block';
    });
}

