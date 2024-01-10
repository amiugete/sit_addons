<?php

function redirect($url)
{
    $string = '<script type="text/javascript">';
    $string .= 'window.location = "' . $url . '"';
    $string .= '</script>';

    //echo $string;
}


session_start();

// definisco la variabile lifetime
$lifetime=86400;
session_set_cookie_params($lifetime);
/*if ($_GET['jwt']){
  setcookie("tokenCookie", $_GET['jwt'], time() + ($lifetime * 7));
}*/

// provo a vedere se c'è già il nome utente salvato
if ($_GET['jwt']){

  // unset cookies che ricreo
  if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-1000);
        setcookie($name, '', time()-1000, '/');
    }
}
  //echo "Caso 1 Cookie named un is not set!<br>";
  // se non ho il nome provo con il token
  $token0=$_GET['jwt'];

  if($token0){
    //echo "Caso 1 A <br>". $token0;
    //set the duration to 0, so that cookie duration will end only when users browser is close
    setcookie("tokenCookie", $token0, time() + ($lifetime * 7));
    //echo 'cookie tokenCookie =' .$_COOKIE["tokenCookie"]."<br>";
    $token=$token0;
  } else {
    //echo $_COOKIE['tokenCookie'];
    $token=$_COOKIE['tokenCookie'];
  }
  //echo $token . "<br><br>";

  //echo $secret_pwd ."ok 0<br><br>";
    if (!$_SESSION['username']){
    if ($token){
      $decoded1=json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));
      foreach($decoded1 as $key => $value)
      {
        //echo $key." is ". $value . "<br>";
        if ($key=='userId') {
              $userId = (int)$value;
        }
        if ($key=='name') {
          //echo "sono qua<br>";
          $_SESSION['username'] = $value;
          setcookie($name, '', time()-1000);
          setcookie("un", $value, time() + (86400 * 7)); // 86400 = 1 day
          //$_COOKIE["un"]=$_SESSION['username'];
          $_SESSION['start'] = time(); // Taking now logged in time.
          // Ending a session in 8 hours from the starting time.
          $_SESSION['expire'] = $_SESSION['start'] + (8* 60 * 60);
        }
        if ($key=='userId') {
          $userId = (int)$value;
        }
        if ($key=='exp') {
              $exp = (int)$value;
              if (time()>$exp){
                  die ('Token di autorizzazione SIT scaduto <br><br><a href="./login.php" class="btn btn-info"> Vai al login </a>');
              }
        }
      }
    }
  } else {
    // se c'è lo username setto i cookies
    setcookie("un", $_SESSION['username'], time() + (86400 * 7)); // 86400 = 1 day
  }

  //echo 'Now: '. time()."<br><br>";
  //echo 'Exp: '.$exp ."<br><br>";
  //echo 'userId: '.$userId ."<br><br>";
} else if ( $_SESSION['username']){
  // sessione aperta
  //echo "Caso 2<br>";
  $_SESSION['username']=$_SESSION['username'];
  $_SESSION['start'] = time(); // Taking now logged in time.
  // Ending a session in 8 hours from the starting time.
  $_SESSION['expire'] = $_SESSION['start'] + (8* 60 * 60);
  setcookie("un", $_SESSION['username'], time() + (86400 * 7)); // 86400 = 1 day
  if (time()>$_SESSION['expire'] AND  basename($_SERVER['PHP_SELF'])!='login.php'){
    die ('Token di autorizzazione scaduto <br><br><a href="./login.php" class="btn btn-info"> Vai al login </a>');
  }
} else if ( $_COOKIE['un']) {
  //echo "Cookie un is set!<br>";
  //echo "Value is: " . $_COOKIE['un'];
  $_SESSION['username']=$_COOKIE['un'];
  $_SESSION['start'] = time(); // Taking now logged in time.
  // Ending a session in 8 hours from the starting time.
  $_SESSION['expire'] = $_SESSION['start'] + (8* 60 * 60);
  if (time()>$_SESSION['expire'] AND  basename($_SERVER['PHP_SELF'])!='login.php'){
    die ('Token di autorizzazione scaduto <br><br><a href="./login.php" class="btn btn-info"> Vai al login </a>');
  }
} else {
  die ('Token di autorizzazione scaduto <br><br><a href="./login.php" class="btn btn-info"> Vai al login </a>');
}



//$id=pg_escape_string($_GET['id']);
//$user = $_SERVER['AUTH_USER'];
//$username = $_SERVER['PHP_AUTH_USER'];


/*if (!$_SESSION['username'] AND  basename($_SERVER['PHP_SELF'])!='login.php'){
  //echo 'NON VA BENE';
  $_SESSION['origine']=basename($_SERVER['PHP_SELF']);
  $_COOKIE['origine']=basename($_SERVER['PHP_SELF']);
  //echo $_SESSION['expire'] ."<br>";
  die ('Sessione scaduta <br><br><a href="./login.php" class="btn btn-info"> Vai al login </a>');
  //redirect('login.php');
  //header("location: ./login.php");
  //exit;
}*/

//echo $_SESSION['expire'] ."<br>";
?>