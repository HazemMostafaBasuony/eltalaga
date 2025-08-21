<?php include('headAndFooter/head.php');?>

<link rel="stylesheet" href="css/style_itemCard.css">

<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><i class="fas fa-box me-2"></i>إدارة الأصناف</h3>
                    <p class="mb-0 " style="color: #6c757d;">إضافة وتعديل وحذف الأصناف في النظام</p>
                </div>
                <button class="btn btn-light" onclick="openAddModal()">
                    <i class="fas fa-plus me-2"></i>إضافة صنف جديد
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="البحث عن صنف...">
                    </div>
                </div>
            </div>

            <div id="loadingDiv" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <p class="mt-2">جاري تحميل البيانات...</p>
            </div>

            <div id="itemsTableDiv" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>رقم الصنف</th>
                                <th>اسم الصنف</th>
                                <th>الوحدة الكبيرة</th>
                                <th>معامل التحويل ك→م</th>
                                <th>الوحدة المتوسطة</th>
                                <th>معامل التحويل م→ص</th>
                                <th>الوحدة الصغيرة</th>
                                <th>المجموعة الرئيسية</th>
                                <th>المجموعة الفرعية</th>
                                <th>المخزون</th>
                                <th>الربح</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <!-- البيانات ستظهر هنا -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal إضافة/تعديل صنف -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">إضافة صنف جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="itemForm">
                    <input type="hidden" id="itemID" name="itemID">
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="itemName" class="form-label">اسم الصنف <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="itemName" name="itemName" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label for="unitL" class="form-label">الوحدة الكبيرة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unitL" name="unitL" required>
                        </div>
                        <div class="col-md-5">
                            <label for="fL2M" class="form-label">معامل التحويل ك→م <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="fL2M" name="fL2M" step="0.01" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <span class="badge bg-info p-2 w-100">1 ك = <span id="fL2MResult">0</span> م</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label for="unitM" class="form-label">الوحدة المتوسطة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unitM" name="unitM" required>
                        </div>
                        <div class="col-md-5">
                            <label for="fM2S" class="form-label">معامل التحويل م→ص <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="fM2S" name="fM2S" step="0.01" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <span class="badge bg-info p-2 w-100">1 م = <span id="fM2SResult">0</span> ص</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="unitS" class="form-label">الوحدة الصغيرة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unitS" name="unitS" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mainGroup" class="form-label">المجموعة الرئيسية <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mainGroup" name="mainGroup" required>
                        </div>
                        <div class="col-md-6">
                            <label for="subGroup" class="form-label">المجموعة الفرعية <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subGroup" name="subGroup" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="stock" class="form-label">المخزون <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="profit" class="form-label">الربح <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="profit" name="profit" step="0.01" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="saveItem()">حفظ</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast للإشعارات -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">إشعار النظام</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

<script>
    let currentEditId = null;
    let itemModal = new bootstrap.Modal(document.getElementById('itemModal'));
    let liveToast = new bootstrap.Toast(document.getElementById('liveToast'));

    // تحميل البيانات عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        loadItems();
        
        // حساب معاملات التحويل عند التغيير
        document.getElementById('fL2M').addEventListener('input', function() {
            document.getElementById('fL2MResult').textContent = this.value;
        });
        
        document.getElementById('fM2S').addEventListener('input', function() {
            document.getElementById('fM2SResult').textContent = this.value;
        });
    });

    // البحث
    document.getElementById('searchInput').addEventListener('input', function() {
        loadItems(this.value);
    });

    // تحميل الأصناف
    function loadItems(search = '') {
        document.getElementById('loadingDiv').style.display = 'block';
        document.getElementById('itemsTableDiv').style.display = 'none';
        
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById('loadingDiv').style.display = 'none';
                document.getElementById('itemsTableDiv').style.display = 'block';
                document.getElementById('itemsTableBody').innerHTML = xhr.responseText;
                
                // تهيئة tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        };
        xhr.open("GET", "itemCard/getItems.php?search=" + encodeURIComponent(search), true);
        xhr.send();
    }

    // فتح نافذة الإضافة
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'إضافة صنف جديد';
        document.getElementById('itemForm').reset();
        document.getElementById('itemID').value = '';
        document.getElementById('fL2MResult').textContent = '0';
        document.getElementById('fM2SResult').textContent = '0';
        currentEditId = null;
        itemModal.show();
    }

    // فتح نافذة التعديل
    function editItem(id) {
        document.getElementById('modalTitle').textContent = 'تعديل الصنف';
        currentEditId = id;
        
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var item = JSON.parse(xhr.responseText);
                if (item.success) {
                    document.getElementById('itemID').value = item.data.itemID;
                    document.getElementById('itemName').value = item.data.itemName;
                    document.getElementById('unitL').value = item.data.unitL;
                    document.getElementById('fL2M').value = item.data.fL2M;
                    document.getElementById('unitM').value = item.data.unitM;
                    document.getElementById('fM2S').value = item.data.fM2S;
                    document.getElementById('unitS').value = item.data.unitS;
                    document.getElementById('mainGroup').value = item.data.mainGroup;
                    document.getElementById('subGroup').value = item.data.subGroup;
                    document.getElementById('stock').value = item.data.stock;
                    document.getElementById('profit').value = item.data.profit;
                    
                    // تحديث معاملات التحويل
                    document.getElementById('fL2MResult').textContent = item.data.fL2M;
                    document.getElementById('fM2SResult').textContent = item.data.fM2S;
                    
                    itemModal.show();
                } else {
                    showToast('خطأ في تحميل بيانات الصنف', 'danger');
                }
            }
        };
        xhr.open("GET", "itemCard/getItem.php?id=" + id, true);
        xhr.send();
    }

    // حفظ الصنف
    function saveItem() {
        var form = document.getElementById('itemForm');
        var formData = new FormData(form);
        
        // التحقق من صحة البيانات
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    showToast(response.message, 'success');
                    itemModal.hide();
                    loadItems();
                } else {
                    showToast('خطأ: ' + response.message, 'danger');
                }
            }
        };
        
        var action = currentEditId ? 'update' : 'add';
        xhr.open("POST", "itemCard/saveItem.php?action=" + action, true);
        xhr.send(formData);
    }

    // حذف الصنف
    function deleteItem(id) {
        if (confirm('هل أنت متأكد من حذف هذا الصنف؟ لا يمكن التراجع عن هذه العملية.')) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showToast(response.message, 'success');
                        loadItems();
                    } else {
                        showToast('خطأ: ' + response.message, 'danger');
                    }
                }
            };
            xhr.open("GET", "itemCard/deleteItem.php?id=" + id, true);
            xhr.send();
        }
    }

    // عرض الإشعارات
    function showToast(message, type = 'success') {
        var toastBody = document.querySelector('.toast-body');
        toastBody.textContent = message;
        
        var toast = document.getElementById('liveToast');
        toast.classList.remove('bg-success', 'bg-danger', 'bg-warning');
        
        if (type === 'success') {
            toast.classList.add('bg-success', 'text-white');
        } else if (type === 'danger') {
            toast.classList.add('bg-danger', 'text-white');
        } else {
            toast.classList.add('bg-warning', 'text-dark');
        }
        
        liveToast.show();
    }
</script>

<?php include('headAndFooter/footer.php'); ?>