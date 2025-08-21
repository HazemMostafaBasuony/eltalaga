
// تعديل على المشروع 
// https://github.com/yamadapc/js-written-number/tree/master/dist
// 22-7-2025
// Hazem


// JS/currencyConverter.js
// تأكد أن writtenNumber معرفة في النطاق العام (من written-number.min.js)

/**
 * يحول مبلغًا رقميًا إلى نص باللغتين العربية والإنجليزية.
 * يدعم الأجزاء العشرية ويضيف العملات.
 * @param {number} amount - المبلغ الرقمي المراد تحويله.
 * @returns {object} كائن يحتوي على النص بالعربية والإنجليزية.
 */
function convertAmountToWords(amount) {
    if (typeof writtenNumber === 'undefined') {
        console.error("خطأ: دالة writtenNumber غير معرفة. تأكد من تضمين written-number.min.js قبل هذا السكريبت.");
        return { ar: "خطأ في التحويل", en: "Conversion Error" };
    }

    let totalString = amount.toString();
    let parts = totalString.split('.');

    let integerPart = parseInt(parts[0], 10);
    let decimalPart = parts[1] ? parseInt(parts[1], 10) : 0;

    // التأكد من أن الجزء العشري مكون من رقمين إذا كان أقل (مثال: .5 يصبح .50)
    if (parts[1] && parts[1].length === 1) {
        decimalPart = decimalPart * 10;
    }

    // تحويل الجزء الصحيح إلى كلمات
    let integerWordsAr = writtenNumber(integerPart, { lang: 'ar' });
    let integerWordsEn = writtenNumber(integerPart, { lang: 'en' });

    // تحويل الجزء العشري إلى كلمات
    let decimalWordsAr = '';
    let decimalWordsEn = '';

    if (decimalPart > 0) {
        decimalWordsAr = writtenNumber(decimalPart, { lang: 'ar' });
        decimalWordsEn = writtenNumber(decimalPart, { lang: 'en' });
    }

    // دمج الأجزاء وإضافة العملة للعربية
    let fullAmountAr = '';
    if (integerPart > 0) {
         fullAmountAr = integerWordsAr + ' ريالاً';
    } else if (integerPart === 0 && decimalPart === 0) {
        fullAmountAr = 'صفر ريال';
    }

    if (decimalPart > 0) {
        if (integerPart > 0) {
            fullAmountAr += ' و';
        }
        fullAmountAr += decimalWordsAr + ' هللة';
    }

    fullAmountAr += ' فقط لا غير';

    // دمج الأجزاء وإضافة العملة للإنجليزية
    let fullAmountEn = '';
    if (integerPart > 0) {
        fullAmountEn = integerWordsEn + ' Riyals';
    } else if (integerPart === 0 && decimalPart === 0) {
        fullAmountEn = 'Zero Riyals';
    }

    if (decimalPart > 0) {
        if (integerPart > 0) {
            fullAmountEn += ' and ';
        }
        fullAmountEn += decimalWordsEn + ' Halalas';
    }

    fullAmountEn += ' only';

    return { ar: fullAmountAr, en: fullAmountEn };
}