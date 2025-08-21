<?php
include('headAndFooter/head.php');
?>
<?php
if (isset($_GET['supplierID'])) {
    $supplierID = $_GET['supplierID'];
    include('hmb/conn.php');

    $sqlSupplier = "SELECT * FROM `suppliers` WHERE `supplierID` = $supplierID";
    $resultSupplier = mysqli_query($conn, $sqlSupplier);
    $rowSupplier = mysqli_fetch_array($resultSupplier);
    $supplierName = $rowSupplier["supplierName"];


    $sql = "SELECT * FROM `invoices` WHERE `fromID` = $supplierID";
    $result = mysqli_query($conn, $sql);
    $invoices = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($invoices, $row);
    }
    $conn->close();
}
?>

<div class="container mt-5">
    <div class="header ">
        <h5 class="text-center">تفاصيل التعامل مع</h5>
        <h5 class="text-center"><?php echo $supplierName; ?></h5>

    </div>
    <div class="row justify-content-center m-5" style="padding: 20px;">
        <div class="col-md-4">
            <button class="btn btn-primary" onclick="showAllInvoices()">
                <i class="fa fa-plus"></i>كل الفواتير
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary" onclick="showTodayInvoices()">
                <i class="fa fa-plus"></i>فواتير اليوم
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary" onclick="showOverdueInvoices()">
                <i class="fa fa-plus"></i>فواتير اجل
            </button>
        </div>
    </div>


    <!-- عرض جدول بالفواتير مع امكانية البحث -->
    <div class="container justify-content-center mt-5">
        <div class="row">
            <!-- البحث -->
            <div class="row">
                <div class="col-md-6">
                    <input id="searchID" type="text" class="form-control" placeholder="ابحث عن رقم الفاتورة">
                </div>
                <div class="col-md-6">
                    <input id="searchDate" type="text" class="form-control" placeholder="ابحث عن تاريخ الفاتورة">
                </div>
            </div>
            <div class="col-md-12">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>التاريخ</th>
                            <th>الإجمالي</th>
                            <th>الإجمالي المدفوع</th>
                            <th>الإجمالي المتبقي</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo $invoice['invoiceID']; ?></td>
                                <!-- ضبط التاريخ -->
                                <td><?php echo date('Y-m-d', strtotime($invoice['date'])); ?></td>
                                <td><?php echo $invoice['generalTotal']; ?></td>
                                <td><?php echo $invoice['paidAmount']; ?></td>
                                <td><?php echo $invoice['remainingAmount']; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-primary btn-sm cursor-pointer hover:bg-blue-600" onclick="showInvoice(<?php echo $invoice['invoiceID']; ?>)">عرض</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



</div>



<script>
    document.getElementById('searchID').addEventListener('input', function() {
        var search = this.value.toLowerCase();
        var rows = document.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            var name = row.cells[0].textContent.toLowerCase();
            if (name.indexOf(search) > -1) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    document.getElementById('searchDate').addEventListener('input', function() {
        var search = this.value.toLowerCase();
        var rows = document.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            var name = row.cells[1].textContent.toLowerCase();
            if (name.indexOf(search) > -1) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    function showInvoice(invoiceID) {
        window.location.href = 'invoiceIn.php?invoiceID=' + invoiceID;
    }

    function showAllInvoices() {
        rows = document.querySelectorAll('tbody tr');
        rows.forEach(function(rows) {
            rows.style.display = '';
        });
    }

    function showTodayInvoices() {
        rows = document.querySelectorAll('tbody tr');
        rows.forEach(function(rows) {
            if (rows.cells[1].textContent == new Date().toISOString().split('T')[0]) {
                rows.style.display = '';
            } else {
                rows.style.display = 'none';
            }
        });
    }

    function showOverdueInvoices() {
        rows = document.querySelectorAll('tbody tr');
        rows.forEach(function(rows) {
            if (rows.cells[4].textContent > 0) {
                rows.style.display = '';
            } else {
                rows.style.display = 'none';
            }
        });
    }
</script>



<?php include('headAndFooter/footer.php'); ?>