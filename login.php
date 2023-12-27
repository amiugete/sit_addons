<?php
/*
Script Name: ldapLogin.php
Author: Roberto Marzochhi modifying a file of Riontino Raffaele
Author URI: https://www.lelezapp.it/
Description: example script for ldap authentication in PHP
Version: 1.0
*/

session_start();


if(!isset($_COOKIE['origine'])) {
	//echo "Cookie named origine is not set!";
	$_COOKIE['origine']=$_SESSION['origine'];
	$origine=$_COOKIE["origine"];
	if ($origine==""){
		$origine="piazzola.php";
	}
} else {
	//echo "Cookie un is set!<br>";
	//echo "Value is: " . $_COOKIE['origine'];
}
//echo $origine."<br>";
//exit;


if ($_SESSION['test']==1) {
    require_once ('./conn_test.php');
} else {
    require_once ('./conn.php');
}
require_once("req.php");


$successMessage = "";
$errorMessage = "";

// connect to ldap server
/*$ldapConnection = ldap_connect($ldapHost, $ldapPort) 
	or die("Could not connect to Ldap server.");*/
$ldapConnection = ldap_connect($ldapHost) 
	or die("Could not connect to Ldap server.");


//echo  $ldapHost. " - LDAP connection " . $ldapConnection."<br>";

//echo(ldap_error($ldapConnection)."<br>");

if (isset($_POST["ldapLogin"])){

	if ($ldapConnection) {
		
		if (isset($_POST["user"]) && $_POST["user"] != ""){ 
			$ldapUser = trim($_POST["user"]);
		} else{ 
			$errorMessage = "Invalid User value!!";
		}
		
		if (isset($_POST["password"]) && $_POST["password"] != "") {
			$ldapPassword = trim($_POST["password"]);
			//echo $ldapPassword;
			//exit;
		} else {
			$errorMessage = "Invalid Password value!!";
		}

		if ($errorMessage == ""){
			// binding to ldap server
			//ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3) or die('Unable to set LDAP protocol version');
			//ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);
			//$ldapbind = @ldap_bind($ldapConnection, $ldapUser . $ldapDomain, $ldapPassword);
			$ldapbind = ldap_bind($ldapConnection, 'DSI\\'.$ldapUser, $ldapPassword);
			// verify binding
			if ($ldapbind){
				ldap_close($ldapConnection);	// close ldap connection
				$successMessage = "Login done correctly with user ".$ldapUser."!!";
				$_SESSION['username']=$ldapUser;
				setcookie('un', $ldapUser, time() + (86400 * 7), "/"); // 86400 = 1 day
				//header("Location: ./piazzola.php");
				//header("Location:./piazzola.php");
				echo '<script>window.location.replace("./'.$origine.'")</script>';
				exit;
			} else{  
				$errorMessage = "Credenziali errate per l'utente DSI\\".$ldapUser." Controlla il nome utente e/o la password inserita!";
			}
		}
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Login</title>
	</head>
	<body data-rsssl=1 data-rsssl=1>
	
    <!--div class="banner"> <div id="banner-image"></div> </div-->

      <div class="container">
		<?php		
			if ($errorMessage != "") echo "<h3 style='color:red;'>$errorMessage</h3>";
			//if ($successMessage != "") echo "<h3 style='color:blue;'>$successMessage</h3>";
		?>
		<h3 style="color:orange">Inserisci credenziali AMIU (utente e password con cui accedi al PC) </h3>
		<form action="" method="post" style="display:inline-block;">
        <div class="row g-3 align-items-center">
			<div class="form-group">
				<label for="user">Utente</label>
				<input type="text" class="form-control" name="user" value="" maxlength="50">
			</div>
			
			<div class="form-group">
				<label for="password">Password</label>
				<input type="password" class="form-control" name="password" id="password" value="" maxlength="50">  <i class="bi bi-eye-slash" 
						id="togglePassword"></i>
			</div>
			<br>
			<div class="form-group">
				<input type="submit"  class="btn btn-primary" name="ldapLogin" value="Login">
			</div>
		</div>
		</form>

		<script>
        const togglePassword = document
            .querySelector('#togglePassword');
  
        const password = document.querySelector('#password');
  
        togglePassword.addEventListener('click', () => {
  
            // Toggle the type attribute using
            // getAttribure() method
            const type = password
                .getAttribute('type') === 'password' ?
                'text' : 'password';
                  
            password.setAttribute('type', type);
  
            // Toggle the eye and bi-eye icon
            this.classList.toggle('bi-eye');
        });
    </script>


</div>
        <?php
        require_once('req_bottom.php');
        require('./footer.php');
        ?>

    </body>
</html>