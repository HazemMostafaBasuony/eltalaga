<?php include('headAndFooter/headMop.php'); ?>

<?php
include('hmb/conn.php');

// جلب بيانات المندوبين
$salesMane = [];
$sql = "SELECT * FROM `salesmane`";
$result = mysqli_query($conn, $sql);
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $salesMane[] = $row;
  }
}

// جلب الفروع
$branch = [];
$sql = "SELECT DISTINCT branch FROM `users`";
$result = mysqli_query($conn, $sql);
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    if ($row['branch'] == 'مدير النظام' || $row['branch'] == 'المخزن الرئيسي') {
      continue;
    }
    $branch[] = $row;
  }
}
$branch[] = array('branch' => 'الكل');

if (!isset($_SESSION['userName'])) {
  die("يجب تسجيل الدخول أولاً");
}
// جلب معرف المندوب
$userName = $_SESSION['userName'];
$salesmaneID = null;
$stmt = $conn->prepare("SELECT salesmaneID FROM `salesmane` WHERE salesmaneName = ?");
$stmt->bind_param("s", $userName);
$stmt->execute();
$stmt->bind_result($salesmaneID);
$stmt->fetch();
$stmt->close();



?>




<div id="allInvoice"></div>
<div class="container ">
  <h3>توزيع البضاعة </h3>

</div>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-12 text-center">
      <div class="card mb-5 shadow">
        <div class="card-body py-5">
          <input type="hidden" id="salesmaneID" value="<?php echo $salesmaneID ?>">
          <h1 class="display-4 mb-4" id="salesmaneName">
            <i class="fa fa-truck me-2"></i>
            <?php echo $userName ?>
          </h1>
          <button class="btn btn-primary w-100" id="btnSale2Customer" style="font-size: 20px;height: 60px;">
            <i class="fa fa-group me-2"></i>بيع
          </button>

          <div class="d-flex justify-content-center flex-wrap d-grid gap-3 m-5">
            <button class="btn btn-secondary w-100" id="btnInventoryMaterial" style="font-size: 20px;">
              <i class="fa fa-refresh me-2"></i> جرد البضاعة
            </button>

            <form action="salesmaneWantMaterial.php" method="POST" class="w-100">
              <input type="hidden" name="salesmaneID" value="<?php echo $salesmaneID; ?>">
              <button type="submit" class="btn btn-success w-100" id="btnInMaterial" style="font-size: 20px;">
                <i class="fa fa-clipboard me-2"></i> طلب بضاعه
              </button>
            </form>

            <form action="salesmane2Store.php" method="POST" class="w-100">
              <input type="hidden" name="salesmaneID" value="<?php echo $salesmaneID; ?>">
              <button class="btn btn-danger w-100" type="submit" style="font-size: 20px;">
                <i class="fa fa-undo me-2"></i> ارجاع بضاعه الى المخزن
              </button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- جدول المندوبين -->
  <div class="row justify-content-center">
    <div class="col-md-12">
      <div class="card shadow" id="salesmanSection" style="display:none;">
        <div class="card-header bg-light">
          <h4 class="mb-0">اختار مندوب</h4>
        </div>
        <div class="card-body">
          <div class="mb-4">
            <div class="input-group">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input type="text" class="form-control" placeholder="ابحث عن مندوب" onkeyup="searchSalesmen(this.value)">
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover table-striped">
              <thead class="table-light">
                <tr>
                  <!-- salesmanName	 -->
                  <th>اسم المندوب</th>
                  <!-- phone -->
                  <th>رقم الهاتف</th>
                  <!-- numperID -->
                  <th>رقم الهوية</th>
                  <!-- photo -->
                  <th>الصورة </th>
                  <!-- serialshift -->
                  <th>رقم الورديه</th>
                  <!-- stockInventory -->
                  <th>المخزون</th>
                </tr>
              </thead>
              <tbody id="salesmaneTableBody">
                <?php for ($i = 0; $i < count($salesMane); $i++): ?>
                  <tr id="<?php echo $salesMane[$i]['salesmaneID']; ?>" class="cursor-pointer">
                    <td><?php echo $salesMane[$i]['salesmaneName']; ?></td>
                    <td><?php echo $salesMane[$i]['phone']; ?></td>
                    <td><?php echo $salesMane[$i]['numperID']; ?></td>
                    <td><?php echo $salesMane[$i]['photo']; ?></td>
                    <td><?php echo $salesMane[$i]['serialshift']; ?></td>
                    <td><?php echo $salesMane[$i]['stockInventory']; ?></td>
                  </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- نهاية جدول المندوبين -->






  <!-- جدول العملاء -->
  <div class="row justify-content-center">
    <div class="col-md-12">
      <div class="card shadow" id="customerSection" style="display:none;">
        <div class="card-header bg-light">
          <h4 class="mb-0">اختار عميل</h4>
          <div class="row justify-content-center">

            <div class="d-flex align-items-center">
              <label for="area" class="form-label mb-0 me-2">المنطقة</label>
              <select class="form-select w-50" name="area" id="area">
                <option value=""> <?php echo $_SESSION['branch'] ?> </option>
                <?php for ($i = 0; $i < count($branch); $i++): ?>
                  <option value="<?php echo $branch[$i]['branch']; ?>"> <?php echo $branch[$i]['branch']; ?> </option>
                <?php endfor; ?>
              </select>
            </div>

          </div>
        </div>
        <div class="card-body">
          <div class="mb-4">
            <div class="input-group">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input type="text" class="form-control" placeholder="ابحث عن عميل" onkeyup="searchCustomers(this.value)">
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover table-striped">
              <thead class="table-light">
                <tr>
                  <th>اسم العميل</th>
                  <th>المنطقة</th>
                  <th>رقم الهاتف</th>
                  <th>الحساب المتبقى</th>
                </tr>
              </thead>
              <tbody id="customersTableBody">

              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- نهاية جدول العملاء -->



</div>
</div>
<script src="js/getLocation.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const userID = document.getElementById('salesmaneID').value;
    var selectedCustomerID = null;
    getLocation(function (result, error) {
      if (error) {
        console.error("فشل الحصول على الموقع:", error);
        return;
      }

      const { lat, lng } = result;

      // سجل بداية الوردية مباشرة بعد الحصول على الموقع
      registerShift(lat, lng);
    });

    // تسجيل بداية الوردية
    function registerShift(lat, lng) {
      fetch('salesmane_HomePage/registerShift.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `userID=${userID}&lat=${lat}&lng=${lng}`
      })
        .then(response => response.json())
        .then(data => {
          if (!data.success) {
            alert("حدث خطأ أثناء تسجيل الوردية: " + data.message);
          } else {
            console.log("Shift started successfully:", data);
          }
        })
        .catch(err => console.error("خطأ في إرسال البيانات للسيرفر:", err));
    }




    // الانتقال إلى صفحة جرد البضاعة
    document.getElementById('btnInventoryMaterial').addEventListener('click', function () {
      window.location.href = "salesmane_inventoryMaterial.php?salesmaneID=" + userID;
    });

    // فلترة العملاء حسب المنطقة
    function filterCustomers(area) {
      fetch('salesmane_HomePage/filterCusttomer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'area=' + encodeURIComponent(area),
      })
        .then(response => response.json())
        .then(data => {
          const customersTableBody = document.getElementById('customersTableBody');
          customersTableBody.innerHTML = '';
          data.forEach(customer => {
            const row = document.createElement('tr');
            row.id = customer.customerID;
            row.addEventListener('click', function () {
              customersTableBody.querySelectorAll('tr').forEach(r => r.classList.remove('selected'));
              this.classList.add('selected');
              selectedCustomerID = this.getAttribute('id');
              window.location.href = "sale2Customer.php?customerID=" + selectedCustomerID + "&salesmaneID=" + userID + "&remainingAmount=" + customer.remainingAmount;
            });
            row.innerHTML = `
                    <td>${customer.customerName}</td>
                    <td>${customer.city}</td>
                    <td>${customer.phone}</td>
                    <td>${customer.remainingAmount}</td>
                `;
            customersTableBody.appendChild(row);
          });
        });
    }

    filterCustomers('<?php echo $_SESSION['branch'] ?>');
    document.getElementById('area').addEventListener('change', function () {
      filterCustomers(this.value);
    });

    // جدول المندوبين
    const salesmanRows = document.querySelectorAll('#salesmaneTableBody tr');
    salesmanRows.forEach(row => {
      row.addEventListener('click', function () {
        salesmanRows.forEach(r => r.classList.remove('selected'));
        this.classList.add('selected');
        window.location.href = "salesmane.php?salesmaneID=" + userID + "&customerID=" + selectedCustomerID;
      });
    });

    // إظهار/إخفاء نموذج العملاء
    document.getElementById('btnSale2Customer').addEventListener('click', function () {
      const form = document.getElementById('customerSection');
      form.style.display = (form.style.display === 'none') ? 'block' : 'none';
      const form2 = document.getElementById('salesmanSection');
      form2.style.display = 'none';
    });

    // استدعاء تحديد الموقع مباشرة بعد تحميل الصفحة
    getLocation();
  });

</script>

<?php include('headAndFooter/footer.php'); ?>