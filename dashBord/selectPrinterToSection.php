<?php
// الاتصال بقاعدة البيانات
include("../hmb/conn.php");

// معالجة تحديث قسم الطباعة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['groupItem'], $_POST['sectionPrint'])) {
    $groupItem = $_POST['groupItem'];
    $sectionPrint = $_POST['sectionPrint'];
    
    // تحديث جميع العناصر في نفس المجموعة
    $updateStmt = $conn->prepare("UPDATE `items` SET `sectionPrint` = ? WHERE `groupItem` = ?");
    $updateStmt->bind_param("ss", $sectionPrint, $groupItem);
    
    if ($updateStmt->execute()) {
        $successMessage = "تم تحديث قسم الطباعة للمجموعة '$groupItem' بنجاح!";
    } else {
        $errorMessage = "حدث خطأ أثناء التحديث: " . $conn->error;
    }
    $updateStmt->close();
}

// جلب جميع المجموعات الفريدة من قاعدة البيانات
$groupsQuery = $conn->query("SELECT DISTINCT `groupItem`, `sectionPrint` FROM `items`");
$groups = [];
while ($row = $groupsQuery->fetch_assoc()) {
    $groups[] = $row;
}

// جلب إحصائيات المجموعات
$statsQuery = $conn->query("SELECT 
    COUNT(DISTINCT groupItem) AS totalGroups,
    SUM(CASE WHEN sectionPrint IS NOT NULL AND sectionPrint != '' THEN 1 ELSE 0 END) AS updatedGroups,
    SUM(CASE WHEN sectionPrint IS NULL OR sectionPrint = '' THEN 1 ELSE 0 END) AS pendingGroups
FROM items");

$stats = $statsQuery->fetch_assoc();
$statsQuery->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة أقسام الطباعة للمجموعات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary: #2c7744;
            --primary-light: #5aaf70;
            --secondary: #f8f9fa;
            --text: #333;
            --light-gray: #f1f1f1;
            --border: #ddd;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--text);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-content h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .header-content p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .logo {
            font-size: 2.5rem;
            background-color: rgba(255, 255, 255, 0.2);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .search-box input:focus {
            border-color: var(--primary-light);
            outline: none;
            box-shadow: 0 0 0 3px rgba(92, 175, 112, 0.2);
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }
        
        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .stat-card {
            flex: 1;
            min-width: 200px;
            background-color: var(--secondary);
            border-radius: 10px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background-color: rgba(44, 119, 68, 0.15);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-info h3 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .groups-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 10px rgba(0, 0, 0, 0.05);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .groups-table th {
            background-color: #f8fafb;
            padding: 15px 20px;
            text-align: right;
            font-weight: 600;
            color: #444;
            border-bottom: 2px solid var(--border);
        }
        
        .groups-table td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
        }
        
        .groups-table tr:last-child td {
            border-bottom: none;
        }
        
        .groups-table tr:hover {
            background-color: #f8fafb;
        }
        
        .section-input {
            display: flex;
            gap: 10px;
        }
        
        .section-input input {
            width: 100px;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 1rem;
            text-align: center;
        }
        
        .section-input input:focus {
            border-color: var(--primary-light);
            outline: none;
            box-shadow: 0 0 0 2px rgba(92, 175, 112, 0.2);
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #236338;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: #333;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }
        
        .btn i {
            font-size: 0.9rem;
        }
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .actions .btn {
            padding: 12px 25px;
            font-size: 1rem;
        }
        
        .status-message {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-weight: 500;
            display: none;
        }
        
        .status-success {
            background-color: #d4edda;
            color: #155724;
            display: block;
        }
        
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            display: block;
        }
        
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
            display: block;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .pagination button {
            padding: 8px 15px;
            border: 1px solid var(--border);
            background-color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .pagination button.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
            
            .stat-card {
                min-width: 100%;
            }
            
            .groups-table {
                display: block;
                overflow-x: auto;
            }
            
            header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .section-input {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .section-input input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1><i class="fas fa-print"></i> إدارة أقسام الطباعة للمجموعات</h1>
                <p>قم بتحديث أرقام أقسام الطباعة للمجموعات المختلفة</p>
            </div>
            <div class="logo">
                <i class="fas fa-layer-group"></i>
            </div>
        </header>
        
        <div class="content">
            <?php if (isset($successMessage)): ?>
                <div class="status-message status-success">
                    <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                </div>
            <?php elseif (isset($errorMessage)): ?>
                <div class="status-message status-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <div class="filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="ابحث عن مجموعة...">
                </div>
            </div>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalGroups"><?php echo $stats['totalGroups']; ?></h3>
                        <p>المجموعات الكلية</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="updatedGroups"><?php echo $stats['updatedGroups']; ?></h3>
                        <p>مجموعات محدثة</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="pendingGroups"><?php echo $stats['pendingGroups']; ?></h3>
                        <p>مجموعات قيد الانتظار</p>
                    </div>
                </div>
            </div>
            
            <?php if (count($groups) > 0): ?>
            <table class="groups-table">
                <thead>
                    <tr>
                        <th>اسم المجموعة</th>
                        <th>قسم الطباعة الحالي</th>
                        <th>قسم الطباعة الجديد</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="groupsTableBody">
                    <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($group['groupItem']); ?></td>
                        <td>
                            <?php if (!empty($group['sectionPrint'])): ?>
                                <?php echo htmlspecialchars($group['sectionPrint']); ?>
                            <?php else: ?>
                                <span style="color: var(--danger);">غير محدد</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" class="section-form">
                                <div class="section-input">
                                    <input type="hidden" name="groupItem" value="<?php echo htmlspecialchars($group['groupItem']); ?>">
                                    <input type="number" min="1" max="10" 
                                           name="sectionPrint"
                                           value="<?php echo htmlspecialchars($group['sectionPrint']); ?>" 
                                           placeholder="أدخل رقم">
                                </div>
                        </td>
                        <td>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ
                            </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>لا توجد مجموعات</h3>
                <p>لم يتم العثور على أي مجموعات في قاعدة البيانات</p>
            </div>
            <?php endif; ?>
            
            <div class="actions">
                <button class="btn btn-warning" id="refreshBtn">
                    <i class="fas fa-sync-alt"></i> تحديث البيانات
                </button>
                <a href="../dashBoard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> العودة للقائمة الرئيسية
                </a>
            </div>
        </div>
    </div>

    <script>
        // البحث في الجدول
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#groupsTableBody tr');
            
            rows.forEach(row => {
                const groupName = row.querySelector('td:first-child').textContent.toLowerCase();
                if (groupName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // تحديث البيانات
        document.getElementById('refreshBtn').addEventListener('click', function() {
            location.reload();
        });
        
        // رسالة تأكيد عند الحفظ
        document.querySelectorAll('.section-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const groupItem = this.querySelector('input[name="groupItem"]').value;
                const sectionPrint = this.querySelector('input[name="sectionPrint"]').value;
                
                if (!sectionPrint) {
                    e.preventDefault();
                    alert('يرجى إدخال رقم قسم الطباعة');
                    return false;
                }
                
                return confirm(`هل أنت متأكد من تحديث قسم الطباعة للمجموعة "${groupItem}" إلى ${sectionPrint}؟`);
            });
        });
        
        // عرض رسالة النجاح لمدة 5 ثواني
        setTimeout(() => {
            const successMessage = document.querySelector('.status-success');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>