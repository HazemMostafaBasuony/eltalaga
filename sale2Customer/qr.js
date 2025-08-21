function generateVatQrCode(companyName, vatNumber, invoiceDate, totalAmount, taxAmount) {
    const qrContainer = document.getElementById('vatQrCode');
    qrContainer.innerHTML = ''; // مسح أي QR قديم

    // صيغة TLV Base64 حسب متطلبات هيئة الزكاة السعودية
    function toHexString(value) {
        return value.toString(16).padStart(2, '0');
    }

    function encodeTLV(tag, value) {
        const textEncoder = new TextEncoder();
        const valueBytes = textEncoder.encode(value);
        return [
            toHexString(tag),
            toHexString(valueBytes.length),
            ...Array.from(valueBytes).map(b => toHexString(b))
        ];
    }

    // بناء TLV
    const tlvBytes = [
        ...encodeTLV(1, companyName),        // اسم البائع
        ...encodeTLV(2, vatNumber),          // الرقم الضريبي
        ...encodeTLV(3, invoiceDate),        // وقت الفاتورة ISO8601
        ...encodeTLV(4, totalAmount),        // الإجمالي مع الضريبة
        ...encodeTLV(5, taxAmount)           // مبلغ الضريبة
    ];

    // تحويل إلى Base64
    const qrCodeData = btoa(String.fromCharCode(...tlvBytes.map(h => parseInt(h, 16))));

    // إنشاء الـ QR باستخدام المكتبة الموجودة عندك
    new QRCode(qrContainer, {
        text: qrCodeData,
        width: 100,
        height: 100
    });
}
