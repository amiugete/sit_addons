<?php
require_once '../session.php';
$res_ok=0;
#require('../validate_input.php');
header('Content-Type: application/json; charset=utf-8');

// DATA PIAZZOLA E TIPO ELEMENTO TIRO FUORI I PERCORSI CHE PASSANO PER QUELL'ELEMENTO 
// O PER ELEMENTI DELLA STESSA TIPOLOGIA


require_once '../conn_ok.php';
//echo "OK";

// recupero id_piazzola
$id_piazzola = isset($_GET['id_piazzola']) ? intval($_GET['id_piazzola']) : null;
$tipo_elemento = isset($_GET['tipo_elemento']) ? $_GET['tipo_elemento'] : null;


//echo $id;
//exit();

if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {

    $query_p_info="
with elementi_piazzola as ( 
	select 
	id_piazzola,
	e.id_elemento,
	tr.colore, 
	case 
		when te2.tipo_raccolta = 'C' then 'campana.svg'
		when te2.tipo_raccolta = 'L' then 'laterale.svg'
		when te2.tipo_raccolta = 'G' then 'gancio.svg'
		when te2.tipo_raccolta in ('B', 'R') then 'bilaterale.svg'
		when te2.tipo_raccolta in ('P') then 'posteriore.svg'
		else 'ellipsis-solid-full.svg'
	end icona, 
	te.descrizione as descrizione_elemento/*,
	tr.ordinamento*/
	from elem.elementi e 
	join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento 
	join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto 
	join elem.tipologie_elemento te2 on te2.tipologia_elemento = te.tipologia_elemento 
	where e.id_piazzola = $1
	and te2.tipologia_elemento = (select tipologia_elemento from elem.tipi_elemento where descrizione = $2)
	and tr.tipo_rifiuto = (select tipo_rifiuto from elem.tipi_elemento where descrizione = $2)
) select 
p.id_percorso, p.cod_percorso,
p.descrizione as descrizione_percorso,
/*count(ep.id_elemento) as num_elementi,*/
jsonb_agg(distinct
	    jsonb_build_object(
	    	'id_elemento', e.id_elemento,
	    	'tipo_elemento', e.descrizione_elemento
	    )
		) AS tipi_disponibili,
ep.id_elemento,
p.stagionalita,
fo.num_giorni, 
fo.descrizione_long, 
ep.colore, ep.icona, 
ep.descrizione_elemento,/* ep.ordinamento,*/ 
t.descrizione as turno, 
eap.ripasso, 
eap.id_elemento_asta_percorso
from elementi_piazzola ep
left join elem.elementi_aste_percorso eap on eap.id_elemento= ep.id_elemento
left join elem.aste_percorso ap on ap.id_asta_percorso = eap.id_asta_percorso 
left join elem.percorsi p on p.id_percorso = ap.id_percorso 
left join elem.servizi s on s.id_servizio = p.id_servizio 
left join etl.frequenze_ok fo on fo.cod_frequenza = eap.frequenza::int
left join elem.turni t on t.id_turno  = p.id_turno 
left join (
	select id_piazzola, id_elemento, 
	e.tipo_elemento, te.descrizione as descrizione_elemento,  te.tipologia_elemento,
	te.tipo_rifiuto 
	from elem.elementi e 
	join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento  
	) e on e.id_piazzola = ep.id_piazzola
	and e.tipologia_elemento = (select tipologia_elemento from elem.tipi_elemento where descrizione = $2)
	and e.tipo_rifiuto = (select tipo_rifiuto from elem.tipi_elemento where descrizione = $2)
where p.id_categoria_uso in (3,6) 
group by p.id_percorso, p.cod_percorso,
p.descrizione,
p.stagionalita,
fo.num_giorni, 
fo.descrizione_long, 
ep.colore, ep.icona, ep.descrizione_elemento, /*ep.ordinamento, */
t.descrizione, ep.id_elemento, 
eap.ripasso, eap.id_elemento_asta_percorso
order by p.cod_percorso
";
    
    $result = pg_prepare($conn_sit, "query_p_info", $query_p_info);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }

    $t0 = microtime(true);

    $result = pg_execute($conn_sit, "query_p_info", array($id_piazzola, $tipo_elemento));
    
        if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
   


    $percorsi = array();

	if ($result === false) {
		die("ERRORE EXECUTE: " . pg_last_error($conn_sit));
	}

	while ($r = pg_fetch_assoc($result)) {

		$percorsi[] = array(
			'id_percorso'       	=> $r['id_percorso'],
			'cod_percorso'      	=> $r['cod_percorso'],
			'descrizione_percorso'  => $r['descrizione_percorso'],
			'id_elemento'  			=> $r['id_elemento'],
			'id_elemento_ap'  		=> $r['id_elemento_asta_percorso'],
			'ripasso'				=> $r['ripasso'],
			'tipo_elemento'			=> $r['descrizione_elemento'],
			'tipi_disponibili'		=> json_decode($r['tipi_disponibili'], true),
			'stagionalita'      	=> $r['stagionalita'],
			'num_giorni'        	=> $r['num_giorni'],
			'descrizione_long'  	=> $r['descrizione_long'],
			'turno'             	=> $r['turno'],
			'colore'            	=> $r['colore'],
			'icona'             	=> $r['icona'],
			'ordinamento'       	=> $r['ordinamento'],
			'turno'       			=> $r['turno']
		);
	}

	echo json_encode(
		array(
			'percorsi' => $percorsi
		),
		JSON_UNESCAPED_UNICODE
	);

}





?>