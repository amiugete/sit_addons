<?php
require_once '../session.php';
$res_ok=0;
#require('../validate_input.php');
header('Content-Type: application/json; charset=utf-8');



require_once '../conn_ok.php';
//echo "OK";

// recupero id_piazzola
$id = isset($_GET['id']) ? intval($_GET['id']) : null;



//echo $id;
//exit();

if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {

    $query_p_info="
select c.descr_comune as comune, 
v.nome as via, 
p.numero_civico,
p.riferimento, 
p.note, 
p.foto,
dett.is_pap, 
dett.interventi,
dett.rifiuti
from elem.piazzole p 
join elem.aste a on a.id_asta = p.id_asta 
join topo.vie v on v.id_via = a.id_via 
join topo.comuni c on c.id_comune = v.id_comune 
LEFT JOIN (
	with ep as (
		select e.id_piazzola,
		e.tipo_elemento,
		e.freq_stimata,
		/*pp.ripasso,*/
		--te.descrizione,
		--te.volume,
		sum(ev.anomalia) as anomalie,
		max(ev_ok.data_programmata) as data_programmata, 
		max(ev_ok.orario_progr) as orario_progr,
		count(distinct e.id_elemento) as num,
		count(distinct ep.id_elemento) as num_privati, 
		count(distinct ii.elemento_id) as num_interventi,
		sum(pp.num_giorni)/count(distinct e.id_elemento) as giorni,
		string_agg(distinct turno, ', ') as turni 
		from elem.elementi e
		--join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento
		LEFT JOIN elem.elementi_privati ep
		           ON ep.id_elemento = e.id_elemento
		/* CON QUESTO CERCO ULTIMO SVUOTAMENTO*/
		left join lateral  (
			 select data_programmata, orario_progr
			 from consunt.report_raccolta rr 
			 where rr.id_elemento = e.id_elemento
			 and id_causale in (100, 110)
			 order by data_programmata desc, orario_progr desc
			 limit 1) ev_OK on true
		/* CON QUESTO CERCO SE CI SONO ANOMALIE ULTIMO SVUOTAMENTO PREVISTO*/
		left join lateral  (
			 select count(*) filter (where id_causale not in (100, 110)) as anomalia
			 from consunt.report_raccolta rr 
			 where rr.id_elemento = e.id_elemento
			 group by data_programmata, orario_progr
			 order by data_programmata desc, orario_progr desc
			 limit 1) ev on true
		left join lateral  (
			 	select distinct p.id_percorso, t.descrizione as turno, fo.num_giorni, eap.ripasso
			 	from elem.elementi_aste_percorso eap 
				left join elem.aste_percorso ap on ap.id_asta_percorso = eap.id_asta_percorso 
				left join elem.percorsi p on p.id_percorso = ap.id_percorso 
				left join elem.servizi s on s.id_servizio = p.id_servizio 
				left join etl.frequenze_ok fo on fo.cod_frequenza = eap.frequenza::int
				left join elem.turni t on t.id_turno  = p.id_turno 
				where  eap.id_elemento = e.id_elemento
				and p.id_categoria_uso in (3,6) 
			 	) pp on true	
		left join (
			select i.id, i.descrizione, i.elemento_id, i.piazzola_id, ti.descrizione as tipo_intervento
			from gestione_oggetti.intervento i 
			join gestione_oggetti.intervento_tipo_intervento iti on iti.intervento_id = i.id 
			join gestione_oggetti.tipo_intervento ti on ti.id = iti.tipo_intervento_id 
			join gestione_oggetti.intervento_tipo_stato_intervento itsi on itsi.intervento_id = i.id 
			where itsi.tipo_stato_intervento_id in (1,5) /*solo aperti e presi in carico*/
			and i.piazzola_id = $1
			) ii on ii.elemento_id = e.id_elemento
		where e.id_piazzola = $1
		group by e.id_piazzola,
		e.tipo_elemento, e.freq_stimata/*, ripasso*/
	) select 
		ep.id_piazzola,
		case 
			when 	sum(num)=sum(num_privati) then 1
			else 0
		end is_pap,
		case 	
			when sum(num_interventi) > 0 then 1
			else 0
		end interventi,	
		--te.descrizione,
		--te.volume, 
		jsonb_agg(
	    jsonb_build_object(
	    	'id', te.tipo_elemento,
	    	'tipo_elem', te.nome_stampa, 
	    	'tipo_elemento', te.descrizione,
	        'rifiuto', tr.nome_stampa, 
	        'ordinamento', tr.ordinamento,
	        'colore', tr.colore,
	        'volume', te.volume, 
	        'num', num, 
	        'data_last_sv', to_char(ep.data_programmata, 'DD/MM/YYYY'),
	        'orario_last_sv', ep.orario_progr, 
	        'anomalie', ep.anomalie,
	        'giorni', ep.giorni,
	        /*'ripasso', ep.ripasso,*/
	        'freq_stimata', ep.freq_stimata,
	        'turni', ep.turni, 
	        'num_interventi', num_interventi
	    )
	    	ORDER BY tr.ordinamento, te.volume desc
		) AS rifiuti
		from ep	
		join elem.tipi_elemento te on te.tipo_elemento = ep.tipo_elemento
		join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto
		group by ep.id_piazzola
		--, te.descrizione,
		--te.volume
) dett ON dett.id_piazzola = p.id_piazzola
where p.id_piazzola = $1
";
    
    $result = pg_prepare($conn_sit, "query_p_info", $query_p_info);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }

    $t0 = microtime(true);

    $result = pg_execute($conn_sit, "query_p_info", array($id));
    
        if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
   


    $rows = array();
    //echo "<br>ok fino a qua";
    if ($result === false) {
        die("ERRORE EXECUTE: ".pg_last_error($conn_sit));
    }


    $piazzola = array(
        'comune' => null,
        'via' => null,
        'numero_civico' => null,
        'riferimento' => null,
        'note' => null,
        'foto' => null,
        'is_pap' => null,
		'interventi' => null,
        'rifiuti' => null
    );

    


    while($r = pg_fetch_assoc($result)) {
        //echo "Aggiungo feature con id_asta: ".$r['id_asta']."\n";
        // Aggiungi la feature usando la funzione geojsonFeature
        $piazzola = array(
            'comune' => $r['comune'],
            'via' => $r['via'],
            'numero_civico' => $r['numero_civico'],
            'riferimento' => $r['riferimento'],
            'note' => $r['note'],
            'foto' => $r['foto'],
            'is_pap' => $r['is_pap'],
			'interventi' => $r['interventi'],
            'rifiuti' => json_decode($r['rifiuti'], true),
        );
    }

}

    // print dei risultati in formato JSON
    echo json_encode($piazzola);



?>