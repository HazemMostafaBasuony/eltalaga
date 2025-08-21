<?php include('../headAndFooter/head.php'); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأصناف - بطاقة الأصناف</title>
    <link rel="stylesheet" href="../css/style_salse.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .items-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .page-header h1 {
            color: var(--light);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .page-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }

        .controls-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: none;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--light);
            font-size: 1.1rem;
            backdrop-filter: blur(10px);
        }

        .search-box input:focus {
            outline: none;
            box-shadow: 0 0 0 2px var(--secondary);
        }

        .search-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .add-btn {
            background: var(--success);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
        }

        .add-btn:hover {
            background: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .items-table-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            backdrop-filter: blur(10px);
            overflow-x: auto;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background: var(--primary);
            color: var(--light);
            padding: 15px 10px;
            text-align: center;
            font-weight: 600;
            font-size: 1rem;
            border-bottom: 2px solid var(--secondary);
        }

        .items-table td {
            padding: 15px 10px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--light);
            font-size: 0.95rem;
        }

        .items-table tr:hover {
            background: rgba(52, 152, 219, 0.1);
        }

        .items-table tbody tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit {
            background: var(--warning);
            color: white;
        }

        .btn-edit:hover {
            background: #e67e22;
        }

        .btn-delete {
            background: var(--accent);
            color: white;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--secondary);
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .loading {
            text-align: center;
            padding: 50px;
            color: var(--light);
        }

        .loading i {
            font-size: 3rem;
            animation: spin 1s linear infinite;
            color: var(--secondary);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .modal-header {
            background: var(--primary);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: var(--accent);
        }

        .modal-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--light);
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--light);
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .modal-footer {
            padding: 20px 30px;
            text-align: left;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-primary {
            background: var(--secondary);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 10px;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-secondary {
            background: var(--gray);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        @media (max-width: 768px) {
            .controls-section {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
                margin-bottom: 15px;
            }

            .items-table-container {
                padding: 10px;
            }

            .items-table {
                font-size: 0.8rem;
            }

            .items-table th,
            .items-table td {
                padding: 10px 5px;
            }

            .modal-content {
                width: 95%;
                margin: 2% auto;
            }

            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="items-container">
        <div class="page-header">
            <h1><i class="fas fa-box"></i> إدارة الأصناف</h1>
            <p>إضافة وتعديل وحذف الأصناف في النظام</p>
        </div>

        <div class="controls-section">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="البحث عن صنف...">
                <i class="fas fa-search"></i>
            </div>
            <button class="add-btn" onclick="openAddModal()">
                <i class="fas fa-plus"></i>
                إضافة صنف جديد
            </button>
        </div>

        <div class="items-table-container">
            <div id="loadingDiv" class="loading">
                <i class="fas fa-spinner"></i>
                <p>جاري تحميل البيانات...</p>
            </div>
            <div id="itemsTableDiv" style="display: none;">
                <table class="items-table">
                    <thead>
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

    <!-- Modal إضافة/تعديل صنف -->
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">إضافة صنف جديد</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="itemForm">
                    <input type="hidden" id="itemID" name="itemID">
                    
                    <div class="form-group">
                        <label for="itemName">اسم الصنف <span style="color: red;">*</span></label>
                        <input type="text" id="itemName" name="itemName" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="unitL">الوحدة الكبيرة <span style="color: red;">*</span></label>
                            <input type="text" id="unitL" name="unitL" required>
                        </div>
                        <div class="form-group">
                            <label for="fL2M">معامل التحويل ك→م <span style="color: red;">*</span></label>
                            <input type="number" id="fL2M" name="fL2M" step="0.01" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="unitM">الوحدة المتوسطة <span style="color: red;">*</span></label>
                            <input type="text" id="unitM" name="unitM" required>
                        </div>
                        <div class="form-group">
                            <label for="fM2S">معامل التحويل م→ص <span style="color: red;">*</span></label>
                            <input type="number" id="fM2S" name="fM2S" step="0.01" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="unitS">الوحدة الصغيرة <span style="color: red;">*</span></label>
                        <input type="text" id="unitS" name="unitS" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="mainGroup">المجموعة الرئيسية <span style="color: red;">*</span></label>
                            <input type="text" id="mainGroup" name="mainGroup" required>
                        </div>
                        <div class="form-group">
                            <label for="subGroup">المجموعة الفرعية <span style="color: red;">*</span></label>
                            <input type="text" id="subGroup" name="subGroup" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="stok">المخزون <span style="color: red;">*</span></label>
                            <input type="number" id="stok" name="stok" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="profit">الربح <span style="color: red;">*</span></label>
                            <input type="number" id="profit" name="profit" step="0.01" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal()">إلغاء</button>
                <button type="button" class="btn-primary" onclick="saveItem()">حفظ</button>
            </div>
        </div>
    </div>

    <script>
        let currentEditId = null;

        // تحميل البيانات عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            loadItems();
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
                }
            };
            xhr.open("GET", "getItems.php?search=" + encodeURIComponent(search), true);
            xhr.send();
        }

        // فتح نافذة الإضافة
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'إضافة صنف جديد';
            document.getElementById('itemForm').reset();
            document.getElementById('itemID').value = '';
            currentEditId = null;
            document.getElementById('itemModal').style.display = 'block';
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
                        document.getElementById('stok').value = item.data.stok;
                        document.getElementById('profit').value = item.data.profit;
                        
                        document.getElementById('itemModal').style.display = 'block';
                    } else {
                        alert('خطأ في تحميل بيانات الصنف');
                    }
                }
            };
            xhr.open("GET", "getItem.php?id=" + id, true);
            xhr.send();
        }

        // حفظ الصنف
        function saveItem() {
            var form = document.getElementById('itemForm');
            var formData = new FormData(form);
            
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert(response.message);
                        closeModal();
                        loadItems();
                    } else {
                        alert('خطأ: ' + response.message);
                    }
                }
            };
            
            var action = currentEditId ? 'update' : 'add';
            xhr.open("POST", "saveItem.php?action=" + action, true);
            xhr.send(formData);
        }

        // حذف الصنف
        function deleteItem(id) {
            if (confirm('هل أنت متأكد من حذف هذا الصنف؟')) {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert(response.message);
                            loadItems();
                        } else {
                            alert('خطأ: ' + response.message);
                        }
                    }
                };
                xhr.open("GET", "deleteItem.php?id=" + id, true);
                xhr.send();
            }
        }

        // إغلاق النافذة
        function closeModal() {
            document.getElementById('itemModal').style.display = 'none';
        }

        // إغلاق النافذة عند الضغط خارجها
        window.onclick = function(event) {
            var modal = document.getElementById('itemModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
<?php include('../headAndFooter/footer.php'); ?>