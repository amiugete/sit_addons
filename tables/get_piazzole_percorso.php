<?php

require_once '../session.php';

require_once '../conn_ok.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {


	$query="with percorso_sel as (
    select id_percorso 
    from elem.percorsi 
    where cod_percorso = $1 
      and id_categoria_uso in (3,6)
    )
    select ap.num_seq, coalesce(e.id_piazzola, e.id_elemento) as id, 
        v.nome as via, 
        coalesce(p.numero_civico, e.numero_civico) as civico, 
        coalesce(p.riferimento, e.riferimento) as riferimento, 
        te.descrizione as tipo_elemento, 
        count(e.id_elemento) as elementi,
        coalesce(p.note,e.note) as note,
        eap.frequenza, fo.descrizione_long, fo.freq_binaria,
        CASE
            WHEN count(distinct e.id_elemento) = count(distinct ep.id_elemento) THEN true
            ELSE false
      	END AS is_pap,
      	concat(mc.descrizione, ' - ', ep.descrizione) as cliente,
        a.id_asta,
        ST_AsGeoJSON(ST_Transform(gg.geoloc, 4326))::json AS geometry 
        from elem.elementi_aste_percorso eap
        join elem.aste_percorso ap on eap.id_asta_percorso = ap.id_asta_percorso 
        join percorso_sel ps on ps.id_percorso = ap.id_percorso
        join elem.elementi e on eap.id_elemento = e.id_elemento
        left join elem.elementi_privati ep on ep.id_elemento = e.id_elemento
        left join utenze.macro_categorie mc on mc.id_macro_categoria = ep.id_macro_categoria
        left join elem.piazzole p on p.id_piazzola = e.id_piazzola
        join elem.aste a on e.id_asta = a.id_asta 
        join topo.vie v on v.id_via = a.id_via
        join geo.grafostradale gg on gg.id = a.id_asta 
        join etl.frequenze_ok fo on eap.frequenza::int = fo.cod_frequenza  
        join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento  
        group by coalesce(e.id_piazzola, e.id_elemento) , 
        v.nome, 
        coalesce(p.numero_civico, e.numero_civico), 
        coalesce(p.riferimento, e.riferimento), 
        te.descrizione, 
        coalesce(p.note,e.note),
        eap.frequenza, fo.descrizione_long, fo.freq_binaria, 
        ap.num_seq, concat(mc.descrizione, ' - ', ep.descrizione), 
        a.id_asta, ST_AsGeoJSON(ST_Transform(gg.geoloc, 4326))
        order by ap.num_seq asc";
		
	$cod_percorso = $_GET['cp'];

	$result = pg_prepare($conn_sit, "my_query", $query);
	$result = pg_execute($conn_sit, "my_query", array($cod_percorso));
	$rows = array();
	while($row = pg_fetch_assoc($result)) {
		$rows[] = $row;
	}
		
	#echo $rows ;
	if (empty($rows)==FALSE){
		//print $rows;
		print json_encode(array_values(pg_fetch_all($result)));
	} else {
		echo "[{\"NOTE\":'No data'}]";
	}
}
?>