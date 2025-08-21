<!DOCTYPE html>
<html>
<title>Point</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="../css/materialize.min.css">
<script src="../js/jquery-3.1.1.min.js"></script>
<script src="../js/materialize.min.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/w3pro.css">
<link rel="stylesheet" href="../css/w3-theme-teal.css">
<link rel="icon" type="image/x-icon" href="assets/images/logo5.png">

<header class="w3-top w3-bar w3-theme" style="opacity: 0.8;">

  <img class="w3-bar-item w3-square" src="../assets/images/logo7.png" alt="avatar" style="width:15%">
</header>

<body>


  <!--///***********************************-->
  <br><br><br><br>


  <?php
  session_start();
  // $currentShiftId = isset($_GET['idShift']) ? $_GET['idShift'] : 0;

  //   إذا كان المستخدم يسجل الدخول الآن، ستأخذ القيم من POST.
  // إذا كان قد سجل الدخول سابقًا (موجودة في الجلسة)، ستأخذها من SESSION.
  // إذا لم تكن موجودة في أي مكان، ستكون فارغة.

  $pass = isset($_POST['password']) ? $_POST['password'] : (isset($_SESSION['password']) ? $_SESSION['password'] : '');
  $userName = isset($_POST['userName']) ? $_POST['userName'] : (isset($_SESSION['userName']) ? $_SESSION['userName'] : '');


  //echo $userName ."<br>" .$pass;
  include_once('conn.php');
  $sel = "SELECT * FROM `users` WHERE `userName`='$userName' AND `password` = '$pass'";
  //echo $sel;
  $run = mysqli_query($conn, $sel);
  if (mysqli_num_rows($run) > 0) {
    $row = mysqli_fetch_assoc($run);

    $_SESSION['userName'] = $userName;
    $_SESSION['phone'] = $row['phone'];
    $_SESSION['password'] = $pass;
    $_SESSION['userId'] = $row['id'];
    $_SESSION['Permission'] = $row['Permission'];

    $_SESSION['branch'] = $row['branch'];
    $_SESSION['userImage'] = $row['userImage'] ? $row['userImage'] : 'default_user.jpg'; // Use default image if not set




    //SELECT `id`, `serialShift`, `date`,
    //  `userName`, `branch`, `state` FROM `serialshift`








    if ($_SESSION['Permission'] === 'admin') {


      //زر اساسي للتحكم
      echo '<br><a href="../dashBoard.php">
          <div class="w3-cell-row " style="opacity: 0.7;">
            <div class="w3-cell w3-container waves-effect waves-light btn">
              <h3>متابعة التحكم فى البرنامج</h3>
              <p >start with us </p>
            </div>
          </div>
          </a>

          ';



      $sql = "select * from `serialshift` WHERE  `state` = 1 ";
      $result = mysqli_query($conn, $sql);

      if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          $alluserName = $row['userName'];
          $allidShift = $row['id'];
          $allserialShift = $row['serial'];
          echo "<h3>يوجد دوام مفتوح بالفعل من قبل المستخدم: $alluserName</h3>";
          echo "<p>تاريخ الدوام: " . $row['date'] . "</p>";
          echo '<br><a href="../report.php?idShift=' . $allidShift . '">
                <div class="w3-cell-row " style="opacity: 0.7;">
                  <div class="w3-cell w3-container waves-effect waves-light btn">
                    <h3>اغلاق</h3>
                    <p >  ' . $alluserName . ' اغلاق الدوام</p>

                    <p> 
                  </div>
                </div>
                </a>
                ';
        }
      }
    } elseif ($_SESSION['Permission'] === 'salesmane') {
      $sql = "select * from `serialshift` WHERE `userName` = '$userName' AND `state` = 1 ORDER BY id DESC LIMIT 1";
      $result = mysqli_query($conn, $sql);
      if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $_SESSION['idShift'] = $row['id']; // Store the shift ID in the session
        $_SESSION['serialShift'] = $row['serial']; // Store the serial shift in the session
        // User has an active shift
        echo "<h3>يوجد لديك دوام مفتوح بالفعل</h3>";
        echo '<p> ' . $row['date'] . ' </p>';
        echo '<br><a href="../salesmane_HomePage.php">
            <div class="w3-cell-row " style="opacity: 0.7;">
              <div class="w3-cell w3-container waves-effect waves-light btn">
                <h3>متابعة البيع</h3>
                <p >start with us </p>
              </div>
            </div>
            </a>
  
            ';
      } else {
        // User does not have an active shift
        echo "<div id='openShift'>";
        echo "<h3>لا يوجد دوام مفتوح</h3>";
        echo "<p>عهدة الخازنه</p>";
        echo '<input type="number" name="cash" id="cash" placeholder="ادخل عهدة الخازنه" required>';
        echo '<p> الرجاء ادخال عهدة الخازنه لفتح الدوام</p>';
        echo '<br>
            <div onclick="openShift(\'salesmane\')" class="w3-cell-row " style="opacity: 0.7;">
              <div class="w3-cell w3-container waves-effect waves-light btn">
                <h3>بسم الله الرحمن الرحيم</h3>
                <p >افتح دوام جديد </p>
              </div>
            </div>
          </div>
            ';
      }
    } else { // If the user is not an admin, check for their active shift

      $sql = "select * from `serialshift` WHERE `userName` = '$userName' AND `state` = 1 ORDER BY id DESC LIMIT 1";
      $result = mysqli_query($conn, $sql);
      if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $_SESSION['idShift'] = $row['id']; // Store the shift ID in the session
        $_SESSION['serialShift'] = $row['serial']; // Store the serial shift in the session
        // User has an active shift
        echo "<h3>يوجد لديك دوام مفتوح بالفعل</h3>";
        echo '<p> ' . $row['date'] . ' </p>';
        echo '<br><a href="../index.php">
          <div class="w3-cell-row " style="opacity: 0.7;">
            <div class="w3-cell w3-container waves-effect waves-light btn">
              <h3>متابعة البيع</h3>
              <p >start with us </p>
            </div>
          </div>
          </a>

          ';
      } else {
        // User does not have an active shift
        echo "<div id='openShift'>";
        echo "<h3>لا يوجد دوام مفتوح</h3>";
        echo "<p>عهدة الخازنه</p>";
        echo '<input type="number" name="cash" id="cash" placeholder="ادخل عهدة الخازنه" required>';
        echo '<p> الرجاء ادخال عهدة الخازنه لفتح الدوام</p>';
        echo '<br>
          <div onclick="openShift(\'user\')" class="w3-cell-row " style="opacity: 0.7;">
            <div class="w3-cell w3-container waves-effect waves-light btn">
              <h3>بسم الله الرحمن الرحيم</h3>
              <p >افتح دوام جديد </p>
            </div>
          </div>
        </div>
          ';
      }
    }

    echo "welcom mr : $userName";
    echo "<br> yor phone is : " . $_SESSION['phone'];
  } else {
    echo '
  <br><a href="../signIn.php">
    <div class="w3-cell-row " style="opacity: 0.7;">
      <div class="w3-cell w3-container waves-effect waves-light btn w3-red">
        <h3>خطأ في اسم المستخدم أو كلمة المرور</h3>
        <p >تسجيل الدخول</p>
      </div>
    </div>
    </a>
  ';
    echo "<br>خطأ في اسم المستخدم أو كلمة المرور";
    echo "<br> الرجاء التأكد من صحة البيانات المدخلة";
  }







  ?>

  <script>
    function openShift(permission) {
      var cash = document.getElementById('cash').value;
      if (cash === '') {
        alert('الرجاء إدخال عهدة الخازنة');
        return;
      } else {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
          if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById("openShift").innerHTML = xhr.responseText;
            setTimeout(function() {
              if (permission == 'user') {
                window.location.href = '../index.php';
              } else {
                window.location.href = '../HomePageSalesmane.php';
              }
            }, 2000);
          }
        };
        xhr.open("GET", "../sales/openShift.php?userName=" + encodeURIComponent("<?php echo $userName; ?>") + "&price=" + encodeURIComponent(cash), true);
        xhr.send();
      }
    }
  </script>