<?php include('headAndFooter/headMop.php'); ?>

<style>
.text-decoration-line-through {
    text-decoration: line-through;
    color: red; /* لون باهت للعناصر المختارة */
}


</style>
<?php
if (isset($_GET['salesmaneID'])) {
    $salesmaneID = $_GET['salesmaneID'];
} else {
    header("Location: salesmane_HomePage.php");
    die("يجب تحديد مندوب");
}
echo "$salesmaneID";
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
        xhr.open("GET", "salesmane_inventoryMaterial/getInventoryMaterial.php?userID=" + userID, true);
        xhr.send();
    }


    document.getElementById("inventoryTableBody").addEventListener('click', function(event) {
    const row = event.target.closest('tr');
    const checkbox = row.querySelector('input[type="checkbox"]');
    
    // تبديل حالة الـ checkbox عند النقر على أي مكان في الصف
    checkbox.checked = !checkbox.checked;
    
    // تطبيق التأثيرات
    row.classList.toggle('text-decoration-line-through' , checkbox.checked);
    row.style.opacity = checkbox.checked ? '0.7' : '1';
});


</script>
<?php include('headAndFooter/footer.php'); ?>