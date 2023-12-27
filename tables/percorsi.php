<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}
//echo "OK";


if(!$conn) {
    die('Connessione fallita !<br />');
} else {



    
    
    $query= "select ep.cod_percorso, ep.id_percorso_sit, 
    ep.descrizione, af.descrizione as famiglia,
    at2.descrizione as tipo,
    u.descrizione as ut,
    count(distinct pu.id_turno) as count_distinct_turni,
    fo.descrizione_long as freq,
    string_agg(distinct t.descrizione, ',') as turno
    from anagrafe_percorsi.elenco_percorsi ep 
    join anagrafe_percorsi.anagrafe_tipo at2 on at2.id = ep.id_tipo  
    join anagrafe_percorsi.anagrafe_famiglie af on af.id = at2.id_famiglia 
    join anagrafe_percorsi.percorsi_ut pu on pu.cod_percorso = ep.cod_percorso 
    left join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo = pu.id_ut
    left join topo.ut u on u.id_ut  = cmu.id_uo_sit 
    join elem.turni t on t.id_turno = pu.id_turno 
    left join etl.frequenze_ok fo on fo.cod_frequenza::int = ep.freq_testata 
    where pu.data_disattivazione > now()::date
    group by ep.cod_percorso, ep.id_percorso_sit, u.descrizione,
    ep.descrizione, af.descrizione, at2.descrizione, fo.descrizione_long
    order by 1,9";

    //print $query."<br>";

    $result = pg_prepare($conn, "my_query", $query);
    $result = pg_execute($conn, "my_query", array());

    $rows = array();
    while($r = pg_fetch_assoc($result)) {
        $rows[] = $r;
        //print $r['id'];
    }
    
    
    //pg_close($conn);
	#echo $rows ;
	if (empty($rows)==FALSE){
		//print $rows;
		print json_encode(array_values(pg_fetch_all($result)));
	} else {
		echo "[{\"NOTE\":'No data'}]";
	}
}


?>