<?php

require_once '../session.php';

require_once '../conn_ok.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {


	/*$query= "select e.id_elemento, e.tipo_elemento, 
	upper(te.descrizione) as descr_elem, tag,
	e.matricola, serratura, matricola_serratura, 
	e.id_materiale, tm.descrizione as materiale,
	e.percent_riempimento, e.freq_stimata::int,
	e.id_elemento as id_elemento1,
	te.tipo_rifiuto, te2.tipo_raccolta,
	coalesce(ep.id_utenzapap,-1) as id_utenzapap,
	e.id_elemento as id_elemento2,
	e.id_elemento as id_elemento3
	from elem.elementi e
	left join elem.elementi_privati ep on ep.id_elemento = e.id_elemento
	left join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento
	left join elem.tipi_materiale tm on tm.id_materiale = e.id_materiale 
	join elem.tipologie_elemento te2 on te2.tipologia_elemento = te.tipologia_elemento
	where id_piazzola = $1 and e.tipo_elemento = $2";*/

	$query="WITH elementi_all AS (
    SELECT 1 AS origine,
    	e.id_elemento, e.tipo_elemento,
        e.tag, e.matricola,
        e.serratura, e.matricola_serratura,
        e.id_materiale, e.percent_riempimento,
        e.freq_stimata, e.id_piazzola,
        ep.id_utenzapap, e.data_disattivazione_temp
    FROM elem.elementi e
    LEFT JOIN elem.elementi_privati ep
        ON ep.id_elemento = e.id_elemento
	/*non mostro in tabella dettagli elementi gli elementi perchè esiste già in elem_temporanei.elementi in attesa di ripristino o di eleminazione/dismissione*/
    WHERE e.id_elemento not in (select e2.id_elemento from elem_temporanei.elementi e2 where e2.inserimento > 0 or e2.eliminazione > 0)
    UNION ALL
    select 2 AS origine,
        e.id_elemento, e.tipo_elemento,
        e.tag, e.matricola,
        e.serratura, e.matricola_serratura,
        e.id_materiale, e.percent_riempimento,
        e.freq_stimata, e.id_piazzola,
        ep.id_utenzapap, e.data_disattivazione_temp
    FROM elem_temporanei.elementi e
    LEFT JOIN elem_temporanei.elementi_privati ep
        ON ep.id_elemento = e.id_elemento
	)
	SELECT
		e.id_elemento, e.tipo_elemento, te.manutenzione,
		upper(te.descrizione) AS descr_elem, e.tag,
		e.matricola, e.serratura,
		e.matricola_serratura, e.id_materiale,
		tm.descrizione AS materiale, e.percent_riempimento,
		e.freq_stimata::int, e.id_elemento AS id_elemento1,
		te.tipo_rifiuto, te2.tipo_raccolta,
		coalesce(e.id_utenzapap, -1) AS id_utenzapap,
		e.data_disattivazione_temp  AS elem_sospeso,
		count(distinct vi.id) as num_interventi_totali,
		bool_or(vi.gestione_sit) AS gestione_sit, 
		string_agg(distinct vi.tipo_intervento_id::text, ',') as id_tipo_intervento,
		string_agg(distinct vi.descrizione, ',') as desc_intervento,
		string_agg(distinct vi.stato_descrizione, ',') as stato_intervento, 
		max(vi.stato) as id_stato_intervento,
		max(vi2.odl) as odl,
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
		e.id_elemento AS id_elemento2,
		e.id_elemento AS id_elemento3
	FROM elementi_all e
	JOIN elem.tipi_elemento te
		ON te.tipo_elemento = e.tipo_elemento
	JOIN elem.tipologie_elemento te2
		ON te2.tipologia_elemento = te.tipologia_elemento
	LEFT JOIN elem.tipi_materiale tm
		ON tm.id_materiale = e.id_materiale
	LEFT JOIN gestione_oggetti.v_intervento vi 
		ON e.id_elemento = vi.elemento_id and vi.stato in (1,5)
	LEFT JOIN gestione_oggetti.v_intervento vi2 
		ON upper(te.descrizione) = vi2.tipo_elemento and e.id_piazzola = vi2.piazzola_id and vi2.stato in (1,5)
	WHERE e.id_piazzola = $1 AND e.tipo_elemento = $2
	GROUP BY e.origine, e.id_elemento, 
	e.tipo_elemento, te.manutenzione, te.descrizione, 
	e.tag, e.matricola, 
	e.serratura, e.matricola_serratura, 
	e.id_materiale, tm.descrizione, 
	e.percent_riempimento, e.freq_stimata,
	te.tipo_rifiuto, te2.tipo_raccolta, e.id_utenzapap,
	e.data_disattivazione_temp
	ORDER BY e.origine, e.id_elemento;";
		
	$id_piazola = $_GET['idp'];
	$id_tipo_elemento = $_GET['idt'];


	$result = pg_prepare($conn_sit, "my_query", $query);
	$result = pg_execute($conn_sit, "my_query", array($id_piazola, $id_tipo_elemento));
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