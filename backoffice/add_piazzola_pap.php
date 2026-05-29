<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
#require('../validate_input.php');
#scrivere su: elem.elementi_privati e util.sys_history.

require_once '../conn_ok.php';


$res_ok=0;

$lista_elem = $_POST["lista_elem"];
//echo 'elementi selezionati: '.$lista_elem."<br>";

$codici_elementi = explode(',', $_POST['lista_elementi_valori']);
/*foreach($codici_elementi as $key ){
  echo "Codice: ".$key."<br>";
}*/


$nomi_elementi = explode(',', $_POST['lista_elementi_nomi']);
/*foreach($nomi_elementi as $key ){
  //echo "Nome elementi: ".strtoupper(trim(explode('(', $key)[1], ')'))."<br>";
  echo "Nome elementi: ".strtoupper($key)."<br>";
}*/

if ($_POST['suolo_privato'] == 'true'){
    $suolo_privato = 1;
} else {
    $suolo_privato = 0;
}

$tcliente = $_POST['tcliente'];
$desc = $_POST['desc'];
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

//echo "macrocategoria: ".$macrocategoria."<br>";
//echo "descrizione: ".$desc."<br>";
//echo "tcliente: ".$tcliente."<br>";

$riferimento = $macrocategoria.' - '.$desc;
$comune = $_POST['comune_list'];
$via = $_POST['via_list'];
$civ = $_POST['civ_list'];

$civ_list = str_split($civ);
if (count($civ_list) > 1){
    $civico = $civ_list[0];
    $lettera_civico = $civ_list[1];
    if (count($civ_list) > 2){
        $colore_civico = $civ_list[2];
    } else {
        $colore_civico = NULL;
    }
}else {
    $civico = $civ_list[0];
    $lettera_civico = NULL;
    $colore_civico = NULL;
}

//echo "civico: ".$civico."<br>";
//echo "lettera_civico: ".$lettera_civico."<br>";
//echo "colore_civico: ".$colore_civico."<br>";

$select_asta = "select * from (
select g.id, 
st_distance(g.geoloc, vc.geoloc ) as distance,
vc.geoloc,
ST_AsText(vc.geoloc) as geoloc_text,
a.id_ut,
a.id_uu
from geo.grafostradale g
join elem.aste a on a.id_asta  = g.id 
JOIN geo.v_civici vc ON vc.cod_strada = $1 AND vc.testo = $2
where a.id_via = $3
order by 2 asc limit 1)";

$result_asta = pg_prepare($conn_sit, "select_asta", $select_asta);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_asta = pg_execute($conn_sit, "select_asta", array($via, $civ, $via)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    //echo pg_last_error($conn_sit);
    echo $via;
    echo $civ;
    echo $_SESSION['username'];
    echo "<br><br>ERRORE su select id_asta<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
while($ra = pg_fetch_assoc($result_asta)) {

    $id_asta = $ra['id'];
    $geom = $ra['geoloc'];
    $geom_text = $ra['geoloc_text'];
    $id_ut = $ra['id_ut'];
    $id_uu = $ra['id_uu'];
}

//echo "asta: ".$id_asta."<br>";
//echo "geom: ".$geom."<br>";
//echo "tipo geom: ".gettype($geom)."<br>";
//echo "geom_text: ".$geom_text."<br>";
//echo "tipo geom_text: ".gettype($geom_text)."<br>";

if (isset($_POST['piazzola_esistente'])){
    $id_piazzola_esistente = $_POST['piazzola_esistente'];
    //verifico se la geom della piazzola esistente ricade in un buffer di 4m dalla geom del civico 
    $select_geom_esistente = "select 
        case when st_intersects(p.geoloc, st_buffer(c.geoloc, 4)) then ST_AsText(ST_LineInterpolatePoint( g.geoloc, ST_LineLocatePoint(g.geoloc, p.geoloc)))
        else 'NO'
        end as coincidono
        from elem.piazzole vp 
        join geo.v_civici c on  c.cod_strada = vp.id_via::text and c.testo ilike vp.numero_civico||coalesce(vp.lettera_civico,'')||coalesce(vp.colore_civico,'')
        join elem.v_piazzole_dwh p on p.id_piazzola  = vp.id_piazzola
        join geo.grafostradale g on g.id = vp.id_asta  
        where vp.id_piazzola = $1";
    $result_geom_esistente = pg_prepare($conn_sit, "select_geom_esistente", $select_geom_esistente);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {          
        pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    $result_geom_esistente = pg_execute($conn_sit, "select_geom_esistente", array($id_piazzola_esistente)); 
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else { 
        //echo pg_last_error($conn_sit);
        echo $_SESSION['username'];
        echo "<br><br>ERRORE su select geom piazzola esistente<br>". pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    while($rge = pg_fetch_assoc($result_geom_esistente)) { 
        $geom_esistente = $rge['coincidono'];
    }
    //echo "geom_esistente: ".$geom_esistente."<br>";
    if ($geom_esistente !== 'NO'){
        //se coincide faccio un update della geom della piazzola esistente spostandola sul punto più vicino dell'asta
        $update_geom_esistente = "update geo.piazzola set geoloc = $1, data_ultima_modifica = CURRENT_TIMESTAMP where id = $2";
        $result_update_geom_esistente = pg_prepare($conn_sit, "update_geom_esistente", $update_geom_esistente);
        if (!pg_last_error($conn_sit)){
            #$res_ok=0;
        } else {
            pg_last_error($conn_sit);
            $res_ok= $res_ok+1; 
        }
        $result_update_geom_esistente = pg_execute($conn_sit, "update_geom_esistente", array($geom_esistente, $id_piazzola_esistente)); 
        if (!pg_last_error($conn_sit)){
            #$res_ok=0;
        } else { 
            //echo pg_last_error($conn_sit);
            echo $_SESSION['username'];
            echo "<br><br>ERRORE su update geom piazzola esistente<br>". pg_last_error($conn_sit);
            $res_ok= $res_ok+1;
        }
    }
}


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

?>
<?php
//insert in elem.piazzole

$insert_piazzola = "INSERT INTO elem.piazzole
(riferimento, numero_civico,
id_asta, colore_civico,
lettera_civico, numero_civico_orig,
id_via, suolo_privato,
id_transitabilita
)
VALUES
($1, $2,
$3, $4,
$5, $6,
$7, $8,
(select id_transitabilita from elem.aste where id_asta = $3)
)
RETURNING id_piazzola;";

$result_piazzola = pg_prepare($conn_sit, "insert_piazzola", $insert_piazzola);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_piazzola = pg_execute($conn_sit, "insert_piazzola", array($riferimento, $civico, $id_asta, $colore_civico, $lettera_civico, $civ, $via, $suolo_privato)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    //echo pg_last_error($conn_sit);
    echo $_SESSION['username'];
    echo "<br><br>ERRORE su insert piazzola<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$piazzola = pg_fetch_assoc($result_piazzola);
$id_piazzola = $piazzola['id_piazzola'];

//insert in geo.piazzole

$insert_geo_piazzola = "INSERT INTO geo.piazzola
(id, geoloc, data_ultima_modifica)
VALUES($1, $2, CURRENT_TIMESTAMP);";

$result_geo_piazzola = pg_prepare($conn_sit, "insert_geo_piazzola", $insert_geo_piazzola);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_geo_piazzola = pg_execute($conn_sit, "insert_geo_piazzola", array($id_piazzola, $geom_text)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else { 
    //echo pg_last_error($conn_sit);
    echo $_SESSION['username'];
    echo "<br><br>ERRORE su insert in geo.piazzola<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}



//insert in elem.elementi

$insert_elementi = "INSERT INTO elem.elementi
(tipo_elemento, id_piazzola, 
id_asta, x_numero_civico_old, riferimento,
nome_attivita, numero_civico, 
lettera_civico, colore_civico,
percent_riempimento, freq_stimata)
VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9,
(select percent_riempimento from elem.tipi_elemento where tipo_elemento = $1),
(select freq_stimata from elem.tipi_elemento where tipo_elemento = $1));";


$result_elementi = pg_prepare($conn_sit, "insert_elementi", $insert_elementi);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

foreach($codici_elementi as $key ){
    $result_elementi = pg_execute($conn_sit, "insert_elementi", array($key, $id_piazzola, $id_asta, $civ, $riferimento, $desc, $civico, $lettera_civico, $colore_civico)); 
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else { 
        //echo pg_last_error($conn_sit);
        echo $_SESSION['username'];
        echo "<br><br>ERRORE su insert in elem.elementi<br>". pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
}


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
$result_utenze = pg_execute($conn_sit, "insert_utenze", array($via, $civ, $riferimento, $id_piazzola, $desc, $id_uu, $id_ut)); 
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


//insert in elem.elementi_privati

$select_id_elemento = "select id_elemento from elem.elementi where id_piazzola = $1";
$result_id_elemento = pg_prepare($conn_sit, "select_id_elemento", $select_id_elemento);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_id_elemento = pg_execute($conn_sit, "select_id_elemento", array($id_piazzola)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else { 
    //echo pg_last_error($conn_sit);
    echo $_SESSION['username'];
    echo "<br><br>ERRORE su select id_elemento<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$id_elementi = array();
while($rie = pg_fetch_assoc($result_id_elemento)) {
    $id_elementi[] = $rie['id_elemento'];
}

$insert_elementi_privati = "INSERT INTO elem.elementi_privati
(id_elemento, id_macro_categoria, 
descrizione, id_utenzapap,
id_tipo_ubicazione, data_attivazione)
VALUES($1, $2, $3, $4, 5, CURRENT_TIMESTAMP);";

$result_elementi_privati = pg_prepare($conn_sit, "insert_elementi_privati", $insert_elementi_privati);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
foreach($id_elementi as $key ){
    $result_elementi_privati = pg_execute($conn_sit, "insert_elementi_privati", array($key, $tcliente, $desc, $id_utenza)); 
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else { 
        //echo pg_last_error($conn_sit);
        //echo $_SESSION['username'];
        echo "<br><br>ERRORE su insert in elem.elementi_privati<br>". pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
}

//insert in util.sys_history
$insert_history="INSERT INTO util.sys_history
(\"type\", \"action\", description, datetime, id_user, id_piazzola, id_utenzapap)
VALUES('PIAZZOLA', 'INSERT', 'Creata nuova piazzola PAP', CURRENT_TIMESTAMP, $1, $2, $3);";

$result_history = pg_prepare($conn_sit, "insert_history", $insert_history);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_history = pg_execute($conn_sit, "insert_history", array($id_user, $id_piazzola, $id_utenza)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    //echo pg_last_error($conn_sit);
    echo $nota_storico;
    echo $cod_percorso;
    echo $_SESSION['username'];
    echo "<br><br>Insert util.sys_history<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

if ($res_ok==0){
    echo 'Piazzola creata correttamente';
} else {
    echo $res_ok.'ERRORE nella creazione della piazzola';
}

?>