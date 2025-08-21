<script>
    //  الشكل الأساسي لـ fetch:
    fetch('some-url')
  .then(response => response.text())
  .then(data => {
    console.log(data);
  })
  .catch(error => {
    console.error('Error:', error);
  });

//   📦 تنسيقات البيانات المدعومة:
// نوع البيانات	الطريقة
// نص (HTML, نصوص عامة)	response.text()
// JSON	response.json()
// Blob (صور، ملفات)	response.blob()
// ArrayBuffer (بيانات ثنائية)	response.arrayBuffer()
// FormData	response.formData()



// -----------------------------------------------------

// 🔁 إرسال بيانات مع fetch (POST, PUT, ...)
// ✅ إرسال JSON:
// js
// نسخ
// تحرير
fetch('save.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'Mohamed',
    age: 30
  })
})
  .then(res => res.json())
  .then(data => console.log(data))
  .catch(err => console.error(err));

//   ------------------------------------------------

// ✅ إرسال بيانات كـ Form:
js
نسخ
تحرير
const formData = new FormData();
formData.append('name', 'Mohamed');
formData.append('file', fileInput.files[0]);

fetch('upload.php', {
  method: 'POST',
  body: formData
})
  .then(res => res.text())
  .then(result => console.log(result));



//   🧠 استخدام async / await مع fetch (نظافة أكثر في الكود):
// js
// نسخ
// تحرير
async function getUserData() {
  try {
    const res = await fetch('user.php?id=5');
    if (!res.ok) throw new Error('HTTP Error ' + res.status);

    const data = await res.json();
    console.log(data);
  } catch (err) {
    console.error('Fetch error:', err);
  }
}



// ⚠️ التعامل مع الأخطاء:
// يجب دائمًا التحقق من حالة الاستجابة:
// js
// نسخ
// تحرير
fetch('api.php')
  .then(res => {
    if (!res.ok) throw new Error(`Error: ${res.status}`);
    return res.json();
  })
  .then(data => console.log(data))
  .catch(err => console.error(err));
</script>