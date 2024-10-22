<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;


$id_elemento = $_POST['id_elemento'];

if ($_POST['matr']){
    $matr = $_POST['matr'];
} else {
    $matr='';
}

if ($_POST['tag']){
    $tag = $_POST['tag'];
} else {
    $tag='';
}

//echo $id_elemento.'<br>';
//echo $matr.'<br>';
//echo $tag.'<br>';





    
if (trim($matr)!=""){
    $query_update_matr="update elem.elementi set matricola=$1
    where id_elemento = $2";

    $result_update_matr = pg_prepare($conn_sovr, "query_update_matr", $query_update_matr);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        //echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }

    $result_update_matr = pg_execute($conn_sovr, "query_update_matr", array($matr, $id_elemento));
    if (pg_last_error($conn_sovr)){
        //echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }

} else {
    //echo 'Sono in questo caso <br>';
    $query_update_matr="update elem.elementi set matricola=null
    where id_elemento = $1";

    $result_update_matr = pg_prepare($conn_sovr, "query_update_matr", $query_update_matr);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        //echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }


    $result_update_matr = pg_execute($conn_sovr, "query_update_matr", array($id_elemento));
    if (pg_last_error($conn_sovr)){
        //echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }

}
    

if (trim($tag)!=''){
    $query_update_tag="update elem.elementi set tag=$1
    where id_elemento = $2";

    $query_update_tag = pg_prepare($conn_sovr, "query_update_tag", $query_update_tag);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        $res_ok=$res_ok+1;
    }

    $query_update_tag = pg_execute($conn_sovr, "query_update_tag", array($tag, $id_elemento));
    if (pg_last_error($conn_sovr)){
        $res_ok=$res_ok+1;
    }

} else {
    $query_update_tag="update elem.elementi set tag=null
    where id_elemento = $1";

    $query_update_tag = pg_prepare($conn_sovr, "query_update_tag", $query_update_tag);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        $res_ok=$res_ok+1;
    }

    $query_update_tag = pg_execute($conn_sovr, "query_update_tag", array($id_elemento));
    if (pg_last_error($conn_sovr)){
        $res_ok=$res_ok+1;
    }


}



    $query_update2="update elem.piazzole set data_ultima_modifica = now() where id_piazzola in ( 
        select distinct id_piazzola from elem.elementi e where id_elemento = $1
    )";



    $result_update2 = pg_prepare($conn_sovr, "query_update2", $query_update2);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        //echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }

    $result_update2 = pg_execute($conn_sovr, "query_update2", array($id_elemento));
    if (pg_last_error($conn_sovr)){
        //echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }







if ($res_ok==0){
    echo '<font color="green"> Dati salvati correttamente!</font>';
} else {
    echo '<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}


?>