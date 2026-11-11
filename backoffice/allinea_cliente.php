<?php
require_once '../session.php';
#require('../validate_input.php');
#scrivere su: elem.elementi_privati e util.sys_history.

require_once '../conn_ok.php';


$res_ok=0;

$tcliente = $_POST['tcliente'];
$desc = $_POST['desc'];
$id_piazzola = $_POST['id_piazzola'];
$id_via = $_POST['id_via'];
$civico = $_POST['civico'];
$id_uu = $_POST['id_uu'];
$id_ut = $_POST['id_ut'];
$check_piazzola = $_POST['check_piazzola'];
$tipo_elemento = $_POST['tipo_elemento'];

//recupero tutti gli id_elemento della piazzola
if ($check_piazzola === '1'){
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
} else {
    $select_all_ids = "SELECT string_agg(id_elemento::text, ',') AS ids_elementi from elem.elementi where id_piazzola = $1 and tipo_elemento = $2";
    $result_all_ids = pg_prepare($conn_sit, "select_all_ids", $select_all_ids);
    if (pg_last_error($conn_sit)) {
        echo pg_last_error($conn_sit);
        $res_ok = $res_ok + 1;
    }

    $result_all_ids = pg_execute($conn_sit, "select_all_ids", array($id_piazzola, $tipo_elemento));
    if (pg_last_error($conn_sit)) {
        echo pg_last_error($conn_sit);
        $res_ok = $res_ok + 1;
    }
}

$row_all_ids = pg_fetch_assoc($result_all_ids);

$codici_elementi = explode(',',$row_all_ids['ids_elementi']);

/*echo 'Elementi selezionati: '.$codici_elementi."<br>";

echo 'Cliente da associare: '.$tcliente.' - '.$desc."<br>";
echo 'Tipo elemento: '.$tipo_elemento."<br>";*/

//exit();

// recupero la descrizione della macrocategoria del cliente per costruire il riferimento
$query_macrocategoria = "select descrizione from utenze.macro_categorie where id_macro_categoria = $1";
$result_macrocategoria = pg_prepare($conn_sit, "query_macrocategoria", $query_macrocategoria);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_macrocategoria = pg_execute($conn_sit, "query_macrocategoria", array($tcliente)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    //echo pg_last_error($conn_sit);
    echo $_SESSION['username'];
    echo "<br><br>ERRORE su query macrocategoria<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
while($rmc = pg_fetch_assoc($result_macrocategoria)) { 
    $macrocategoria = $rmc['descrizione'];
}

$riferimento = $macrocategoria.' - '.$desc;

// recupero id_user per scrivere in sys_history
$query_user = "select id_user from util.sys_users su where \"name\" ilike $1";
$result_user = pg_prepare($conn_sit, "query_user", $query_user);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_user = pg_execute($conn_sit, "query_user", array($_SESSION['username'])); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    //echo pg_last_error($conn_sit);
    echo $_SESSION['username'];
    echo "<br><br>ERRORE su query id_user<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
while($ru = pg_fetch_assoc($result_user)) { 
    $id_user = $ru['id_user'];
}


// verifico se esiste già l'utenza per il cliente indicato altrimenti la creo per poterla poi inserire in elem.elementi_privati
$select_utenza = "SELECT * from utenze.utenze where id_piazzola = $1 and nome_attivita = $2";
$result_utenza = pg_prepare($conn_sit, "select_utenza", $select_utenza);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_utenza = pg_execute($conn_sit, "select_utenza", array($id_piazzola, $desc)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    //echo pg_last_error($conn_sit);
    echo $_SESSION['username'];
    echo "<br><br>ERRORE su query id_utenzapap<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
if (pg_num_rows($result_utenza) > 0) {
    $row_utenza = pg_fetch_assoc($result_utenza);
    $id_utenza = $row_utenza['id_utenza'];
} else {
    // insert in utenze.utenze per ricavare id_utenza_pap  

    $insert_utenze = "INSERT INTO utenze.utenze
    (id_via, civico, 
    riferimento, id_piazzola, 
    nome_attivita, data_inserimento, 
    id_uu, id_ut)
    VALUES($1, $2, $3, $4, $5, CURRENT_TIMESTAMP, $6, $7)
    RETURNING id_utenza;";

    $result_utenze = pg_prepare($conn_sit, "insert_utenze", $insert_utenze);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    $result_utenze = pg_execute($conn_sit, "insert_utenze", array($id_via, $civico, $riferimento, $id_piazzola, $desc, $id_uu, $id_ut)); 
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else { 
        //echo pg_last_error($conn_sit);
        echo $_SESSION['username'];
        echo "<br><br>ERRORE su insert in utenze.utenze<br>". pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }

    $utenza = pg_fetch_assoc($result_utenze);
    $id_utenza = $utenza['id_utenza'];
}

pg_query($conn_sit, "BEGIN");

//update di elem.elementi
$update_elementi = "UPDATE elem.elementi
SET riferimento = $1, nome_attivita = $2
WHERE id_piazzola = $3 AND id_elemento = $4";

$result_elementi = pg_prepare($conn_sit, "update_elementi", $update_elementi);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

// verifico se è già un elemento privato, se si faccio update altrimenti insert
$insert_elementi_privati = "INSERT INTO elem.elementi_privati
(
    id_elemento,
    id_macro_categoria,
    descrizione,
    id_utenzapap,
    id_tipo_ubicazione,
    data_attivazione
)
VALUES ($1, $2, $3, $4, 5, CURRENT_TIMESTAMP)
ON CONFLICT (id_elemento)
DO UPDATE SET
    id_macro_categoria = EXCLUDED.id_macro_categoria,
    descrizione = EXCLUDED.descrizione,
    id_utenzapap = EXCLUDED.id_utenzapap,
    id_tipo_ubicazione = 5,
    data_attivazione = CURRENT_TIMESTAMP";

$result_elementi_privati = pg_prepare($conn_sit, "insert_elementi_privati", $insert_elementi_privati);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}



//insert in util.sys_history
$insert_history="INSERT INTO util.sys_history
(\"type\", \"action\", description, datetime, id_user, id_elemento, id_utenzapap)
VALUES('ELEMENTO', 'INSERT', $1, CURRENT_TIMESTAMP, $2, $3, $4);";

$result_history = pg_prepare($conn_sit, "insert_history", $insert_history);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

//$id_elementi = array();
foreach($codici_elementi as $key ){
    $result_elementi = pg_execute($conn_sit, "update_elementi", array($riferimento, $desc, $id_piazzola, $key)); 
    if (!pg_last_error($conn_sit)){
        /*while($rie = pg_fetch_assoc($result_elementi)) {
            $id_elementi[] = $rie['id_elemento'];
        }*/
        #$res_ok=0;
    } else { 
        //echo pg_last_error($conn_sit);
        echo $_SESSION['username'];
        echo "<br><br>ERRORE su update di elem.elementi<br>". pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }

    $result_elementi_privati = pg_execute($conn_sit, "insert_elementi_privati", array($key, $tcliente, $desc, $id_utenza)); 
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else { 
        //echo pg_last_error($conn_sit);
        //echo $_SESSION['username'];
        echo "<br><br>ERRORE su insert in elem.elementi_privati<br>". pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }

    $result_history = pg_execute($conn_sit, "insert_history", array('Aggiunto Elemento PAP '.$key, $id_user, $key, $id_utenza)); 
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        //echo pg_last_error($conn_sit);
        echo $_SESSION['username'];
        echo "<br><br>Insert util.sys_history<br>". pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
}


if ($res_ok==0){
    pg_query($conn_sit, "COMMIT");
    echo 'Cliente correttamente associato a tutti gli elementi della piazzola';
} else {
    pg_query($conn_sit, "ROLLBACK");
    echo $res_ok.'ERRORE nell\'associazione del cliente agli elementi della piazzola';
    http_response_code(400);
}

?>