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
 
    
$query0="select ordine_rifiuto, rifiuto, descr_orario,
descr_servizio, id_percorso, descr_percorso,
uo, 
uo_esec,
causali, 
causali_text,
case 
	when check_previsto > 0 then 'PREVISTO'
	else 'NON PREVISTO'
end in_previsione, 
case 
	when causali='100' then 'COMPLETATO'
	when causali like '%100%' then 'NON COMPLETATO' 
	when causali is not null and causali not like '%100%' then 'NON EFFETTUATO' 
	when causali is null then 'NON CONSUNTIVATO'
end stato_consuntivazione, datalav
from (
	select min(ordine_rifiuto) as ordine_rifiuto, string_agg(distinct tipo_rifiuto,', ') as rifiuto, descr_orario,
	descr_servizio, id_percorso, descr_percorso, 
	string_agg(distinct desc_uo, ',') as uo_esec,
	array_agg(distinct id_uo_esec) as id_uo_esec,
	array_agg(distinct id_uo) as uo,
	sum(
		check_previsto
	) as check_previsto,
	string_agg(distinct causale, ',') as causali, 
	string_agg(distinct descr_causale, ',') as causali_text, datalav
	from (
		select tr.ordinamento as ordine_rifiuto, cpra.tipo_rifiuto, at2.descr_orario,
		cpra.descr_servizio, cpra.id_percorso, cpra.descr_percorso, 
		cpra.desc_uo, cpra.id_uo as id_uo_esec,
		pu.id_uo,
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
		ea.datalav
		from raccolta.cons_percorsi_raccolta_amiu cpra
		left join raccolta.anagr_turni at2 on at2.id_turno = cpra.id_turno
		left join raccolta.tipi_rifiuto tr on tr.nome= cpra.tipo_rifiuto 
		left join raccolta.piazzole_ut pu on pu.id_piazzola=cpra.id_piazzola
		left join raccolta.effettuati_amiu ea on ea.id_tappa::bigint = cpra.id_tappa::bigint 
											and ea.datalav = to_date($1, 'DD/MM/YYYY')
		left join raccolta.causali_testi ct on trim(ct.descrizione) = trim(ea.causale)
		where (to_date($1, 'DD/MM/YYYY') between cpra.data_inizio and (cpra.data_fine - interval '1' day))
		) as step0
	group by descr_servizio, id_percorso, descr_percorso, descr_orario,datalav
) as step1
where (causali is not null or check_previsto > 0) and ($2 = any(uo)  or $2 = any(id_uo_esec))
order by descr_orario, ordine_rifiuto, descr_servizio, descr_percorso
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