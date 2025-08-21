<?php
session_start();
if (!isset($_SESSION['userName']) || $_SESSION['userName'] == "") {
  header("Location: signIn.php");
  exit;
}

$userName = $_SESSION['userName'];
$userId = isset($_SESSION['userId']) ? $_SESSION['userId'] : 0;
$Permission = isset($_SESSION['Permission']) ? $_SESSION['Permission'] : '';
$userImage = isset($_SESSION['userImage']) ? $_SESSION['userImage'] : 'default_user.png';
$branch = isset($_SESSION['branch']) ? $_SESSION['branch'] : ' ثلاجة الياس ';

?>

<?php include('hmb/conn.php'); ?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<title>Elias Store</title>

<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="css/bootstrap.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<!-- jQuery -->
<script src="js/jquery-3.1.1.min.js"></script>
<script src="js/jquery-3.4.1.min.js"></script>
<!-- W3.CSS (mantenido para compatibilidad) -->
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="css/w3pro.css">
<link rel="stylesheet" href="css/items.css?v=<?php echo time(); ?>">
<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="assets/images/logo5.png">
<!-- Tema Bootstrap personalizado -->
<link rel="stylesheet" href="css/bootstrap-theme.css?v=<?php echo time(); ?>">

<!-- Scripts adicionales -->
<script type="text/javascript" src="js/xlsx.full.min.js"></script>
<!-- Bootstrap JS Bundle (incluye Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Script de inicialización de Bootstrap -->
<script src="js/bootstrap-init.js"></script>
<!-- fas fa-box-open -->

<!-- superagent -->
 <!-- بديل ل fetch , xmlHttpRequest-->
<script src="https://cdn.jsdelivr.net/npm/superagent"></script>



<body >
<div class="w3-sidebar w3-bar-block w3-card w3-animate-left" style="display:none" id="mySidebar">
    <button class="w3-bar-item w3-button w3-large" onclick="w3_close()">إغلاق &times;</button>
    <header class="p-3 border-bottom">
      <div class="user-info">
        <img src="assets/images/<?php echo $userImage ?> " alt="صورة الموظف" style="width: 50px; height: 50px;">
        <div>
          <div class="fw-bold"><?php echo $userName ?></div>
          <div class="text-muted"><?php echo $Permission ?></div>
        </div>
      </div>
    </header> 
    <?php if ($Permission == 'user') {
      echo
        '
    <a class="w3-bar-item w3-button" href="index.php">
      <i class="fa fa-home me-2"></i> الصفحة الرئيسيه
    </a>
    <a class="w3-bar-item w3-button" href="itemsCard.php">
      <i class="fa fa-list-alt me-2"></i> كروت صنف المنتجات
    </a>
   
    <a class=" w3-bar-item w3-button" href="suppliers.php">
      <i class="fa fa-truck me-2"></i> الموردين
    </a>
    <a class="w3-bar-item w3-button" href="signIn.php">
      <i class="fa fa-sign-out me-2"></i> تسجيل خروج
    </a>
    ';
    }
   

if ($Permission == 'salesmane') {
      echo
        '
    <a class="w3-bar-item w3-button" href="salesmane_HomePage.php">
      <i class="fa fa-home me-2"></i> الصفحة الرئيسيه
    </a>

    <a class="w3-bar-item w3-button" href="signIn.php">
      <i class="fa fa-sign-out me-2"></i> تسجيل خروج
    </a>
    ';
   
    }


    if ($Permission == 'admin') {
      echo
        '
      <a class="w3-bar-item w3-button" href="invoices.php">
        <i class="fa fa-file-text-o me-2"></i> جميع الفواتير
      </a>
      <a class="w3-bar-item w3-button" href="#">
        <i class="fa fa-users me-2"></i> العاملين
      </a>
      <a class="w3-bar-item w3-button" href="inputItemsFromExcl.php">
        <i class="fa fa-plus-circle me-2"></i> إضافةالمنتجات من ملف Excell
      </a>
      <a class="w3-bar-item w3-button" href="#">
        <i class="fa fa-user-plus me-2"></i> اضافة مستخدم
      </a>
      <a class="w3-bar-item w3-button" href="report.php">
        <i class="fa fa-bar-chart me-2"></i> تقرير المبيعات
      </a>
      <a class="w3-bar-item w3-button" href="invoices.php">
        <i class="fa fa-file-text me-2"></i> الفواتير
      </a>
      <a class="w3-bar-item w3-button" href="dashboard.php">
        <i class="fa fa-cogs me-2"></i> التحكم فى النظام
      </a>
      <a class="w3-bar-item w3-button" href="signIn.php">
        <i class="fa fa-sign-out me-2"></i> تسجيل خروج
      </a>
      ';
    }
    ?>

  </div>

  <div id="main">

    <div class="container-fluid d-flex align-items-center">
            <button id="openNav" class="btn btn-light" onclick="w3_open()">
        <i class="fa fa-bars"></i>
  </button>

        <img style="height: 30px; width:15%;" class="w3-bar-item " src="assets/images/logo7.png" alt="avatar">
      
    </div>



    <div onclick="w3_close()">