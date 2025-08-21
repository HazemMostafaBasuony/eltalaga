<?php
header('Content-Type: application/json'); // تحديد نوع المحتوى كـ JSON

// 1. معلومات اتصال قاعدة البيانات
// تأكد أن مسار ملف conn.php صحيح ويعيد كائن اتصال $conn
include('../hmb/conn.php');

// التحقق من الاتصال
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'فشل الاتصال بقاعدة البيانات: ' . $conn->connect_error]);
    exit();
}

// تعيين ترميز الأحرف إلى UTF-8 لدعم اللغة العربية
$conn->set_charset("utf8mb4");

// 2. استقبال البيانات المرسلة من JavaScript
$jsonData = file_get_contents('php://input');
$items = json_decode($jsonData, true); // تحويل JSON إلى مصفوفة PHP

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'بيانات JSON غير صالحة: ' . json_last_error_msg()]);
    exit();
}

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'لا توجد بيانات لحفظها.']);
    exit();
}

// 3. تحضير استعلام التحقق من الوجود
$checkSql = "SELECT itemID FROM itemscard WHERE itemName = ?";
$checkStmt = $conn->prepare($checkSql);
if (!$checkStmt) {
    echo json_encode(['success' => false, 'message' => 'فشل تحضير استعلام التحقق: itemscard 35 ' . $conn->error]);
    exit();
}
$checkStmt->bind_param("s", $itemNameCheck); // نربط اسم المنتج للتحقق

// 4. تحضير استعلام الإدخال
// لاحظ أننا لم نعد ندرج itemID في قائمة الأعمدة
$insertSql = "INSERT INTO itemscard (itemName, unitL, fL2M, unitM, fM2S, unitS, mainGroup, subGroup, stock, profit, priceL, priceM, priceS) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$insertStmt = $conn->prepare($insertSql);

if (!$insertStmt) {
    echo json_encode(['success' => false, 'message' => 'فشل تحضير استعلام الإدخال: itemscard 45 ' . $conn->error]);
    $checkStmt->close(); // أغلق استعلام التحقق قبل الخروج
    exit();
}

// تحديد أنواع البيانات للمعلمات (s=string, i=integer, d=double/float)
// عدد الـ 'd' يجب أن يتطابق مع عدد الأعمدة
// هنا 12 متغيرًا و 12 حرفًا (s = 7, d = 5) => ssdsdsssdddd
$insertStmt->bind_param("ssdsdsssddddd",
    $itemName, $unitL, $fL2M, $unitM, $fM2S, $unitS, $mainGroup, $subGroup, $stock, $profit, $priceL, $priceM, $priceS
);

// 5. بدء معاملة (Transaction) لضمان تكامل البيانات
$conn->begin_transaction();
$savedItems = [];
$skippedItems = [];
$technicalErrors = []; // للأخطاء الفنية (مثل فشل SQL)

foreach ($items as $item) {
    // التأكد من وجود جميع المفاتيح وتعيين قيم افتراضية إذا كانت مفقودة أو غير صالحة
    // تم إزالة تعيين $itemID لأنه يتم إنشاؤه تلقائيًا
    $itemName = isset($item['itemName']) ? (string)$item['itemName'] : '';
    $unitL = isset($item['unitL']) ? (string)$item['unitL'] : '';
    $fL2M = isset($item['fL2M']) ? (float)$item['fL2M'] : 0.0;
    $unitM = isset($item['unitM']) ? (string)$item['unitM'] : '';
    $fM2S = isset($item['fM2S']) ? (float)$item['fM2S'] : 0.0;
    $unitS = isset($item['unitS']) ? (string)$item['unitS'] : '';
    $mainGroup = isset($item['mainGroup']) ? (string)$item['mainGroup'] : 'ثلاجة';
    $subGroup = isset($item['subGroup']) ? (string)$item['subGroup'] : '';
    $stock = isset($item['stock']) ? (float)$item['stock'] : 0.0;
    $profit = isset($item['profit']) ? (float)$item['profit'] : 0.0;
    $priceL = isset($item['priceL']) ? (float)$item['priceL'] : 0.0;
    $priceM = isset($item['priceM']) ? (float)$item['priceM'] : 0.0;
    $priceS = isset($item['priceS']) ? (float)$item['priceS'] : 0.0;

    // *** هذا هو الجزء الذي تم تعديله أو إزالته ***
    // بما أن itemID هو AUTO_INCREMENT، لا نحتاج للتحقق منه هنا.
    // التحقق الوحيد الضروري الآن هو أن itemName ليس فارغًا.
    if (empty($itemName)) {
        $skippedItems[] = [
            'itemName' => $itemName,
            'reason' => 'اسم الصنف فارغ'
        ];
        continue; // تخطي هذا العنصر والانتقال إلى التالي
    }

    // *** التحقق من وجود اسم المنتج بالفعل ***
    $itemNameCheck = $itemName; // قم بتعيين قيمة للمعلمة المرتبطة
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // المنتج موجود بالفعل
        $skippedItems[] = [
            'itemName' => $itemName,
            'reason' => 'اسم المنتج موجود بالفعل في قاعدة البيانات'
        ];
    } else {
        // المنتج غير موجود، يمكننا إدخاله
        if (!$insertStmt->execute()) {
            // إضافة الخطأ الفني. لا نستخدم continue هنا حتى لا نخرج من المعاملة
            $technicalErrors[] = "فشل إدخال الصنف " . $itemName . ": " . $insertStmt->error;
            // يمكن هنا اختيار التوقف عن المعالجة والقيام بـ rollback فورًا
            // أو الاستمرار في جمع الأخطاء ثم rollback في النهاية
            // الكود الحالي يستمر في جمع الأخطاء، وهو خيار جيد لمعالجة دفعات كبيرة
        } else {
            $savedItems[] = $itemName;
        }
    }
}

// 6. إتمام المعاملة أو التراجع عنها وتقديم التقرير
if (empty($technicalErrors)) {
    // إذا لم تكن هناك أخطاء فنية، قم بتأكيد المعاملة
    $conn->commit();
    $responseMessage = "تم حفظ " . count($savedItems) . " صنفًا بنجاح.";
    if (!empty($skippedItems)) {
        $responseMessage .= " لم يتم حفظ " . count($skippedItems) . " صنفًا (مكررة أو بيانات غير صالحة).";
    }
    echo json_encode([
        'success' => true,
        'message' => $responseMessage,
        'saved' => $savedItems,
        'skipped' => $skippedItems,
        'technicalErrors' => [] // لا توجد أخطاء فنية
    ]);
} else {
    // إذا كان هناك أي خطأ فني، قم بالتراجع عن المعاملة بأكملها
    $conn->rollback();
    // بما أننا قمنا بالـ rollback، فإنه لم يتم حفظ أي شيء فعليًا في قاعدة البيانات
    $responseMessage = "حدثت أخطاء أثناء الحفظ. تم التراجع عن العملية. لم يتم حفظ أي صنف بنجاح في قاعدة البيانات.";
    if (!empty($skippedItems)) {
        $responseMessage .= " لم يتم حفظ " . count($skippedItems) . " صنفًا بسبب التكرار أو البيانات غير الصالحة.";
    }
    $responseMessage .= " الأخطاء الفنية التي تسببت في التراجع: " . implode("; ", $technicalErrors);

    echo json_encode([
        'success' => false,
        'message' => $responseMessage,
        'saved' => [], // لا يوجد شيء محفوظ فعليا بسبب الـ rollback
        'skipped' => $skippedItems,
        'technicalErrors' => $technicalErrors
    ]);
}

// إغلاق العبارات والاتصال
$checkStmt->close();
$insertStmt->close();
$conn->close();
?>