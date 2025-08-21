


<!-- ارسال و استقبال من قاعدة البيانات فى ابسط صورة  -->
<script>
    // ارسال البيانات فى ابسط صورها
    function getMainGroup() {
        fetch(`payFromSuppliers/getMainGroup.php?supplierID=${supplierID}`)
            .then(response => response.text())
            .then(data => document.getElementById('mainGroup').innerHTML = data);
    }
</script>
<?php 
        // الاستقبال
        $supplierID = isset($_REQUEST['supplierID']) ? intval($_REQUEST['supplierID']) : 0;
        // الطريقه العاديه للاستعلام
        include("../hmb/conn.php");
        $sql = "SELECT DISTINCT mainGroup FROM itemscard";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            echo "<div class='alert alert-danger'>Error al cargar los grupos principales: " . mysqli_error($conn) . "</div>";
        } else {
            $mainGroup = "";
            $count = 0;
            
            while ($row = mysqli_fetch_array($result)) {
                $count++;
                $mainGroup = $row['mainGroup'];
                ?>
                <button class="btn btn-primary w-auto mb-2 text-start" 
                        onclick="getSubGroup('<?= $mainGroup ?>')" 
                        type="button" 
                        name="mainGroup">
                    <i class="fa fa-folder-open me-2"></i> <?= $mainGroup ?>
                </button>
                <?php
            }
            
            if ($count > 0) {
                echo "<script>getSubGroup('" . $mainGroup . "');</script>";
            } else {
                echo "<div class='alert alert-warning'>لا توجد مجموعات رئيسية متاحة</div>";
            }
        }
        $conn->close();
?>


<!-- ---------------------------------------------------------------------
 ---------------------------------------------------------
 -----------------------------------------
 ---------------------- -->

<!-- ارسال و استقبال من قاعدة البيانات -->
<SCript>
    
async function sendDataInvoice() {
  const loadingToast = showToast('جاري حفظ الفاتورة...', 'info', 0);

  try {
    const formData = new FormData();

    // جمع بيانات العناصر
    const rows = document.querySelectorAll('#invoiceItemsTable tr');
    if (rows.length === 0) {
      showToast('لا توجد عناصر في الفاتورة!', 'danger');
      return;
    }

    const items = [];
    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      if (cells.length >= 10) {
        items.push({
          itemID: cells[1].textContent.trim(),
          itemName: cells[2].textContent.trim(),
          count: parseFloat(cells[3].textContent.replace(/[^0-9.-]+/g, "")),
          unit: cells[4].textContent.trim(),
          price: parseFloat(cells[5].textContent.replace(/[^0-9.-]+/g, "")),
          priceWithVat: parseFloat(cells[6].textContent.replace(/[^0-9.-]+/g, "")),
          discount: parseFloat(cells[7].textContent.replace(/[^0-9.-]+/g, "")),
          total: parseFloat(cells[8].textContent.replace(/[^0-9.-]+/g, ""))
        });
      }
    });

    // إضافة الفاتورة الأصلية إذا وجدت
    const originalInvoice = document.getElementById('originalInvoiceUpload').files[0];
    if (originalInvoice) {
        // formData.append(key, value);
      formData.append('originalInvoice', originalInvoice);
    }

    // إعداد بيانات الفاتورة
    const invoiceData = {
      fromID: supplierID,
      toID: document.getElementById('userId').value,
      fromType: "supplier",
      toType: "branch",
      action: "purchase",
      state: 1,
      date: new Date().toISOString(),
      paymentMethod: document.getElementById('typePay').value,
      total: parseFloat(document.getElementById('subtotal').textContent.replace(/[^0-9.-]+/g, "")),
      discount: parseFloat(document.getElementById('discount').textContent.replace(/[^0-9.-]+/g, "")),
      totalDue: parseFloat(document.getElementById('finalTotal').textContent.replace(/[^0-9.-]+/g, "")),
      vat: parseFloat(document.getElementById('taxAmount').textContent.replace(/[^0-9.-]+/g, "")),
      generalTotal: parseFloat(document.getElementById('finalTotal').textContent.replace(/[^0-9.-]+/g, "")),
      notes: document.getElementById('invoiceNotes').value || 'فاتورة شراء من المورد',
      paidAmount: parseFloat(document.getElementById('paidInput').value) || 0,
      remainingAmount: parseFloat(document.getElementById('changeInput').value) || 0,
      dateRemainingAmount: document.getElementById('dateWantDebt').value || null,
      items: items
    };


    // NOW I HAVE TOW ARRAY ITEM & INVOICE ---------------------------------------------------------------------
    formData.append('invoiceData', JSON.stringify(invoiceData));


    // الارسال ----------------------------------------------------------------<

    const response = await fetch('payFromSuppliers/addItemToInvoice.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.success) {
      printInvoice(data.invoiceID); // طباعة الفاتورة
      showToast('تم حفظ الفاتورة بنجاح', 'success');
      setTimeout(() => {
        window.location.href = 'index.php';
      }, 500);
    } else {
      throw new Error(data.message || 'فشل حفظ الفاتورة');
    }
  } catch (error) {
    console.error('Error:', error);
    showToast(`حدث خطأ: ${error.message}`, 'danger');
  } finally {
    if (loadingToast && loadingToast.hide) {
      loadingToast.hide();
    }
  }
}
</SCript>

<!-- الاستقبال -->

<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

include('../hmb/conn.php');
// تحقق من اتصال قاعدة البيانات
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'فشل الاتصال بقاعدة البيانات']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // معالجة البيانات المرسلة سواء كانت JSON أو FormData


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // معالجة البيانات المرسلة سواء كانت JSON أو FormData
    $invoiceData = [];
    $originalInvoice = null;

    if (!empty($_FILES['originalInvoice'])) {
        $originalInvoice = $_FILES['originalInvoice'];
    }

    if (!empty($_POST['invoiceData'])) {
        $invoiceData = json_decode($_POST['invoiceData'], true);
    } else {
        $jsonData = file_get_contents('php://input');
        $invoiceData = json_decode($jsonData, true);
    }

    if (json_last_error() !== JSON_ERROR_NONE && empty($invoiceData)) {
        echo json_encode(['success' => false, 'message' => 'خطأ في تنسيق البيانات المرسلة']);
        exit;
    }




    echo json_encode([
            'success' => true,
            'message' => 'تم حفظ الفاتورة بنجاح',
            'invoiceID' => $invoiceID,
            'invoiceNumber' => $invoiceID,
            'originalInvoicePath' => $invoiceValues['originalInvoicePath']
        ]);
} else {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صحيحة']);
}



        // إضافة الفاتورة
        $stmtInvoice = $conn->prepare("INSERT INTO invoices (
            fromID, toID, fromType, toType, action, state,
            date, paymentMethod, total, discount, totalDue, 
            vat, generalTotal, notes, paidAmount, paidDate,
            remainingAmount, dateRemainingAmount, originalInvoicePath
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? , ? )");


        $stmtInvoice->bind_param(
            "iisssissdddddsdsdss",
            $invoiceValues['fromID'], //i
            $invoiceValues['toID'], //i
            $invoiceValues['fromType'], //s
            $invoiceValues['toType'], //s
            $invoiceValues['action'], //s
            $invoiceValues['state'], //i
            $invoiceValues['date'],     //s
            $invoiceValues['paymentMethod'], //s
            $invoiceValues['total'], //d
            $invoiceValues['discount'], //d
            $invoiceValues['totalDue'], //d
            $invoiceValues['vat'], //d
            $invoiceValues['generalTotal'], //d
            $invoiceValues['notes'], //s
            $invoiceValues['paidAmount'], //d
            $invoiceValues['paidDate'], //s
            $invoiceValues['remainingAmount'], //  d
            $invoiceValues['dateRemainingAmount'],//s
            $invoiceValues['originalInvoicePath'] //s
        );
 if (!$stmtInvoice->execute()) {
            throw new Exception('فشل إضافة الفاتورة: ' . $stmtInvoice->error);
        }
        $invoiceID = $conn->insert_id;
        return $invoiceID;
    } catch (Exception $e) {
}