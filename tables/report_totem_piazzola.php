<?php
require_once '../session.php';
#require('../validate_input.php');

header('Content-Type: application/json; charset=utf-8');


require_once '../conn_ok.php';
//echo "OK";

$dt= new DateTime();
$today = new DateTime();
$last_month = $dt->modify("-1 month");




/*echo $_GET["d"];
echo "<br>";
echo $_GET["uos"] ;
*/
# cerco le UT dell'utente


$filter_bis=" and (step0.causale is null or step0.causale <> 100) ";
if($_GET["c"]=='all'){
	$filter_bis="";
}  

#exit();






if(!$conn_totem) {
    die('Connessione fallita !<br />');
} else {
 
if ($_GET['filter']){
    foreach(json_decode($_GET['filter']) as $key => $val) {
        /*if (is_numeric($val)){
            $filter = $filter. " AND ".$key." = ".$val." ";
        } else {*/
            $filter = $filter." AND upper(".$key.") LIKE upper('%".$val."%') ";
        //} 
         
    }
} 

$query="select piazzola, ordine_rifiuto, tipo_rifiuto, id_percorso, descr_percorso, descr_orario,
uo_esec, id_uo_esec, id_uo, stato_consuntivazione,
descr_causale, check_previsto, 
datalav
from (
select concat(id_piazzola, ' - ',
		nome_via, ' ',
		civico, utente_posizione) as piazzola, 
		nome_via, civico, ordine_rifiuto, tipo_rifiuto, id_percorso, descr_percorso, descr_orario,
string_agg(desc_uo, ', ') as uo_esec, 
array_agg(id_uo_esec) as id_uo_esec,
id_uo,
case
	when vc.descrizione = 'COMPLETATO' then 'OK'
	when vc.descrizione is null then 'NON CONSUNTIVATO'
	when vc.descrizione != 'COMPLETATO' then 'ANOMALIA'
end stato_consuntivazione,
vc.descrizione as descr_causale, 
case 
	when check_previsto > 0 then 'PREVISTO'
	else 'NON PREVISTO'
end check_previsto, 
datalav
from (
	select 
		tr.ordinamento as ordine_rifiuto,
		cpra.tipo_rifiuto, 
		cpra.id_percorso, 
		cpra.desc_percorso as descr_percorso, 
		cpra.desc_uo,
		cpra.id_uo as id_uo_esec,
		pu.id_uo,
		cpra.id_piazzola, 
		cpra.nome_via, 
		cpra.civico,
		cpra.utente_posizione,
		ea.id_causale as causale,
		totem.verify_daily_frequency(
            cod_frequenza_tratto,            
            TO_DATE($1, 'DD/MM/YYYY'),
            freq_settimane
        ) AS check_previsto,
		ea.datalav, at2.descr_orario
		from raccolta.cons_percorsi_raccolta_amiu cpra 
		left join raccolta.tipi_rifiuto tr on tr.nome= cpra.tipo_rifiuto
		left join raccolta.anagr_turni at2 on at2.id_turno = cpra.id_turno
		left join raccolta.piazzole_ut pu on pu.id_piazzola=cpra.id_piazzola
		left join raccolta.effettuati_amiu ea on ea.tappa::bigint = cpra.id_tappa::bigint 
											and ea.datalav = to_date($1, 'DD/MM/YYYY')
		--left join raccolta.causali_testi ct on trim(ct.descrizione) = trim(ea.causale)
		where (to_date($1, 'DD/MM/YYYY') between cpra.data_inizio and (cpra.data_fine - interval '1' day))
		/*and (pu.id_uo = ANY(string_to_array($2, ',')::int[]) OR $2 = any(id_uo_esec))*/
) as step0
left join totem.v_causali vc on vc.id = step0.causale
where (check_previsto = 1 or causale is not null) ".$filter_bis."
group by  id_piazzola, nome_via, civico, utente_posizione, tipo_rifiuto, id_percorso, descr_percorso, descr_causale, 
causale, 
check_previsto, 
datalav, ordine_rifiuto, descr_orario, id_uo
) as step1
where id_uo=$2 or $2=any(id_uo_esec)
order by descr_orario, stato_consuntivazione, ordine_rifiuto, nome_via, civico
            ";


//echo $query0;
//echo $uos;
//echo "Sono qua";

$query0 = "select * from (".$query.") a where 1=1 ".$filter ;

$result = pg_prepare($conn_totem, "query0", $query0);

if (!pg_last_error($conn_totem)){
    #$res_ok=0;
} else {
    pg_last_error($conn_totem);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 2";
$result = pg_execute($conn_totem, "query0", array($_GET["d"], $_GET['uos']));  
if (!pg_last_error($conn_totem)){
    #$res_ok=0;
} else {
    pg_last_error($conn_totem);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 3";


$rows = array();
while($r = pg_fetch_assoc($result)) {
    $rows[] = $r;
    //echo $r['piazzola'];
}
        


require_once("./json_no_paginazione.php");



exit(0);
}


?>