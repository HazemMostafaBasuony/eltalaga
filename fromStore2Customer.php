<!-- i come from index.php -->
<!-- to sale material to customer -->

<?php include('headAndFooter/head.php'); ?>
<?php
if (isset($_GET['customerID'])) {
    $customerID = $_GET['customerID'];
    include('hmb/conn.php');

    $sqlCustomer = "SELECT * FROM `customers` WHERE `customerID` = $customerID";
    $resultCustomer = mysqli_query($conn, $sqlCustomer);
    $rowCustomer = mysqli_fetch_array($resultCustomer);
    $customerName = $rowCustomer["customerName"];
    $conn->close();
}
?>

<input type="hidden" id="customerID" value="<?php echo $customerID; ?>">
<input type="hidden" id="userID" value="<?php echo $userID; ?>">
<!-- تخطيط متجاوب للمجموعات والبحث -->
<div class="row g-2 mb-3">
    <!-- المجموعات الرئيسية -->
    <div class="col-lg-6 col-md-12">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0"><i class="fa fa-folder me-1"></i>المجموعات الرئيسية</h6>
            </div>
            <div class="card-body py-2">
                <div id="mainGroup" class="d-flex flex-wrap gap-1">

                    <!-- يتم تعبئتها بالجافاسكريبت -->
                </div>
            </div>
        </div>
    </div>

    <!-- المجموعات الفرعية -->
    <div class="col-lg-6 col-md-12">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-success text-white py-2">
                <h6 class="mb-0"><i class="fa fa-folder-open me-1"></i>المجموعات الفرعية</h6>
            </div>
            <div class="card-body py-2">
                <button onclick="getItems('all')" class="btn btn-outline-primary btn-sm mb-2 w-100">
                    <i class="fa fa-list-ul me-1"></i> عرض جميع الأصناف
                </button>
                <div id="supGroup" class="d-flex flex-wrap gap-1">
                    <!-- يتم تعبئتها بالجافاسكريبت -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- شريط البحث المحسن -->
<div class="card mb-3 shadow-sm">
    <div class="card-header bg-info text-white py-2">
        <h6 class="mb-0"><i class="fa fa-search me-2"></i>البحث والمسح</h6>
    </div>

    <div class="card-body py-2">
        <!-- البحث النصي -->
        <div class="row g-2 mb-2">
            <div class="col-md-8">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                    <input type="text" id="searchItem" class="form-control" placeholder="ابحث عن الصنف بالاسم أو الرقم..."
                        autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()" title="مسح البحث">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary btn-sm w-100" id="toggleScannerBtn">
                    <i class="fa fa-camera me-1"></i>مسح الباركود
                </button>
            </div>
        </div>

        <!-- نتائج البحث -->
        <div class="d-flex justify-content-between align-items-center">
            <small id="searchResults" class="text-info fw-bold"></small>
        </div>

        <!-- منطقة الكاميرا -->
        <div id="statusMessage" class="message"></div>
        <div id="preview-container">
            <video id="preview"></video>
            <div class="center-text">
                <div class="row green">
                    <input id="qrtext" type="text" name="" readonly placeholder="الباركود الممسوح سيظهر هنا">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- قسم الأصناف المحسن -->
<div class="card shadow-lg border-0 ">
    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fa fa-shopping-bag me-2"></i>الأصناف</h4>
            <span class="badge bg-light text-dark" id="itemsCount">0 صنف</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="items" class="p-2" style="max-height:500px; overflow-y: auto;">
            <div class="text-center text-muted py-5">
                <i class="fa fa-box-open fa-3x mb-3"></i>
                <p>اختر مجموعة لعرض الأصناف</p>
            </div>
        </div>
    </div>
</div>
</div>

<!-- مودال إضافة العنصر -->
<div id="sendItem" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة عنصر جديد</h5> <input type="hidden" id="itemID">
                <button type="button" class="btn-close" onclick="hideSendItemModal()"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="countItem" class="form-label">العدد:</label>
                    <input id="countItem" type="number" class="form-control" placeholder="أدخل العدد" min="1" step="1">
                </div>

                <div class="mb-3">
                    <label class="w-100 " id="priceLabel1"></label>
                    <label class="w-100" id="priceLabel2"></label>
                    <label for="priceItem" class="form-label">السعر:</label>
                    <input id="priceItem" type="number" class="form-control" placeholder="أدخل السعر" min="0" step="0.01">
                </div>
                <div class="mb-3">
                    <label for="priceXcount" class="form-label">الاجمالى:</label>
                    <input id="priceXcount" type="number" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label for="price_vat" class="form-label">السعر بعد الضريبة:</label>
                    <input id="price_vat" type="number" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label for="price_vatXcount" class="form-label">الاجمالى بعد الضريبة:</label>
                    <input id="price_vatXcount" type="number" class="form-control" readonly>
                </div>

                <div class="mb-3">
                    <label for="unitSelect" class="form-label">الوحدة:</label>
                    <select id="unitSelect" class="form-select">
                        <option value="" disabled selected>اختر الوحدة</option>

                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="discountItem" class="form-label">الخصم:</label>
                    <input id="discountItem" type="number" class="form-control" placeholder="أدخل الخصم" min="0" step="0.25" value="0">
 
                </div>
                <div class="mb-3">
                    <label for="totalDisplay" class="form-label">الإجمالي النهائي:</label>
                    <input id="totalDisplay" type="number" class="form-control bg-warning  fw-bold" readonly>
                </div>
            </div>

            <div class="modal-footer">
                <button id="add" onclick="confirmAddItem()" class="btn btn-primary">إضافة للفاتورة</button>
                <button onclick="hideSendItemModal()" class="btn btn-secondary">إلغاء</button>
            </div>
        </div>
    </div>
</div>


<!-- مودل معدل لاضافة العنصر -->

<!-- أيقونة الفاتورة -->
<div class="invoice-icon-container">
    <img src="assets/images/invoice.png" alt="فاتورة" class="invoice-icon" onclick="showInvoiceModal()">
    <div class="invoice-counter" id="invoiceCounter">0</div>
</div>

<!-- مودال الفاتورة -->
<div id="invoiceModal" class="modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success ">
                <h5 class="modal-title">
                    <i class="fa fa-shopping-cart me-2"></i> سلة المشتريات
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="hideInvoiceModal()"></button>
            </div>
            <div class="modal-body">
                <div class="invoice-container">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="company-info">
                                <h4>ثلاجة إلياس</h4>
                                <p>العنوان: مملكة العربيه السعوديه - الجعرانه</p>
                                <p>الهاتف: 050000000</p>
                                <p>الرقم الضريبي: 123-456-789</p>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="invoice-info">
                                <h5>فاتورة ضريبية</h5>
                                <p>رقم الفاتورة: <span id="invoiceNumber">INV-001</span></p>
                                <p>التاريخ: <span id="invoiceDate"></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">بيانات العميل/المورد</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p>اسم العميل: <span id="customerName">اسم المورد</span></p>
                                    <p>العنوان: <span id="customerAddress">عنوان المورد</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p>الهاتف: <span id="customerPhone">هاتف المورد</span></p>
                                    <p>الرقم الضريبي: <span id="customerTaxNumber">الرقم الضريبي للمورد</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-striped table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th width="5%">م</th>
                                    <th width="5%">كود الصنف</th>
                                    <th width="30%">اسم الصنف</th>
                                    <th width="10%">الكمية</th>
                                    <th width="15%">الوحدة</th>
                                    <th width="15%">السعر</th>
                                    <th width="15%">الإجمالي</th>
                                    <th width="5%">الخصم</th>
                                    <th class="d-print-none" width="5%">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="invoiceItemsTable">
                                <!-- سيتم تعبئتها بالجافاسكريبت -->
                            </tbody>
                        </table>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6 text-start">المجموع الفرعي:</div>
                                <div class="col-6 text-end" id="subtotal">0.00</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-start">الضريبة (14%):</div>
                                <div class="col-6 text-end" id="taxAmount">0.00</div>
                            </div>
                            <div class="row fw-bold">
                                <div class="col-6 text-start">الإجمالي:</div>
                                <div class="col-6 text-end" id="totalAmount">0.00</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-start">الخصم:</div>
                                <div class="col-6 text-end" id="discountAmount">0.00</div>
                            </div>
                            <div class="row fw-bold">
                                <div class="col-6 text-start">الإجمالي النهائي:</div>
                                <div class="col-6 text-end" id="generalTotalAmount">0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p>شكراً لتعاملكم معنا</p>
                        <p>هذه فاتورة ضريبية معتمدة</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" onclick="confirmPurchase()">
                    <i class="fa fa-check-circle me-1"></i> تأكيد الشراء
                </button>
                <button class="btn btn-primary" onclick="printInvoice()">
                    <i class="fa fa-print me-1"></i> طباعة الفاتورة
                </button>

                <button class="btn btn-primary" onclick="showInvoiceInfoModal()">تفاصيل الفاتورة
                    <i class="fa fa-info-circle me-1"></i>
                </button>

                <button class="btn btn-secondary" onclick="hideInvoiceModal()">إغلاق</button>
            </div>
        </div>
    </div>
</div>


<!-- تفاصيل الفاتورة -->
<div id="invoiceInfoModal" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل اضافيه للفاتورة</h5>
                <button type="button" class="btn-close" onclick="hideInvoiceInfoModal()"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="discountInput" class="form-label">اضافة خصم:</label>
                    <input id="discountInput" type="number" class="form-control" placeholder="أدخل الخصم" min="0" step=".01" value="0">
                </div>
                <div class="mb-3">
                    <label for="dateWantDebt" class="form-label"> موعد الدفع المتفق عليه</label>
                    <input id="dateWantDebt" type="date" class="form-control" placeholder="أدخل التاريخ">
                </div>
                <div class="mb-3">
                    <label for="dateWantDebt" class="form-label"> طريقة الدفع</label>
                    <select id="typePay" class="form-select">
                        <option value="transfer" selected>تحويل</option>
                        <option value="card">شبكة</option>
                        <option value="cash">كاش</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" onclick="confirmInfo()">
                    <i class="fa fa-check-circle me-1"></i> حفظ المعلومات
                </button>
                <button class="btn btn-secondary" onclick="hideInvoiceInfoModal()">إغلاق</button>

            </div>
        </div>
    </div>
</div>





<script>
  var customerID = <?php echo intval($customerID); ?>;
</script>
 <script src="js/instascan.min.js"></script> 

 <script src="fromStore2Customer/items.js"></script>
 <script src="fromStore2Customer/funModal.js"></script>
<?php include('headAndFooter/footer.php'); ?>