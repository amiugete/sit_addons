<?php

require_once '../session.php';

require_once '../conn_ok.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {

	/*$query= "select 
		count(distinct e.id_elemento) as num,
		string_agg(distinct e.id_elemento::text, ',') as ids_elementi, 
		te.tipo_rifiuto,
		tr.ordinamento,
		tr.nome as rifiuto,
		te.tipologia_elemento,
		tr.colore,
		tr.fa_icon,
		te2.descrizione as tipo_raccolta,
		te.tipo_elemento,
		te.manutenzione,
		upper(te.descrizione) as tipo_elem, 
		coalesce(ep.id_utenzapap,-1) as id_utenzapap,
		concat (mc.descrizione, ' - ', ep.descrizione) as cliente,
		count(distinct vi2.id) as num_interventi_totali,
		coalesce(string_agg(distinct vi.tipo_intervento_id::text, ','), string_agg(distinct vi2.tipo_intervento_id::text, ',')) as id_tipo_intervento,
		coalesce(string_agg(distinct vi.descrizione, ','), string_agg(distinct vi2.descrizione, ',')) as desc_intervento,
		coalesce(string_agg(distinct vi.stato_descrizione, ','), string_agg(distinct vi2.stato_descrizione, ',')) as stato_intervento, 
		max(vi2.stato) as id_stato_intervento,
		max(vi2.odl) as odl,
		case 
		when te.tipologia_elemento in ('T') or (te.tipologia_elemento = 'A' and is_grande=0)
		then 1
		else 0
		end no_cestino,
		e.percent_riempimento,
		e.freq_stimata::int,
		'xxx' as freq_reale,
		'yyy' as freq_consunt,
		BOOL_AND(e.data_disattivazione_temp IS NOT NULL) AS elem_sospesi
		from elem.elementi e
		join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento 
		join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto
		join elem.tipologie_elemento te2 on te2.tipologia_elemento = te.tipologia_elemento
		left join elem.elementi_privati ep on ep.id_elemento = e.id_elemento
		left join utenze.macro_categorie mc on mc.id_macro_categoria = ep.id_macro_categoria
		left join gestione_oggetti.v_intervento vi on e.id_elemento = vi.elemento_id and vi.stato in (1,5)
		left join gestione_oggetti.v_intervento vi2 on upper(te.descrizione) = vi2.tipo_elemento and e.id_piazzola = vi2.piazzola_id and vi2.stato in (1,5)
		where id_piazzola = $1  
		group by 
		--e.id_elemento,
		tr.ordinamento,
		te.tipo_rifiuto,
		tr.nome,
		te.tipologia_elemento,
		tr.colore,
		tr.fa_icon,
		te2.descrizione ,
		te.tipo_elemento,
		te.manutenzione,
		te.descrizione , 
		coalesce(ep.id_utenzapap,-1),
		mc.descrizione,
		ep.descrizione, ep.nome_attivita,
		e.percent_riempimento, e.freq_stimata
		order by tr.ordinamento, tr.nome, te.descrizione";*/

	$query = " WITH elementi_all AS (
		SELECT
			e.id_elemento,
			e.tipo_elemento,
			e.id_piazzola,
			e.percent_riempimento,
			e.freq_stimata,
			e.data_disattivazione_temp,
			ep.id_utenzapap,
			ep.descrizione,
			ep.id_macro_categoria
		FROM elem.elementi e
		LEFT JOIN elem.elementi_privati ep
			ON ep.id_elemento = e.id_elemento
		/* non mostro gli elementi che hanno già una modifica pendente */
		WHERE e.id_elemento NOT IN (
			SELECT e2.id_elemento
			FROM elem_temporanei.elementi e2
			WHERE e2.inserimento > 0
			OR e2.eliminazione > 0
		)
		UNION ALL
		SELECT
			e.id_elemento,
			e.tipo_elemento,
			e.id_piazzola,
			e.percent_riempimento,
			e.freq_stimata,
			e.data_disattivazione_temp,
			ep.id_utenzapap,
			ep.descrizione,
			ep.id_macro_categoria
		FROM elem_temporanei.elementi e
		LEFT JOIN elem_temporanei.elementi_privati ep
			ON ep.id_elemento = e.id_elemento
	)
	SELECT
		count(distinct e.id_elemento) AS num,
		string_agg(distinct e.id_elemento::text, ',') AS ids_elementi,
		te.tipo_rifiuto,
		tr.ordinamento,
		tr.nome AS rifiuto,
		te.tipologia_elemento,
		tr.colore,
		tr.fa_icon,
		te2.descrizione AS tipo_raccolta,
		te.tipo_elemento,
		te.manutenzione,
		upper(te.descrizione) AS tipo_elem,
		coalesce(e.id_utenzapap,-1) AS id_utenzapap,
		concat(mc.descrizione, ' - ', e.descrizione) AS cliente,
		count(distinct vi2.id) AS num_interventi_totali,
		coalesce(string_agg(distinct vi.tipo_intervento_id::text, ','), string_agg(distinct vi2.tipo_intervento_id::text, ',') ) AS id_tipo_intervento,
		coalesce( string_agg(distinct vi.descrizione, ','), string_agg(distinct vi2.descrizione, ',') ) AS desc_intervento,
		coalesce( string_agg(distinct vi.stato_descrizione, ','), string_agg(distinct vi2.stato_descrizione, ',') ) AS stato_intervento,
		max(vi2.stato) AS id_stato_intervento,
		max(vi2.odl) AS odl,
		GREATEST(
		    MAX(
		        CASE
		            WHEN vi.tipo_intervento_id NOT IN (1,2,3)
		                 AND vi.stato = 5
		            THEN 1
		            ELSE 0
		        END
		    ),
		    MAX(
		        CASE
		            WHEN vi2.tipo_intervento_id NOT IN (1,2,3)
		                 AND vi2.stato = 5
		            THEN 1
		            ELSE 0
		        END
		    )
		) AS disabilita,
		CASE
			WHEN te.tipologia_elemento IN ('T')
				OR (te.tipologia_elemento = 'A' AND is_grande = 0)
			THEN 1
			ELSE 0
		END AS no_cestino,
		e.percent_riempimento,
		e.freq_stimata::int,
		'xxx' AS freq_reale,
		'yyy' AS freq_consunt,
		bool_and(e.data_disattivazione_temp IS NOT NULL) AS elem_sospesi,
		CASE
	    	WHEN bool_and(e.id_elemento < 0)
	    	THEN 1
	    	ELSE 0
		END AS id_negativi
	FROM elementi_all e
	JOIN elem.tipi_elemento te
		ON te.tipo_elemento = e.tipo_elemento
	JOIN elem.tipi_rifiuto tr
		ON tr.tipo_rifiuto = te.tipo_rifiuto
	JOIN elem.tipologie_elemento te2
		ON te2.tipologia_elemento = te.tipologia_elemento
	LEFT JOIN utenze.macro_categorie mc
		ON mc.id_macro_categoria = e.id_macro_categoria
	LEFT JOIN gestione_oggetti.v_intervento vi
		ON e.id_elemento = vi.elemento_id
	AND vi.stato IN (1,5)
	LEFT JOIN gestione_oggetti.v_intervento vi2
		ON upper(te.descrizione) = vi2.tipo_elemento
	AND e.id_piazzola = vi2.piazzola_id
	AND vi2.stato IN (1,5)
	WHERE e.id_piazzola = $1
	GROUP BY
		tr.ordinamento,
		te.tipo_rifiuto,
		tr.nome,
		te.tipologia_elemento,
		tr.colore,
		tr.fa_icon,
		te2.descrizione,
		te.tipo_elemento,
		te.manutenzione,
		te.descrizione,
		coalesce(e.id_utenzapap,-1),
		mc.descrizione,
		e.descrizione,
		e.percent_riempimento,
		e.freq_stimata
	ORDER by 
		CASE
			WHEN bool_and(e.id_elemento < 0) THEN 1
			ELSE 0
		END,
		tr.ordinamento, tr.nome, te.descrizione";
		
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