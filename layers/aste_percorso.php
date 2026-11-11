<?php

require_once '../session.php';
$res_ok=0;
#require('../validate_input.php');
header('Content-Type: application/json; charset=utf-8');



require_once '../conn_ok.php';
//echo "OK";


$cod_percorso = $_GET['cod_percorso'];


if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {

    // $query_asta="select ROW_NUMBER() OVER (ORDER BY ap.num_seq) - 1 AS id, ap.num_seq,
    //     ap.id_asta, ap.id_asta_percorso, 
    //     count(distinct eap.id_elemento ) as count_elem,
    //     array_agg(distinct e.id_piazzola ), 
    //     count(distinct e.id_piazzola ) as count_piazzole,
    //     ap.num_seq, 
    //     ST_AsGeoJSON(ST_Transform(gg.geoloc, 4326)) as geometry
    //     from elem.aste_percorso ap
   	//     left join elem.elementi_aste_percorso eap on eap.id_asta_percorso = ap.id_asta_percorso
   	//     left join elem.elementi e on eap.id_elemento = e.id_elemento
    //     join elem.percorsi p on p.id_percorso = ap.id_percorso
    //     join geo.grafostradale gg on gg.id = ap.id_asta
    //     where p.cod_percorso  = $1 and now() between p.data_attivazione and p.data_dismissione
    //     group by ap.id_asta, ap.id_asta_percorso ,gg.geoloc
    //     order by ap.num_seq"; 

    // $query_asta = "
    //     WITH elementi_percorso AS (
    //         SELECT DISTINCT eap.id_elemento
    //         FROM elem.aste_percorso ap
    //         JOIN elem.elementi_aste_percorso eap ON eap.id_asta_percorso = ap.id_asta_percorso
    //         JOIN elem.percorsi p ON p.id_percorso = ap.id_percorso
    //         WHERE p.cod_percorso = $1 AND NOW() BETWEEN p.data_attivazione AND p.data_dismissione
    //     ),
    //     tipi_servizio AS (
    //         SELECT DISTINCT es.tipo_elemento
    //         FROM elem.percorsi p
    //         JOIN elem.elementi_servizio es ON es.id_servizio = p.id_servizio
    //         WHERE p.cod_percorso = $1 AND NOW() BETWEEN p.data_attivazione AND p.data_dismissione
    //     )
    //     SELECT
    //         ROW_NUMBER() OVER (ORDER BY ap.num_seq) - 1 AS id,
    //         ap.num_seq,
    //         ap.id_asta,
    //         ap.id_asta_percorso,
    //         v.nome,
    //         -- Elementi del percorso
    //         COUNT(DISTINCT eap.id_elemento) AS count_elem,
    //         ARRAY_AGG(distinct array[
    //             e.id_piazzola::text, 
    //             pz.riferimento, 
    //             concat(pz.numero_civico,'',pz.lettera_civico,'',pz.colore_civico)]
    //         ) FILTER (WHERE e.id_piazzola IS NOT NULL) AS array_piazzole,
    //         COUNT(DISTINCT e.id_piazzola) AS count_piazzole,
    //         -- Elementi dello stesso servizio non assegnati al percorso
    //         COUNT(DISTINCT enp.id_elemento) AS count_elem_np,
    //         ARRAY_AGG(DISTINCT array[
    //             enp.id_piazzola::text, 
    //             enp.riferimento, 
    //             concat(enp.numero_civico,'',enp.lettera_civico,'',enp.colore_civico)
    //         ]) FILTER (WHERE enp.id_piazzola IS NOT NULL) as array_piazzole_np,
    //         COUNT(DISTINCT enp.id_piazzola) AS count_piazzole_np,
    //         ST_AsGeoJSON(ST_Transform(gg.geoloc, 4326)) AS geometry
    //     FROM elem.aste_percorso ap
    //     LEFT JOIN elem.elementi_aste_percorso eap ON eap.id_asta_percorso = ap.id_asta_percorso
    //     LEFT JOIN elem.elementi e ON e.id_elemento = eap.id_elemento
    //     left join elem.piazzole pz on pz.id_piazzola  = e.id_piazzola
    //     -- Recupero gli elementi dello stesso servizio
    //     -- che insistono sulla stessa asta
    //     LEFT JOIN (
    //         SELECT
    //             e.id_elemento,
    //             e.id_piazzola,
    //             ap_non.id_asta,
    //             pz.riferimento,
    //             pz.numero_civico,
    //             pz.lettera_civico,
    //             pz.colore_civico
    //         FROM elem.elementi e
    //         JOIN elem.elementi_aste_percorso eap ON eap.id_elemento = e.id_elemento
    //         JOIN elem.aste_percorso ap_non ON ap_non.id_asta_percorso = eap.id_asta_percorso
    //         JOIN tipi_servizio ts ON ts.tipo_elemento = e.tipo_elemento
    //         left join elem.piazzole pz on pz.id_piazzola  = e.id_piazzola
    //         WHERE NOT EXISTS (
    //             SELECT 1
    //             FROM elementi_percorso ep
    //             WHERE ep.id_elemento = e.id_elemento
    //         )
    //     ) enp ON enp.id_asta = ap.id_asta
    //     JOIN elem.percorsi p ON p.id_percorso = ap.id_percorso
    //     JOIN geo.grafostradale gg ON gg.id = ap.id_asta
    //     join elem.aste aa on aa.id_asta = ap.id_asta
    //     join topo.vie v on v.id_via = aa.id_via 
    //     WHERE p.cod_percorso = $1 AND NOW() BETWEEN p.data_attivazione AND p.data_dismissione
    //     GROUP BY
    //         ap.num_seq,
    //         ap.id_asta,
    //         ap.id_asta_percorso,
    //         v.nome,
    //         gg.geoloc
    //     ORDER BY ap.num_seq;";

    $query_asta="WITH elementi_percorso AS (
        SELECT DISTINCT eap.id_elemento
        FROM elem.aste_percorso ap
        JOIN elem.elementi_aste_percorso eap ON eap.id_asta_percorso = ap.id_asta_percorso
        JOIN elem.percorsi p ON p.id_percorso = ap.id_percorso
        WHERE p.cod_percorso = $1 AND NOW() BETWEEN p.data_attivazione AND p.data_dismissione
    ),
    tipi_servizio AS (
        SELECT DISTINCT es.tipo_elemento
        FROM elem.percorsi p
        JOIN elem.elementi_servizio es ON es.id_servizio = p.id_servizio
        WHERE p.cod_percorso = $1 AND NOW() BETWEEN p.data_attivazione AND p.data_dismissione
    ),
    elementi_piazzole AS (
        SELECT DISTINCT
            ap.id_asta_percorso,
            e.id_elemento,
            e.id_piazzola,
            e.tipo_elemento
        FROM elem.aste_percorso ap
        JOIN elem.elementi_aste_percorso eap
            ON eap.id_asta_percorso = ap.id_asta_percorso
        JOIN elem.elementi e
            ON e.id_elemento = eap.id_elemento
        JOIN elem.percorsi p
            ON p.id_percorso = ap.id_percorso
        WHERE p.cod_percorso = $1
        AND NOW() BETWEEN p.data_attivazione AND p.data_dismissione
        AND e.id_piazzola IS NOT NULL
    ),
    conteggi_piazzole AS (
        SELECT
            id_asta_percorso,
            id_piazzola,
            eep.tipo_elemento,
            te.descrizione,
            COUNT(DISTINCT id_elemento) AS count_elementi
        FROM elementi_piazzole eep
        left join elem.tipi_elemento te on te.tipo_elemento = eep.tipo_elemento
        GROUP BY
            id_asta_percorso,
            id_piazzola,
            eep.tipo_elemento,
            te.descrizione
    ),
    dizionario_piazzole AS (
        SELECT
            id_asta_percorso,
            JSONB_OBJECT_AGG(
                id_piazzola::text,
                conteggi
            ) AS dizionario
        FROM (
            SELECT
                id_asta_percorso,
                id_piazzola,
                JSONB_AGG(
                    JSONB_BUILD_ARRAY(
                        tipo_elemento,
                        descrizione,
                        count_elementi
                    )
                    ORDER BY tipo_elemento
                ) AS conteggi
            FROM conteggi_piazzole
            GROUP BY
                id_asta_percorso,
                id_piazzola
        ) d
        GROUP BY id_asta_percorso
    ),
    elementi_piazzole_non_percorso AS (
        SELECT DISTINCT
            ap.id_asta_percorso,
            enp.id_elemento,
            enp.id_piazzola,
            enp.tipo_elemento
        FROM elem.aste_percorso ap
        JOIN elem.percorsi p
            ON p.id_percorso = ap.id_percorso
        JOIN (
            SELECT
                e.id_elemento,
                e.id_piazzola,
                e.tipo_elemento,
                ap_non.id_asta
            FROM elem.elementi e
            JOIN elem.elementi_aste_percorso eap
                ON eap.id_elemento = e.id_elemento
            JOIN elem.aste_percorso ap_non
                ON ap_non.id_asta_percorso = eap.id_asta_percorso
            JOIN tipi_servizio ts
                ON ts.tipo_elemento = e.tipo_elemento
            WHERE NOT EXISTS (
                SELECT 1
                FROM elementi_percorso ep
                WHERE ep.id_elemento = e.id_elemento
            )
        ) enp
            ON enp.id_asta = ap.id_asta
        WHERE p.cod_percorso = $1
        AND NOW() BETWEEN p.data_attivazione AND p.data_dismissione
        AND enp.id_piazzola IS NOT NULL
    ),
    conteggi_piazzole_non_percorso AS (
        SELECT
            id_asta_percorso,
            id_piazzola,
            eenp.tipo_elemento,
            te.descrizione,
            COUNT(DISTINCT id_elemento) AS count_elementi
        FROM elementi_piazzole_non_percorso eenp
        left join elem.tipi_elemento te on te.tipo_elemento = eenp.tipo_elemento
        GROUP BY
            id_asta_percorso,
            id_piazzola,
            eenp.tipo_elemento,
            te.descrizione
    ),
    dizionario_piazzole_non_percorso AS (
        SELECT
            id_asta_percorso,
            JSONB_OBJECT_AGG(
                id_piazzola::text,
                conteggi
            ) AS dizionario
        FROM (
            SELECT
                id_asta_percorso,
                id_piazzola,
                JSONB_AGG(
                    JSONB_BUILD_ARRAY(
                        tipo_elemento,
                        descrizione,
                        count_elementi
                    )
                    ORDER BY tipo_elemento
                ) AS conteggi
            FROM conteggi_piazzole_non_percorso
            GROUP BY
                id_asta_percorso,
                id_piazzola
        ) d
        GROUP BY id_asta_percorso
    )
    SELECT
        ROW_NUMBER() OVER (ORDER BY ap.num_seq) - 1 AS id,
        ap.num_seq,
        ap.id_asta,
        ap.id_asta_percorso,
        v.nome,
        -- Elementi del percorso
        COUNT(DISTINCT eap.id_elemento) AS count_elem,
        dp.dizionario AS dizionario_piazzole,
        JSONB_AGG(DISTINCT JSONB_BUILD_ARRAY(e.id_piazzola::text, pz.riferimento, concat(pz.numero_civico,'',pz.lettera_civico,'',pz.colore_civico))) FILTER (WHERE e.id_piazzola IS NOT NULL) AS array_piazzole,
        COUNT(DISTINCT e.id_piazzola) AS count_piazzole,
        -- Elementi dello stesso servizio non assegnati al percorso
        COUNT(DISTINCT enp.id_elemento) AS count_elem_non_percorso,
        dpnp.dizionario AS dizionario_piazzole_non_percorso,
        JSONB_AGG(DISTINCT JSONB_BUILD_ARRAY(enp.id_piazzola::text, enp.riferimento, concat(enp.numero_civico,'',enp.lettera_civico,'',enp.colore_civico))) FILTER (WHERE enp.id_piazzola IS NOT NULL) as array_piazzole_np,
        COUNT(DISTINCT enp.id_piazzola) AS count_piazzole_non_percorso,
        ST_AsGeoJSON(ST_Transform(gg.geoloc, 4326)) AS geometry
    FROM elem.aste_percorso ap
    LEFT JOIN elem.elementi_aste_percorso eap ON eap.id_asta_percorso = ap.id_asta_percorso
    LEFT JOIN elem.elementi e ON e.id_elemento = eap.id_elemento
    left join elem.piazzole pz on pz.id_piazzola  = e.id_piazzola
    LEFT JOIN dizionario_piazzole dp ON dp.id_asta_percorso = ap.id_asta_percorso
    LEFT JOIN dizionario_piazzole_non_percorso dpnp ON dpnp.id_asta_percorso = ap.id_asta_percorso
    -- Recupero gli elementi dello stesso servizio
    -- che insistono sulla stessa asta
    LEFT JOIN (
        SELECT
            e.id_elemento,
            e.id_piazzola,
            ap_non.id_asta,
            pz.riferimento,
            pz.numero_civico,
            pz.lettera_civico,
            pz.colore_civico
        FROM elem.elementi e
        JOIN elem.elementi_aste_percorso eap ON eap.id_elemento = e.id_elemento
        JOIN elem.aste_percorso ap_non ON ap_non.id_asta_percorso = eap.id_asta_percorso
        JOIN tipi_servizio ts ON ts.tipo_elemento = e.tipo_elemento
        left join elem.piazzole pz on pz.id_piazzola  = e.id_piazzola
        WHERE NOT EXISTS (
            SELECT 1
            FROM elementi_percorso ep
            WHERE ep.id_elemento = e.id_elemento
        )
    ) enp ON enp.id_asta = ap.id_asta
    JOIN elem.percorsi p ON p.id_percorso = ap.id_percorso
    JOIN geo.grafostradale gg ON gg.id = ap.id_asta
    join elem.aste aa on aa.id_asta = ap.id_asta
    join topo.vie v on v.id_via = aa.id_via 
    WHERE p.cod_percorso = $1 AND NOW() BETWEEN p.data_attivazione AND p.data_dismissione
    GROUP BY
        ap.num_seq,
        ap.id_asta,
        ap.id_asta_percorso,
        v.nome,
        gg.geoloc,
        dp.dizionario,
        dpnp.dizionario
    ORDER BY ap.num_seq;
    ";


    $result = pg_prepare($conn_sit, "query_asta", $query_asta);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    $result = pg_execute($conn_sit, "query_asta", array($cod_percorso));
    
        if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
   


    $features = [];

    while ($row = pg_fetch_assoc($result)) {

        // Asta
        $features[] = [
            'type' => 'Feature',
            'id' => (int)$row['id'],
            'geometry' => json_decode($row['geometry']),
            'properties' => [
                'id' => (int)$row['id'],
                'num_seq' => (int)$row['num_seq'],
                'id_asta' => (int)$row['id_asta'],
                'id_asta_percorso' => (int)$row['id_asta_percorso'],
                'nome_via' => $row['nome'],
                'count_elem' => (int)$row['count_elem'],
                'array_piazzole' => json_decode($row['array_piazzole'], true),
                'count_piazzole' => (int)$row['count_piazzole'],
                'count_elem_np' => (int)$row['count_elem_np'],
                'array_piazzole_np' => json_decode($row['array_piazzole_np'], true),
                'count_piazzole_np' => (int)$row['count_piazzole_np'],
                'dict_elem_piazzole' => json_decode($row['dizionario_piazzole'], true),
                'dict_elem_piazzole_np' => json_decode($row['dizionario_piazzole_non_percorso'], true)
            ]
        ];
    }

    echo json_encode([
        'type' => 'FeatureCollection',
        'features' => $features
    ]);

}


?>