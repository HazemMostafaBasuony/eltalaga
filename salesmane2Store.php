<?php include('headAndFooter/headMop.php');?>
<!-- 2) أنيميشن إخفاء الصف في الواجهة-->
<style>
    .fade-out {
  transition: all 0.8s ease;
  opacity: 0;
  transform: translateX(-35px);
}
</style>
<?php

if (isset($_POST['salesmaneID'])) {
    $salesmaneID = $_POST['salesmaneID'];
} else {
    header("Location: salesmane_HomePage.php");
    die("يجب تحديد مندوب");
}
?>

<!-- تقرير المواد الموجودة مع المورد -->
<div class="container" id="printArea">
    <h1 class="text-center"> <?php echo $salesmaneID; ?> تقرير المواد الموجودة مع المورد</h1>
    <p class="text-center">التاريخ : <?php echo date('Y-m-d'); ?> </p>
    <p class="text-center">الوقت : <?php echo date('H:i:s'); ?> </p>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>تم</th>
                                    <th>اسم المادة</th>
                                    <th>الكمية</th>
                                    <th>الوحدة</th>
                                    <th>السعر</th>
                                    <th>الإجمالي</th>
                                    <th>إجراءات </th>

                                </tr>
                            </thead>
                            <tbody id="inventoryTableBody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<div id="printButton" class="text-center d-flex justify-content-center">
    <button class="btn btn-primary w-100 m-2" onclick="printArea('printArea')">طباعة</button>
</div>
<script>
// مسح العناصر المنهية
function cleanZero(userID) {
    if (!confirm("هل أنت متأكد أنك تريد حذف العناصر المنتهية (الكمية = صفر)؟")) return;

    fetch(`salesmane2Store/cleanZero.php?userID=${userID}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.deleted > 0) {
                    data.ids.forEach(id => {
                        let row = document.getElementById("row" + id);
                        if (row) {
                            row.style.transition = "opacity 0.5s ease";
                            row.style.opacity = "0";
                            setTimeout(() => row.remove(), 500);
                        }
                    });
                    showToast(`تم حذف ${data.deleted} عنصر منتهي.`, 'success');
                } else {
                    showToast("لا توجد عناصر بكمية صفر لحذفها.", 'info');
                }
            } else {
                showToast("خطأ: " + data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast("حدث خطأ أثناء عملية الحذف", 'error');
        });
}



// 1️⃣ تحديث الصفحة كل 5 ثواني
    // 2️⃣ التحديث اللحظي للحالة
  function updateReturnStatus() {
  const userID = "<?php echo $salesmaneID; ?>";

  fetch(`salesmane2Store/getReturnStatus.php?userID=${userID}`)
    .then(res => res.json())
    .then(data => {
      if (!data || !Array.isArray(data.requests)) return;

      data.requests.forEach(req => {
        const row = document.getElementById('row' + req.itemID);
        if (!row) return;

        // أنيميشن ثم إزالة الصف
        row.classList.add('fade-out');
        setTimeout(() => {
          row.remove();
        }, 800);
      });
    })
    .catch(() => { /* تجاهل أو سجل لو عايز */ });
}

// أول نداء + كل 5 ثواني
updateReturnStatus();
setInterval(updateReturnStatus, 5000);




function cancelRequest(itemID){
    const salesmaneID = "<?php echo $salesmaneID; ?>";
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            showToast('تم إلغاء طلب الإرجاع', 'info');

            // استعادة الزرار الأصلي
            document.getElementById('requestReturnStatus' + itemID).innerHTML = '';
            document.getElementById('requestReturnBtn' + itemID).style.display = 'inline-block';
        }
    };

    xhr.open("GET", "salesmane2Store/cancelRequest.php?itemID=" + itemID + "&salesmaneID=" + salesmaneID , true);
    xhr.send();
}


// ارسال طلب ارجاع
function reqrequestReturn(itemID){
    const salesmaneID = "<?php echo $salesmaneID; ?>";
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            showToast('تم طلب الارجاع بنجاح', 'success');

            // اخفاء الزر
            document.getElementById('requestReturnBtn' + itemID).style.display = 'none';

            // عرض النص اللي جاي من السيرفر
            document.getElementById('requestReturnStatus' + itemID).innerHTML = xhr.responseText;

            // هنا ممكن إرسال حدث WebSocket لاحقًا إذا حبينا التحديث يكون لحظي
        }
    };

    xhr.open("GET", "salesmane2Store/requestReturn.php?itemID=" + itemID + "&salesmaneID=" + salesmaneID , true);
    xhr.send();
}



function printArea(areaID) {
    var printContents = document.getElementById(areaID).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    showToast('تم طباعة التقرير بنجاح', 'success');
    // تاخير .5 ثواني قبل التحويل
    setTimeout(function() {
        location.href = "salesmane_HomePage.php";
    }, 500);
}


    document.addEventListener('DOMContentLoaded', function() {
   

        getInventoryMaterial(<?php echo $salesmaneID ?>);
    });

    function getInventoryMaterial(userID) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById("inventoryTableBody").innerHTML = xhr.responseText;
                showToast('تم جلب البيانات بنجاح', 'success');
            }
        }
        xhr.open("GET", "salesmane2Store/getInventory.php?userID=" + userID, true);
        xhr.send();
    }

    document.getElementById("inventoryTableBody").addEventListener('click', function(event) {
        // لو الكليك كان على زرار أو داخل زرار → ما تعملش حاجة
        if (event.target.tagName.toLowerCase() === 'button' || event.target.closest('button')) {
            return;
        }

        const row = event.target.closest('tr');
        if (!row) return;

        const checkbox = row.querySelector('input[type="checkbox"]');
        if (!checkbox) return;

        // تبديل حالة الـ checkbox عند النقر على أي مكان في الصف
        checkbox.checked = !checkbox.checked;

        // تطبيق التأثيرات
        row.classList.toggle('text-decoration-line-through', checkbox.checked);
        row.style.opacity = checkbox.checked ? '0.7' : '1';
    });
   
</script>








<?php include('headAndFooter/footer.php'); ?>