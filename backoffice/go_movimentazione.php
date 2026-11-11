<?php
require_once '../session.php';
#require('../validate_input.php');


require_once '../conn_ok.php';


$res_ok=0;

/*
    - aggiungere elemento in eleme.elementi_temporanei
    - insert in gestione_oggetti.intervento
    - insert in gestione_oggetti.intervento_stato_intervento
    - insert in gestione_oggetti.intervento_tipo_intervento
    - insert in util.sys_history

    verificare poi se su getsione_oggetti test si vede effettivamente l'intervento
*/

$id_piazzola = intval(explode('_',$_POST['id_piazzola'])[0]);

$tipo_elemento = intval(explode('_',$_POST['id_piazzola'])[1]);


if (isset($_POST['id_utenzapap'])) {
    $id_utenzapap = intval($_POST['id_utenzapap']);
} else {
    $id_utenzapap = -1;
}

if (isset($_POST['ids_elementi']) && !empty($_POST['ids_elementi'])) {
    echo 'da dettagli elementi<br>';
    $ids_elementi = explode(',', $_POST['ids_elementi']);
} else {
    if ($_POST['check_piazzola'] == 0) {
        $ids_elementi = [];
    } else {
        echo 'da piazzola<br>';
        $select_all_ids = "SELECT string_agg(id_elemento::text, ',') AS ids_elementi from elem.elementi where id_piazzola = $1";
        $result_all_ids = pg_prepare($conn_sit, "select_all_ids", $select_all_ids);
        if (pg_last_error($conn_sit)) {
            echo pg_last_error($conn_sit);
            $res_ok = $res_ok + 1;
        }

        $result_all_ids = pg_execute($conn_sit, "select_all_ids", array($id_piazzola));
        if (pg_last_error($conn_sit)) {
            echo pg_last_error($conn_sit);
            $res_ok = $res_ok + 1;
        }

        $row_all_ids = pg_fetch_assoc($result_all_ids);

        $ids_elementi = explode(',',$row_all_ids['ids_elementi']);
    }

}

if ($_POST['tipo_intervento'] == 'add' || $_POST['tipo_intervento'] == 'rip') {
    if ($_POST['tipo_intervento'] == 'rip'){
        $descrizione_intervento = 'Ripristino posizionamento';
        // se questo script viene chiamato da tasto ripristino $inserimento = 2 --> va gestito lato GO affinchè con inserimento = 2 non venga aggiunto l'elemento 
        // ma solo ripristinato ovvero riportando a null la colonna data_disattivazione_temp in elem.elementi
        // e se check_piazzola = 1 mettendo colonna sospesa = null in elem.piazzole
        // lato elem.elementi_temporanei l'elemento viene comunque aggiunto con inserimento = 2 e eliminazione = 0 e id_elemento = id_elemento dell'elemento da ripristinare
        $inserimento = 2;   
    }else{
        $descrizione_intervento = 'Nuovo posizionamento';
        $inserimento = 1;
        if (isset($_POST['origine'])) {
            $origine = $_POST['origine'];
            $perc_riempimento = $_POST['new_riempimento'];
            $freq_stimata = $_POST['new_freq'];
            $n_elem = intval($_POST['n_elem']);
            $exists = $_POST['exists'];
        }else{
            $origine = '';
            $n_elem = 1;
        }
    }
    $id_tipo_intervento = 1;
    $eliminazione = 0;
    $action = 'inserito';
    $msg_log = 'Richiesta movimentazione: aggiunta elemento';
    $check_piazzola = $_POST['check_piazzola'];
} else {
    if ($_POST['tipo_intervento'] == 'dis'){
        $descrizione_intervento = 'Eliminazione temporanea elemento';
        // se questo script viene chiamato da tasto dismissione $eliminazione = 2 --> va gestito lato GO affinchè con eliminazione = 2 non venga eliminato l'elemento 
        // ma solo dismesso temporaneamente ovvero popolando la colonna data_disattivazione_temp in elem.elementi
        // e se check_piazzola = 1 mettendo colonna sospesa = true in elem.piazzole
        $eliminazione = 2;   
    }else{
        $descrizione_intervento = 'Eliminazione elemento';
        $eliminazione = 1;
    }
    $id_tipo_intervento = 3;
    $inserimento = 0;
    $action = 'eliminato';
    $msg_log = 'Richiesta movimentazione: eliminazione elemento';
    //$id_elemento = $_POST['id_elemento'];
    $check_piazzola = $_POST['check_piazzola'];
}

/*echo 'INTERVENTO CON MANUTENZIONE<br>';
echo $id_piazzola.'<br>';
echo $tipo_elemento.'<br>';
echo $id_utenzapap.'<br>';
if ($id_utenzapap > intval(-1)) {
    echo "utenza pap associata <br>";
}else{
    echo "nessuna utenza pap associata <br>";
}
echo $_POST['tipo_intervento'].'<br>';
echo 'tipo intervento: '.$descrizione_intervento.'<br>';

echo 'ids_elementi: '.implode(',',$ids_elementi).'<br>';*/

//exit();

pg_query($conn_sit, "BEGIN");

# aggiungo l'interevento per l'elemento
$add_intervento = "INSERT INTO gestione_oggetti.intervento
(descrizione, tipo_priorita_id, data_creazione, elemento_id, piazzola_id, utente)
VALUES($1, 2, NOW(), $2, $3, $4) returning id";

$result_add_intervento = pg_prepare($conn_sit, "add_intervento", $add_intervento);
if (pg_last_error($conn_sit)){
    echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}

// insert su getsione-oggetti.intervento_tipo_intervento
$insert_tipo_intervento = "INSERT INTO gestione_oggetti.intervento_tipo_intervento
(intervento_id, data_creazione, tipo_intervento_id)
VALUES($1, now(), $2);";

$result_add_tipo_intervento = pg_prepare($conn_sit, "add_tipo_intervento", $insert_tipo_intervento);
if (pg_last_error($conn_sit)){
    echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}

// insert su gestione_oggetti.intervento_tipo_stato_intervento
$insert_stato_intervento = "INSERT INTO gestione_oggetti.intervento_tipo_stato_intervento
(tipo_stato_intervento_id, intervento_id, data_ora)
VALUES(1, $1, now());";

$result_add_stato_intervento = pg_prepare($conn_sit, "add_stato_intervento", $insert_stato_intervento);
if (pg_last_error($conn_sit)){
    echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}

// preparo il log sulla history
$desc_hist= $msg_log.' tipo_elemento = '. $tipo_elemento.' da piazzola '.$id_piazzola;
$sql_history= "INSERT INTO util.sys_history (\"type\", \"action\", 
description, datetime, 
id_user,
id_piazzola, id_elemento, id_utenzapap) 
VALUES('PIAZZOLA', 'UPDATE_ELEM', $1, now(),
(select id_user from util.sys_users su where \"name\" ilike $2), 
$3, $4, null);";

$result_h = pg_prepare($conn_sit, "sql_history", $sql_history);
if (pg_last_error($conn_sit)){
    echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}



// query per aggiungere eventuali elemento privato in elem_temporanei.elementi_privati
if ($id_utenzapap > intval(-1)) {
    $insert_elem_privati = 'INSERT INTO elem_temporanei.elementi_privati
    (id_elemento, 
    id_macro_categoria, descrizione, 
    nota, pog, 
    id_utenzapap, id_utenzatia)
    (SELECT distinct $1::integer, id_macro_categoria,  
    descrizione, nota, pog, 
    id_utenzapap, 
    id_utenzatia
    FROM elem.elementi_privati where id_elemento in (
        select id_elemento from elem.elementi e where id_piazzola = $2
        and tipo_elemento = $3
    ) and id_utenzapap = $4)';

    $result_insert_privati = pg_prepare($conn_sit, "insert_privati", $insert_elem_privati);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }
}
//se aggiungo elemento con richiesta di manutenzione
if ($_POST['tipo_intervento'] == 'add') {
    // aggiungi elemento temporaneo con id_intervento = null
    if ($origine == 'dettagli_piazzola'){

    //echo '<br>aggiungo elemento da dettagli piazzola<br>';

        $add_elemento='INSERT INTO elem_temporanei.elementi
        (inserimento, eliminazione, 
        tipo_elemento, id_piazzola, 
        id_asta, 
        posizione, privato, peso_reale, peso_stimato, 
        riferimento, 
        id_utenza, nome_attivita, 
        percent_riempimento, freq_stimata, 
        numero_civico, 
        lettera_civico, 
        colore_civico, 
        note, 
        serratura, id_materiale)
        values
        (
            $1::integer, $2::integer,
            $3, $4, 
            (select id_asta from elem.piazzole where id_piazzola = $4), 
            1, 0, 0, 0,  
            (select riferimento from elem.piazzole p where p.id_piazzola = $4), 
            $5, NULL, 
            $6, $7, 
            /*numero_civico, */
            (select numero_civico from elem.piazzole p where p.id_piazzola = $4),
            /*lettera_civico,*/
            (select lettera_civico from elem.piazzole p where p.id_piazzola = $4),
            /*colore_civico,*/ 
            (select colore_civico from elem.piazzole p where p.id_piazzola = $4),
            /*note,*/
            (select note from elem.piazzole p where p.id_piazzola = $4),
            0, 1
        ) returning id_elemento';

        
        $result_add = pg_prepare($conn_sit, "add_elemento", $add_elemento);
        //echo  pg_last_error($conn_sit);
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }
        
    }else{
        // query per aggiungere elemento
        $add_elemento='INSERT INTO elem_temporanei.elementi
        (inserimento, eliminazione, 
        tipo_elemento, id_piazzola, 
        id_asta, posizione, privato, 
        peso_reale, peso_stimato, 
        riferimento, id_utenza, 
        nome_attivita, percent_riempimento, 
        freq_stimata, numero_civico, 
        lettera_civico, colore_civico, 
        note, serratura, id_materiale)
        (select 
            distinct $1::integer, $2::integer, tipo_elemento, id_piazzola, 
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
            where e.id_piazzola = $3 and e.tipo_elemento = $4
            group by tipo_elemento, id_piazzola,
            id_utenza, nome_attivita
        ) returning id_elemento';

        
        $result_add = pg_prepare($conn_sit, "add_elemento", $add_elemento);
        //echo  pg_last_error($conn_sit);
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }
    }

    # aggiorno l'id_intervento nell'elemento temporaneo
    $update_id_intervento="UPDATE elem_temporanei.elementi
    SET id_intervento=$1
    WHERE id_elemento=$2;";

    $result_update_intervento = pg_prepare($conn_sit, "update_intervento", $update_id_intervento);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    $check_percorsi='SELECT distinct p.id_percorso, eap.id_asta_percorso, 
    eap.frequenza, eap.ripasso
    FROM elem.percorsi p
    JOIN elem.aste_percorso ap
        ON ap.id_percorso = p.id_percorso
    JOIN elem.elementi_aste_percorso eap
        ON eap.id_asta_percorso = ap.id_asta_percorso
    JOIN elem.elementi e
        ON e.id_elemento = eap.id_elemento
    where e.id_piazzola = $1 AND e.tipo_elemento = $2 
    and p.id_categoria_uso in (3,6)';

    $result_check_percorsi = pg_prepare($conn_sit, "check_percorsi", $check_percorsi);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    $insert_percorsi_temp = 'INSERT INTO elem_temporanei.elementi_percorso
    (id_percorso, id_elemento, frequenza, ripasso)
    VALUES($1, $2, $3, $4);';

    $result_insert_percorsi_temp = pg_prepare($conn_sit, "insert_percorsi_temp", $insert_percorsi_temp);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    for ($i = 0; $i < $n_elem; $i++) {
        if ($origine == 'dettagli_piazzola'){
            $result_add = pg_execute($conn_sit, "add_elemento", array($inserimento, $eliminazione, $tipo_elemento, $id_piazzola, $id_utenzapap, $perc_riempimento, $freq_stimata));
            if (pg_last_error($conn_sit)){
                echo pg_last_error($conn_sit);
                $res_ok=$res_ok+1;
            }
        }else{
            $result_add = pg_execute($conn_sit, "add_elemento", array($inserimento, $eliminazione, $id_piazzola, $tipo_elemento));
            if (pg_last_error($conn_sit)){
                echo pg_last_error($conn_sit);
                $res_ok=$res_ok+1;
            }
        }
    

        #recupero dalla query precedente l'id_elemento ed eseguo la query add_interevento
        $id_elemento=0;
        $arr = pg_fetch_array($result_add, 0, PGSQL_NUM);
        $id_elemento=intval($arr[0]);

        
        $result_add_intervento = pg_execute($conn_sit, "add_intervento", array($descrizione_intervento, $id_elemento, $id_piazzola, $_SESSION['username']));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        # recupero l'id_interevento dalla query precedente
        $new_id_intervento=0;
        $arr_int = pg_fetch_array($result_add_intervento, 0, PGSQL_NUM);
        $new_id_intervento=intval($arr_int[0]);

        

        $result_update_intervento = pg_execute($conn_sit, "update_intervento", array($new_id_intervento, $id_elemento));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        // se elemento privato aggiungo l'elemento anche a elem_temporanei.elementi_privati
        if ($id_utenzapap > intval(-1)) {

            $result_insert_privati = pg_execute($conn_sit, "insert_privati", array($id_elemento, $id_piazzola, $tipo_elemento, $id_utenzapap));
            if (pg_last_error($conn_sit)){
                echo pg_last_error($conn_sit);
                $res_ok=$res_ok+1;
            }
        }

        // verifico se gli elementi già presenti hanno dei percorsi associati 
        $result_check_percorsi = pg_execute($conn_sit, "check_percorsi", array($id_piazzola, $tipo_elemento));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        //ciclo sui percorsi che trovo associati ai contenitori dello stesso tipo e faccio insert su elem_temporanei.elem_percorsi

        if (pg_num_rows($result_check_percorsi) > 0) {
            while ($row = pg_fetch_assoc($result_check_percorsi)) {
                $id_asta_percorso = $row['id_asta_percorso'];
                $id_percorso = $row['id_percorso'];
                $frequenza = $row['frequenza'];
                $ripasso = $row['ripasso'];

                $result_insert_percorsi_temp = pg_execute($conn_sit, "insert_percorsi_temp", array($id_percorso, $id_elemento, $frequenza, $ripasso));
                if (pg_last_error($conn_sit)){
                    echo pg_last_error($conn_sit);
                    $res_ok=$res_ok+1;
                }
            }
        }

        $result_add_tipo_intervento = pg_execute($conn_sit, "add_tipo_intervento", array($new_id_intervento, $id_tipo_intervento));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        $result_add_stato_intervento = pg_execute($conn_sit, "add_stato_intervento", array($new_id_intervento));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }
        if ($res_ok==0) {
            $result_h = pg_execute($conn_sit, "sql_history", array($desc_hist, 
                                    $_SESSION['username'],
                                    $id_piazzola, 
                                    $id_elemento));
            if (pg_last_error($conn_sit)){
                echo pg_last_error($conn_sit);
                $res_ok=$res_ok+1;
            }
        }
    }

}elseif ($_POST['tipo_intervento'] == 'rip') {

    // query per aggiungere elemento
    $add_elemento='INSERT INTO elem_temporanei.elementi
    (id_intervento, inserimento, 
    eliminazione, id_elemento,
    tipo_elemento, id_piazzola, 
    id_asta, posizione, privato, 
    peso_reale, peso_stimato, 
    riferimento, id_utenza, 
    nome_attivita, percent_riempimento, 
    freq_stimata, numero_civico, 
    lettera_civico, colore_civico, 
    note, serratura, id_materiale, data_disattivazione_temp)
    (select 
        distinct $1::integer, $2::integer, 
        $3::integer, $4::integer,
        tipo_elemento, id_piazzola, 
        (select id_asta from elem.piazzole where id_piazzola = e.id_piazzola), 
        max(posizione)as posizione, 
        max(privato) as privato,
        max(peso_reale) as peso_reale, 
        max(peso_stimato) as peso_stimato,  
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
        min(serratura) as serratura,
        min(id_materiale) as materiale,
        max(data_disattivazione_temp) as data_disattivazione_temp
        from elem.elementi e
        where e.id_piazzola = $5 and e.tipo_elemento = $6
        group by tipo_elemento, id_piazzola,
        id_utenza, nome_attivita
    )';

    // aggiungi elemento temporaneo con id_intervento = null
    $result_add = pg_prepare($conn_sit, "add_elemento", $add_elemento);
    //echo  pg_last_error($conn_sit);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }
    

    // preparo la query per verificare se gli elementi già presenti hanno dei percorsi associati 
    $check_percorsi='SELECT distinct p.id_percorso, eap.id_asta_percorso, 
    eap.frequenza, eap.ripasso
    FROM elem.percorsi p
    JOIN elem.aste_percorso ap
        ON ap.id_percorso = p.id_percorso
    JOIN elem.elementi_aste_percorso eap
        ON eap.id_asta_percorso = ap.id_asta_percorso
    JOIN elem.elementi e
        ON e.id_elemento = eap.id_elemento
    where e.id_elemento =$1 
    and p.id_categoria_uso in (3,6)';

    $result_check_percorsi = pg_prepare($conn_sit, "check_percorsi", $check_percorsi);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }


        
    foreach($ids_elementi as $ide){


        $result_add_intervento = pg_execute($conn_sit, "add_intervento", array($descrizione_intervento, $ide, $id_piazzola, $_SESSION['username']));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        # recupero l'id_interevento dalla query precedente
        $new_id_intervento=0;
        $arr_int = pg_fetch_array($result_add_intervento, 0, PGSQL_NUM);
        $new_id_intervento=intval($arr_int[0]);
        

        $result_add = pg_execute($conn_sit, "add_elemento", array($new_id_intervento, $inserimento, $eliminazione, $ide, $id_piazzola, $tipo_elemento));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        // se elemento privato aggiungo l'elemento anche a elem_temporanei.elementi_privati
        if ($id_utenzapap > intval(-1)) {
            
            $result_insert_privati = pg_execute($conn_sit, "insert_privati", array($ide, $id_piazzola, $tipo_elemento, $id_utenzapap));
            if (pg_last_error($conn_sit)){
                echo pg_last_error($conn_sit);
                $res_ok=$res_ok+1;
            }
        }

        $result_check_percorsi = pg_execute($conn_sit, "check_percorsi", array($ide));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        //ciclo sui percorsi che trovo associati ai contenitori dello stesso tipo e faccio insert su elem_temporanei.elem_percorsi

        $insert_percorsi_temp = 'INSERT INTO elem_temporanei.elementi_percorso
        (id_percorso, id_elemento, frequenza, ripasso)
        VALUES($1, $2, $3, $4);';

        $result_insert_percorsi_temp = pg_prepare($conn_sit, "insert_percorsi_temp", $insert_percorsi_temp);
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        if (pg_num_rows($result_check_percorsi) > 0) {
            while ($row = pg_fetch_assoc($result_check_percorsi)) {
                $id_asta_percorso = $row['id_asta_percorso'];
                $id_percorso = $row['id_percorso'];
                $frequenza = $row['frequenza'];
                $ripasso = $row['ripasso'];

                $result_insert_percorsi_temp = pg_execute($conn_sit, "insert_percorsi_temp", array($id_percorso, $ide, $frequenza, $ripasso));
                if (pg_last_error($conn_sit)){
                    echo pg_last_error($conn_sit);
                    $res_ok=$res_ok+1;
                }
            }
        }

        $result_add_tipo_intervento = pg_execute($conn_sit, "add_tipo_intervento", array($new_id_intervento, $id_tipo_intervento));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        $result_add_stato_intervento = pg_execute($conn_sit, "add_stato_intervento", array($new_id_intervento));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }
        if ($res_ok==0) {
            $result_h = pg_execute($conn_sit, "sql_history", array($desc_hist, 
                                    $_SESSION['username'],
                                    $id_piazzola, 
                                    $id_elemento));
            if (pg_last_error($conn_sit)){
                echo pg_last_error($conn_sit);
                $res_ok=$res_ok+1;
            }
        }
    }
}else{

    $del_elemento='INSERT INTO elem_temporanei.elementi
    (id_intervento, inserimento, 
    eliminazione, id_elemento,
    tipo_elemento, id_piazzola, 
    id_asta, posizione, privato, 
    peso_reale, peso_stimato, 
    riferimento, id_utenza, 
    nome_attivita, percent_riempimento, 
    freq_stimata, numero_civico, 
    lettera_civico, colore_civico, 
    note, serratura, id_materiale)
    (select 
        distinct $1::integer, $2::integer, $3::integer, $4::integer, tipo_elemento, id_piazzola, 
        (select id_asta from elem.piazzole where id_piazzola = e.id_piazzola), 
        max(posizione)as posizione, 
        max(privato) as privato,
        max(peso_reale) as peso_reale, 
        max(peso_stimato) as peso_stimato,  
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
        min(serratura) as serratura,
        min(id_materiale) as materiale
        from elem.elementi e
        where e.id_piazzola = $5 and e.tipo_elemento = $6
        group by tipo_elemento, id_piazzola,
        id_utenza, nome_attivita
    )';

    $result_del = pg_prepare($conn_sit, "del_elemento", $del_elemento);
    //echo  pg_last_error($conn_sit);
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    foreach($ids_elementi as $ide){

        // eseguo insert in gestione_oggetti.intervento
        $result_add_intervento = pg_execute($conn_sit, "add_intervento", array($descrizione_intervento, $ide, $id_piazzola, $_SESSION['username']));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        # recupero l'id_interevento dalla query precedente
        $new_id_intervento=0;
        $arr_int = pg_fetch_array($result_add_intervento, 0, PGSQL_NUM);
        $new_id_intervento=intval($arr_int[0]);

        $result_del = pg_execute($conn_sit, "del_elemento", array($new_id_intervento, $inserimento, $eliminazione, $ide, $id_piazzola, $tipo_elemento));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }
        $result_add_tipo_intervento = pg_execute($conn_sit, "add_tipo_intervento", array($new_id_intervento, $id_tipo_intervento));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }

        $result_add_stato_intervento = pg_execute($conn_sit, "add_stato_intervento", array($new_id_intervento));
        if (pg_last_error($conn_sit)){
            echo pg_last_error($conn_sit);
            $res_ok=$res_ok+1;
        }
        if ($res_ok==0) {
            $result_h = pg_execute($conn_sit, "sql_history", array($desc_hist, 
                                $_SESSION['username'],
                                $id_piazzola, 
                                $ide));
            if (pg_last_error($conn_sit)){
                echo pg_last_error($conn_sit);
                $res_ok=$res_ok+1;
            }
        }
    }

}

######################################################################################
#AGIUNGERE ANCHE LA SCRITTURA DI LOG SU UTIL_GO.SYS_HISTORY
######################################################################################


if ($res_ok==0) {
    pg_query($conn_sit, "COMMIT");
    echo 'Operazione terminata correttamente';
} else {
    pg_query($conn_sit, "ROLLBACK");
    echo '<br>ERRORE<br>';
    http_response_code(400);
}
?>