<?php

// query usata da più parti per estrarre i dati su cui fare le ispezioni di sovrariempimento

/*$query_ps= "SELECT pi.id_piazzola, null as id_elemento, concat(v.nome, ',',p.numero_civico, ' - ',riferimento)  as rif, 
pi.anno, c.descr_comune as comune, mac.nome_municipio as municipio,
case 
	when p.data_eliminazione <= current_date then 1
	else 0
end as eliminata, 
count(distinct i.id) as n_ispezioni_anno
  FROM  sovrariempimenti.programmazione_ispezioni pi 
  join elem.piazzole p on p.id_piazzola = pi.id_piazzola 
  join elem.aste a on a.id_asta= p.id_asta
  join topo.vie v on v.id_via = a.id_via
  join topo.comuni c on c.id_comune = v.id_comune 
  left join geo.municipi_area_comune mac on mac.codice_municipio::int = a.id_circoscrizione
  --left join elem.v_piazzole_dwh vpd on pi.id_piazzola = vpd.id_piazzola
  join (select id_elemento, id_piazzola, null as data_eliminazione from elem.elementi e
	union 
	select id_elemento, id_piazzola, data_eliminazione from history.elementi e) e on e.id_piazzola = p.id_piazzola 
  left join sovrariempimenti.ispezione_elementi ie on ie.id_elemento = e.id_elemento 
  left join sovrariempimenti.ispezioni i on i.id = ie.id_ispezione and to_char(i.data_ora, 'YYYY')::int=pi.anno 
  group by pi.id_piazzola, concat(v.nome, ',',p.numero_civico, ' - ',riferimento), 
pi.anno, c.descr_comune, mac.nome_municipio,
case 
	when p.data_eliminazione <= current_date then 1
	else 0
end
union 
SELECT pi.id_piazzola, pi.id_elemento, concat(v.nome, ',',e.numero_civico,e.lettera_civico, e.colore_civico, ' - ',e.riferimento)  as rif, 
pi.anno, c.descr_comune as comune, mac.nome_municipio as municipio, 
case 
	when e.data_eliminazione <= current_date then 1
	else 0
end as eliminata, 
count(distinct i.id) as n_ispezioni_anno
  FROM  sovrariempimenti.programmazione_ispezioni pi
  join (select id_elemento, id_asta, e.numero_civico,e.lettera_civico, e.colore_civico, e.riferimento, null as data_eliminazione from elem.elementi e
	union 
	select id_elemento, id_asta, e.numero_civico,e.lettera_civico, e.colore_civico, e.riferimento, data_eliminazione from history.elementi e) e on e.id_elemento= pi.id_elemento 
  join elem.aste a on a.id_asta= e.id_asta
  join topo.vie v on v.id_via = a.id_via
  join topo.comuni c on c.id_comune = v.id_comune 
  left join geo.municipi_area_comune mac on mac.codice_municipio::int = a.id_circoscrizione 
  left join sovrariempimenti.ispezione_elementi ie on ie.id_elemento = e.id_elemento 
  left join sovrariempimenti.ispezioni i on i.id = ie.id_ispezione and to_char(i.data_ora, 'YYYY')::int=pi.anno
  group by pi.id_piazzola, pi.id_elemento, concat(v.nome, ',',e.numero_civico,e.lettera_civico, e.colore_civico, ' - ',e.riferimento), 
pi.anno, c.descr_comune, mac.nome_municipio,
case 
	when e.data_eliminazione <= current_date then 1
	else 0
end";*/


$query_ps = "SELECT p.id_piazzola, p.id_elemento, rif, anno, comune, municipio, elementi, percorsi, eliminata, n_ispezioni_anno
FROM sovrariempimenti.mv_report_piazzole_da_analizzare p";



?>