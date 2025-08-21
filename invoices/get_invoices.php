<?php
include('../hmb/conn.php');

// جلب معاملات الطلب
$state = isset($_GET['state']) ? intval($_GET['state']) : 1;
$shiftId = isset($_GET['shiftId']) ? intval($_GET['shiftId']) : 1;

// استعلام الفواتير حسب الحالة
$sql = "SELECT * FROM `invoices` 
        WHERE `state` = $state 
        AND `shiftId` = $shiftId";

$result = $conn->query($sql);
?>

<table class="w3-table-all w3-hoverable w3-card-4" style="color:black;>
    <thead>
        <tr class="w3-indigo" >
            <th>رقم الفاتورة</th>
            <th>العميل</th>
            <th>التاريخ</th>
            <th>المبلغ</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($invoice = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $invoice['invoiceNumber']; ?></td>
                    <td><?php echo $invoice['customerName']; ?></td>
                    <td><?php echo $invoice['date']; ?></td>
                    <td><?php echo number_format($invoice['totalPrice'], 2); ?> ر.س</td>
                    <td>
                        <?php if($invoice['state'] == 1): ?>
                            <span class="w3-tag w3-orange w3-round">مفتوحة</span>
                        <?php elseif($invoice['state'] == 2): ?>
                            <span class="w3-tag w3-green w3-round">مكتملة</span>
                        <?php else: ?>
                            <span class="w3-tag w3-red w3-round">مرتجع</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="w3-button w3-blue w3-small" onclick="printInvoice(<?php echo $invoice['id']; ?>)">
                            <i class="fa fa-print"></i> طباعة
                        </button>
                        
                        <?php if($invoice['state'] != 3): ?>
                            <button class="w3-button w3-red w3-small" onclick="convertToReturn(<?php echo $invoice['id']; ?>)">
                                <i class="fa fa-undo"></i> مرتجع
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="w3-center w3-padding-24">
                    <i class="fa fa-info-circle w3-text-gray" style="font-size:48px"></i>
                    <p class="w3-large">لا توجد فواتير</p>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
?>