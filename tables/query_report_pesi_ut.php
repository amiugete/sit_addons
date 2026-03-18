<?php 


$query0 = "select
	row_number() OVER () as id,
    zona,
    id_rimessa,
    rimessa,
    id_ut,
    ut,
    cod_cer,
    descr_rifiuto,
    data_percorso,
    COUNT(*) AS numero_pesate,
    SUM(peso) AS peso_totale,
    COUNT(cod_percorso) AS numero_percorsi
FROM consunt.v_dettaglio_pesi_percorso";

$query00 = "
GROUP BY
    zona,
    id_rimessa,
    rimessa,
    id_ut,
    ut,
    cod_cer,
    descr_rifiuto,
	data_percorso
order by ut, data_percorso desc";



//print $query."<br>";






?>