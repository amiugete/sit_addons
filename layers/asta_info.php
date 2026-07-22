<?php

require_once '../session.php';
$res_ok=0;
#require('../validate_input.php');
header('Content-Type: application/json; charset=utf-8');



require_once '../conn_ok.php';
//echo "OK";


// recupero i parametri della bounding box dalla richiesta GET
$id_asta = isset($_GET['id_asta']) ? intval($_GET['id_asta']) : null;



if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {
    $query_asta="
    select 
    a.id_asta,
    c.descr_comune as comune, 
    mac.nome_municipio || ' ('||mac.codice_municipio::text ||')' as municipio, 
    q.nome as quartiere, 
    u.descrizione as ut,
    v.nome as via, 
    a.lung_asta::int as lung
    from elem.aste a
    join topo.vie v on v.id_via = a.id_via 
    join topo.comuni c on c.id_comune = v.id_comune 
    left join geo.municipi_area_comune mac on mac.id= a.id_circoscrizione
    left join topo.quartieri q on q.id_quartiere = a.id_quartiere 
    left join topo.ut u on u.id_ut = a.id_ut 
    WHERE a.id_asta = $1
    ";
    


    $result = pg_prepare($conn_sit, "query_asta", $query_asta);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    $result = pg_execute($conn_sit, "query_asta", array($id_asta));
    
        if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
   


    if ($result === false) {
        die("ERRORE EXECUTE: ".pg_last_error($conn_sit));
    }

    $asta = array(
        'id_asta' => null,
        'comune' => null,
        'municipio' => null,
        'quartiere' => null,
        'ut' => null,
        'via' => null,
        'lung' => null
    );

    


    while($r = pg_fetch_assoc($result)) {
        //echo "Aggiungo feature con id_asta: ".$r['id_asta']."\n";
        // Aggiungi la feature usando la funzione geojsonFeature
        $asta = array(
            'id_asta' => $r['id_asta'],
            'comune' => $r['comune'],
            'municipio' => $r['municipio'],
            'quartiere' => $r['quartiere'],
            'ut' => $r['ut'],
            'via' => $r['via'],
            'lung' => $r['lung']
        );
    }




     // creo json con varie sezioni 
    $response = [
        'asta' => null,
        'elem_piazzole' => [],
        'altri_elementi' => [],
        'percorsi_raccolta' => [],
        'percorsi_spazzamento' => []
    ];

    $response['asta'] = $asta;


    // popolo le altre sezioni del json con le piazzole e gli altri elementi

    $query_elem_piazzola="
    select  
    p.id_piazzola, 
    v.nome as via, 
    p.riferimento, 
    p.numero_civico, 
    p.note, 
    tr.fa_icon, 
    tr.colore, 
    tr.nome as rifiuto, 
    te.descrizione as tipo_elemento, 
    e.id_elemento,
    case 
    	when ep.id_elemento is not null then 1
		else 0
    end privato
    from elem.piazzole p
    join elem.elementi e on e.id_piazzola = p.id_piazzola  
    left join elem.elementi_privati ep on ep.id_elemento = e.id_elemento 
    join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento 
    join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto 
    join elem.aste a on a.id_asta = p.id_asta 
    join topo.vie v on v.id_via = a.id_via
    where p.id_asta = $1
    and p.data_eliminazione is null
    order by p.id_piazzola, tr.ordinamento, coalesce(te.volume, 0)
    ";
    


    $result = pg_prepare($conn_sit, "query_elem_piazzola", $query_elem_piazzola);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    $result = pg_execute($conn_sit, "query_elem_piazzola", array($id_asta));
    
        if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
   


    if ($result === false) {
        die("ERRORE EXECUTE: ".pg_last_error($conn_sit));
    }

    


    while($r = pg_fetch_assoc($result)) {
        //echo "Aggiungo feature con id_asta: ".$r['id_asta']."\n";
        // Aggiungi la feature usando la funzione geojsonFeature
        $response['elem_piazzole'][] = [
            'id_piazzola' => $r['id_piazzola'],
            'via' => $r['via'],
            'riferimento' => $r['riferimento'],
            'numero_civico' => $r['numero_civico'],
            'note' => $r['note'],   
            'fa_icon' => $r['fa_icon'],
            'colore' => $r['colore'],
            'rifiuto' => $r['rifiuto'],
            'tipo_elemento' => $r['tipo_elemento'],
            'id_elemento' => $r['id_elemento'],
            'privato' => $r['privato']
        ];

    }

   

    

    




    // print dei risultati in formato JSON
    echo json_encode($response);
}


?>