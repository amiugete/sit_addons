<?php
session_start();
#require('../validate_input.php');

header('Content-Type: application/json; charset=utf-8');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}
//echo "OK";

$dt= new DateTime();
$today = new DateTime();
$last_month = $dt->modify("-1 month");




/*echo $_GET["d"];
echo "<br>";
echo $_GET["uos"] ;
*/
# cerco le UT dell'utente


$filter_bis=" and trim(coalesce(descr_causale,'')) != 'COMPLETATO' ";
if($_GET["c"]=='all'){
	$filter_bis="";
}  

#exit();






if(!$conn_hub) {
    die('Connessione fallita !<br />');
} else {
 
    
$query0="select concat(id_piazzola, ' - ',
		nome_via, ' ',
		civico, utente_posizione) as piazzola, ordine_rifiuto, tipo_rifiuto, id_percorso, descr_percorso, descr_orario,
string_agg(desc_uo, ', ') as uo_esec, 
case
	when descr_causale = 'COMPLETATO' then 'OK'
	when descr_causale is null then 'NON CONSUNTIVATO'
	when descr_causale != 'COMPLETATO' then 'ANOMALIA'
end stato_consuntivazione,
descr_causale, 
check_previsto, 
datalav
from (
	select 
		tr.ordinamento as ordine_rifiuto, cpra.tipo_rifiuto, cpra.id_percorso, cpra.descr_percorso, 
		cpra.desc_uo,
		pu.id_uo,
		cpra.id_piazzola, 
		cpra.nome_via, 
		cpra.civico,
		cpra.utente_posizione,
		case 
			when (ea.fatto=ea.num_elementi and trim(replace(ea.causale, ' - (no in questa giornata)', '')) = '') then 'COMPLETATO'
			else trim(replace(ea.causale, ' - (no in questa giornata)', '')) 
		end as descr_causale
		,
		case 
			when (ea.fatto=ea.num_elementi and trim(replace(ea.causale, ' - (no in questa giornata)', '')) = '') then '100'
			else ct.id 
		end as causale,
		case 
			when extract(dow from to_date($1, 'DD/MM/YYYY'))=1 then cpra.lun
			when extract(dow from to_date($1, 'DD/MM/YYYY'))=2 then cpra.mar
			when extract(dow from to_date($1, 'DD/MM/YYYY'))=3 then cpra.mer
			when extract(dow from to_date($1, 'DD/MM/YYYY'))=4 then cpra.gio
			when extract(dow from to_date($1, 'DD/MM/YYYY'))=5 then cpra.ven
			when extract(dow from to_date($1, 'DD/MM/YYYY'))=6 then cpra.sab
			when extract(dow from to_date($1, 'DD/MM/YYYY'))=7 then cpra.dom
		end as check_previsto, 
		ea.datalav, at2.descr_orario
		from raccolta.cons_percorsi_raccolta_amiu cpra 
		left join raccolta.tipi_rifiuto tr on tr.nome= cpra.tipo_rifiuto
		left join raccolta.anagr_turni at2 on at2.id_turno = cpra.id_turno
		left join raccolta.piazzole_ut pu on pu.id_piazzola=cpra.id_piazzola
		left join raccolta.effettuati_amiu ea on ea.id_tappa::bigint = cpra.id_tappa::bigint 
											and ea.datalav = to_date($1, 'DD/MM/YYYY')
		left join raccolta.causali_testi ct on trim(ct.descrizione) = trim(ea.causale)
		where (to_date($1, 'DD/MM/YYYY') between cpra.data_inizio and cpra.data_fine)
		and pu.id_uo = ANY(string_to_array($2, ',')::int[])
) as step0
where (check_previsto = 1 or causale is not null) ".$filter_bis."
group by  id_piazzola, nome_via, civico, utente_posizione, tipo_rifiuto, id_percorso, descr_percorso, descr_causale, 
causale, 
check_previsto, 
datalav, ordine_rifiuto, descr_orario
order by descr_orario, stato_consuntivazione, ordine_rifiuto, nome_via, civico 
            ";


//echo $query0;
//echo $uos;
//echo "Sono qua";



$result = pg_prepare($conn_hub, "query0", $query0);

if (!pg_last_error($conn_hub)){
    #$res_ok=0;
} else {
    pg_last_error($conn_hub);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 2";
$result = pg_execute($conn_hub, "query0", array($_GET["d"], $_GET['uos']));  
if (!pg_last_error($conn_hub)){
    #$res_ok=0;
} else {
    pg_last_error($conn_hub);
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