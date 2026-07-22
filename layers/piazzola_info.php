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
dett.is_pap, 
dett.rifiuti
from elem.piazzole p 
join elem.aste a on a.id_asta = p.id_asta 
join topo.vie v on v.id_via = a.id_via 
join topo.comuni c on c.id_comune = v.id_comune 
LEFT JOIN (
	with ep as (
		select e.id_piazzola,
		e.tipo_elemento,
		--te.descrizione,
		--te.volume,
		count(e.id_elemento) as num,
		count(ep.id_elemento) as num_privati
		from elem.elementi e
		--join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento
		LEFT JOIN elem.elementi_privati ep
		           ON ep.id_elemento = e.id_elemento
		where e.id_piazzola = $1
		group by e.id_piazzola,
		e.tipo_elemento
	) select 
		ep.id_piazzola,
		case 
			when 	sum(num)=sum(num_privati) then 1
			else 0
		end is_pap,
		--te.descrizione,
		--te.volume, 
		jsonb_agg(
	    jsonb_build_object(
	    	'id', te.tipo_elemento,
	    	'tipo_elem', te.descrizione,
	        'rifiuto', tr.nome_stampa, 
	        'ordinamento', tr.ordinamento,
	        'colore', tr.colore,
	        'volume', te.volume, 
	        'num', num
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
        'is_pap' => null,
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
            'is_pap' => $r['is_pap'],
            'rifiuti' => json_decode($r['rifiuti'], true),
        );
    }

}

    // print dei risultati in formato JSON
    echo json_encode($piazzola);



?>