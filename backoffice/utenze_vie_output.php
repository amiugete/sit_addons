<?php

?>


<!DOCTYPE html>
<html lang="it">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Ricerca utenze - Risposta</title>
<!--?php 
require_once('../req.php');
?--> 

</head>

<body>


      <div class="container">


<?php 

if (isset($_POST)){

if ($_POST['submit']) {
     //Save File        

    //echo "sono qua<br>";
    $utenze = $_POST['ute-list'];
    //echo $utenze.'<br>';
    
    $zona = $_POST['zona'];
    //echo $zona.'<br>';

    
    $consegne=$_POST['consegne'];
    if ($consegne=='cons'){
      $cons=1; 
    } else {
      $cons=0;
    }
    
    //echo "<br>sono qua 2<br>";
    //exit;

    $file = fopen("./utenze_file/elenco_vie.txt","w+");
    //echo $file;
    $text = $_POST["lista_vie"];
    fwrite($file, $text);
    fclose($file);

    

    echo $text;
    //exit;
    //echo getcwd();

    //$comando='/usr/bin/python3 ../py_scripts/seleziona_utenze_vie.py -i /var/www/html/utenze_bko/elenco_vie.txt -m '.$mail.'  -p '. $zona.'  -c '. $cons.' > /dev/null 2>&1 &';
    $comando='/usr/bin/python3 ../py_scripts/seleziona_utenze_vie.py -i /var/www/html/utenze_bko/elenco_vie.txt -p '. $zona.' -u '.$utenze.' -c '. $cons;
    echo $comando;
    //exit();

    //echo '<br><br>';
    $output=null;
    $retval=null;
    
    exec($comando, $output, $retval);
    foreach($output as $key => $value)
    {
      echo $key." ".$value."<br>";
    }
    //echo 'RET= '. $retval .'<br>';
    //echo "Returned with status $retval and output:\n";
    //print_r($output);
    #exit();
    if ($retval == 0) {
        //echo 'OK<br>';
      $zipfile = trim(file_get_contents("/tmp/utenze_via/last_zip.txt"));
      echo $zipfile;
      #exit;
      if (file_exists($zipfile)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        $mime =  finfo_file($finfo, $zipfile);
        finfo_close($finfo);

        ob_clean();
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=" . basename($zipfile));
        header("Content-Length: " . filesize($zipfile));
        readfile($zipfile);
        

        //header("Content-Transfer-Encoding: binary");
        //header('Cache-Control: must-revalidate');
        //header('Pragma: public');
        //header('Expires: 0');
        //header("Pragma: public"); // required


        //stream file
        ob_get_clean();
        //echo file_get_contents($file_name);
        //readfile($file_name);
        ob_end_flush();
        //readfile($file_name);//important this line
        //unlink($file_name);

        //die();
        http_response_code(200);
    } else {
        echo "Errore: file ZIP non trovato ($zipfile)";
    }
   
      

    } else {
      echo "KO";
      echo $comando;
      echo "C'è un problema con l'invio dei dati ti invitiamo a contattare il gruppo GETE via mail (assterritorio@amiu.genova.it) 
            o telefonicamente al 010 55 84496 ";
    }
/*
    $zipfile = trim(file_get_contents("../utenze/last_zip.txt"));

    if (file_exists($zipfile)) {
      header("Content-Type: application/zip");
      header("Content-Disposition: attachment; filename=" . basename($zipfile));
      header("Content-Length: " . filesize($zipfile));
      readfile($zipfile);
      exit;
    } else {
        echo "Errore: file non trovato ($zipfile)";
    }*/
 }

}
require_once('./req_bottom.php');
?>

</div>
</body>

</html>