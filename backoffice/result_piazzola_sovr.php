<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;

/*foreach ($_POST as $key => $value) {
    echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";
}*/

$id_piazzola = $_POST['id_piazzola'];
$data_ora = $_POST['data_isp'].' '.$_POST['ora']; 
$ispettore = $_POST['ispettore']; 
//echo $ispettore;
//exit();


if ($id_piazzola> 0){
    // PREPARAZIONE QUERY DETTAGLI; 
    // query_dettagli 
    $insert_dettagli="INSERT INTO sovrariempimenti.ispezione_elementi 
    (id_ispezione, id_elemento, sovrariempito, 
    num_svuotamenti_settimana, 
    dettagli_svuotamenti) 
    VALUES
    ($1, $2 , $3, 
    (select sum(fo.num_giorni)::int
    from elem.elementi e 
    left join elem.elementi_aste_percorso eap on e.id_elemento = eap.id_elemento 
    left join elem.aste_percorso ap on ap.id_asta_percorso = eap.id_asta_percorso 
    left join elem.percorsi p on p.id_percorso = ap.id_percorso 
    left join etl.frequenze_ok fo on fo.cod_frequenza = eap.frequenza::int 
    left join elem.turni t on t.id_turno = p.id_turno 
    where p.id_categoria_uso in (3) 
    and e.id_elemento = $4),
    (select string_agg( 
        concat(p.cod_percorso,' - ', p.descrizione, ' - ', fo.descrizione_long, ' - ', t.cod_turno),';'
    )
    from elem.elementi e 
    left join elem.elementi_aste_percorso eap on e.id_elemento = eap.id_elemento 
    left join elem.aste_percorso ap on ap.id_asta_percorso = eap.id_asta_percorso 
    left join elem.percorsi p on p.id_percorso = ap.id_percorso 
    left join etl.frequenze_ok fo on fo.cod_frequenza = eap.frequenza::int 
    left join elem.turni t on t.id_turno = p.id_turno 
    where p.id_categoria_uso in (3) 
    and e.id_elemento = $5)
    )";
} else {
    $insert_dettagli="INSERT INTO sovrariempimenti.ispezione_elementi 
 (id_ispezione, id_elemento, sovrariempito, 
 num_svuotamenti_settimana, 
 dettagli_svuotamenti) 
 VALUES
 ($1, $2 , $3, 
 (select sum(fo.num_giorni)::int
 from elem.elementi e 
 join elem.aste a on e.id_asta = a.id_asta
 left join elem.aste_percorso ap on ap.id_asta = a.id_asta
 left join elem.percorsi p on p.id_percorso = ap.id_percorso 
 left join etl.frequenze_ok fo on fo.cod_frequenza = ap.frequenza::int 
 left join elem.turni t on t.id_turno = p.id_turno 
 where p.id_categoria_uso =3 and ap.lung_trattamento > 0
 and e.id_elemento = $4),
 (select string_agg( 
     concat(p.cod_percorso,' - ', p.descrizione, ' - ', fo.descrizione_long, ' - ', t.cod_turno),';'
 )
 from elem.elementi e
 join elem.aste a on e.id_asta = a.id_asta
 left join elem.aste_percorso ap on ap.id_asta = a.id_asta
 left join elem.percorsi p on p.id_percorso = ap.id_percorso 
 left join etl.frequenze_ok fo on fo.cod_frequenza = ap.frequenza::int 
 left join elem.turni t on t.id_turno = p.id_turno 
 where p.id_categoria_uso =3 and ap.lung_trattamento > 0 
 and e.id_elemento = $5)
 )";

}


$result_elem = pg_prepare($conn_sovr, "insert_dettagli", $insert_dettagli);
//echo  pg_last_error($conn_sovr);
if (pg_last_error($conn_sovr)){
echo pg_last_error($conn_sovr);
$res_ok=$res_ok+1;
}

// query select dettagli 
$select_dettagli="SELECT sovrariempito 
FROM sovrariempimenti.ispezione_elementi 
 WHERE id_ispezione =$1 AND id_elemento = $2";

$result_select = pg_prepare($conn_sovr, "select_dettagli", $select_dettagli);
//echo  pg_last_error($conn_sovr);
if (pg_last_error($conn_sovr)){
    echo pg_last_error($conn_sovr);
    $res_ok=$res_ok+1;
}

// query deleet dettagli 
$delete_dettagli="DELETE FROM sovrariempimenti.ispezione_elementi 
 WHERE id_ispezione =$1";

$result_delete = pg_prepare($conn_sovr, "delete_dettagli", $delete_dettagli);
//echo  pg_last_error($conn_sovr);
if (pg_last_error($conn_sovr)){
    echo pg_last_error($conn_sovr);
    $res_ok=$res_ok+1;
}


// query update_dettagli true
$update_dettagli="UPDATE sovrariempimenti.ispezione_elementi 
 SET sovrariempito = $1 
 WHERE id_ispezione =$2 AND id_elemento = $3 
 ";

$result_update = pg_prepare($conn_sovr, "update_dettagli", $update_dettagli);
//echo  pg_last_error($conn_sovr);
if (pg_last_error($conn_sovr)){
    echo pg_last_error($conn_sovr);
    $res_ok=$res_ok+1;
}





if ($_POST['id']){
    //echo 'ID definito';

    $update_ispezione= "UPDATE sovrariempimenti.ispezioni 
    SET id_piazzola = $1, data_ora = to_timestamp($2, 'DD/MM/YYYY HH24:MI'), ispettore = $3
    WHERE id= $4 "; 

    $result_id1 = pg_prepare($conn_sovr, "update_ispezione", $update_ispezione);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }

    $result_id1 = pg_execute($conn_sovr, "update_ispezione", array($id_piazzola, $data_ora, $ispettore, $_POST['id']));

    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }


    $result_delete = pg_execute($conn_sovr, "delete_dettagli", array($_POST['id']));
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }



    if ($res_ok==0){
        $new_id=$_POST['id'];
    } else {
        echo '<br>Problema update ispezione<br>';
    }


} else {       
    // query insert ispezione
    $insert_ispezione="INSERT INTO sovrariempimenti.ispezioni (id_piazzola, data_ora, ispettore) 
    VALUES($1, to_timestamp($2, 'DD/MM/YYYY HH24:MI'), $3) RETURNING id;";
    //inizializzo a 0
    $res_ok=0;
    $result_id = pg_prepare($conn_sovr, "insert_ispezione", $insert_ispezione);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }



    $result_id = pg_execute($conn_sovr, "insert_ispezione", array($id_piazzola, $data_ora, $ispettore));

    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }

    $arr = pg_fetch_array($result_id, 0, PGSQL_NUM);
    $new_id=intval($arr[0]);
    //echo 'ID='.$new_id;
    //exit();
}
    

if ($res_ok==0) {
    //$i=0;
    foreach ($_POST as $key => $value) {
        if (!in_array($key, ['id_piazzola', 'data_isp', 'ora', 'ispettore'])) {
            if (count(explode('_', $key))==1){
                $id_elemento=intval($key);
                if ($id_elemento > 0){
                    // faccio insert
                    $result_elem = pg_execute($conn_sovr, "insert_dettagli", array($new_id, $id_elemento, 'f', $id_elemento, $id_elemento));
                    if (pg_last_error($conn_sovr)){
                        echo pg_last_error($conn_sovr);
                        $res_ok=$res_ok+1;
                    }
                }
            } else {
                // faccio update
                $id_elemento = intval(explode('_', $key)[0]);
                $result_update = pg_execute($conn_sovr, "update_dettagli", array('t', $new_id, $id_elemento));
                if (pg_last_error($conn_sovr)){
                    echo pg_last_error($conn_sovr);
                    $res_ok=$res_ok+1;
                }
            }
            //echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";

        }
    }
} else {
    if ($_POST['id']){
        echo '<br>Problema update ispezione<br>';
    } else {
        echo '<br>Problema inserimento ispezione<br>';
    }
}


    if ($res_ok==0) {
        if ($_POST['id']){
            echo $_POST['id'].'$$<font color="green">Dati aggiornati correttamente </font>';
        } else {
            echo $new_id.'$$<font color="green">Dati inseriti correttamente </font>';
        } 
    } else {
        echo '<br>ERRORE<br>';
    }


?>