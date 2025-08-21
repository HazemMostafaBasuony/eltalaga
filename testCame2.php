<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ماسح الباركود</title>


    <script src="js/instascan.min.js"></script> 
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
          integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">

    <style type="text/css">
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f2f5;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
        }

        .container {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
        }

        h1 {
            color: #007bff;
            margin-bottom: 20px;
            font-size: 24px;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        button:hover:not(:disabled) {
            background-color: #218838;
        }

        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        /* حاوية الفيديو، للتحكم في حجمه */
        #preview-container {
            position: relative;
            width: 100%;
            max-width: 320px; /* العرض المطلوب */
            height: 240px;    /* الارتفاع المطلوب */
            background-color: #eee;
            border: 2px solid black; /* إطار حول بث الفيديو */
            border-radius: 8px;
            overflow: hidden;
            margin: 20px auto; /* توسيط الحاوية أفقياً */
            display: none; /* مخفي افتراضياً، يتم إظهاره عند بدء المسح */
        }

        #preview-container video {
            width: 100%;
            height: 100%;
            object-fit: cover; /* يضمن أن الفيديو يملأ الحاوية بالكامل */
            /* خيار 'mirror: false' في Instascan بيعالج اتجاه الصورة، 
               فمش محتاجين CSS transform هنا إلا لو كانت لأغراض تصميمية خاصة */
        }

        #qrtext {
            width: calc(100% - 20px); /* تعديل العرض ليناسب الحاوية مع البادينج */
            padding: 10px;
            margin-top: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1.1em;
            text-align: center;
        }

        /* تنسيقات رسائل الحالة */
        .message {
            margin-top: 15px;
            padding: 10px;
            background-color: #f8d7da; /* أحمر فاتح للخطأ */
            color: #721c24; /* أحمر غامق للخطأ */
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            display: none; /* مخفي افتراضياً */
        }
        .message.success {
            background-color: #d4edda; /* أخضر فاتح للنجاح */
            color: #155724; /* أخضر غامق للنجاح */
            border-color: #c3e6cb;
        }
        .message.info { /* لو عايز تضيف ستايل لرسائل المعلومات */
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>ماسح الباركود</h1>

        <button id="toggleScannerBtn">بدء المسح</button>

        <div id="statusMessage" class="message"></div>

        <div id="preview-container">
            <video id="preview"></video>
        </div>

        <div class="center-text">
            <div class="row green">
                <input id="qrtext" type="text" name="" readonly placeholder="الباركود الممسوح سيظهر هنا">
            </div>
        </div>

        <div style="margin-top: 20px; font-size: 0.9em; color: #666;">
            <p><strong>ملاحظة (للتطوير المحلي):</strong> إذا واجهت مشكلة في الوصول إلى الكاميرا على <code>localhost</code>، قد تحتاج إلى السماح بذلك يدوياً في إعدادات متصفح Chrome:</p>
            <a href="chrome://flags/#unsafely-treat-insecure-origin-as-secure" target="_blank" style="color: #007bff;">
                افتح <code>chrome://flags/#unsafely-treat-insecure-origin-as-secure</code>
            </a>
            <p>ثم أضف <code>http://localhost:8000</code> (أو المنفذ الذي تستخدمه) في مربع النص واضغط "Relaunch".</p>
        </div>
    </div>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleScannerBtn = document.getElementById('toggleScannerBtn');
            const previewContainer = document.getElementById('preview-container');
            const previewVideo = document.getElementById('preview');
            const qrtextInput = document.getElementById('qrtext');
            const statusMessageDiv = document.getElementById('statusMessage');

            let scanner = null; // متغير لتخزين مثيل Instascan.Scanner
            let isScannerRunning = false; // لتتبع حالة الماسح

            // --- دوال مساعدة لعرض الرسائل ---
            function showStatusMessage(message, type = 'error') {
                statusMessageDiv.textContent = message;
                statusMessageDiv.className = 'message ' + type; // إعادة تعيين الكلاسات وإضافة النوع الجديد
                statusMessageDiv.style.display = 'block';
                setTimeout(() => {
                    statusMessageDiv.style.display = 'none';
                }, 5000); // إخفاء الرسالة بعد 5 ثواني
            }

            // --- إعداد والتحكم في Instascan ---
            async function startScanner() {
                toggleScannerBtn.disabled = true; // تعطيل الزر مؤقتاً
                toggleScannerBtn.textContent = 'جاري التشغيل...';
                previewContainer.style.display = 'block'; // إظهار حاوية الفيديو
                qrtextInput.value = ''; // مسح أي باركود سابق
                qrtextInput.placeholder = 'جاري البحث عن باركود...'; // رسالة للمستخدم
                showStatusMessage('جاري تشغيل الكاميرا...', 'info'); 

                // تهيئة الماسح إذا لم يكن مهيأ بعد
                if (!scanner) {
                    scanner = new Instascan.Scanner({
                        video: previewVideo, // العنصر اللي هيعرض الفيديو
                        scanPeriod: 5, // فترة المسح بالمللي ثانية (قيمة أقل = مسح أسرع)
                        mirror: false // مهم جداً للكاميرا الخلفية عشان الصورة متكونش معكوسة
                    });

                    // إضافة مستمع لحدث اكتشاف الباركود
                    scanner.addListener('scan', function (content) {
                        qrtextInput.value = content; // وضع قيمة الباركود في حقل الإدخال
                        showItem(content); // استدعاء دالتك الخاصة لمعالجة الباركود
                        stopScanner(true); // إيقاف المسح بعد قراءة ناجحة
                        showStatusMessage('تم مسح الباركود بنجاح!', 'success');
                    });

                    // مستمع لحدث "active" لما بث الكاميرا يشتغل بنجاح
                    scanner.addListener('active', function () {
                        showStatusMessage('الكاميرا قيد التشغيل. وجّه الكاميرا نحو الباركود.', 'success');
                        toggleScannerBtn.textContent = 'إيقاف المسح';
                        toggleScannerBtn.disabled = false;
                        isScannerRunning = true;
                    });
                }

                try {
                    const cameras = await Instascan.Camera.getCameras(); // جلب الكاميرات المتاحة
                    if (cameras.length > 0) {
                        let selectedCamera = cameras[0]; // البدء بأول كاميرا كافتراضي

                        // محاولة إيجاد الكاميرا الخلفية بناءً على اسمها
                        for (let i = 0; i < cameras.length; i++) {
                            if (cameras[i].name && (cameras[i].name.toLowerCase().includes('back') || cameras[i].name.toLowerCase().includes('environment'))) {
                                selectedCamera = cameras[i];
                                break;
                            }
                            // لو مفيش اسم واضح "خلفي"، وعدد الكاميرات أكتر من واحدة،
                            // غالباً الكاميرا الثانية (index 1) بتكون الخلفية في الجوالات.
                            if (i === 0 && cameras.length > 1) {
                                selectedCamera = cameras[1];
                            }
                        }

                        await scanner.start(selectedCamera); // بدء الماسح بالكاميرا المختارة
                        // isScannerRunning هيتم تعيينها بواسطة مستمع 'active'
                    } else {
                        console.error('لم يتم العثور على كاميرات.');
                        showStatusMessage('لم يتم العثور على أي كاميرات في جهازك.', 'error');
                        stopScanner(false); // إيقاف الماسح بدون إعادة تعيين الرسالة
                    }
                } catch (err) {
                    console.error('فشل الوصول إلى الكاميرا أو تهيئة الماسح:', err);
                    let errorMessage = 'لم يتمكن من الوصول إلى الكاميرا. يرجى التأكد من منح الإذن.';
                    if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                        errorMessage = 'تم رفض الوصول إلى الكاميرا. يرجى السماح بالوصول في إعدادات المتصفح/الجهاز.';
                    } else if (err.name === 'NotFoundError') {
                        errorMessage = 'لم يتم العثور على كاميرا في جهازك.';
                    } else if (err.name === 'NotReadableError') {
                        errorMessage = 'الكاميرا قيد الاستخدام بواسطة تطبيق آخر.';
                    } else if (err.name === 'DomException' && err.message.includes('permission')) {
                         errorMessage = 'الوصول إلى الكاميرا مرفوض. يرجى مراجعة أذونات الموقع.';
                    }
                    showStatusMessage(errorMessage, 'error');
                    stopScanner(false); // إيقاف الماسح بدون إعادة تعيين الرسالة
                }
            }

            // دالة لإيقاف الماسح
            function stopScanner(resetMessage = true) {
                if (scanner && isScannerRunning) {
                    scanner.stop();
                }
                isScannerRunning = false;
                previewContainer.style.display = 'none'; // إخفاء حاوية الفيديو
                toggleScannerBtn.textContent = 'بدء المسح'; // إعادة نص الزر
                toggleScannerBtn.disabled = false; // تفعيل الزر
                if (resetMessage) {
                    qrtextInput.placeholder = 'الباركود الممسوح سيظهر هنا'; // إعادة نص حقل الإدخال
                }
            }

            // --- مستمعات الأحداث ---
            toggleScannerBtn.addEventListener('click', function () {
                if (isScannerRunning) {
                    stopScanner();
                } else {
                    startScanner();
                }
            });

            // التأكد من إيقاف الماسح إذا غادر المستخدم الصفحة أو أغلق التبويب
            window.addEventListener('beforeunload', function () {
                if (scanner && isScannerRunning) {
                    scanner.stop();
                }
            });

            // --- دالاتك المخصصة (تأكد من تعريفها) ---
            // هذا مكان لدالة showItem - تحتاج لتعريف ماذا تفعل هذه الدالة
            function showItem(barcodeValue) {
                console.log("تم استدعاء showItem بالباركود:", barcodeValue);
                // هنا ضع المنطق الخاص بك، مثل:
                // جلب تفاصيل الصنف من قاعدة البيانات بناءً على قيمة الباركود
                // تحديث عناصر HTML أخرى في الصفحة بالمعلومات المسترجعة
                // مثال بسيط:
                // alert("سيتم عرض تفاصيل الصنف للباركود: " + barcodeValue);
            }

            // دالة chickQrNo تبدو زائدة عن الحاجة لو Instascan هو اللي بيضع القيمة في الحقل وبينادي showItem.
            // لو حقل qrtext مخصص كمان للإدخال اليدوي، ساعتها هتحتاج تتعامل مع تغييراته بشكل منفصل.
            // حالياً، تم إزالة onkeyup من HTML ولن يتم استخدام chickQrNo هنا.
            /*
            function chickQrNo(noQR) {
                if (qrtextInput.value !== "") {
                    // هذه الدالة غالباً للإدخال اليدوي لو أردت ذلك
                    // الماسح سيضع قيمة في qrtextInput.value مباشرة
                    console.log("تم استدعاء chickQrNo بـ:", noQR);
                }
            }
            */

            // مثال على كيفية استخدام دالة show الموجودة عندك (لو عندك عناصر معينة عايز تظهرها/تخفيها)
            // (تحتاج لتعديل استخدامها لو عندك ID's معينة للعناصر)
            // function show(id) {
            //     var x = document.getElementById(id);
            //     if (x.style.display === "block") {
            //         x.style.display = "none";
            //     } else {
            //         x.style.display = "block";
            //     }
            // }

        }); // نهاية DOMContentLoaded
    </script>
    =
</body>
</html>