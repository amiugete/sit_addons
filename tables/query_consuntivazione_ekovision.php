<?php 


$query0 = "SELECT concat(concat(ss.ID_SERVZIO_STAMPA, '-'), ss.DESCRIZIONE) AS FAM_SERVIZIO, 
as2.DESC_SERVIZIO, 
LISTAGG(au.ID_UO, ', ') within group (order by au.ID_UO) AS ID_UTS,
LISTAGG(au.DESC_UO, ', ') within group (order by au.ID_UO) AS UT,
vrpe.DATA_PIANIFICATA, vrpe.DATA_ESECUZIONE, vrpe.COD_PERCORSO,
vrpe.DESCRIZIONE, 
concat(concat(vrpe.COD_PERCORSO, ' - '),vrpe.DESCRIZIONE) as PERCORSO,
CASE 
    when vrpe.PREVISTO = 1 then 'Previsto'
    else 'Non previsto'
end PREVISTO,
vrpe.ORARIO_ESECUZIONE, 
vrpe.FASCIA_TURNO, vrpe.FLG_SEGN_SRV_NON_COMPL,
vrpe.FLG_SEGN_SRV_NON_EFFETT, 
cd.DESCRIZIONE AS DESCR_CAUSALE, 
vrpe.STATO, vrpe.ID_SCHEDA 
FROM UNIOPE.V_REPORT_PERCORSI_EKOVISION vrpe
JOIN ANAGR_SER_PER_UO aspu ON vrpe.DATA_PIANIFICATA >=aspu.DTA_ATTIVAZIONE 
						AND vrpe.DATA_PIANIFICATA < aspu.DTA_DISATTIVAZIONE 
						AND trim(vrpe.COD_PERCORSO) = trim(aspu.ID_PERCORSO) 
JOIN ANAGR_UO au ON au.ID_UO = aspu.ID_UO 
JOIN ANAGR_SERVIZI as2 ON as2.ID_SERVIZIO = aspu.ID_SERVIZIO 
JOIN SERVIZIO_STAMPA ss ON ss.ID_SERVZIO_STAMPA = as2.ID_SERVIZIO_STAMPA
LEFT JOIN CAUSE_DISSERV cd ON CAST(vrpe.COD_CAUS_SRV_NON_ESEG_EXT AS integer) = cd.CODICE";


$query1= "GROUP BY ss.ID_SERVZIO_STAMPA, ss.DESCRIZIONE, as2.DESC_SERVIZIO, vrpe.DATA_PIANIFICATA, vrpe.DATA_ESECUZIONE, vrpe.COD_PERCORSO,
vrpe.DESCRIZIONE, vrpe.PREVISTO, vrpe.ORARIO_ESECUZIONE, 
vrpe.FASCIA_TURNO, vrpe.FLG_SEGN_SRV_NON_COMPL,
vrpe.FLG_SEGN_SRV_NON_EFFETT, vrpe.STATO, vrpe.ID_SCHEDA, cd.DESCRIZIONE
ORDER BY vrpe.DATA_PIANIFICATA, ss.ID_SERVZIO_STAMPA, vrpe.COD_PERCORSO";



//print $query."<br>";






?>