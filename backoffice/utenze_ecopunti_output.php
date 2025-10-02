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
<?php 
//require_once('./req.php');
require_once('../conn.php');
?> 

</head>

<body>


      <div class="container">


<?php 

if (isset($_POST)){

if ($_POST['submit']) {
     //Save File        


    $utenze = $_POST['ute-list'];
    $eco = $_POST['eco'];

    #echo $eco;
    #exit;
    # popolo la tabella base_ecopunti

    # pulisco la tabella
    $query0="TRUNCATE TABLE etl.base_ecopunti CONTINUE IDENTITY RESTRICT;";
    #$result0 = pg_prepare($conn, "my_query0", $query0);
    #$result0 = pg_execute($conn, "my_query0", array());
    $result0 = pg_query($conn, $query0);

    if (!$result0) {
        echo "An error occurred.\n";
        exit;
    }

    # la popolo con i dati dei civici neri
    $query1="insert into etl.base_ecopunti 
(id, geom, cod_strada, numero, lettera, colore, testo, cod_civico, ins_date, mod_date)
select n.* from geo.civici_neri n, etl.aree_ecopunti_4326 a 
where a.id=$1 and st_intersects(n.geoloc, st_transform(a.geom, 3003));";
    


    # la popolo con i dati dei civici rossi
    $query2="insert into etl.base_ecopunti 
(id, geom, cod_strada, numero, lettera, colore, testo, cod_civico, ins_date, mod_date)
select n.* from geo.civici_rossi n, etl.aree_ecopunti_4326 a 
where a.id=$1 and st_intersects(n.geoloc, st_transform(a.geom, 3003));";
    
$result1 = pg_prepare($conn, "my_query1", $query1);
$result2 = pg_prepare($conn, "my_query2", $query2);

    if ($utenze == 'uted') {
        // solo civici neri
        $result1 = pg_execute($conn, "my_query1", array($eco));
    } else if ($utenze == 'utend') {
        // solo civici rossi
        $result2 = pg_execute($conn, "my_query2", array($eco));
    } else if ($utenze == 'ute') {
        // entrambi
        $result1 = pg_execute($conn, "my_query1", array($eco));
        $result2 = pg_execute($conn, "my_query2", array($eco));
    }

    $query3="insert into etl.ecopunti
    (cod_strada,numero,lettera,colore,testo,cod_civico)
    select cast (cod_strada as numeric),cast (numero as numeric),lettera,cast(colore as numeric),
    testo,cod_civico from etl.base_ecopunti
    where cod_civico not in (select cod_civico from etl.ecopunti)";
    $result3 = pg_prepare($conn, "my_query3", $query3);
    $result3 = pg_execute($conn, "my_query3", array());



    $comando='/usr/bin/python3 ../py_scripts/ecopunti_parte2.py  -u '.$utenze.' -a '.$eco.' -e true';
    #echo $comando;
    #echo '<br><br>';
    exec($comando, $output, $retval);
    foreach($output as $key => $value)
    {
      echo $key." ".$value."<br>";
    }

    if ($retval == 0) {
         $zipfile = trim(file_get_contents("/tmp/utenze_area/last_zip_eco.txt"));
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
 }

}
require_once('./req_bottom.php');
?>

</div>
</body>

</html>