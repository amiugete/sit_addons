<?php

function redirect($url)
{
    $string = '<script type="text/javascript">';
    $string .= 'window.location = "' . $url . '"';
    $string .= '</script>';

    echo $string;
}


session_start();

// definisco la variabile lifetime
$lifetime=86400;
session_set_cookie_params($lifetime);


// provo a vedere se c'è già il nome utente salvato
if(!isset($_COOKIE['un'])) {
  //echo "Cookie named un is not set!";
  // se non ho il nome provo con il token
  $token0=$_GET['jwt'];

  if($token0){
    //set the duration to 0, so that cookie duration will end only when users browser is close
    setcookie("tokenCookie", $token0, 0);
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
          $_SESSION['username'] = $value;
        }
        if ($key=='userId') {
          $userId = (int)$value;
        }
        if ($key=='exp') {
              $exp = (int)$value;
              if (time()>$exp){
                  die ('Token di autorizzazione scaduto <br><br><a href="./login.php" class="btn btn-info"> Vai al login </a>');
              }
        }
      }
    }
  } /*else {
    redirect('login.php');
    //header("location: ./login.php");
  }*/

  //echo 'Now: '. time()."<br><br>";
  //echo 'Exp: '.$exp ."<br><br>";
  //echo 'userId: '.$userId ."<br><br>";
} else {
  //echo "Cookie un is set!<br>";
  //echo "Value is: " . $_COOKIE['un'];
  $_SESSION['username']=$_COOKIE['un'];
}



//$id=pg_escape_string($_GET['id']);
//$user = $_SERVER['AUTH_USER'];
//$username = $_SERVER['PHP_AUTH_USER'];


if (!$_SESSION['username']){
  //echo 'NON VA BENE';
  $_SESSION['origine']=basename($_SERVER['PHP_SELF']);
  $_COOKIE['origine']=basename($_SERVER['PHP_SELF']);
  redirect('login.php');
  //header("location: ./login.php");
  //exit;
}

?>