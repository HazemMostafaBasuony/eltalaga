<!DOCTYPE html>
<html>
<title>fresh</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="css/materialize.min.css">
<script src="js/jquery-3.1.1.min.js"></script>
    <script src="js/materialize.min.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/w3pro.css">
<link rel="stylesheet" href="css/w3-theme-teal.css">


<header class="w3-top w3-bar w3-theme" style="opacity: 0.8;">

  <h1 class="w3-bar-item">point</h1>
</header>

<body>


<!--///***********************************-->
<br><br><br><br>
<div class="container center">


<div class="row">
	 <form class="col s12" action="hmb/addUser.php" method="post">
		 <div class="row">
			 <div class="input-field col s6">
				 <i class="material-icons prefix">account_circle</i>
				 <input id="icon_prefix" name="FirstName"type="text" class="validate">
				 <label for="icon_prefix">First Name</label>
			 </div>
			 <div class="input-field col s6">
				 <i class="material-icons prefix">phone</i>
				 <input id="icon_telephone" type="tel" class="validate" name="Telephone">
				 <label for="icon_telephone">Telephone</label>
			 </div>
		 </div>
		 <div class="row">
        <div class="input-field col s12">
          <input id="password" type="password" class="validate" name="password">
          <label for="password">Password</label>
        </div>
      </div>
			<div class="row">
        <div class="input-field col s12">
          <input id="password2" name="password2" type="password" class="validate">
          <label for="password2">re Password</label>
        </div>
      </div>


  <input class="btn waves-effect waves-light" type="submit" name="submit" value="sign up">

	 </form>
 </div>
</div>

<?php include('headAndFooter/footer.php'); ?>
