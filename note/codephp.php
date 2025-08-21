

<div id="invoiceInfoModal" class="modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">تفاصيل إضافية للفاتورة</h5>
        <button type="button" class="btn-close" onclick="hideInvoiceInfoModal()"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="paidInput" class="form-label">المبلغ المدفوع</label>
          <input id="paidInput" type="number" class="form-control" placeholder="أدخل المبلغ المدفوع" min="0" step=".01">
        </div>
        <div class="mb-3">
          <label for="changeInput" class="form-label">المبلغ المتبقي</label>
          <input id="changeInput" type="number" class="form-control" placeholder="سيتم حسابه تلقائياً" readonly>
        </div>
        <div class="mb-3">
          <label for="discountInput" class="form-label">خصم إضافي</label>
          <input id="discountInput" type="number" class="form-control" placeholder="أدخل الخصم" min="0" step=".01">
        </div>
        <div class="mb-3">
          <label for="typePay" class="form-label">طريقة الدفع</label>
          <select id="typePay" class="form-select">
            <option value="transfer">تحويل بنكي</option>
            <option value="card">بطاقة ائتمان</option>
            <option value="cash">نقدي</option>
            <option value="wait">آجل</option>
          </select>
        </div>
        <div class="mb-3" id="dateWantDebtDiv" style="display:none;">
          <label for="dateWantDebt" class="form-label">موعد الدفع المتفق عليه</label>
          <input id="dateWantDebt" type="date" class="form-control">
        </div>
        <div class="mb-3">
          <label for="invoiceNotes" class="form-label">ملاحظات</label>
          <textarea id="invoiceNotes" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" onclick="sendDataInvoice()">
          <i class="fa fa-check-circle me-1"></i> حفظ الفاتورة
        </button>
        <button class="btn btn-secondary" onclick="hideInvoiceInfoModal()">إغلاق</button>
      </div>
    </div>
  </div>
</div>
<?php
// عرض/إخفاء مودال الفاتورة
function showInvoiceModal() {
  document.getElementById('invoiceModal').classList.add('show');
  document.getElementById('invoiceModal').style.display = 'block';
}

function hideInvoiceModal() {
  document.getElementById('invoiceModal').classList.remove('show');
  document.getElementById('invoiceModal').style.display = 'none';
}

// ***********************************************************************




// دالة التقريب لأقرب ربع (0.25)
function roundToQuarter($value) {
    return round($value * 4) / 4;
}


$newFinalPrice = roundToQuarter($newFinalPrice);
?>
// injs
<script>

  
    function roundToQuarter(value) {
    // return round($value * 4) / 4;
    qrtr = Math.round(value * 4) / 4;
      return qrtr.toFixed(2);
    }

    // fff=roundToQuarter(5.564654658);
    // alert(fff);

    </script>

    <?php

// $_REQUEST['customerName']
$id_invoice_items= $_REQUEST['id_invoice_items'];
$id_invoice_items = intval($id_invoice_items); // تأكد من تحويل المعرف إلى عدد صحيح

include '../hmb/conn.php';
// حذف المنتج من جدول invoice_items
$sql = "DELETE FROM `invoice_items` WHERE `id` = $id_invoice_items";
if ($conn->query($sql) === TRUE) {
  // طباعة فى الكونسول
  print_r( "تم حذف المنتج بنجاح!" . $id_invoice_items);
} else {
    // إذا حدث خطأ أثناء الحذف، يمكنك إظهار رسالة خطأ
    echo "خطأ في حذف المنتج: " . $conn->error;
}

// إغلاق الاتصال
$conn->close();








$sqlUpdate = "UPDATE `invoices` SET
        `totalPrice` = $newTotalPrice,
        `totalVat` = $newTotalVat,
        `finalPrice` = $newFinalPrice
        WHERE `id` = $invoiceId";
    $conn->query($sqlUpdate);



// ابحث عن آخر فاتورة مفتوحة
$sql = "SELECT * FROM `invoices` WHERE `state` = 1 AND 
        `shiftId` = $idShift AND 
        `shiftSerial` = $serialShift AND
        `userId` = $idUser AND
        `customerId` = $customerId
          ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {}



// **************************************************************************************

// استعلام مُحسَّن لجلب البيانات مرة واحدة مع التجميع
$sql="SELECT ii.itemName, SUM(ii.count) as total_count 
      FROM invoices i 
      JOIN invoice_items ii ON i.id = ii.invoiceId 
      WHERE i.shiftId = '$shiftId' AND i.state = '2' 
      GROUP BY ii.itemName 
      ORDER BY total_count DESC";

$result = $conn->query($sql);
if ($result->num_rows > 0) {
  // عرض البيانات مباشرة
  while($row = $result->fetch_assoc()) {
    echo "<tr><td>".$row["itemName"]."</td>";
    echo "<td>".$row["total_count"]."</td></tr>";
  }
} else {
  echo "<tr><td colspan='2'>لا توجد بيانات لعرضها</td></tr>";
}
// ********************************************************************
?>
<script>
  function removeItemFromInvoice(id_invoice_items) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        getItemsInvoice("<?php echo $customerId; ?>", "<?php echo $invoiceType; ?>", "<?php echo $PriceDelivery; ?>");
        // document.getElementById("allInvoice").innerHTML = xhr.responseText;
        // alert(id_invoice_items);

      }
    }
    xhr.open("GET", "sales/removeItemFromInvoice.php?id_invoice_items=" + id_invoice_items, true);
    xhr.send();
  }
</script>

<p class="d-print-none">هذا النص يظهر في المتصفح فقط ولا يظهر عند الطباعة.</p>
<script>
// توليد QR Code
                if (invoiceData.branch && invoiceData.invoice) {
                    const qrText = [
                        `فاتورة ضريبية`,
                        `رقم: ${invoiceData.invoice.invoiceID || ''}`,
                        `التاريخ: ${invoiceData.invoice.date || ''}`,
                        `الإجمالي: ${grandTotal.toFixed(2)} ريال`,
                        `الضريبة: ${vatAmount.toFixed(2)} ريال`,
                        `الرقم الضريبي: ${invoiceData.branch.numberTax || ''}`
                    ].join('\n');

                    new QRCode(document.getElementById("qrCode"), {
                        text: qrText,
                        width: 150,
                        height: 150
                    });
                }

              </script>