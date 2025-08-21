<?php include('headAndFooter/head.php'); ?>
<?php
include('hmb/conn.php');
$sql = "SELECT * FROM `suppliers` ORDER BY `totalDebt` DESC";
$result = mysqli_query($conn, $sql);
$suppliers = [];
while ($row = mysqli_fetch_assoc($result)) {
  array_push($suppliers, $row);
}
$conn->close();

include('hmb/conn.php');
$sql = "SELECT * FROM `salesmane`";
$result = mysqli_query($conn, $sql);
$salesMane = [];
while ($row = mysqli_fetch_assoc($result)) {
  array_push($salesMane, $row);
}
$conn->close();


include("hmb/conn.php");
$customers = [];
$sql = "SELECT * FROM `customers` ";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
  array_push($customers, $row);
}
$conn->close();

?>
<!-- اشكال الازرار من هنا -->
<!-- https://www.w3schools.com/icons/fontawesome_icons_transportation.asp -->



<div id="allInvoice"></div>



<div class="container mt-5">

  <div id="notificationIcon" style="position: relative; display: inline-block; cursor: pointer;">
    <i class="fa fa-bell fa-2x m-0" style="color: #df9432c2;"></i>
    <span id="notificationCount" style="
        position: absolute;
        top: -5px;
        right: -5px;
        background: red;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
        display: none;
    ">0</span>

    <!-- Dropdown للطلبات -->
    <div id="notificationDropdown" style="
        display: none;
        position: absolute;
        left: 0;
        top: 35px;
        background: white;
        border: 1px solid #ccc;
        width: 300px;
        max-height: 400px;
        overflow-y: auto;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        z-index: 1000;
    " dir="rtl"><i onclick="closeRequest()" class="fa fa-times m-2"></i>
      <ul id="notificationList" style="list-style: none; margin: 0; padding: 0;">
        <!-- هنا هتتحط الرسائل بالـ JS -->
      </ul>
      <div style="text-align:center; padding:5px;">
        <a href="storeRequests.php">عرض كل الطلبات</a>
      </div>
    </div>
  </div>


  <div class="row justify-content-center" style="padding: 20px;">
    <div class="col-md-10 text-center">
      <div class="card mb-5 shadow">
        <div class="card-body py-5">
          <h1 class="display-4 mb-4">حركة الخامات</h1>

          <div class="d-flex justify-content-center flex-wrap d-grid gap-3">
            <button class="btn btn-primary btn-lg px-4 py-3 p-2  m-3" id="btnOutMaterial">
              <i class="fa fa-shopping-cart me-2"></i> بيع
            </button>

            <button class="btn btn-success btn-lg px-4 py-3 p-2 m-3" id="btnInMaterial">
              <i class="fa fa-truck me-2"></i> شراء
            </button>


            <button onclick="showTransferForm()" class="btn btn-primary btn-lg px-4 py-3 p-2  m-3"
              id="btnTransferMaterial">
              <i class="fa fa-sitemap me-2"></i>نقل من مخزن الى مندوب
            </button>


            <form action="invoiceStore.php" method="POST" class="w-100">
              
              <button type="submit" class="btn btn-danger btn-lg px-4 py-3 p-2  m-3" id="btnInvoice">
                <i class="fa fa-undo me-2"></i> ارجاع بضاعه
              </button>
            </form>


          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- جدول المندوبين -->
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card shadow" id="salesmanSection" style="display:none;">
        <div class="card-header bg-light">
          <h4 class="mb-0">اختار مندوب</h4>
        </div>
        <div class="card-body">
          <div class="mb-4">
            <div class="input-group">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input type="text" class="form-control" placeholder="ابحث عن مندوب" onkeyup="searchSalesman(this.value)">
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
              <!-- get data from sam page up    -->
              <tbody id="salesmanTableBody">
                <?php for ($i = 0; $i < count($salesMane); $i++): ?>
                  <tr onclick="transferMaterial(<?php echo $salesMane[$i]['salesmaneID']; ?>)"
                    id="<?php echo $salesMane[$i]['salesmaneID']; ?>" class="cursor-pointer">
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






  <!-- جدول الموردين -->
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card shadow" id="supplierSection" style="display:none;">
        <div class="card-header bg-light">
          <h4 class="mb-0">اختار مورد</h4>
        </div>
        <div class="card-body">
          <div class="mb-4">
            <div class="input-group">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input type="text" class="form-control" placeholder="ابحث عن مورد" onkeyup="searchSupplier(this.value)">
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover table-striped">
              <thead class="table-light">
                <tr>
                  <th>اسم المورد</th>
                  <th>العنوان</th>
                  <th>رقم الهاتف</th>
                  <th>الحساب المتبقى</th>
                  <th>عمليات</th>
                </tr>
              </thead>
              <tbody id="supplierTableBody">
                <?php for ($i = 0; $i < count($suppliers); $i++): ?>
                  <tr id="<?php echo $suppliers[$i]['supplierID']; ?>" class="cursor-pointer">
                    <td><?php echo $suppliers[$i]['supplierName']; ?></td>
                    <td><?php echo $suppliers[$i]['city']; ?></td>
                    <td><?php echo $suppliers[$i]['phone']; ?></td>
                    <td><?php echo $suppliers[$i]['wantDebt']; ?>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <div class=" m-2">
                          <button class="btn btn-primary btn-sm cursor-pointer hover:bg-blue-600"
                            onclick="showInvoiceSupplier(<?php echo $suppliers[$i]['supplierID']; ?>)">
                            <i class="fa fa-copy"></i>نسخ الفواتير</button>
                        </div>
                        <div class=" m-2">
                          <button class="btn btn-primary btn-sm cursor-pointer hover:bg-blue-600"
                            onclick="payFromSupplier(<?php echo $suppliers[$i]['supplierID']; ?>)">
                            <i class="fa fa-car"></i>شراء </button>
                        </div>
                      </div>

                    </td>
                  </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- نهاية جدول الموردين -->





  <!-- جدول العملاء -->
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card shadow" id="customerSection" style="display:none;">
        <div class="card-header bg-light">
          <h4 class="mb-0">اختار العميل</h4>
        </div>
        <div class="card-body">
          <div class="mb-4">
            <div class="input-group">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input type="text" class="form-control" placeholder="ابحث عن العميل" onkeyup="searchCustomer(this.value)">
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover table-striped">
              <thead class="table-light">
                <tr>
                  <!-- customerName	 -->
                  <th>اسم العميل</th>
                  <!-- phone -->
                  <th>رقم الهاتف</th>
                  <!-- area -->
                  <th> العنوان</th>
                  <!-- totalDebt -->
                  <th>الحساب المتبقى</th>
                  <!-- actions -->
                  <th>عمليات</th>
                </tr>
              </thead>
              <!-- get data from sam page up    -->
              <tbody id="customerTableBody">
                <?php for ($i = 0; $i < count($customers); $i++): ?>
                  <tr id="<?php echo $customers[$i]['customerID']; ?>" class="cursor-pointer">
                    <td><?php echo $customers[$i]['customerName']; ?></td>
                    <td><?php echo $customers[$i]['phone']; ?></td>
                    <td><?php echo $customers[$i]['area']; ?></td>
                    <td><?php echo $customers[$i]['remainingAmount']; ?></td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <div class=" m-2">
                          <button class="btn btn-primary btn-sm cursor-pointer hover:bg-blue-600"
                            onclick="showInvoiceCustomer(<?php echo $customers[$i]['customerID']; ?>)">
                            <i class="fa fa-copy"></i>نسخ الفواتير</button>
                        </div>
                        <div class=" m-2">
                          <button class="btn btn-primary btn-sm cursor-pointer hover:bg-blue-600"
                            onclick="fromStore2Customer(<?php echo $customers[$i]['customerID']; ?>)">
                            <i class="fa fa-car"></i>فاتورة جديدة </button>
                        </div>
                      </div>

                    </td>
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




  <!-- عرض الطلبية -->
  <!-- المودال -->
  <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          <h5 class="modal-title w-100">تفاصيل الطلبية</h5>

        </div>

        <div class="modal-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>الكود</th>
                <th>الوصف</th>
                <th> الكمية بالمخزن</th>
              </tr>
            </thead>
            <tbody id="invoiceItemsTable">
              <!-- هيتعبّى ديناميك -->
            </tbody>
          </table>
        </div>

        <div class="modal-footer d-print-none d-flex justify-content-between">
          <button type="button" class="btn btn-primary w-50" onclick="printSectionById('invoiceModal')">طباعة</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
        </div>

      </div>
    </div>
  </div>
  <!-- نهاية المودال عرض الطلبية -->

</div>


<script>
  // عرض الاشعارات
  notificationIcon.addEventListener('click', () => {
    // Toggle القائمة
    notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';

    if (notificationDropdown.style.display === 'block') {
      fetch('index/getNewRequests.php')
        .then(res => res.json())
        .then(data => {
          notificationList.innerHTML = ''; // مسح القديم
          if (data.requests && data.requests.length > 0) {
            data.requests.forEach(req => {

              let requestType, approveText, rejectText;
              let approveHandler, rejectHandler;

              if (req.request_type === 'return_request') {
                requestType = 'طلب استرجاع';
                approveText = 'تم استلام الخامة';
                rejectText = 'رفض الطلب';

                approveHandler = () => approveRequest(req.requestID);
                rejectHandler = () => rejectRequest(req.requestID);

              } else if (req.request_type === 'wantMaterial') {
                requestType = 'طلبية';
                approveText = 'عرض الطلبية';
                rejectText = 'ذكرني لاحقا';

                approveHandler = () => showOrderDetails(req.requestID, req.dart);
                rejectHandler = () => remindLater(req.requestID);
              }

              const li = document.createElement('li');
              li.style.borderBottom = '1px solid #eee';
              li.style.padding = '5px 10px';
              li.innerHTML = `
                            <div>
                                <strong>${requestType} : </strong> ${req.message}<br>
                                <small>من : ${req.fromName}</small><br>
                            </div>
                        `;

              // زرار القبول
              const approveBtn = document.createElement('button');
              approveBtn.className = 'btn btn-success btn-sm me-1';
              approveBtn.textContent = approveText;
              approveBtn.addEventListener('click', approveHandler);

              // زرار الرفض
              const rejectBtn = document.createElement('button');
              rejectBtn.className = 'btn btn-danger btn-sm';
              rejectBtn.textContent = rejectText;
              rejectBtn.addEventListener('click', rejectHandler);

              li.querySelector('div').appendChild(approveBtn);
              li.querySelector('div').appendChild(rejectBtn);

              notificationList.appendChild(li);
            });
          } else {
            notificationList.innerHTML = '<li style="padding:10px; text-align:center;">لا توجد طلبات جديدة</li>';
          }
        })
        .catch(err => console.error(err));
    }
  });

  // دوال الموافقة 
  function approveRequest(requestID) {
    fetch(`index/handleRequest.php?action=approve&requestID=${requestID}`)
      .then(res => res.text())
      .then(res => {
        showToast('تمت الموافقة على الطلب', 'success');
        // إزالة الطلب من القائمة مباشرة
        const li = document.querySelector(`#notificationList li button[onclick*="${requestID}"]`).parentNode.parentNode;
        // li.remove();
      });
  }
  // رفض طلب ارجاع البضاعة
  function rejectRequest(requestID) {
    fetch(`index/handleRequest.php?action=reject&requestID=${requestID}`)
      .then(res => res.text())
      .then(res => {
        showToast('تم رفض الطلب', 'success');
        const li = document.querySelector(`#notificationList li button[onclick*="${requestID}"]`).parentNode.parentNode;
        // li.remove();
      });
  }

  function closeRequest(requestID) {
    const li = document.querySelector(`#notificationList li button[onclick*="${requestID}"]`).parentNode.parentNode;
    li.remove();
  }

  // عرض الطلبية
  function showOrderDetails(requestID, IDs) {
    // 1- تحويل النص إلى مصفوفة
    // IDs ممكن يكون مثل: "12,15,18"
    const idArray = IDs.split(',').map(id => id.trim()).filter(id => id !== "");

    if (idArray.length === 0) {
      showToast('لا توجد عناصر مرتبطة بهذه الطلبيه', 'warning');
      return;
    }

    // 2- ارسال الطلبات للـ backend لجلب بيانات الصفوف
    fetch('index/getOrderItems.php', {
      method: 'POST',
      body: 'ids=' + encodeURIComponent(idArray.join(',')), // تحويل المصفوفة إلى نص مرة تانية
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      }
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // 3- عرض البيانات في مودال أو صفحة
          populateOrderModal(requestID, data.items);
        } else {
          showToast('حدث خطأ أثناء جلب عناصر الطلبيه', 'danger');
        }
      })
      .catch(err => console.error(err));
  }

  // عرض الطلبيه فى مديول
  function populateOrderModal(requestID, items) {
    const modalEl = document.getElementById('invoiceModal');
    const modalInstance = new bootstrap.Modal(modalEl);
    const tableBody = document.getElementById('invoiceItemsTable');
    tableBody.innerHTML = '';

    items.forEach(item => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
            <td>${item.dart}</td>
            <td>${item.message}</td>
            <td>${item.stock}</td>
        `;
      tableBody.appendChild(tr);
    });

    // إظهار المودال
    modalInstance.show();
  }


  // ذكرنى لاحقا
  function remindLater(requestID) {
    fetch('index/markRemindLater.php', {
      method: 'POST',
      body: 'requestID=' + encodeURIComponent(requestID),
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showToast(' سوف يتم الارسال بعد 10 دقايق   ', 'success');
        } else {
          showToast('حدث خطأ أثناء وضع التذكير', 'danger');
        }
      })
      .catch(err => console.error(err));
  }


  // تذكير بعد 10 دقايق
  // كل 10 دقائق نرجع الطلبات remind_later إلى pending
  setInterval(() => {
    fetch('index/resetRemindLater.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          console.log('تم تحديث الطلبات من remind_later إلى pending');
        }
      })
      .catch(err => console.error(err));
  }, 600000); //  ملي ثانية = 10 دقائق




  // الاشعارات 
  document.addEventListener('DOMContentLoaded', function () {
    const countSpan = document.getElementById('notificationCount');

    function updateNotifications() {
      fetch('index/getNewRequests.php')
        .then(res => res.json())
        .then(data => {
          if (data.newRequests > 0) {
            countSpan.style.display = 'inline-block';
            countSpan.textContent = data.newRequests;
          }
          else {
            countSpan.style.display = 'none';

          }
        })
        .catch(err => console.error(err));
    }

    // أول تحديث عند تحميل الصفحة
    updateNotifications();

    // تحديث كل 5 ثواني
    setInterval(updateNotifications, 3000);
  });





  function transferMaterial(salesmaneID) {
    window.location.href = "fromStore2Salesmane.php?salesmaneID=" + salesmaneID;
  }

  function showInvoiceSupplier(supplierID) {
    window.location.href = "invoiceSupplier.php?supplierID=" + supplierID;
  }

  function payFromSupplier(supplierID) {
    window.location.href = "payFromSupplier.php?supplierID=" + supplierID;
  }

  function fromStore2Customer(customerID) {
    window.location.href = "fromStore2Customer.php?customerID=" + customerID;
  }

  function showInvoiceCustomer(customerID) {
    window.location.href = "invoiceCustomer.php?customerID=" + customerID;
  }


  // تفعيل تأثير النقر على صفوف الجدول
  document.addEventListener('DOMContentLoaded', function () {
    // تفعيل تأثير النقر على صفوف جدول المندوبين
    const salesmanRows = document.querySelectorAll('#salesmaneTableBody tr');
    salesmanRows.forEach(row => {
      row.addEventListener('click', function () {
        salesmanRows.forEach(r => r.classList.remove('selected'));
        this.classList.add('selected');
        var selectedSalesmaneId = this.getAttribute('id');
        window.location.href = "salesmane.php?salesmaneID=" + selectedSalesmaneId;
      });
    });



    // إظهار/إخفاء نموذج الموردين
    document.getElementById('btnInMaterial').addEventListener('click', function () {
      const form = document.getElementById('supplierSection');
      form.style.display = (form.style.display === 'none') ? 'block' : 'none';
      const form2 = document.getElementById('salesmanSection');
      form2.style.display = 'none';
      const form3 = document.getElementById('customerSection');
      form3.style.display = 'none';

    });
  });

  // اظهار اخفاء نموذج العملاء
  document.getElementById('btnOutMaterial').addEventListener('click', function () {
    const customerSection = document.getElementById('customerSection');
    customerSection.style.display = (customerSection.style.display === 'none') ? 'block' : 'none';
    const supplierSection = document.getElementById('supplierSection');
    supplierSection.style.display = 'none';
    const salesmanSection = document.getElementById('salesmanSection');
    salesmanSection.style.display = 'none';
  });


  function showTransferForm() {
    const form = document.getElementById('salesmanSection');
    form.style.display = (form.style.display === 'none') ? 'block' : 'none';
    const form2 = document.getElementById('customerSection');
    const form3 = document.getElementById('supplierSection');
    form2.style.display = 'none';
    form3.style.display = 'none';
  }

  function searchSupplier(value) {
    const supplierTableBody = document.getElementById('supplierTableBody');
    const rows = supplierTableBody.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      const supplierName = row.cells[0].textContent.toLowerCase();
      const supplierPhone = row.cells[2].textContent.toLowerCase();
      const supplierAddress = row.cells[1].textContent.toLowerCase();
      if (supplierName.indexOf(value) > -1 || supplierPhone.indexOf(value) > -1 || supplierAddress.indexOf(value) > -1) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    }
  }

  function searchCustomer(value) {
    const customerTableBody = document.getElementById('customerTableBody');
    const rows = customerTableBody.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      const customerName = row.cells[0].textContent.toLowerCase();
      const customerPhone = row.cells[1].textContent.toLowerCase();
      const customerAddress = row.cells[2].textContent.toLowerCase();
      if (customerName.indexOf(value) > -1 || customerPhone.indexOf(value) > -1 || customerAddress.indexOf(value) > -1) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    }
  }


  function searchSalesman(value) {
    const salesmanTableBody = document.getElementById('salesmanTableBody');
    const rows = salesmanTableBody.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      const salesmanName = row.cells[0].textContent.toLowerCase();
      const salesmanPhone = row.cells[2].textContent.toLowerCase();
      const salesmanAddress = row.cells[1].textContent.toLowerCase();
      if (salesmanName.indexOf(value) > -1 || salesmanPhone.indexOf(value) > -1 || salesmanAddress.indexOf(value) > -1) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    }
  }


  function printSectionById(sectionId) {

    // الحصول على العنصر المطلوب
    const section = document.getElementById(sectionId);
    if (!section) {
      alert("العنصر غير موجود!");
      return;
    }

    // فتح نافذة طباعة جديدة
    const printWindow = window.open('', '', 'width=800,height=600');

    // نسخ الـ CSS الخاص بالصفحة
    const styles = Array.from(document.styleSheets)
      .map(sheet => {
        try {
          return Array.from(sheet.cssRules)
            .map(rule => rule.cssText)
            .join('\n');
        } catch (e) {
          return ''; // تجاهل ملفات CSS من دومين خارجي
        }
      })
      .join('\n');

    // كتابة المحتوى في النافذة الجديدة مع الـ CSS
    printWindow.document.write(`
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>طباعة</title>
            <style>${styles}</style>
        </head>
        <body>
            ${section.outerHTML}
            <script>
                window.onload = function() {
                    window.print();
                    window.close();
                }
            <\/script>
        </body>
        </html>
    `);

    printWindow.document.close();
  }


</script>

<?php include('headAndFooter/footer.php'); ?>