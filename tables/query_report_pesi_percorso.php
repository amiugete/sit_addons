<?php 


$query0 = "SELECT id, zona, 
rimessa, id_rimessa, 
ut, id_ut, 
cod_percorso, descrizione, 
servizio, cod_cer, descr_rifiuto,
turno, orario, 
data_percorso, dataoraconf, 
mezzopercorso, targa, sportelli,
mezzipertarga, portataprev, 
portataeff, percentualeportata, 
personale, provenienza, 
destinazione, peso
FROM consunt.v_dettaglio_pesi_percorso";




// se volessi non usare la vista ma la query esplicita 
/*
$query0 = "
SELECT tp.id_peso_percorso AS id,
    za.cod_zona AS zona,
    u1.descrizione AS rimessa,
    cmu1.id_uo AS id_rimessa,
    u.descrizione AS ut,
    cmu.id_uo AS id_ut,
    tp.cod_percorso,
    ep.descrizione,
    at2.desc_servizio_sit AS servizio,
    tp.cod_cer,
    tp.descr_rifiuto,
    ft.descrizione AS turno,
    (((((t.inizio_ora || ':'::text) || lpad(t.inizio_minuti::text, 2, '0'::text)) || ' - '::text) || t.fine_ora) || ':'::text) || lpad(t.fine_minuti::text, 2, '0'::text) AS orario,
    tp.data_percorso,
    (tp.data_conferimento || ' '::text) || COALESCE(tp.ora_conferimento::text, '00:00'::text) AS dataoraconf,
    a.categoria AS mezzopercorso,
    tp.targa,
    ms.sportelli,
    ms.mezzipertarga,
    a.portata AS portataprev,
    ms.portata AS portataeff,
    round(tp.peso / NULLIF(ms.portata, 0::numeric) * 100::numeric) AS percentualeportata,
    tp.autista,
    hs.personale,
    tp.provenienza,
    tp.destinazione AS id_destinazione,
    ad.destinazione,
    tp.peso
   FROM consunt.tb_pesi_percorsi tp
   	 left join consunt.schede_eseguite_ekovision see 
   	 on tp.percorso is null and tp.permesso is not null 
   	 	and see.cod_caus_srv_non_eseg_ext is null
   	 	and tp.permesso = see.codice_serv_pred  
   	 	and tp.data_percorso = consunt.to_date_ekovision(see.data_esecuzione_prevista)
     LEFT JOIN ( SELECT p.id_scheda_ekovision,
            string_agg(DISTINCT a_1.nominativo::text, ', '::text) AS personale
           FROM consunt.persone p
             LEFT JOIN ( SELECT DISTINCT (lpad(t_anagr_pers_ekovision.cod_matlibromat::text, 5, '0'::text) || '_'::text) || t_anagr_pers_ekovision.id_azienda::text AS cod_dipendente,
                    t_anagr_pers_ekovision.nominativo
                   FROM anagrafiche.t_anagr_pers_ekovision) a_1 ON a_1.cod_dipendente = p.cod_dipendente::text
          GROUP BY p.id_scheda_ekovision) hs 
     ON hs.id_scheda_ekovision::numeric = 
     COALESCE(tp.id_scheda_ekovision, 
     	NULLIF(TRIM(BOTH FROM \"right\"(replace(tp.percorso::text, ','::text, ''::text), 6)), ''::text)::numeric,
     	see.id_scheda)
     LEFT JOIN ( SELECT m.id_scheda_ekovision,
            string_agg(DISTINCT m.sportello::text, ', '::text) AS sportelli,
            string_agg(DISTINCT me.descrizione_tipologia_mezzo::text, ', '::text) AS mezzipertarga,
            max(me.portata) AS portata
           FROM consunt.mezzi m
             LEFT JOIN etl.mezzi_ekovision me ON TRIM(BOTH FROM replace(me.sportello::text, ' '::text, ''::text)) = TRIM(BOTH FROM replace(m.sportello::text, ' '::text, ''::text))
          GROUP BY m.id_scheda_ekovision) ms 
     ON ms.id_scheda_ekovision::numeric = 
     COALESCE(tp.id_scheda_ekovision, 
     	NULLIF(TRIM(BOTH FROM \"right\"(replace(tp.percorso::text, ','::text, ''::text), 6)), ''::text)::numeric,
     	see.id_scheda)
     LEFT JOIN anagrafe_percorsi.elenco_percorsi ep ON tp.cod_percorso::text = ep.cod_percorso::text AND tp.data_percorso >= ep.data_inizio_validita AND tp.data_percorso <= ep.data_fine_validita
     LEFT JOIN anagrafe_percorsi.percorsi_ut pu ON tp.cod_percorso::text = pu.cod_percorso::text AND tp.data_percorso >= pu.data_attivazione AND tp.data_percorso <= pu.data_disattivazione AND pu.responsabile::text = 'S'::text
     LEFT JOIN anagrafe_percorsi.percorsi_ut pu1 ON tp.cod_percorso::text = pu1.cod_percorso::text AND tp.data_percorso >= pu1.data_attivazione AND tp.data_percorso <= pu1.data_disattivazione AND pu1.rimessa::text = 'S'::text
     LEFT JOIN anagrafe_percorsi.cons_mapping_uo cmu ON cmu.id_uo::double precision = pu.id_ut
     LEFT JOIN anagrafe_percorsi.cons_mapping_uo cmu1 ON cmu1.id_uo::double precision = pu1.id_ut
     LEFT JOIN topo.ut u ON u.id_ut = cmu.id_uo_sit
     LEFT JOIN topo.ut u1 ON u1.id_ut = cmu1.id_uo_sit
     LEFT JOIN topo.zone_amiu za ON u.id_zona = za.id_zona
     LEFT JOIN anagrafe_percorsi.anagrafe_tipo at2 ON at2.id = ep.id_tipo::text
     LEFT JOIN elem.turni t ON t.id_turno = ep.id_turno
     LEFT JOIN elem.fascia_turni ft ON ft.fascia::text = t.descrizione
     LEFT JOIN elem.automezzi a ON a.cdaog3::text = pu.cdaog3::text
     LEFT JOIN anagrafiche.destinazioni ad ON ad.id_destinazione::numeric = tp.destinazione
";
*/
//print $query."<br>";






?>