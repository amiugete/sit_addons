<?php
require_once '../session.php';
#require('../validate_input.php');


require_once '../conn_ok.php';


$res_ok=0;

/*foreach ($_POST as $key => $value) {
    echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";
}*/


$id_piazzola = intval(explode('_',$_POST['id_piazzola'])[0]);

$tipo_elemento = intval(explode('_',$_POST['id_piazzola'])[1]);

$id_utenzapap = intval($_POST['id_utenzapap']);


$ids_elementi = explode(',', $_POST['ids_elementi']);
/*foreach($ids_elementi as $id_elemento){
    echo 'id elemento da eliminare: '.$id_elemento.'<br>';
}



echo $id_piazzola.'<br>';
echo $tipo_elemento.'<br>';
echo $id_utenzapap.'<br>';
if ($id_utenzapap > intval(-1)) {
    echo "utenza pap associata <br>";
}else{
    echo "nessuna utenza pap associata <br>";
}*/



#exit();


foreach($ids_elementi as $id_elemento){
    //echo 'id elemento da eliminare: '.$id_elemento.'<br>';

    // recupero gli id_asta_percorso associati all'elemento da eliminare
    $get_asta_percorso ='SELECT eap.id_asta_percorso 
    from elem.elementi_aste_percorso eap 
    where id_elemento = $1';

    $result_asta_percorso = pg_prepare($conn_sit, "get_asta_percorso", $get_asta_percorso);
    //echo  pg_last_error($conn_sit);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    $result_asta_percorso = pg_execute($conn_sit, "get_asta_percorso", array($id_elemento));
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    $id_aste_percorso = array();

    while ($reap = pg_fetch_assoc($result_asta_percorso)) {
        $id_aste_percorso[] = $reap['id_asta_percorso'];
    }


    /* Al momento commentato perchè già nel trigger su elem.elementi
    $del_ele_asta_percorso='DELETE FROM elem.elementi_aste_percorso
        WHERE id_elemento=$1';

    $result_del_ele_asta_percorso = pg_prepare($conn_sit, "del_eap", $del_ele_asta_percorso);
    //echo  pg_last_error($conn_sit);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    $result_del_ele_asta_percorso = pg_execute($conn_sit, "del_eap", array($id_elemento));
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }
    */


    /*preparo verifica se all'id_asta_percorso è associato ancora qualche elemento, se si non faccio il delete altrimenti si*/
    $check_elem_asta = 'SELECT id_elemento from elem.elementi_aste_percorso where id_asta_percorso = $1';
    $result_check_elem_asta = pg_prepare($conn_sit, "check_elem", $check_elem_asta);
    //echo  pg_last_error($conn_sit);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    // preparo eliminazione da aste percorso
    $del_ap = 'DELETE FROM elem.aste_percorso
    WHERE id_asta_percorso = $1';

    $result_del_ap = pg_prepare($conn_sit, "del_ap", $del_ap);
    //echo  pg_last_error($conn_sit);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }


    /* preparo recupero id del percorso*/
    $get_id_percorsi = 'SELECT distinct id_percorso from elem.aste_percorso where id_asta_percorso = $1';
    $result_id_percorsi = pg_prepare($conn_sit, "get_percorsi", $get_id_percorsi);
    //echo  pg_last_error($conn_sit);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    $id_percorso = array();
    
    // ciclo sulle aste percorso
    foreach($id_aste_percorso as $idap){

        // eseguo recupero id_percorso da usare dopo nella history
        $result_id_percorsi = pg_execute($conn_sit, "get_percorsi", array($idap));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }
        while ($ridp = pg_fetch_assoc($result_id_percorsi)) {
            $id_percorso[] = $ridp['id_percorso'];
        }

        // eseguo verifica su altri elementi associata all'asta_percorso
        $result_check_elem_asta = pg_execute($conn_sit, "check_elem", array($idap));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        // se non ci sono altri elementi elimino l'asta_percorso
        if (pg_num_rows($result_check_elem_asta) == 0){
            $result_del_ap = pg_execute($conn_sit, "del_ap", array($idap));
            if (pg_last_error($conn_sit)){
                echo pg_last_error($conn_sit);
                $res_ok=$res_ok+1;
            }
        }

    }

    // delete elemento da elem.elementi
    $del_elemento='DELETE FROM elem.elementi
    WHERE id_elemento = $1;';

    $result_del = pg_prepare($conn_sit, "del_elemento", $del_elemento);
    //echo  pg_last_error($conn_sit);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    $result_del = pg_execute($conn_sit, "del_elemento", array($id_elemento));
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    // se pap elimino elemento anche dagli elementi privati 
    if ($id_utenzapap > intval(-1)) {
        $query_del_pap= "DELETE FROM elem.elementi_privati
        WHERE id_elemento=$1;";

        $result_add_pap = pg_prepare($conn_sit, "del_elemento_pap", $query_del_pap);
        //echo  pg_last_error($conn_sit);
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        $result_add_pap = pg_execute($conn_sit, "del_elemento_pap", array($id_elemento));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }


    }// fine if pap

    /*foreach($id_percorso as $idp) {
        echo 'il percorso è: '.$idp.'<br>';
    }*/

    if ($res_ok==0){
        // aggiungi history percorsi
        $desc_hist= 'Eliminato elemento tipo_elemento = '. $tipo_elemento.' da piazzola '.$id_piazzola. '(id elemento ='.$id_elemento.')';
        $sql_history= "INSERT INTO util.sys_history (\"type\", \"action\", 
                description, datetime, 
                id_user,
                id_piazzola, id_percorso, id_elemento, id_utenzapap) 
                VALUES('PERCORSO', 'UPDATE_ELEM', $1, now(),
                (select id_user from util.sys_users su where \"name\" ilike $2), 
                $3, $4, $5, null);";
    
        $result_h = pg_prepare($conn_sit, "sql_history", $sql_history);
        if (pg_last_error($conn_sit)){
            $res_ok=$res_ok+1;
        }

        // ciclo sui percorsi per scrivere nella history
        foreach($id_percorso as $idp) {
    
            $result_h = pg_execute($conn_sit, "sql_history", array($desc_hist, 
                                                    $_SESSION['username'],
                                                    $id_piazzola, 
                                                    $idp,
                                                    $id_elemento));
            if (pg_last_error($conn_sit)){
                //echo pg_last_error($conn_sit);
                $res_ok=$res_ok+1;
            }
    
        }
    
    }
    
}//fine foreach

if ($res_ok==0) {
    echo '<font color="green">Elemento eliminato correttamente. </font>';
} else {
    echo '<br>ERRORE<br>';
    http_response_code(400);
}

?>