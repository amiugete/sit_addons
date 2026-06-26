<?php

require_once '../session.php';

require_once '../conn_ok.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {

	$query= "select 
		count(distinct e.id_elemento) as num, 
		te.tipo_rifiuto,
		tr.ordinamento,
		tr.nome as rifiuto,
		te.tipologia_elemento,
		tr.colore,
		tr.fa_icon,
		te2.descrizione as tipo_raccolta,
		te.tipo_elemento,
		upper(te.descrizione) as tipo_elem, 
		coalesce(ep.id_utenzapap,-1) as id_utenzapap,
		concat (mc.descrizione, ' - ', ep.descrizione, ' - ', ep.nome_attivita) as cliente,
		string_agg(distinct concat(vi.tipo, ' - ', vi.descrizione), ',') as desc_intervento,
		string_agg(distinct vi.stato_descrizione, ',') as stato_intervento, 
		max(vi.stato) as id_stato_intervento,
		max(vi.odl) as odl,
		case 
		when te.tipologia_elemento in ('T') or (te.tipologia_elemento = 'A' and is_grande=0)
		then 1
		else 0
		end no_cestino,
		e.percent_riempimento,
		e.freq_stimata::int,
		'xxx' as freq_reale,
		'yyy' as freq_consunt
		from elem.elementi e
		join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento 
		join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto
		join elem.tipologie_elemento te2 on te2.tipologia_elemento = te.tipologia_elemento
		left join elem.elementi_privati ep on ep.id_elemento = e.id_elemento
		left join utenze.macro_categorie mc on mc.id_macro_categoria = ep.id_macro_categoria
		left join gestione_oggetti.v_intervento vi on e.id_elemento = vi.elemento_id and vi.stato in (1,5)
		where id_piazzola = $1  
		group by 
		/*e.id_elemento, */
		tr.ordinamento,
		te.tipo_rifiuto,
		tr.nome,
		te.tipologia_elemento,
		tr.colore,
		tr.fa_icon,
		te2.descrizione ,
		te.tipo_elemento,
		te.descrizione , 
		coalesce(ep.id_utenzapap,-1),
		mc.descrizione,
		ep.descrizione, ep.nome_attivita,
		e.percent_riempimento, e.freq_stimata
		order by tr.nome, te.descrizione";
		
	$id_piazola = $_GET['idp'];

	$result = pg_prepare($conn_sit, "my_query", $query);
	$result = pg_execute($conn_sit, "my_query", array($id_piazola));
	$rows = array();
		while($r = pg_fetch_assoc($result)) {
			$rows[] = $r;
			//print $r['id'];
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