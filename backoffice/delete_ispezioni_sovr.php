<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;





$id_isp = $_POST['id_isp'];

$motivazione = $_POST['motivazione'];








    

$query_insert1="INSERT INTO sovrariempimenti.ispezione_elementi_eliminate
(id_ispezione, id_elemento, sovrariempito, 
num_svuotamenti_settimana, dettagli_svuotamenti, data_inserimento,
data_ultima_modifica, data_eliminazione) 
(select id_ispezione, id_elemento, sovrariempito, 
num_svuotamenti_settimana, dettagli_svuotamenti, data_inserimento,
data_ultima_modifica, now() 
from sovrariempimenti.ispezione_elementi 
where id_ispezione = $1)";

$result_insert1 = pg_prepare($conn_sovr, "query_insert1", $query_insert1);
//echo  pg_last_error($conn_sovr);
if (pg_last_error($conn_sovr)){
    echo pg_last_error($conn_sovr);
    $res_ok=$res_ok+1;
}

$result_insert1 = pg_execute($conn_sovr, "query_insert1", array($id_isp));
if (pg_last_error($conn_sovr)){
    echo pg_last_error($conn_sovr);
    $res_ok=$res_ok+1;
}

  
$query_insert2="INSERT INTO sovrariempimenti.ispezioni_eliminate 
(id, id_piazzola, data_ora, ispettore, data_inserimento, data_ultima_modifica, 
data_eliminazione, motivazione, id_user) 
(SELECT id, id_piazzola, data_ora, ispettore, data_inserimento, data_ultima_modifica,
now(), $1, $2
FROM sovrariempimenti.ispezioni
where id = $3)";

$result_insert2 = pg_prepare($conn_sovr, "query_insert2", $query_insert2);
//echo  pg_last_error($conn_sovr);
if (pg_last_error($conn_sovr)){
    echo pg_last_error($conn_sovr);
    $res_ok=$res_ok+1;
}

$result_insert2 = pg_execute($conn_sovr, "query_insert2", array($motivazione, $_SESSION['id_user'], $id_isp));
if (pg_last_error($conn_sovr)){
    echo pg_last_error($conn_sovr);
    $res_ok=$res_ok+1;
}


#  cancello solo se non ci sono stati errori sopra
if ($res_ok ==0 ){
    $query_delete1="DELETE FROM sovrariempimenti.ispezione_elementi 
WHERE id_ispezione = $1";

    $result_delete1 = pg_prepare($conn_sovr, "query_delete1", $query_delete1);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }

    $result_delete1 = pg_execute($conn_sovr, "query_delete1", array($id_isp));
    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }
} 


  
#  cancello solo se non ci sono stati errori sopra
if ($res_ok ==0){
    $query_delete2="DELETE FROM sovrariempimenti.ispezioni 
where id = $1 RETURNING id";

    $result_delete2 = pg_prepare($conn_sovr, "query_delete2", $query_delete2);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }

    $result_delete2 = pg_execute($conn_sovr, "query_delete2", array($id_isp));
    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }
}
    
    $arr = pg_fetch_array($result_delete2, 0, PGSQL_NUM);
    $num_eliminate=intval($arr[0]);







if ($res_ok==0){
    if ($num_eliminate > 0){
        echo '<font color="green"> '.$num_eliminate.' Ispezione eliminata correttamente!</font>';
    } else {
        echo '<font color="red"> Non ci sono ispezioni con id '.$id_isp.' da eliminare!</font>';
    }
} else {
    echo '<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}


?>