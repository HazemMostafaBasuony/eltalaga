<?php include('headAndFooter/head.php'); ?>
<?php
if(!isset($userName) || $userName == "" || $userID == 0){
    header("Location: signIn.php");
    exit();   
}

include('hmb/conn_pdo.php');

// عدد النتائج في الصفحة
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// العدد الكلي
$totalStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM invoices i
    JOIN customers s ON i.toID = s.customerID
    WHERE i.toType = 'customer' AND (i.state = 1 OR i.state = 2)
");
$totalStmt->execute();
$totalInvoices = $totalStmt->fetchColumn();
$totalPages = ceil($totalInvoices / $limit);

// جلب الفواتير
$stmt = $pdo->prepare("
    SELECT i.invoiceID, i.generalTotal, i.paidDate, s.customerName, s.customerID, i.notes
    FROM invoices i
    JOIN customers s ON i.toID = s.customerID
    WHERE i.toType = 'customer' AND (i.state = 1 OR i.state = 2)
    ORDER BY i.invoiceID DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="card mt-3">
        <div class="header-card p-3">
            <h4>قائمة الفواتير</h4>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered text-center">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>اسم العميل</th>
                        <th>المبلغ المدفوع</th>
                        <th>تاريخ الدفع</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($invoices as $inv): ?>
                    <tr data-id="<?= $inv['invoiceID'] ?>">
                        <td><?= $inv['invoiceID'] ?></td>
                        <td><?= htmlspecialchars($inv['customerName']) ?></td>
                        <td><?= number_format($inv['generalTotal'], 2) ?></td>
                        <td><?= $inv['paidDate'] ? date('Y-m-d', strtotime($inv['paidDate'])) : '-' ?></td>
                        <td><?= htmlspecialchars($inv['notes']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>">السابق</a></li>
                    <?php endif; ?>

                    <?php for($i=1; $i<=$totalPages; $i++): ?>
                        <li class="page-item <?= ($i==$page)?'active':'' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>">التالي</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

        </div>
    </div>
</div>

<!-- تفعيل النقر على الصف -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll("tr[data-id]").forEach(row => {
        row.style.cursor = "pointer";
        row.addEventListener("click", () => {
            let invoiceID = row.getAttribute("data-id");
            window.location.href = "invoiceDetails.php?invoiceID=" + invoiceID;
        });
    });
});
</script>

<?php include('headAndFooter/footer.php'); ?>
