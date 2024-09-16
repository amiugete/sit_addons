<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}
//echo "OK";

if ($_SESSION['username']){
    $user=$_SESSION['username'];
} else {
    $user= $_COOKIE['un'];
}


if(!$conn) {
    die('Connessione fallita !<br />');
} else {


    $query0= "select ep.cod_percorso as cp_edit, ep.cod_percorso, ep.cod_percorso as cp_report, p.id_percorso as id_percorso_sit, 
    ep.descrizione, af.descrizione as famiglia,
    at2.descrizione as tipo,
    array_agg(u.id_ut) as id_uts,
    string_agg(distinct u.descrizione, ',') as ut,
    count(distinct pu.id_turno) as count_distinct_turni,
    fo.descrizione_long as freq,
    string_agg(distinct t.descrizione, ',') as turno, 
    ep.versione_testata as versione, 
    case 
    when ep.data_fine_validita <= now()::date then 'Disattivo'
    when ep.data_inizio_validita > now()::date and ep.data_fine_validita > now()::date then 'In attivazione'
    when ep.data_inizio_validita < now()::date and ep.data_fine_validita <= (current_date + 7)::date then 'In disattivazione'
    else 'Attivo'
    end flg_disattivo
    from anagrafe_percorsi.elenco_percorsi ep 
    left join elem.percorsi p on p.cod_percorso = ep.cod_percorso 
    	and p.id_categoria_uso in (3,6) 
    	and ep.versione_testata  = (select max(versione_testata) from anagrafe_percorsi.elenco_percorsi ep2 where ep2.cod_percorso=ep.cod_percorso)
    join anagrafe_percorsi.anagrafe_tipo at2 on at2.id = ep.id_tipo  
    join anagrafe_percorsi.anagrafe_famiglie af on af.id = at2.id_famiglia 
    left join anagrafe_percorsi.percorsi_ut pu on pu.cod_percorso = ep.cod_percorso 
    /*and pu.data_attivazione=ep.data_inizio_validita*/
    and pu.data_disattivazione=ep.data_fine_validita
    left join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo = pu.id_ut
    left join topo.ut u on u.id_ut  = cmu.id_uo_sit 
    join elem.turni t on t.id_turno = pu.id_turno 
    left join etl.frequenze_ok fo on fo.cod_frequenza::int = ep.freq_testata 
    group by ep.cod_percorso, p.id_percorso, 
    ep.descrizione, af.descrizione, at2.descrizione, fo.descrizione_long, ep.versione_testata,  
    ep.data_inizio_validita, ep.data_fine_validita
    order by 1,9";
    
    if($_GET['ut']) {
        $query= "select * from (".$query0.") a where $1 = any(id_uts) " ;  
    } else {
        require_once("../query_ut.php");
        $query= "select * from (".$query0.") a 
                where (select array_agg(id_ut) from (".$query_ut.") b) && (id_uts) ";
    }

    //print $query."<br>";
    //print $_SESSION['username'];

    $result = pg_prepare($conn, "my_query", $query);
    
    if($_GET['ut']) {
        $result = pg_execute($conn, "my_query", array($_GET['ut']));  
    } else {
        $result = pg_execute($conn, "my_query", array($user));
        //$result = pg_execute($conn, "my_query", array());
    }

   

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