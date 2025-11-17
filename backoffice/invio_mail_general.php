<?php
//****************************************************************************
//			Invio mail
//****************************************************************************



// $query="SELECT mail FROM users.t_mail_incarichi WHERE cod='".$uo."';";
// $result=pg_query($conn, $query);
// $mails=array();
// while($r = pg_fetch_assoc($result)) {
//   array_push($mails,$r['mail']);
// }



//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';


//echo "<br>OK 1<br>";
//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');
//require '../../vendor/autoload.php';
//Create a new PHPMailer instance
$mail = new PHPMailer;

//echo "<br>OK 1<br>";
//Tell PHPMailer to use SMTP
$mail->isSMTP();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 0;
//Set the hostname of the mail server

// host and port on the file credenziali_mail.php
require('./credenziali_mail.php');


// commentato perchè definito in invio_mail_rutt.php
//Set who the message is to be sent from
//$mail->setFrom('no-reply@amiu.genova.it', 'No Reply');




//Set an alternative reply-to address
$mail->addReplyTo('no-reply@amiu.genova.it', 'No Reply');
//Set who the message is to be sent to
?>