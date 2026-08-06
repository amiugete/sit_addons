<?php 


$query0 = "select
	row_number() OVER () as id,
    zona,
    ut_titolare, id_ut_titolare, 
    ut_esecutrice, id_ut_esecutrice, 
    cod_cer,
    descr_rifiuto,
    data_percorso,
    COUNT(*) AS numero_pesate,
    SUM(peso) AS peso_totale,
    COUNT(cod_percorso) AS numero_percorsi
FROM consunt.v_dettaglio_pesi_percorso_ok";

$query00 = "
GROUP BY
    zona,
    ut_titolare, id_ut_titolare, 
    ut_esecutrice, id_ut_esecutrice, 
    cod_cer,
    descr_rifiuto,
	data_percorso
order by ut_titolare, data_percorso desc";



//print $query."<br>";






?>