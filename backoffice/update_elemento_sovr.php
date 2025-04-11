<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;


$id_elemento = intval($_POST['id_elemento']);

$num_freq = intval($_POST['num_freq']);

# controllo se è cambiata la tipologia elemento
$query_tipo_elemento_es="select tipo_elemento from elem.elementi 
where id_elemento = $1";

$result_tes = pg_prepare($conn_sovr, "query_tipo_elemento_es", $query_tipo_elemento_es);
//echo  pg_last_error($conn_sovr);
if (pg_last_error($conn_sovr)){
    //echo pg_last_error($conn_sovr);
    $res_ok=$res_ok+1;
}

$result_tes = pg_execute($conn_sovr, "query_tipo_elemento_es", array($id_elemento));
if (pg_last_error($conn_sovr)){
    //echo pg_last_error($conn_sovr);
    $res_ok=$res_ok+1;
}

while($rum = pg_fetch_assoc($result_tes)) {
    $tipo_elemento_es=$rum['tipo_elemento'];
}

//echo '<br>Tipo_elemento attuale: '.$tipo_elemento_es;
//echo '<br>Tipo_elemento form: '.$_POST['tipo_elemento_tt'];

$check_cambio_tipo=0;
if ($tipo_elemento_es != $_POST['tipo_elemento_tt']){
    $check_cambio_tipo=1;
    //echo "<br>Update<br>";
    # devo fare update
    $query_update_tipo="update elem.elementi set tipo_elemento=$1
    where id_elemento = $2";

    $result_update_tipo = pg_prepare($conn_sovr, "query_update_tipo", $query_update_tipo);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        //echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }

    $result_update_tipo = pg_execute($conn_sovr, "query_update_tipo", array(intval($_POST['tipo_elemento_tt']), $id_elemento));
    if (pg_last_error($conn_sovr)){
        //echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }
    

    if ($num_freq>0){
    # update anche dei log per vedere la modifica nelle variazioni del giorno dopo
    //echo "<br>Hist<br>";
    $update_history_tipo="INSERT INTO util.sys_history 
        (\"type\", \"action\", description, datetime, id_user, id_percorso, id_elemento, id_piazzola) 
        (
            select distinct 
            'PERCORSO', 
            'UPDATE_ELEM', 
            'Modificata tipologia elemento durante ispezione', 
            now(),
            (select id_user from util.sys_users su where \"name\" ilike $1),
            ap.id_percorso,
            $2::int,
            (select id_piazzola from elem.elementi where id_elemento = $3)
            from elem.elementi_aste_percorso eap 
            join elem.aste_percorso ap on ap.id_asta_percorso = eap.id_asta_percorso 
            join elem.percorsi p on p.id_percorso = ap.id_percorso
            where eap.id_elemento = $4
            and p.id_categoria_uso in (3,6)
        )";
        //echo $update_history_tipo;
        //echo "<br><br>";

        $result_uh_tipo = pg_prepare($conn_sovr, "update_history_tipo", $update_history_tipo);
        //echo  pg_last_error($conn_sovr);
        if (pg_last_error($conn_sovr)){
            echo pg_last_error($conn_sovr);
            $res_ok=$res_ok+1;
        }


        $result_uh_tipo = pg_execute($conn_sovr, "update_history_tipo", array($_SESSION['username'],
                                                                        $id_elemento,
                                                                        $id_elemento,
                                                                        $id_elemento));
        if (pg_last_error($conn_sovr)){
            echo pg_last_error($conn_sovr);
            $res_ok=$res_ok+1;
        }
    }
    //echo '<br>ok';
} else {
    //echo '<br>Non ho fatto niente';
    $check_cambio_tipo=0;
}
#exit();



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
    if ($check_cambio_tipo==1){
        echo '9999';
    }
    echo '<font color="green"> Dati salvati correttamente!</font>';
} else {
    echo '<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}


?>