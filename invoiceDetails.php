

<?php include('headAndFooter/head.php'); ?>
<?php
include('hmb/conn_pdo.php');

$invoiceID = $_GET['invoiceID'] ?? 0;

// بيانات الفاتورة + العميل
$stmt = $pdo->prepare("
    SELECT i.*, c.customerName, c.phone, c.street, c.area, c.city, c.country, c.bulding, c.postCode, c.email
    FROM invoices i
    JOIN customers c ON i.toID = c.customerID
    WHERE i.invoiceID = :invoiceID
");
$stmt->execute(['invoiceID' => $invoiceID]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$invoice){
    echo "<div class='alert alert-danger m-3'>الفاتورة غير موجودة</div>";
    exit;
}

// الأصناف (مع استبعاد المحذوفة)
$itemStmt = $pdo->prepare("
    SELECT actionID, itemID, `date`, `action`, `count`, `price`, `discount`, `unit`, `itemName`
    FROM itemaction
    WHERE invoiceID = :invoiceID
    AND action <> 'delete'
");
$itemStmt->execute(['invoiceID' => $invoiceID]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4" id="invoiceArea">

  <!-- بيانات الفاتورة والعميل -->
  <div class="card mb-3 shadow-sm">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">فاتورة معدلة رقم #<?= $invoice['invoiceID'] ?></h5>
    </div>
    <div class="card-body">
      <p><strong>العميل:</strong> <?= htmlspecialchars($invoice['customerName']) ?></p>
      <p><strong>الهاتف:</strong> <?= htmlspecialchars($invoice['phone']) ?></p>
      <p><strong>العنوان:</strong> 
        <?= htmlspecialchars($invoice['street']." ".$invoice['area']." ".$invoice['city']." ".$invoice['country']) ?>
      </p>
      <p><strong>التاريخ:</strong> <?= htmlspecialchars($invoice['date']) ?></p>
    </div>
  </div>

  <!-- جدول الأصناف -->
  <div class="card shadow-sm">
    <div class="card-header bg-secondary text-white">
      <h6 class="mb-0">الأصناف</h6>
    </div>
    <div class="card-body p-0">
      <table class="table table-striped mb-0 text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>الصنف</th>
            <th>الكمية</th>
            <th>الوحدة</th>
            <th>السعر</th>
            <th>الخصم</th>
            <th>الإجمالي</th>
            <th>الإجراء</th>
            <th class="d-print-none">خيارات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($items as $i => $row): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($row['itemName']) ?></td>
            <td><?= $row['count'] ?></td>
            <td><?= $row['unit'] ?></td>
            <td><?= $row['price'] ?></td>
            <td><?= $row['discount'] ?></td>
            <td><?= ($row['count'] * $row['price']) - $row['discount'] ?></td>
            <td>
              <?php 
                if($row['action']=='return') echo "<span class='badge bg-danger'>مرتجع</span>";
                elseif($row['action']=='edit') echo "<span class='badge bg-warning text-dark'>تعديل</span>";
                else echo "<span class='badge bg-success'>بيع</span>";
              ?>
            </td>
            <td class="d-print-none">
              <button class="btn btn-sm btn-outline-warning" onclick="editPrice(<?= $row['actionID'] ?>, <?= $row['price'] ?>)">تعديل السعر</button>
              <button class="btn btn-sm btn-outline-danger" onclick="returnItem(<?= $row['actionID'] ?>, <?= $row['count'] ?>)">إرجاع</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="card-footer text-center d-print-none" >
      <button class="btn btn-danger" id="returnWholeInvoice">إرجاع كامل الفاتورة</button>
      <button class="btn btn-primary" ONCLICK="printArea('invoiceArea')">طباعة الفاتورة</button>
      <a href="invoiceStore.php">
        <button class="btn btn-secondary" > عودة</button>
      </a>
    </div>
  </div>
</div>

<!-- Modal لتعديل السعر -->
<div class="modal fade" id="editPriceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">تعديل السعر</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editActionID">
        <div class="mb-3">
          <label class="form-label">السعر الجديد</label>
          <input type="number" class="form-control" id="newPrice" step="0.01">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button class="btn btn-primary" onclick="savePrice()">حفظ</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// تعديل السعر
function editPrice(actionID, currentPrice){
  document.getElementById("editActionID").value = actionID;
  document.getElementById("newPrice").value = currentPrice;
  new bootstrap.Modal(document.getElementById("editPriceModal")).show();
}

function savePrice(){
  const actionID = document.getElementById("editActionID").value;
  const price = document.getElementById("newPrice").value;

  superagent.post("editItem.php")
    .type("form")
    .send({actionID, price})
    .end((err,res)=>{
      if(res.body.success){
        location.reload();
      }else{
        alert(res.body.message);
      }
    });
}

// إرجاع صنف
function returnItem(actionID, count){
  let qty = prompt("أدخل الكمية المرتجعة:", count);
  if(qty===null) return;
  superagent.post("returnItem.php")
    .type("form")
    .send({actionID, count: qty})
    .end((err,res)=>{
      if(res.body.success){
        location.reload();
      }else{
        alert(res.body.message);
      }
    });
}

// إرجاع الفاتورة بالكامل
document.getElementById("returnWholeInvoice").addEventListener("click", ()=>{
  if(confirm("هل تريد إرجاع كامل الفاتورة؟")){
    superagent.post("returnInvoice.php")
      .type("form")
      .send({invoiceID: <?= $invoiceID ?>})
      .end((err,res)=>{
        if(res.body.success){
          location.reload();
        }else{
          alert(res.body.message);
        }
      });
  }
});


function printArea(areaID) {
    var printContents = document.getElementById(areaID).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    showToast('تم طباعة التقرير بنجاح', 'success');
    // تاخير .5 ثواني قبل التحويل
    setTimeout(function() {
        location.href = "index.php";
    }, 500);
}

</script>

<?php include('headAndFooter/footer.php'); ?>