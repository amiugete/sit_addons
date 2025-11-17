<?php


session_start();





if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;

//require_once('./credenziali_mail.php');

//echo $_SESSION['user'];


// cerco la mail dell'utente

$query_mail='select email from util.sys_users su where "name" = $1 ';
$result_m = pg_prepare($conn, "my_query_mail", $query_mail);
if (pg_last_error($conn)){
    echo pg_last_error($conn);
    $res_ok=$res_ok+1;
}
$result_m = pg_execute($conn, "my_query_mail", array($_SESSION['username']));
if (pg_last_error($conn)){
    echo pg_last_error($conn);
    $res_ok=$res_ok+1;
}







$status1= pg_result_status($result_m);
//echo "Status1=".$status1."<br>";
    
while($rm = pg_fetch_assoc($result_m)) {
    $mail_utente = $rm['email'];
}

//echo $mail_utente."<br>";
$id_piazzola=$_POST['id_piazzola'];

//echo $id_piazzola."<br>";


$query_ut="select id_piazzola, concat(v.nome, ', ', p.numero_civico, ' - rif:', p.riferimento) as indirizzo, 
u.descrizione as ut, u.mail as mail_ut
from elem.piazzole p 
left join elem.aste a on a.id_asta = p.id_asta 
left join topo.vie v on v.id_via = a.id_via 
left join topo.ut u on u.id_ut = a.id_ut 
where p.id_piazzola = $1";
$result_u = pg_prepare($conn, "query_ut", $query_ut);
if (pg_last_error($conn)){
    echo pg_last_error($conn);
    $res_ok=$res_ok+1;
}
$result_u = pg_execute($conn, "query_ut", array(intval($id_piazzola)));
if (pg_last_error($conn)){
    echo pg_last_error($conn);
    $res_ok=$res_ok+1;
}

while($ru = pg_fetch_assoc($result_u)) {
    $riferimento_piazzola = $ru['indirizzo'];
    $ut=$ru['ut'];
    $mail_ut=$ru['mail_ut'];
}


if ($_SESSION['test']==1) {
    $testo_mail = "AMBIENTE DI TEST. Questa mail non arriva al RUTT, è solo un esempio. <br>
    In produzione arriverà invece a ".$mail_ut." <br><hr><br>";
} else {
    $testo_mail = '';
}

$testo_mail=$testo_mail. "Buongiorno ". $ut .",<br>" . $_POST['testo_mail'];

$testo_mail= $testo_mail."<br><br>Ricevi questo messaggio 
per conto dell'utente ".$_SESSION['username']." che sta effettuato un'ispezione di sovrariempimento
sulla piazzola ".$id_piazzola. " ".$riferimento_piazzola ;

//echo $testo_mail."<br>";


//exit();
//****************************************************************************
//			Invio mail
//****************************************************************************

require('./invio_mail_general.php');

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
//echo "fino a qua<br>";
// In questo momento il pezzo sopra non serve.. più semplice indirizzo fisso


if ($_SESSION['test']==1) {
    $interni='assterritorio@amiu.genova.it';
    $mails = array_map('trim', explode(',', $interni));
    $mails[] = $mail_utente;
}else{ 
    $interno='assterritorio@amiu.genova.it';
    $mails = array_map('trim', explode(',', $mail_ut));
    $mails[] = $mail_utente;
    $mails[] = $interno;
    //$mails=array('assterritorio@amiu.genova.it',  $mail_utente, $mail_ut);
}

//echo "fino a qua 1<br>";

/*while (list($key, $val) = each ($mails)) {
  $mail->AddAddress($val);
}*/

foreach ($mails as $val) {
    $mail->AddAddress($val);
    }

//echo "fino a qua 2";
//Set the subject line
$mail->setFrom($mail_utente, $_SESSION['username']);

$mail->Subject = 'Messaggio inviato da '. $_SESSION['username'] .' durante ispezione sovrariempimento ';
//$mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$body =  $testo_mail;
  
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


} else {
    
    echo 'Mail inviata correttamente al RUTT';
    
}
//exit;
//header("location: ../dettagli_incarico.php?id=".$id);


?>
