
<?php include('headAndFooter/head.php'); ?>

<?php
// Get all suppliers for display
$sql = "SELECT * FROM suppliers ORDER BY supplierName";
$suppliersResult = $conn->query($sql);
?>

<div class="container mt-4">
    <!-- Page Header -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body text-center py-4">
            <h1 class="display-5 mb-2"><i class="fa fa-users me-2"></i> إدارة الموردين</h1>
            <p class="lead text-muted">نظام إدارة الموردين والمواد الخام</p>
        </div>
    </div>

    <!-- Suppliers Section -->
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">قائمة الموردين</h5>
            <button class="btn btn-primary" onclick="openAddSupplierModal()">
                <i class="fa fa-plus me-1"></i> إضافة مورد جديد
            </button>
        </div>
        
        <div class="card-body">
            <!-- Search Bar -->
            <div class="row mb-4">
                <div class="col-md-6 mx-auto">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="البحث عن المورد..." onkeyup="filterSuppliers(this.value)">
                    </div>
                </div>
            </div>

            <!-- Suppliers Table -->
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="suppliersTable">
                    <thead class="table-light">
                        <tr>
                            <th>اسم المورد</th>
                            <th>النوع</th>
                            <th>رقم الهاتف</th>
                            <th>البريد الإلكتروني</th>
                            <th>المدينة</th>
                            <th>إجمالي الديون</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($supplier = $suppliersResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($supplier['supplierName']); ?></td>
                            <td><?php echo $supplier['type'] == 'individual' ? 'فردي' : 'شركة'; ?></td>
                            <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['city']); ?></td>
                            <td><?php echo number_format($supplier['totalDebt'], 2); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info" onclick="viewSupplier(<?php echo $supplier['supplierID']; ?>)" title="عرض">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="editSupplier(<?php echo $supplier['supplierID']; ?>)" title="تعديل">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteSupplier(<?php echo $supplier['supplierID']; ?>)" title="حذف">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Supplier Modal -->
<div id="supplierModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">إضافة مورد جديد</h5>
                <button type="button" class="btn-close" onclick="closeSupplierModal()"></button>
            </div>
            
            <form id="supplierForm" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="supplierID" name="supplierID">
                    
                    <div class="row g-3">
                        <!-- بيانات أساسية -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">البيانات الأساسية</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="supplierName" class="form-label">اسم المورد *</label>
                            <input type="text" class="form-control" id="supplierName" name="supplierName" required>
                            <div class="invalid-feedback" id="supplierName-error">
                                يرجى إدخال اسم المورد
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="type" class="form-label">النوع *</label>
                            <input class="form-control" list="dataList" name="type" id="type" required>
                            <datalist id="dataList">
                                <option value="individual">فردي</option>
                                <option value="company">شركة</option>
                            </datalist>
                            <div class="invalid-feedback" id="type-error">
                                يرجى اختيار نوع المورد
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="numberRC" class="form-label">رقم السجل التجاري</label>
                            <input type="text" class="form-control" id="numberRC" name="numberRC">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="numberTax" class="form-label">الرقم الضريبي</label>
                            <input type="text" class="form-control" id="numberTax" name="numberTax">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="phone" class="form-label">رقم الهاتف *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                            <div class="invalid-feedback" id="phone-error">
                                يرجى إدخال رقم الهاتف
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        
                        <!-- بيانات العنوان -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-2">بيانات العنوان</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="street" class="form-label">الشارع</label>
                            <input type="text" class="form-control" id="street" name="street">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="area" class="form-label">المنطقة</label>
                            <input type="text" class="form-control" id="area" name="area">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="city" class="form-label">المدينة</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="country" class="form-label">البلد</label>
                            <input type="text" class="form-control" id="country" name="country">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="building" class="form-label">رقم المبنى</label>
                            <input type="text" class="form-control" id="building" name="building">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="postCode" class="form-label">الرمز البريدي</label>
                            <input type="text" class="form-control" id="postCode" name="postCode">
                        </div>
                        
                        <!-- بيانات مالية -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-2">بيانات مالية وتواريخ</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="startDate" class="form-label">تاريخ بداية التعامل</label>
                            <input type="date" class="form-control" id="startDate" name="startDate" value="<?php echo date('Y-m-d'); ?>" readonly>
                            <small class="form-text text-muted">يتم تسجيل التاريخ الحالي تلقائياً</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="lastInvoiceDate" class="form-label">تاريخ آخر فاتورة</label>
                            <input type="date" class="form-control" id="lastInvoiceDate" name="lastInvoiceDate">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="totalDebt" class="form-label">إجمالي الديون</label>
                            <input type="number" class="form-control" id="totalDebt" name="totalDebt" step="0.01" min="0" value="0">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="wantDebt" class="form-label">الدين المطلوب</label>
                            <input type="number" class="form-control" id="wantDebt" name="wantDebt" step="0.01" min="0" value="0">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="dateWantedDebt" class="form-label">تاريخ استحقاق الدين</label>
                            <input type="date" class="form-control" id="dateWantedDebt" name="dateWantedDebt">
                        </div>
                        
                        <div class="col-12">
                            <label for="notes" class="form-label">ملاحظات (اختيارية)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="يمكنك إضافة ملاحظات إضافية عن المورد هنا..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Items Section -->
                    <div class="mt-4 border-top pt-3" id="itemsSection" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">إدارة المواد الخام</h5>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadItems()">
                                    <i class="fa fa-refresh me-1"></i> تحديث القائمة
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="debugItems()">
                                    <i class="fa fa-bug me-1"></i> Debug
                                </button>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        المواد المتاحة
                                    </div>
                                    <div class="card-body">
                                        <div id="itemsTree" class="overflow-auto" style="max-height: 300px;"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <span>المواد المحددة</span>
                                        <span class="badge bg-primary" id="selectedItemsCount">0</span>
                                    </div>
                                    <div class="card-body">
                                        <div id="selectedItemsList" class="overflow-auto" style="max-height: 300px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeSupplierModal()">
                        <i class="fa fa-times me-1"></i> إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentSupplierID = null;
let availableItems = [];
let selectedItems = [];

// Modal functions
function openAddSupplierModal() {
    document.getElementById('modalTitle').textContent = 'إضافة مورد جديد';
    document.getElementById('supplierForm').reset();
    document.getElementById('supplierID').value = '';
    document.getElementById('itemsSection').style.display = 'none';
    
    // Reset all form fields to their default state
    const inputs = document.querySelectorAll('#supplierForm input, #supplierForm select, #supplierForm textarea');
    inputs.forEach(input => {
        input.disabled = false;
        input.readOnly = false;
        // Restore required attributes based on field type
        if (input.name === 'supplierName' || input.name === 'type' || input.name === 'phone') {
            input.setAttribute('required', 'required');
        }
    });
    
    // Set default values
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('startDate').value = today;
    document.getElementById('totalDebt').value = '0';
    document.getElementById('wantDebt').value = '0';
    document.getElementById('lastInvoiceDate').value = today;
    document.getElementById('dateWantedDebt').value = today;
    
    // Clear any validation errors
    document.querySelectorAll('.invalid-feedback').forEach(error => {
        error.style.display = 'none';
    });
    
    // Reset field validation state
    document.querySelectorAll('#supplierForm input, #supplierForm select').forEach(field => {
        field.classList.remove('is-invalid');
        field.classList.remove('is-valid');
    });
    
    // Show modal using Bootstrap
    var myModal = new bootstrap.Modal(document.getElementById('supplierModal'));
    myModal.show();
    
    currentSupplierID = null;
}

function closeSupplierModal() {
    // Hide modal using Bootstrap
    var myModal = bootstrap.Modal.getInstance(document.getElementById('supplierModal'));
    if (myModal) {
        myModal.hide();
    }
    
    document.getElementById('supplierForm').reset();
    
    // Reset all form fields to default state
    const inputs = document.querySelectorAll('#supplierForm input, #supplierForm select, #supplierForm textarea');
    inputs.forEach(input => {
        input.disabled = false;
        input.readOnly = false;
        // Restore required attributes for essential fields
        if (input.name === 'supplierName' || input.name === 'type' || input.name === 'phone') {
            input.setAttribute('required', 'required');
        }
    });
    
    selectedItems = [];
    currentSupplierID = null;
}

function editSupplier(supplierID) {
    currentSupplierID = supplierID;
    document.getElementById('modalTitle').textContent = 'تعديل المورد';
    document.getElementById('itemsSection').style.display = 'block';
    
    // Reset form fields to editable state
    const inputs = document.querySelectorAll('#supplierForm input, #supplierForm select, #supplierForm textarea');
    inputs.forEach(input => {
        input.disabled = false;
        input.readOnly = false;
        // Restore required attributes based on field type
        if (input.name === 'supplierName' || input.name === 'type' || input.name === 'phone') {
            input.setAttribute('required', 'required');
        }
    });
    
    // Reset validation state
    document.querySelectorAll('#supplierForm input, #supplierForm select').forEach(field => {
        field.classList.remove('is-invalid');
        field.classList.remove('is-valid');
    });
    
    // Load supplier data
    fetch('suppliers_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_supplier&supplierID=' + supplierID
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => { 
        try {
            const data = JSON.parse(text);
            if (data.success) {
                const supplier = data.supplier;
                document.getElementById('supplierID').value = supplier.supplierID;
                document.getElementById('supplierName').value = supplier.supplierName;
                document.getElementById('type').value = supplier.type;
                document.getElementById('numberRC').value = supplier.numberRC;
                document.getElementById('numberTax').value = supplier.numberTax;
                document.getElementById('phone').value = supplier.phone;
                document.getElementById('email').value = supplier.email;
                document.getElementById('street').value = supplier.street;
                document.getElementById('area').value = supplier.area;
                document.getElementById('city').value = supplier.city;
                document.getElementById('country').value = supplier.country;
                document.getElementById('building').value = supplier.bulding;
                document.getElementById('postCode').value = supplier.postCode;
                document.getElementById('startDate').value = supplier.startDate;
                document.getElementById('lastInvoiceDate').value = supplier.lastInvoiceDate;
                document.getElementById('totalDebt').value = supplier.totalDebt;
                document.getElementById('wantDebt').value = supplier.wantDebt;
                document.getElementById('dateWantedDebt').value = supplier.dateWantedDebt;
                document.getElementById('notes').value = supplier.notes;
                
                // Show modal using Bootstrap
                var myModal = new bootstrap.Modal(document.getElementById('supplierModal'));
                myModal.show();
                
                // Add small delay to ensure modal is fully rendered
                setTimeout(() => {
                    loadItems();
                }, 100);
            } else {
                alert('خطأ في تحميل بيانات المورد: ' + data.message);
            }
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Response text:', text);
            alert('خطأ في تحليل استجابة الخادم');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        alert('خطأ في الاتصال بالخادم');
    });
}

function viewSupplier(supplierID) {
    editSupplier(supplierID);
    
    // Make all fields readonly after a short delay to ensure the modal is loaded
    setTimeout(() => {
        // Make all fields readonly
        const inputs = document.querySelectorAll('#supplierForm input, #supplierForm select, #supplierForm textarea');
        inputs.forEach(input => {
            input.readOnly = true;
            input.disabled = true;
            // Remove required attribute to prevent validation errors
            input.removeAttribute('required');
        });
        
        document.getElementById('modalTitle').textContent = 'عرض المورد';
        
        // Hide submit button
        const submitBtn = document.querySelector('#supplierForm button[type="submit"]');
        if (submitBtn) {
            submitBtn.style.display = 'none';
        }
    }, 300);
}

function deleteSupplier(supplierID) {
    if (confirm('هل أنت متأكد من حذف هذا المورد؟ سيتم حذف جميع المواد المرتبطة به.')) {
        fetch('suppliers_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=delete_supplier&supplierID=' + supplierID
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم حذف المورد بنجاح');
                location.reload();
            } else {
                alert('خطأ في حذف المورد');
            }
        });
    }
}

// Form submission
document.getElementById('supplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Disable browser validation to use custom validation
    this.setAttribute('novalidate', 'novalidate');
    
    // Check for required fields with better validation
    const requiredFields = [
        { id: 'supplierName', name: 'اسم المورد', errorId: 'supplierName-error' },
        { id: 'type', name: 'النوع', errorId: 'type-error' },
        { id: 'phone', name: 'رقم الهاتف', errorId: 'phone-error' }
    ];
    
    let hasError = false;
    let errorMessages = [];
    
    // Clear previous errors
    document.querySelectorAll('.field-error').forEach(error => {
        error.style.display = 'none';
    });
    
    requiredFields.forEach(fieldInfo => {
        const field = document.getElementById(fieldInfo.id);
        const errorElement = document.getElementById(fieldInfo.errorId);
        
        if (!field) {
            console.error('Field not found:', fieldInfo.id);
            return;
        }
        
        // Check if field is disabled (should not be for required fields)
        if (field.disabled) {
            console.warn('Required field is disabled:', fieldInfo.id);
            field.disabled = false;
        }
        
        // Validate field value
        if (!field.value || field.value.trim() === '') {
            field.style.border = '2px solid #e74c3c';
            if (errorElement) {
                errorElement.style.display = 'block';
            }
            errorMessages.push(fieldInfo.name);
            hasError = true;
        } else {
            field.style.border = '1px solid #ddd';
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }
    });
    
    if (hasError) {
        alert('يرجى تعبئة الحقول المطلوبة التالية:\n' + errorMessages.join('\n'));
        // Focus on first error field
        const firstErrorField = document.getElementById(requiredFields.find(f => 
            !document.getElementById(f.id).value.trim()
        ).id);
        if (firstErrorField) {
            firstErrorField.focus();
        }
        return;
    }
    
    const formData = new FormData(this);
    const action = document.getElementById('supplierID').value ? 'update_supplier' : 'add_supplier';
    formData.append('action', action);
    
    fetch('suppliers_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                alert(data.message);
                if (action === 'add_supplier') {
                    location.reload();
                } else {
                    // Save items if editing
                    if (currentSupplierID && selectedItems.length > 0) {
                        saveSupplierItems();
                    } else {
                        location.reload();
                    }
                }
            } else {
                alert(data.message);
            }
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Response text:', text);
            alert('خطأ في تحليل استجابة الخادم');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        alert('خطأ في الاتصال بالخادم');
    });
});

// Items management
function loadItems() {
    console.log('loadItems called for supplier:', currentSupplierID);
    const supplierID = currentSupplierID;
    
    fetch('suppliers_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_items' + (supplierID ? '&supplierID=' + supplierID : '')
    })
    .then(response => response.json())
    .then(data => {
        console.log('Items data received:', data);
        if (data.success) {
            availableItems = data.items || [];
            selectedItems = data.selectedItems || [];
            console.log('Available items:', availableItems.length);
            console.log('Selected items:', selectedItems.length);
            renderItemsTree();
            renderSelectedItems();
        } else {
            console.error('Error loading items:', data.message);
        }
    })
    .catch(error => {
        console.error('Error loading items:', error);
    });
}

function renderItemsTree() {
    console.log('renderItemsTree called with', availableItems.length, 'items');
    const container = document.getElementById('itemsTree');
    
    if (!container) {
        console.error('Items tree container not found!');
        return;
    }
    
    container.innerHTML = '';
    
    if (!availableItems || availableItems.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: #666; padding: 20px;">لا توجد مواد متاحة</div>';
        return;
    }
    
    // Group items by mainGroup and subGroup
    const groupedItems = {};
    availableItems.forEach(item => {
        if (!groupedItems[item.mainGroup]) {
            groupedItems[item.mainGroup] = {};
        }
        if (!groupedItems[item.mainGroup][item.subGroup]) {
            groupedItems[item.mainGroup][item.subGroup] = [];
        }
        groupedItems[item.mainGroup][item.subGroup].push(item);
    });
    
    // Render tree structure using simple HTML approach using simple HTML approach
    Object.keys(groupedItems).forEach(mainGroup => {
        const mainGroupDiv = document.createElement('div');
        mainGroupDiv.className = 'items-tree';
        
        // Create main group HTML
        mainGroupDiv.innerHTML = `
            <div class="group-header" style="background-color: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 5px; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" class="main-group-checkbox" data-main-group="${mainGroup}" style="width: 16px; height: 16px; cursor: pointer; margin: 0;" onclick="handleMainGroupClick('${mainGroup}', this.checked)">
                <span style="font-weight: bold; color: #495057; font-size: 14px;">${mainGroup}</span>
            </div>
            <div class="group-content" style="margin-left: 20px; margin-bottom: 10px;">
                ${Object.keys(groupedItems[mainGroup]).map(subGroup => `
                    <div style="margin-bottom: 10px;">
                        <div class="subgroup-header" style="background-color: #e9ecef; padding: 8px; border-radius: 3px; margin-bottom: 5px; display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" class="sub-group-checkbox" data-main-group="${mainGroup}" data-sub-group="${subGroup}" style="width: 14px; height: 14px; cursor: pointer; margin: 0;" onclick="handleSubGroupClick('${mainGroup}', '${subGroup}', this.checked)">
                            <span style="font-weight: 500; color: #6c757d; font-size: 13px;">${subGroup}</span>
                        </div>
                        <div class="subgroup-content" style="margin-left: 15px;">
                            ${groupedItems[mainGroup][subGroup].map(item => {
                                const isSelected = selectedItems.some(selected => selected.itemID == item.itemID);
                                let details = [];
                                if (item.unitL) details.push(`الوحدة الكبرى: ${item.unitL}`);
                                if (item.unitM) details.push(`الوحدة المتوسطة: ${item.unitM}`);
                                if (item.unitS) details.push(`الوحدة الصغرى: ${item.unitS}`);
                                
                                return `
                                    <div class="item-entry" style="display: flex; align-items: center; gap: 10px; padding: 5px; border: 1px solid #e9ecef; border-radius: 3px; margin-bottom: 3px; background-color: #fff;">
                                        <input type="checkbox" class="item-checkbox" data-item-id="${item.itemID}" ${isSelected ? 'checked' : ''} style="width: 14px; height: 14px; cursor: pointer; margin: 0;" onclick="handleItemClick(${item.itemID}, this.checked)">
                                        <div class="item-info" style="flex: 1; margin-left: 10px;">
                                            <div class="item-name" style="color: #495057; font-size: 12px; font-weight: 500;">${item.itemName}</div>
                                            ${details.length > 0 ? `<div class="item-details" style="font-size: 10px; color: #6c757d; margin-top: 2px;">${details.join(' • ')}</div>` : ''}
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        
        container.appendChild(mainGroupDiv);
    });
    
    // Update checkbox states based on selected items
    updateCheckboxStates();
}

// Simple onclick handlers
function handleMainGroupClick(mainGroup, checked) {
    console.log('Main group checkbox clicked:', mainGroup, 'checked:', checked);
    toggleMainGroup(mainGroup, checked);
}

function handleSubGroupClick(mainGroup, subGroup, checked) {
    console.log('Sub group checkbox clicked:', mainGroup, subGroup, 'checked:', checked);
    toggleSubGroup(mainGroup, subGroup, checked);
}

function handleItemClick(itemID, checked) {
    console.log('Item checkbox clicked:', itemID, 'checked:', checked);
    toggleItem(itemID, checked);
}

// Simple onclick handlers
function handleMainGroupClick(mainGroup, checked) {
    console.log('Main group checkbox clicked:', mainGroup, 'checked:', checked);
    toggleMainGroup(mainGroup, checked);
}

function handleSubGroupClick(mainGroup, subGroup, checked) {
    console.log('Sub group checkbox clicked:', mainGroup, subGroup, 'checked:', checked);
    toggleSubGroup(mainGroup, subGroup, checked);
}

function handleItemClick(itemID, checked) {
    console.log('Item checkbox clicked:', itemID, 'checked:', checked);
    toggleItem(itemID, checked);
}

function updateCheckboxStates() {
    console.log('updateCheckboxStates called');
    
    // Update main group checkboxes
    const mainGroups = document.querySelectorAll('.group-header');
    console.log('Found main groups:', mainGroups.length);
    
    mainGroups.forEach(groupHeader => {
        const groupContent = groupHeader.nextElementSibling;
        if (!groupContent) return;
        
        const allItemCheckboxes = groupContent.querySelectorAll('.item-entry input[type="checkbox"]');
        const checkedItemCheckboxes = groupContent.querySelectorAll('.item-entry input[type="checkbox"]:checked');
        
        const mainGroupCheckbox = groupHeader.querySelector('input[type="checkbox"]');
        if (mainGroupCheckbox) {
            if (checkedItemCheckboxes.length === 0) {
                mainGroupCheckbox.checked = false;
                mainGroupCheckbox.indeterminate = false;
            } else if (checkedItemCheckboxes.length === allItemCheckboxes.length) {
                mainGroupCheckbox.checked = true;
                mainGroupCheckbox.indeterminate = false;
            } else {
                mainGroupCheckbox.checked = false;
                mainGroupCheckbox.indeterminate = true;
            }
        }
    });
    
    // Update sub group checkboxes
    const subGroups = document.querySelectorAll('.subgroup-header');
    console.log('Found sub groups:', subGroups.length);
    
    subGroups.forEach(subGroupHeader => {
        const subGroupContent = subGroupHeader.nextElementSibling;
        if (!subGroupContent) return;
        
        const allItemCheckboxes = subGroupContent.querySelectorAll('.item-entry input[type="checkbox"]');
        const checkedItemCheckboxes = subGroupContent.querySelectorAll('.item-entry input[type="checkbox"]:checked');
        
        const subGroupCheckbox = subGroupHeader.querySelector('input[type="checkbox"]');
        if (subGroupCheckbox) {
            if (checkedItemCheckboxes.length === 0) {
                subGroupCheckbox.checked = false;
                subGroupCheckbox.indeterminate = false;
            } else if (checkedItemCheckboxes.length === allItemCheckboxes.length) {
                subGroupCheckbox.checked = true;
                subGroupCheckbox.indeterminate = false;
            } else {
                subGroupCheckbox.checked = false;
                subGroupCheckbox.indeterminate = true;
            }
        }
    });
}

function renderSelectedItems() {
    console.log('renderSelectedItems called with', selectedItems.length, 'items');
    const container = document.getElementById('selectedItemsList');
    
    if (!container) {
        console.error('Selected items container not found!');
        return;
    }
    
    container.innerHTML = '';
    
    if (!selectedItems || selectedItems.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: #666; padding: 20px;">لا توجد مواد محددة</div>';
        return;
    }
    
    selectedItems.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'selected-item';
        
        itemDiv.innerHTML = `
            <div class="selected-item-info">
                <div class="selected-item-name">${item.itemName}</div>
                <div class="selected-item-groups">${item.mainGroup} - ${item.subGroup}</div>
                <div class="selected-item-details">
                    ${item.unitL ? `<small><strong>الوحدة الكبرى:</strong> ${item.unitL}</small>` : ''}
                    ${item.fL2M && item.fL2M > 0 ? `<small><strong>معامل التحويل:</strong> ${item.fL2M}</small>` : ''}
                    ${item.unitM ? `<small><strong>الوحدة المتوسطة:</strong> ${item.unitM}</small>` : ''}
                    ${item.fM2S && item.fM2S > 0 ? `<small><strong>معامل التحويل:</strong> ${item.fM2S}</small>` : ''}
                    ${item.unitS ? `<small><strong>الوحدة الصغرى:</strong> ${item.unitS}</small>` : ''}
                    ${item.stok && item.stok > 0 ? `<small><strong>المخزون:</strong> ${item.stok}</small>` : ''}
                    ${item.profit && item.profit > 0 ? `<small><strong>الربح:</strong> ${item.profit}%</small>` : ''}
                </div>
            </div>
            <button class="remove-item-btn" onclick="removeSelectedItem(${item.itemID})" title="إزالة المادة">
                <i class="fa fa-times"></i>
            </button>
        `;
        
        container.appendChild(itemDiv);
    });
    
    // Update items count
    const countElement = document.getElementById('selectedItemsCount');
    if (countElement) {
        countElement.textContent = selectedItems.length;
    }
}

function toggleMainGroup(mainGroup, checked) {
    console.log('toggleMainGroup called:', mainGroup, checked);
    const items = availableItems.filter(item => item.mainGroup === mainGroup);
    console.log('Found items:', items.length);
    
    items.forEach(item => {
        if (checked) {
            if (!selectedItems.some(selected => selected.itemID == item.itemID)) {
                // حفظ كل معلومات العنصر
                selectedItems.push({
                    itemID: item.itemID,
                    itemName: item.itemName,
                    unitL: item.unitL,
                    fL2M: item.fL2M,
                    unitM: item.unitM,
                    fM2S: item.fM2S,
                    unitS: item.unitS,
                    mainGroup: item.mainGroup,
                    subGroup: item.subGroup,
                    stok: item.stok,
                    profit: item.profit
                });
            }
        } else {
            selectedItems = selectedItems.filter(selected => selected.itemID != item.itemID);
        }
    });
    
    console.log('Selected items count:', selectedItems.length);
    renderItemsTree();
    renderSelectedItems();
}

function toggleSubGroup(mainGroup, subGroup, checked) {
    const items = availableItems.filter(item => item.mainGroup === mainGroup && item.subGroup === subGroup);
    
    items.forEach(item => {
        if (checked) {
            if (!selectedItems.some(selected => selected.itemID == item.itemID)) {
                // حفظ كل معلومات العنصر
                selectedItems.push({
                    itemID: item.itemID,
                    itemName: item.itemName,
                    unitL: item.unitL,
                    fL2M: item.fL2M,
                    unitM: item.unitM,
                    fM2S: item.fM2S,
                    unitS: item.unitS,
                    mainGroup: item.mainGroup,
                    subGroup: item.subGroup,
                    stok: item.stok,
                    profit: item.profit
                });
            }
        } else {
            selectedItems = selectedItems.filter(selected => selected.itemID != item.itemID);
        }
    });
    
    renderItemsTree();
    renderSelectedItems();
}

function toggleItem(itemID, checked) {
    const item = availableItems.find(item => item.itemID == itemID);
    
    if (checked) {
        if (!selectedItems.some(selected => selected.itemID == itemID)) {
            // حفظ كل معلومات العنصر
            selectedItems.push({
                itemID: item.itemID,
                itemName: item.itemName,
                unitL: item.unitL,
                fL2M: item.fL2M,
                unitM: item.unitM,
                fM2S: item.fM2S,
                unitS: item.unitS,
                mainGroup: item.mainGroup,
                subGroup: item.subGroup,
                stok: item.stok,
                profit: item.profit
            });
        }
    } else {
        selectedItems = selectedItems.filter(selected => selected.itemID != itemID);
    }
    
    renderSelectedItems();
    updateCheckboxStates();
}

// تم حذف دالة updateItemPrice لأنها لم تعد مطلوبة

function removeSelectedItem(itemID) {
    selectedItems = selectedItems.filter(item => item.itemID != itemID);
    renderItemsTree();
    renderSelectedItems();
}

function saveSupplierItems() {
    if (!currentSupplierID) return;
    
    fetch('suppliers_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=save_supplier_items&supplierID=' + currentSupplierID + '&items=' + JSON.stringify(selectedItems)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('خطأ في حفظ المواد');
        }
    });
}

// Search functionality
function filterSuppliers(searchTerm) {
    const table = document.getElementById('suppliersTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length - 1; j++) { // -1 to exclude actions column
            if (cells[j].textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

// Add additional CSS for checkboxes
const checkboxStyles = document.createElement('style');
checkboxStyles.textContent = `
    .items-tree input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        width: 16px;
        height: 16px;
        border: 2px solid #007bff;
        border-radius: 3px;
        background: white;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
    }
    
    .items-tree input[type="checkbox"]:checked {
        background: #007bff;
        border-color: #007bff;
    }
    
    .items-tree input[type="checkbox"]:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 12px;
        font-weight: bold;
    }
    
    .items-tree input[type="checkbox"]:indeterminate {
        background: #6c757d;
        border-color: #6c757d;
    }
    
    .items-tree input[type="checkbox"]:indeterminate::after {
        content: '−';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 14px;
        font-weight: bold;
    }
    
    .items-tree input[type="checkbox"]:hover {
        border-color: #0056b3;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }
    
    .items-tree input[type="checkbox"]:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }
    
    .item-entry:hover {
        background-color: #f8f9fa !important;
    }
    
    .group-header:hover {
        background-color: #e9ecef !important;
    }
    
    .subgroup-header:hover {
        background-color: #dee2e6 !important;
    }
    
    .selected-items {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 10px;
        background-color: #f8f9fa;
    }
    
    .selected-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px;
        margin-bottom: 5px;
        background-color: white;
        border: 1px solid #dee2e6;
        border-radius: 3px;
    }
    
    .selected-item-info {
        flex: 1;
    }
    
    .selected-item-name {
        font-weight: 500;
        color: #495057;
        font-size: 13px;
    }
    
    .selected-item-groups {
        font-size: 11px;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .selected-item-details {
        display: flex;
        flex-direction: column;
        gap: 3px;
        margin-top: 8px;
    }
    
    .selected-item-details small {
        font-size: 11px;
        color: #495057;
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        border-left: 3px solid #007bff;
    }
    
    .selected-item-details small strong {
        color: #007bff;
    }
    
    .items-count {
        background: #007bff;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        margin-right: 8px;
        font-weight: bold;
    }
    
    .selected-item-price {
        font-weight: bold;
        color: #007bff;
        margin: 0 10px;
    }
    
    .remove-item-btn {
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 3px;
        padding: 5px 8px;
        cursor: pointer;
        font-size: 12px;
    }
    
    .remove-item-btn:hover {
        background: #c82333;
    }
`;
document.head.appendChild(checkboxStyles);

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('supplierModal');
    if (event.target == modal) {
        closeSupplierModal();
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing suppliers page');
    
    // Make sure all functions are available
    if (typeof loadItems === 'function') {
        console.log('loadItems function is available');
    } else {
        console.error('loadItems function is not available');
    }
    
    if (typeof renderItemsTree === 'function') {
        console.log('renderItemsTree function is available');
    } else {
        console.error('renderItemsTree function is not available');
    }
    
    // Test if items containers exist
    const itemsTree = document.getElementById('itemsTree');
    const selectedItemsList = document.getElementById('selectedItemsList');
    
    if (itemsTree) {
        console.log('Items tree container found');
    } else {
        console.error('Items tree container not found');
    }
    
    if (selectedItemsList) {
        console.log('Selected items list container found');
    } else {
        console.error('Selected items list container not found');
    }
});

// Add manual trigger for debugging
function debugItems() {
    console.log('Debug items called');
    console.log('Available items:', availableItems);
    console.log('Selected items:', selectedItems);
    console.log('Current supplier ID:', currentSupplierID);
    
    if (availableItems.length > 0) {
        renderItemsTree();
    } else {
        console.log('No items available, trying to load...');
        loadItems();
    }
}
</script>

<script src="JS/suppliers.js"></script>

<?php include('headAndFooter/footer.php'); ?>