<?php
session_start();
session_unset();    // حذف جميع متغيرات الجلسة
session_destroy();  // إنهاء الجلسة تمامًا
?>

<!DOCTYPE html>
<html>
<title>Elias Store</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="css/materialize.min.css">
<script src="js/jquery-3.1.1.min.js"></script>
<script src="js/materialize.min.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/w3pro.css">
<link rel="stylesheet" href="css/w3-theme-teal.css">

<link rel="icon" type="image/x-icon" href="assets/images/logo5.png">

<header class="w3-top w3-bar w3-theme " style="opacity: 0.8; text-align:right; height:70px ;width:100%; background-color:#f9a825;">

  <img class="w3-bar-item w3-square" src="assets/images/logo7.png" alt="avatar" style="width:15%">


  <style>
    .mask1 {
      -webkit-mask-image: linear-gradient(black, transparent);
      mask-image: linear-gradient(black, transparent);
    }
  </style>
</header>

<body>


  <!--///***********************************-->
  <br><br><br><br>
  <div class="container center">
    <img class="w3-square w3-animate-zoom  mask1" src="assets/images/logo5.png" alt="avatar" style="width:20%">

    <div class="row container border">
      <form class="col s12" action="hmb/signIn.php" method="post">


        <div class="row ">
          <div class="input-field col s12">
            <!--<i class="material-icons prefix">account_circle</i>-->

            <input id="icon_prefix" name="userName" type="text" class="validate">
            <label for="icon_prefix">user Name or phone</label>
          </div>

        </div>
        <div class="row">
          <div class="input-field col s12">
            <input id="password" type="password" class="validate" name="password">
            <label for="password">Password</label>
          </div>
        </div>
        <input class="btn waves-effect waves-light" type="submit" name="submit" value="sign In" style="width:100%">

      </form>


    </div>
  </div>

  <?php include('headAndFooter/footer.php'); ?>