<?php
session_start();
#require('../validate_input.php');

$scheda=$_GET['s'];

if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}
//echo "OK";


if(!$oraconn) {
    die('Connessione fallita !<br />');
} else {
    

    $query_raccolta="SELECT POSIZIONE, eip.ID_PIAZZOLA, s.NOME1 AS via, eip.NUM_CIVICO AS num_civ, 
    eip.RIFERIMENTO, count(COD_COMPONENTE) AS NUM_ELEMENTI, teds.NOME AS tipo_elemento, TOTEM, RIPROGRAMMATO,
    causale,
    cd.DESCRIZIONE AS descr_causale
    FROM CONSUNT_EKOVISION_RACCOLTA cer 
    JOIN (SELECT DISTINCT cast(ID_ELEMENTO AS INTEGER) AS ID_ELEMENTO,
        cmt2.id_piazzola, cmt2.ID_ASTA, cmt2.RIFERIMENTO, cmt.NUM_CIVICO   
        FROM CONS_MICRO_TAPPA cmt 
        JOIN CONS_MACRO_TAPPA cmt2 
        ON cmt2.ID_MACRO_TAPPA = cmt.ID_MACRO_TAPPA 
        WHERE REGEXP_LIKE(ID_ELEMENTO, '^[[:digit:]]+$'))  eip 
    ON eip.ID_ELEMENTO = cer.COD_COMPONENTE 
    JOIN (SELECT TIPO_ELEMENTO, cast(ID_ELEMENTO AS INTEGER) AS ID_ELEMENTO
        FROM CONS_ELEMENTI WHERE REGEXP_LIKE(ID_ELEMENTO, '^[[:digit:]]+$')) ce 
        ON ce.ID_ELEMENTO = cer.COD_COMPONENTE
    JOIN TIPI_ELEMENTO_DA_SIT teds ON teds.TIPO_ELEMENTO = ce.TIPO_ELEMENTO
    --LEFT JOIN strade.PIAZZOLE p ON p.ID_PIAZZOLA = eip.ID_PIAZZOLA 
    JOIN strade.ASTE a ON a.ID_ASTA= eip.ID_ASTA
    JOIN STRADE.STRADE s ON a.ID_VIA = s.CODICE_VIA 
    LEFT JOIN CAUSE_DISSERV cd ON cd.CODICE = cer.CAUSALE 
    WHERE ID_SCHEDA = :p1 AND RECORD_VALIDO = 'S'
    GROUP BY POSIZIONE, eip.ID_PIAZZOLA, s.NOME1, eip.NUM_CIVICO, 
eip.RIFERIMENTO, teds.NOME, TOTEM, RIPROGRAMMATO, cer.CAUSALE, cd.DESCRIZIONE
ORDER BY POSIZIONE";

    $result = oci_parse($oraconn, $query_raccolta);
    oci_bind_by_name($result, ':p1', $scheda);
    oci_execute($result);

    $rows = array();
    while($r = oci_fetch_assoc($result)) { 
        //print $r;
        $rows[] = $r;
    }
    oci_free_statement($result);
    oci_close($oraconn);
    //pg_close($conn);
    #echo $rows ;
    if (empty($rows)==FALSE){
        //print $rows;
        $json = json_encode(array_values($rows));
    } else {
        echo "[{\"NOTE\":'No data'}]";
    }

    if ($json){
        echo $json;
    } else {
        echo json_last_error_msg();
    }

    //exit(0);
    }


?>