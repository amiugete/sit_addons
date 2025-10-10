<?php
session_start();
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
//require_once('../req.php');
require_once('../conn.php');
?> 

</head>

<body>


      <div class="container">


<?php 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

//if ($_POST['submit']) {
     //Save File        


    $utenze = $_POST['ute-list'];
    $eco = $_POST['eco'];
    //$ecopoint = $_POST['ecop'];

    //echo $eco."<br>";
    $query_eco= "select ecopunto from etl.aree_4326 where id = $1;";
    $resulteco =pg_prepare($conn, "my_query_eco", $query_eco);
    $resulteco = pg_execute($conn, "my_query_eco", array($eco));
    while($rec = pg_fetch_assoc($resulteco)) {
        $ecopoint = $rec['ecopunto'];
    }
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
select n.* from geo.civici_neri n, etl.aree_4326 a 
where a.id=$1 and st_intersects(n.geoloc, st_transform(a.geom, 3003));";


    # la popolo con i dati dei civici rossi
    $query2="insert into etl.base_ecopunti 
(id, geom, cod_strada, numero, lettera, colore, testo, cod_civico, ins_date, mod_date)
select n.* from geo.civici_rossi n, etl.aree_4326 a 
where a.id=$1 and st_intersects(n.geoloc, st_transform(a.geom, 3003));";


    $result1 =pg_prepare($conn, "my_query1", $query1);
    $result2 =pg_prepare($conn, "my_query2", $query2);

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
    
    if ($ecopoint == 't'){
        $query3="insert into etl.ecopunti
        (cod_strada,numero,lettera,colore,testo,cod_civico)
        select cast (cod_strada as numeric),cast (numero as numeric),lettera,cast(colore as numeric),
        testo,cod_civico from etl.base_ecopunti
        where cod_civico not in (select cod_civico from etl.ecopunti)";
        $result3 = pg_prepare($conn, "my_query3", $query3);
        $result3 = pg_execute($conn, "my_query3", array());
        
        $comando='/usr/bin/python3 ../py_scripts/ecopunti_parte2.py  -u '.$utenze.' -a '.$eco.' -e true';
    }else{
        $comando='/usr/bin/python3 ../py_scripts/ecopunti_parte2.py  -u '.$utenze.' -a '.$eco.' -e false';
    }

    
    //echo $comando;
    //exit;
    
    
    #echo '<br><br>';
    exec($comando, $output, $retval);
    /*foreach($output as $key => $value)
    {
      echo $key." ".$value."<br>";
    }*/

    /*if ($retval == 0) {
        //echo 'OK<br>';
      $zipfile = trim(file_get_contents("/tmp/utenze_area/last_zip.txt"));
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
    }*/

    if ($retval === 0) {
    $zipfile = trim(file_get_contents("/tmp/utenze_area/last_zip.txt"));
    if ($zipfile && file_exists($zipfile)) {
        if (ob_get_length()) ob_end_clean(); // chiudi qualsiasi output buffer
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=" . basename($zipfile));
        header("Content-Length: " . filesize($zipfile));
        flush();
        readfile($zipfile);
        // salvo il log
        $description_log='Nuova estrazione utenze per area'; 
        $insert_history="INSERT INTO util.sys_history 
        (\"type\", \"action\", 
        description, 
        datetime,  id_user) 
        VALUES(
        'UTENZE', 'DOWNLOAD',
        $1, 
        CURRENT_TIMESTAMP, 
        (select id_user from util.sys_users su where \"name\" ilike $2));";

        $result_sit3 = pg_prepare($conn, "insert_history", $insert_history);
        if (pg_last_error($conn)){
        echo pg_last_error($conn).'<br>';
        $res_ok=$res_ok+1;
        }

        $result_sit3 = pg_execute($conn, "insert_history", array($description_log, $_SESSION['username'])); 
        if (pg_last_error($conn)){
        echo pg_last_error($conn).'<br>';
        $res_ok=$res_ok+1;
        }
        exit;
    } else {
        http_response_code(500);
        echo json_encode(["error" => "File ZIP non trovato"]);
        exit;
    }
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Errore nello script Python"]);
        exit;
    }
   
      

    } else {
      echo "KO";
      echo $comando;
      echo "C'è un problema con l'invio dei dati ti invitiamo a contattare il gruppo GETE via mail (assterritorio@amiu.genova.it)";
    }
//}

//}
require_once('./req_bottom.php');
?>

</div>
</body>

</html>