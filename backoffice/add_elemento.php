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


$id_piazzola = intval(explode('_',$_POST['id_piazzola'])[0]);

$tipo_elemento = intval(explode('_',$_POST['id_piazzola'])[1]);

$id_utenzapap = intval($_POST['id_utenzapap']);

#echo $id_piazzola.'<br>';
#echo $tipo_elemento.'<br>';
#echo $id_utenzapap.'<br>';
/*if ($id_utenzapap > intval(-1)) {
    echo "OK";
}
exit();*/


// aggiungi elemento
$add_elemento='INSERT INTO elem.elementi
(tipo_elemento, id_piazzola, id_asta, posizione, 
privato, peso_reale, peso_stimato , 
riferimento, id_utenza, nome_attivita, percent_riempimento, freq_stimata, numero_civico, 
lettera_civico, colore_civico, note, serratura, id_materiale)
(select distinct tipo_elemento, id_piazzola, 
(select id_asta from elem.piazzole where id_piazzola = e.id_piazzola), 
max(coalesce(posizione,1))as posizione, 
max(coalesce(privato,0)) as privato,
max(coalesce(peso_reale,0)) as peso_reale, 
max(coalesce(peso_stimato, 0)) as peso_stimato,  
(select riferimento from elem.piazzole p where p.id_piazzola = e.id_piazzola), 
id_utenza, 
nome_attivita, 
max(percent_riempimento), 
max(freq_stimata), 
/*numero_civico, */
(select numero_civico from elem.piazzole p where p.id_piazzola = e.id_piazzola) as numero_civico,
/*lettera_civico,*/
(select lettera_civico from elem.piazzole p where p.id_piazzola = e.id_piazzola) as lettera_civico,
/*colore_civico,*/ 
(select colore_civico from elem.piazzole p where p.id_piazzola = e.id_piazzola) as colore_civico,
/*note,*/
(select note from elem.piazzole p where p.id_piazzola = e.id_piazzola) as note,
min(coalesce(serratura,0)) as serratura,
min(coalesce(id_materiale,1)) as materiale
from elem.elementi e
where e.id_piazzola = $1 and e.tipo_elemento = $2
group by tipo_elemento, id_piazzola,
id_utenza, nome_attivita
) returning id_elemento';

$result_add = pg_prepare($conn_sovr, "add_elemento", $add_elemento);
//echo  pg_last_error($conn_sovr);
if (pg_last_error($conn_sovr)){
    echo pg_last_error($conn_sovr);
    $res_ok=$res_ok+1;
}

$result_add = pg_execute($conn_sovr, "add_elemento", array($id_piazzola, $tipo_elemento));
if (pg_last_error($conn_sovr)){
    echo pg_last_error($conn_sovr);
    $res_ok=$res_ok+1;
}

//exit();




// aggiungi elemento a percorsi della stessa tipologia
$new_id=0;
$arr = pg_fetch_array($result_add, 0, PGSQL_NUM);
$new_id=intval($arr[0]);
//$new_id_text=$arr[0];





if ($new_id==0) {
    // aggiunta elemento altro tipo (non lo aggiungo a percorso )
    $add_elemento2="INSERT INTO elem.elementi
(tipo_elemento, id_piazzola, id_asta, 
privato, peso_reale, peso_stimato , 
riferimento, id_utenza, nome_attivita,
percent_riempimento, 
freq_stimata)
(select distinct $1::int as tipo_elemento, id_piazzola, (select id_asta from elem.piazzole where id_piazzola = $2) ,  
0, 0, 0 ,  
NULL, -1, NULL,
(select percent_riempimento
from elem.tipi_elemento te where tipo_elemento = $3),
(select freq_stimata
from elem.tipi_elemento te where tipo_elemento = $4)
from elem.elementi e
where e.id_piazzola = $5
) returning id_elemento";


    $result_add2 = pg_prepare($conn_sovr, "add_elemento2", $add_elemento2);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }

    $result_add2 = pg_execute($conn_sovr, "add_elemento2", array($tipo_elemento, 
                                                                $id_piazzola, 
                                                                $tipo_elemento, 
                                                                $tipo_elemento, 
                                                                $id_piazzola));
    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }


    $arr2 = pg_fetch_array($result_add2, 0, PGSQL_NUM);
    $new_id=intval($arr2[0]);

} else {


    // se pap aggiungo elemento anche agli elementi privati 
    if ($id_utenzapap > intval(-1)) {
        $query_insert_pap= "INSERT INTO elem.elementi_privati (
            id_elemento, 
            id_macro_categoria, 
            id_cliente,
            id_utenzatia,
            id_progetto_utenze_dettaglio,
            descrizione,
            nota,
            nome_attivita,
            pog,
            id_utenzapap, 
            id_tipo_ubicazione
            ) (
            SELECT distinct $1::int,  id_macro_categoria, 
            id_cliente, 
            id_utenzatia, 
            id_progetto_utenze_dettaglio, 
            descrizione, nota,
            nome_attivita, pog, 
            id_utenzapap, 
            id_tipo_ubicazione
            FROM elem.elementi_privati where id_elemento in (
                select id_elemento from elem.elementi e where id_piazzola = $2
                and tipo_elemento = $3
            ) and id_utenzapap = $4
            )";

        $result_add_pap = pg_prepare($conn_sovr, "add_elemento_pap", $query_insert_pap);
        //echo  pg_last_error($conn_sovr);
        if (pg_last_error($conn_sovr)){
            echo pg_last_error($conn_sovr);
            $res_ok=$res_ok+1;
        }

        $result_add_pap = pg_execute($conn_sovr, "add_elemento_pap", array(intval($new_id),  $id_piazzola, $tipo_elemento, $id_utenzapap));
        if (pg_last_error($conn_sovr)){
            echo pg_last_error($conn_sovr);
            $res_ok=$res_ok+1;
        }


    }

    // aggiunta aste percorso
    $add_eap = 'INSERT INTO elem.elementi_aste_percorso
    (id_elemento, id_asta_percorso, frequenza, ripasso)
    (
        select distinct $1::int, id_asta_percorso, frequenza, ripasso
        from elem.elementi_aste_percorso eap where id_elemento::int in  
        (select id_elemento from elem.elementi e 
        where e.id_piazzola = $2 and e.tipo_elemento = $3)
        and id_asta_percorso in (select id_asta_percorso from elem.aste_percorso 
        where id_percorso in (select id_percorso from elem.percorsi where id_categoria_uso in (3,6)
        )
        )
    )';

    $result_add = pg_prepare($conn_sovr, "add_eap", $add_eap);
    //echo  pg_last_error($conn_sovr);
    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }

    $result_add = pg_execute($conn_sovr, "add_eap", array($new_id, $id_piazzola, $tipo_elemento));
    if (pg_last_error($conn_sovr)){
        echo pg_last_error($conn_sovr);
        $res_ok=$res_ok+1;
    }



    if ($res_ok==0){

        // CICLO SUI PERCORSI 
        $select_percorsi= 'SELECT distinct id_percorso from elem.aste_percorso ap 
            where id_asta_percorso in (
                select distinct id_asta_percorso
                from elem.elementi_aste_percorso eap where id_elemento in  
                (select id_elemento from elem.elementi e 
                where e.id_piazzola = $1 and e.tipo_elemento = $2)
            ) and id_percorso in 
                (
                select id_percorso from elem.percorsi where id_categoria_uso in (3,6)
                )';
        $result_p = pg_prepare($conn_sovr, "select_percorsi", $select_percorsi);
        //echo  pg_last_error($conn_sovr);
        if (pg_last_error($conn_sovr)){
            //echo pg_last_error($conn_sovr);
            $res_ok=$res_ok+1;
        }
        
        $result_p = pg_execute($conn_sovr, "select_percorsi", array($id_piazzola, $tipo_elemento));
        if (pg_last_error($conn_sovr)){
            //echo pg_last_error($conn_sovr);
            $res_ok=$res_ok+1;
        }
    
    
    
        // aggiungi history percorsi
        $desc_hist= 'Aggiunto elemento tipo_elemento = '. $tipo_elemento.' da piazzola '.$id_piazzola. '(id elemento ='.$new_id.')';
        $sql_history= "INSERT INTO util.sys_history (\"type\", \"action\", 
                description, datetime, 
                id_user,
                id_piazzola, id_percorso, id_elemento, id_utenzapap) 
                VALUES('PERCORSO', 'UPDATE_ELEM', $1, now(),
                (select id_user from util.sys_users su where \"name\" ilike $2), 
                $3, $4, $5, null);";
    
        // la preparazione fuori dal ciclo
        $result_h = pg_prepare($conn_sovr, "sql_history", $sql_history);
        //echo  pg_last_error($conn_sovr);
        if (pg_last_error($conn_sovr)){
            //echo pg_last_error($conn_sovr);
            $res_ok=$res_ok+1;
        }


    
        
    
    
        while($rp = pg_fetch_assoc($result_p)) {
    
            $result_h = pg_execute($conn_sovr, "sql_history", array($desc_hist, 
                                                    $_SESSION['username'],
                                                    $id_piazzola, 
                                                    $rp['id_percorso'],
                                                    $new_id));
            if (pg_last_error($conn_sovr)){
                //echo pg_last_error($conn_sovr);
                $res_ok=$res_ok+1;
            }
    
        }
    
    
    }

}







if ($res_ok==0) {
    echo '<font color="green">Elemento '.$new_id.' inserito correttamente. 
    <!--a class="btn btn-success btn-sm" onclick="return RefreshWindow();" 
    title="Clicca per ricaricare la pagina">
    <i class="fa-solid fa-rotate-right"></i>
    </a-->
    </font>';
} else {
    echo '<br>ERRORE<br>';
    http_response_code(400);
}

?>