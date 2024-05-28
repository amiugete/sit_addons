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
    

    $query_spazzamento="SELECT min(posizione) AS POS, s.NOME1 AS VIA, COALESCE(eip.NOTA_VIA,' ') AS NOTA_VIA,
    count(DISTINCT COD_TRATTO) AS NUM_TRATTI, 
    sum(QTA_TOT_SPAZZAMENTO) AS MQ_DA_SPAZZARE, TOTEM, RIPROGRAMMATO, RIPASSO,
    ces.QUALITA,
    ces.CAUSALE,
    cd.DESCRIZIONE AS descr_causale
    FROM CONSUNT_EKOVISION_SPAZZAMENTO ces 
    JOIN ( SELECT * FROM CONS_MACRO_TAPPA cmt 
     JOIN CONS_PERCORSI_VIE_TAPPE cpvt ON cpvt.ID_TAPPA = cmt.ID_MACRO_TAPPA ) eip 
    ON eip.ID_ASTA  = ces.COD_TRATTO
    AND eip.ID_PERCORSO=ces.CODICE_SERV_PRED 
    AND eip.DATA_PREVISTA = (SELECT max(DATA_PREVISTA) FROM CONS_PERCORSI_VIE_TAPPE 
    WHERE DATA_PREVISTA <= to_date(ces.DATA_ESECUZIONE_PREVISTA , 'YYYYMMDD') AND to_char(DATA_PREVISTA, 'HH24') = '00' AND
    ID_PERCORSO = ces.CODICE_SERV_PRED)
    JOIN strade.ASTE a ON a.ID_ASTA= eip.ID_ASTA
    JOIN STRADE.STRADE s ON a.ID_VIA = s.CODICE_VIA 
    LEFT JOIN CAUSE_DISSERV cd ON cd.CODICE = ces.CAUSALE 
    WHERE ID_SCHEDA = :p1 AND RECORD_VALIDO = 'S'
    GROUP BY s.NOME1, COALESCE(eip.NOTA_VIA,' '), TOTEM, RIPROGRAMMATO, ces.QUALITA, ces.CAUSALE, cd.DESCRIZIONE, 
    RIPASSO
    ORDER BY 1";

    $result = oci_parse($oraconn, $query_spazzamento);
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