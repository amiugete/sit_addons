<?php
session_start();
#require('../validate_input.php');

if ($_SESSION['test']==1) {
    //echo "CONNESSIONE TEST<br>";
    $checkTest=1;
    require_once ('../conn_test.php');
} else {
    //echo "CONNESSIONE ESERCIZIO<br>";
    $checkTest=0;
    require_once ('../conn.php');
}


$res_ok=0;





$cod_percorso = $_POST['id_percorso'];
//echo $cod_percorso."<br>";



$vers = intval($_POST['old_vers']);
//echo $vers."<br>";


$id_ser_uo = $_POST['id_servizio_uo'];


if (!empty($_POST['percentuali'])) {
  $comuni_percent = $_POST['percentuali'];}
else {
    $comuni_percent = [];
}

/*foreach($_POST['percentuali'] as $key => $value){
  echo "Comune: ".$key." - Percentuale: ".$value."<br>";
}*/


//exit();
if(count($comuni_percent)>0){
    //echo "<br><br>Insert in percorsi_comuni_percentuali<br>";
    /*$insert_comuni_percentuali = "INSERT INTO anagrafe_percorsi.percorsi_comuni
    (cod_percorso, versione, id_comune, competenza) 
      VALUES
      ($1, $2, $3, $4)";*/

    $insert_comuni_percentuali = "INSERT INTO anagrafe_percorsi.percorsi_comuni
        (cod_percorso, versione, id_comune, competenza)  
        VALUES ($1, $2, $3, $4)
        ON CONFLICT (cod_percorso, versione, id_comune) /* or you may use [DO NOTHING;] */ 
        DO UPDATE  SET competenza=EXCLUDED.competenza;";


    foreach($comuni_percent as $id_comune => $percentuale){
      $result_comuni_percentuali = pg_prepare($conn_sit, "insert_comuni_percentuali_".$id_comune, $insert_comuni_percentuali);
      //echo "<br><br> ERRORI COMUNI PERCENTUALI PREP: <br>";
      //echo  pg_last_error($conn_sit);
      $result_comuni_percentuali = pg_execute($conn_sit, "insert_comuni_percentuali_".$id_comune, array($cod_percorso, $vers, $id_comune, $percentuale)); 
      if (pg_last_error($conn_sit)){
        echo "<br><br> ERRORI COMUNI PERCENTUALI EXEC: <br>";
        echo  pg_last_error($conn_sit);
        echo  pg_result_error($result_comuni_percentuali);
        $res_ok= $res_ok+1;
        }
    }
    if ($checkTest == 0){
      /*$insert_uo_comuni = "INSERT INTO UNIOPE.ANAGR_SER_PER_UO_COMUNI 
        (ID_SER_PER_UO, ID_COMUNE, PERCENTUALE) 
        VALUES
        (:p1, :p2, :p3)";

      foreach($comuni_percent as $id_comune => $percentuale){
        $result_uo_com = oci_parse($oraconn, $insert_uo_comuni);
        # passo i parametri
        oci_bind_by_name($result_uo_com, ':p1', $id_ser_uo);
        oci_bind_by_name($result_uo_com, ':p2', $id_comune);
        oci_bind_by_name($result_uo_com, ':p3', $percentuale);
        $risuocom=oci_execute($result_uo_com);

        if (!$risuocom) {
          echo "<br>ci sono errori<br>";
          $e = oci_error($result_uo_com);  // For oci_execute errors pass the statement handle
          echo $e;
          echo $e['message'];
          echo htmlentities($e['message']);
          echo "\n<pre>\n";
          echo htmlentities($e['sqltext']);
          echo "\n%".($e['offset']+1)."s", "^";
          echo  "\n</pre>\n";
        }

        echo "<br> sono arrivato qua a inserire i comuni";
        oci_free_statement($result_uo_com);
      }
      */

      $upsert_uo_comuni = "
      MERGE INTO UNIOPE.ANAGR_SER_PER_UO_COMUNI t
      USING (
          SELECT :p1 AS ID_SER_PER_UO,
                :p2 AS ID_COMUNE,
                :p3 AS PERCENTUALE
          FROM dual
      ) src
      ON (
          t.ID_SER_PER_UO = src.ID_SER_PER_UO
          AND t.ID_COMUNE = src.ID_COMUNE
      )
      WHEN MATCHED THEN
          UPDATE SET t.PERCENTUALE = src.PERCENTUALE
      WHEN NOT MATCHED THEN
          INSERT (ID_SER_PER_UO, ID_COMUNE, PERCENTUALE)
          VALUES (src.ID_SER_PER_UO, src.ID_COMUNE, src.PERCENTUALE)
      ";

      foreach($comuni_percent as $id_comune => $percentuale){
          $result_uo_com = oci_parse($oraconn, $upsert_uo_comuni);

          oci_bind_by_name($result_uo_com, ':p1', $id_ser_uo);
          oci_bind_by_name($result_uo_com, ':p2', $id_comune);
          oci_bind_by_name($result_uo_com, ':p3', $percentuale);

          $risuocom = oci_execute($result_uo_com);

          if (!$risuocom) {
          echo "<br>ci sono errori<br>";
          $e = oci_error($result_uo_com);  // For oci_execute errors pass the statement handle
          echo $e;
          echo $e['message'];
          echo htmlentities($e['message']);
          echo "\n<pre>\n";
          echo htmlentities($e['sqltext']);
          echo "\n%".($e['offset']+1)."s", "^";
          echo  "\n</pre>\n";
        }
        echo "<br> sono arrivato qua a inserire i comuni";
        oci_free_statement($result_uo_com);
      }



    }
}

if ($res_ok==0){
    echo '<font color="green"> Percentuale comuni salvata correttamente!</font>';
} else {
    echo $res_ok.'<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}

?>