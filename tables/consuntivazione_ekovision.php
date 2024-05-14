<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}
//echo "OK";


if(!$oraconn) {
    die('Connessione fallita !<br />');
} else {
    $query0 = "SELECT ss.DESCRIZIONE AS FAM_SERVIZIO, 
as2.DESC_SERVIZIO, 
LISTAGG(au.DESC_UO, ', ') within group (order by au.ID_UO) AS UT,
vrpe.DATA_PIANIFICATA, vrpe.DATA_ESECUZIONE, vrpe.COD_PERCORSO,
vrpe.DESCRIZIONE, 
CASE 
    when vrpe.PREVISTO = 1 then 'Previsto'
    else 'Non previsto'
end PREVISTO,
vrpe.ORARIO_ESECUZIONE, 
vrpe.FASCIA_TURNO, vrpe.FLG_SEGN_SRV_NON_COMPL,
vrpe.FLG_SEGN_SRV_NON_EFFETT, vrpe.STATO, vrpe.ID_SCHEDA 
FROM UNIOPE.V_REPORT_PERCORSI_EKOVISION vrpe
JOIN ANAGR_SER_PER_UO aspu ON vrpe.DATA_PIANIFICATA >=aspu.DTA_ATTIVAZIONE 
						AND vrpe.DATA_PIANIFICATA < aspu.DTA_DISATTIVAZIONE 
						AND trim(vrpe.COD_PERCORSO) = trim(aspu.ID_PERCORSO) 
JOIN ANAGR_UO au ON au.ID_UO = aspu.ID_UO 
JOIN ANAGR_SERVIZI as2 ON as2.ID_SERVIZIO = aspu.ID_SERVIZIO 
JOIN SERVIZIO_STAMPA ss ON ss.ID_SERVZIO_STAMPA = as2.ID_SERVIZIO_STAMPA";


$query1= "GROUP BY ss.ID_SERVZIO_STAMPA, ss.DESCRIZIONE, as2.DESC_SERVIZIO, vrpe.DATA_PIANIFICATA, vrpe.DATA_ESECUZIONE, vrpe.COD_PERCORSO,
vrpe.DESCRIZIONE, vrpe.PREVISTO, vrpe.ORARIO_ESECUZIONE, 
vrpe.FASCIA_TURNO, vrpe.FLG_SEGN_SRV_NON_COMPL,
vrpe.FLG_SEGN_SRV_NON_EFFETT, vrpe.STATO, vrpe.ID_SCHEDA 
ORDER BY vrpe.DATA_PIANIFICATA, ss.ID_SERVZIO_STAMPA, vrpe.COD_PERCORSO";

$query = $query0 ." WHERE vrpe.DATA_PIANIFICATA > to_date('20240401', 'YYYYMMDD' )". $query1;

//print $query."<br>";



$result = oci_parse($oraconn, $query);
//oci_bind_by_name($result3, ':p1', $turno);
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

exit(0);
}


?>