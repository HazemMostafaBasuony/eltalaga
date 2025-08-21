
// حساب الجمالى بناءا على العدد والسعر
function calculateTotals() {
    const countItem = parseFloat(document.getElementById('countItem').value) || 0;
    const priceItem = parseFloat(document.getElementById('priceItem').value) || 0;
    const discountItem = parseFloat(document.getElementById('discountItem').value) || 0;
    // الحسابات باستخدام toFixed
    const priceXcount = (countItem * priceItem).toFixed(2);
    const price_vat = ((priceItem) * (1 + VAT_RATE)).toFixed(2);
    const price_vatXcount = ((priceXcount) * (1 + VAT_RATE)).toFixed(2);
    const totalDisplay = (price_vatXcount - discountItem).toFixed(2);

    // تحديث القيم (استخدم القيمة الرقمية فقط في value والعرض المنسق في placeholder)
    document.getElementById('priceXcount').value = priceXcount;
    document.getElementById('price_vat').value = price_vat;
    document.getElementById('price_vatXcount').value = price_vatXcount;
    document.getElementById('totalDisplay').value = totalDisplay;

}

// حساب السعر بناءا على الجمالى
function calculatePriceItem() {
    const countItem = parseFloat(document.getElementById('countItem').value) || 1;
    const price_vatXcount = parseFloat(document.getElementById('price_vatXcount').value) || 0;
    const discountItem = parseFloat(document.getElementById('discountItem').value) || 0;

    const totalDisplay = (price_vatXcount - discountItem).toFixed(2);
    const price_vat = (totalDisplay / countItem).toFixed(2);
    const priceXcount = (totalDisplay / (1 + VAT_RATE)).toFixed(2);
    const priceItem = (priceXcount / countItem).toFixed(2);

    // تحديث القيم (استخدم القيمة الرقمية فقط في value والعرض المنسق في placeholder)
    document.getElementById('priceXcount').value = priceXcount;
    document.getElementById('price_vat').value = price_vat;
    document.getElementById('priceItem').value = priceItem;
    document.getElementById('totalDisplay').value = totalDisplay;

}

document.getElementById('countItem').addEventListener('input', function () {
    if (this.value < 0 || this.value == "" || this.value == null) {
        document.getElementById('add').disabled = true;
    } else {
        document.getElementById('add').disabled = false;
    }
});
