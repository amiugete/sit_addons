<?php
session_start();

if ($_SESSION['test']==1) {
    require_once('./conn_test.php');
  } else {
    require_once('./conn.php');
  }
echo $_SESSION['test']."<br>";
$id_piazzola=$_POST['piazzola'];
echo $id_piazzola."<br>";

$check=0;
$res_ok=0;
// Get reference to uploaded image
$image_file = $_FILES["fileToUpload"];

// Exit if no file uploaded
if (!isset($image_file)) {
    die('No file uploaded.');
}



if(is_uploaded_file($_FILES['fileToUpload']['tmp_name'])) {

    //controllo che il file non superi i 100 KB (1 kilobyte = 1024 byte)
    /*if($_FILES['fileToUpload']['size']>102400)
        $messaggio.="Il file ha dimensioni che superano i 100 KB<br />";*/

    //recupero le informazioni sull'immagine
    list($width, $height, $type, $attr)=getimagesize($_FILES['fileToUpload']['tmp_name']);

    echo 'Tipo ' .$type. '<br>';

    //controllo che le dimensioni (in pixel) non superino 800x600
    /*if(($width>800) or ($height>600))
        $messaggio.="Il file non deve superare le dimensioni di 800x600<br />";*/

    //controllo che il file sia in uno dei formati GIF, JPG 2 o PNG 3
    if(($type!=2)){
        $messaggio.="Il file caricato deve essere un'immagine JPEG<br>";
        $check=1;
    }
    //controllo che non esista già un file con lo stesso nome
    /*if(file_exists('upload_img/'.$_FILES['fileToUpload']['name']))
        $messaggio.="Esiste già un file con lo stesso nome. Rinominare l'immagine prima di caricarla<br />";*/

    if($check==0){
    //salvo il file nella cartella di destinazione
        if(!move_uploaded_file($_FILES['fileToUpload']['tmp_name'], '/foto_SIT/sit/'.$id_piazzola.'.jpg')){
            $messaggio.="Errore imprevisto nel caricamento del file. Controllare i permessi della cartella di destinazione";
            $check=1;
            echo $messaggio."<br>";
        } else {
            $messaggio.="File caricato con successo";
            echo $messaggio."<br>";
            $update_foto="UPDATE elem.piazzole SET foto = 1 WHERE id_piazzola = $1 and foto = 0;";
            echo $update_foto;
            $result_p = pg_prepare($conn, "update_foto", $update_foto);
            echo "<br>".$res_ok;
            if (!pg_last_error($conn)){
                #$res_ok=0;
            } else {
                echo pg_last_error($conn);
                $res_ok= $res_ok+1;
            }
            $result_p = pg_execute($conn, "update_foto", array($id_piazzola));
            echo "<br>".$res_ok;
            if (!pg_last_error($conn)){
                #$res_ok=0;
            } else {
                echo pg_last_error($conn);
                $res_ok= $res_ok+1;
            }
            $statusp= pg_result_status($result_p);
            echo "<br>".$res_ok;
            if ($res_ok == 0) {
                header('Location: piazzola.php?piazzola='.$id_piazzola.'');
            } else {
                $messaggio.="Errore imprevisto nell'aggiornamento del SIT";
                $check=1;
                echo $messaggio."<br>";
            }
        }
        
        echo $messaggio;
        
    } else {
        // c'era un qualche errore
        echo $messaggio;
    }
} else {
    echo "Sono qua e non va bene <br>";
    $check=1;
}


?>