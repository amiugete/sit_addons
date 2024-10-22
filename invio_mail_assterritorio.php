<?php


session_start();




if ($_SESSION['test']==1) {
    require_once ('./conn_test.php');
} else {
    require_once ('./conn.php');
}

require_once('./credenziali_mail.php');

//echo $_SESSION['user'];



// cerco la mail dell'utente

$query_mail='select email from util.sys_users su where "name" = $1 ';
$result_m = pg_prepare($conn, "my_query_mail", $query_mail);
$result_m = pg_execute($conn, "my_query_mail", array($_SESSION['username']));

$status1= pg_result_status($result1);
//echo "Status1=".$status1."<br>";
    
while($rm = pg_fetch_assoc($result_m)) {
    $mail_utente = $rm['email'];
}

$id_piazzola=$_POST['id_piazzola'];

echo $id_piazzola."<br>";


$testo_mail=$_POST['testo_mail'];

echo $testo_mail."<br>";



//****************************************************************************
//			Invio mail
//****************************************************************************

require('invio_mail_general.php');

/*$query="SELECT mail, id_telegram FROM users.t_mail_incarichi WHERE cod=$1;";
$result = pg_prepare($conn, "myquery0", $query);
$result = pg_execute($conn, "myquery0", array($uo));
$mails=array();
$telegram=array();
$messaggio="\xE2\x9C\x89 Messaggio inviato da Protezione Civile Genova circa l'incarico assegnato: ".$note."";
while($r = pg_fetch_assoc($result)) {
  array_push($mails,$r['mail']);
  array_push($telegram,$r['id_telegram']);
  //sendMessage($r['id_telegram'], $messaggio , $token);
}
*/
echo "fino a qua";
// In questo momento il pezzo sopra non serve.. più semplice indirizzo fisso
$mails=array('assterritorio@amiu.genova.it', 'roberto.marzocchi@amiu.genova.it',  $mail_utente);


while (list ($key, $val) = each ($mails)) {
  $mail->AddAddress($val);
}

echo "fino a qua 2";
//Set the subject line
$mail->Subject = 'Messaggio inviato dal territorio attraverso l\'applicativo per il passaggio al bilaterale';
//$mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$body =  'Piazzola: '.$id_piazzola.'<br><br>'.$testo_mail.'
    <br> <br> '.$_SESSION['username'].'

    <br> <br> '.$titolo_app.'';
  
require('./informativa_privacy_mail.php');

$mail-> Body=$body ;

//$mail->Body =  'Corpo del messaggio';
//$mail->msgHTML(file_get_contents('E\' arrivato un nuovo incarico da parte del Comune di Genova. Visualizza lo stato dell\'incarico al seguente link e aggiornalo quanto prima. <br> Ti chiediamo di non rispondere a questa mail'), __DIR__);
//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';
//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');
//send the message, check for errors
//echo "<br>OK 2<br>";
if (!$mail->send()) {
    echo "<h3>Problema nell'invio della mail: " . $mail->ErrorInfo;
	?>
	<script> //alert(<?php echo "Problema nell'invio della mail: " . $mail->ErrorInfo;?>) </script>
	<?php
	//echo '<br>La comunicazione è stata correttamente inserita a sistema, ma si è riscontrato un problema nell\'invio della mail.';
	echo '<div style="text-align: center;"><img src="../../img/no_mail_com.png" width="75%" alt=""></div>';
	echo '<br>Entro 10" verrai re-indirizzato alla pagina precedente, clicca al seguente ';
	echo '<a href="./piazzola.php?piazzola='.$id_piazzola.'">link</a> per saltare l\'attesa.</h3>' ;
	//sleep(30);
    header("refresh:10;url=./piazzola.php?piazzola=".$id_piazzola."");
} else {
    echo "Message sent!";
	header("location: ./piazzola.php?piazzola=".$id_piazzola);
}
//exit;
//header("location: ../dettagli_incarico.php?id=".$id);


?>


?>