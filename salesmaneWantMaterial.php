<?php
if (isset($_POST['salesmaneID'])) {
    $salesmaneID = $_POST['salesmaneID'];
} else {
    header("Location: salesmane_HomePage.php");
    die("يجب تحديد مندوب");
}

?>

<?php include 'headAndFooter/headMop.php';?>
<input type="hidden" id="customerID" value="<?php echo $customerID; ?>">
<input type="hidden" id="userID" value="<?php echo $userID; ?>">
<div id="loader">eeeeeeee</div>
<!-- تخطيط متجاوب للمجموعات والبحث -->
<div class="row  mb-1">
    <!-- المجموعات الرئيسية -->
    <div class="col-lg-6 col-md-6">
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
    <div class="col-lg-6 col-md-6">
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



<!-- قسم الأصناف المحسن -->
<div class="card shadow-lg border-0 ">
    <div class="card-header bg-gradient"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fa fa-shopping-bag me-2"></i>الأصناف</h4>
            <span class="badge bg-light text-dark" id="itemsCount">0 صنف</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input type="text" id="searchItem" class="form-control" placeholder="ابحث عن الصنف بالاسم أو الرقم..."
                autocomplete="off">
            <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()" title="مسح البحث">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <small id="searchResults" class="text-info fw-bold"></small>
        </div>


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
<div id="sendItem" class="modal" tabindex="-1" dir="rtl">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" onclick="hideSendItemModal()"></button>
                <h5 class="modal-title w-100">إضافة عنصر جديد</h5> <input type="hidden" id="itemID">
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="countItem" class="form-label ">العدد:</label>
                    <p id="countInfo" class="text-muted">توجد معلومات خاظئه فى هذا الصنف</p>
                    <input id="countItem" type="number" class="form-control" placeholder="أدخل العدد" min="1" step="1">
                </div>

                <div class="mb-3">
                    <label for="unitSelect" class="form-label">الوحدة:</label>
                    <select id="unitSelect" class="form-select">
                        <option value="" disabled selected>اختر الوحدة</option>

                    </select>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between">
                <button id="add" onclick="confirmAddItem()" class="btn btn-primary ">إضافة للطلبية</button>
                <button onclick="hideSendItemModal()" class="btn btn-secondary ">إلغاء</button>
            </div>
        </div>
    </div>
</div>


<!-- مودل معدل لاضافة العنصر -->

<!-- أيقونة الفاتورة -->
<div class="invoice-icon-container">

    <img id="invoiceIcon" src="" alt="فاتورة" class="invoice-icon" onclick="showInvoiceModal()">
    <div class="invoice-counter" id="invoiceCounter">0</div>
</div>

<!-- مودال الفاتورة -->
<div id="invoiceModal" class="modal" tabindex="-1" dir="rtl">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success ">
                <button type="button" class="btn-close btn-close-dark" onclick="hideInvoiceModal()"></button>
                <h5 class="modal-title w-100">
                    <i class="fa fa- me-2"></i> طلبية المخزن 
                </h5>
                
            </div>
            <div class="modal-body">
                <div class="invoice-container">
                    <div class="row mb-12">
                        <div class="col-md-6 text-md-end">
                            <div class="invoice-info">
                                <h5> طلببية المخزن</h5>
                                <h6 id="salesmaneName"></h6>
                                <p>رقم الطلبية: <span id="requestNumber">INV-001</span></p>
                                <p>التاريخ: <span id="receiveDate"></span></p>
                            </div>
                        </div>
                    </div>



                    <div class="table-responsive mb-4">
                        <table class="table table-striped table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th width="5%">كود الصنف</th>
                                    <th width="90%"> البيان</th>
                                    <th class="d-print-none" width="5%">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="invoiceItemsTable">
                                <!-- سيتم تعبئتها بالجافاسكريبت -->
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center mt-4">
                        <p id="extraSmS"></p>
                        <p>شكراً لتعاملكم معنا</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" onclick="confirmPurchase()">
                    <i class="fa fa-check-circle me-1"></i> ارسال الطلبية
                </button>


                <button class="btn btn-secondary" onclick="hideInvoiceModal()">إغلاق</button>
            </div>
        </div>
    </div>
</div>


<!-- تفاصيل الفاتورة -->
<div id="invoiceInfoModal" class="modal" tabindex="-1" dir="rtl">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" onclick="hideInvoiceInfoModal()"></button>
                <h5 class="modal-title  w-100">اضافة رسالة لمسئول المخزن  </h5> 
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="message">الرسالة</label>
                    <textarea class="form-control" id="message" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" onclick="sendMessageToStore()">
                    <i class="fa fa-check-circle me-1"></i> حفظ و ارسال الى مسئول المخزن
                </button>
                <button class="btn btn-secondary" onclick="hideInvoiceInfoModal()">إغلاق</button>

            </div>
        </div>
    </div>
</div>



<?php include 'headAndFooter/footer.php'; ?>


<script src="salesmaneWantMaterial/items.js"></script>

<script src="salesmaneWantMaterial/funModal.js"></script>




















