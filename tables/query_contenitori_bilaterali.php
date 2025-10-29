<?php

//$filter="WHERE 1=1 ";

if ($_GET['filter']){
    foreach(json_decode($_GET['filter']) as $key => $val) {
        /*if (is_numeric($val)){
            $filter = $filter. " AND ".$key." = ".$val." ";
        } else {*/
            $filter = $filter." AND upper(".$key.") LIKE upper('%".$val."%') ";
        //} 
         
    }
} 

#echo $filter;
#echo '<br>';

#exit();

$query0= "
    /*report contenitori*/
select vpd.id_piazzola, 
concat(vpd.id_piazzola, ' - ', vpd.via, ' ',vpd.civ,' ', vpd.riferimento) as indirizzo,
vpd.municipio, 
vpd.quartiere,
cc.descrizione as frazione,
ci.targa_contenitore, 
ci.volume_contenitore,
/*date(ci.data_ultimo_agg) as data_ultimo_agg,
date_trunc('minute',ci.data_ultimo_agg::time) as ora_ultimo_agg,*/ 
date_trunc('minute', ci.data_ultimo_agg) as data_ultimo_agg, /* approssimo al minuto */
case 
    when ci.val_riemp > 100 then null
    else ci.val_riemp
end val_riemp,
ci.val_bat_elettronica,
ci.val_bat_bocchetta,
/*date(sv.data_ora_last_sv) as data_last_sv,
date_trunc('minute',sv.data_ora_last_sv::time) ora_last_sv, */
date_trunc('minute', sv.data_ora_last_sv) as data_ora_last_sv, /* approssimo al minuto */
sv.riempimento as riempimento_svuotamento,
mc.media_conf_giorno,
string_agg(distinct pp.percorso, ', ') as percorsi
from idea.censimento_idea ci 
left join elem.v_piazzole_dwh vpd on vpd.id_piazzola = ci.id_piazzola::int
/* aggiungo i conferimenti medi al giorno (vista materializzata) */
left join idea.mv_conferimenti_per_giorno_ultimo_mese mc on mc.targa_contenitore = ci.targa_contenitore
join idea.codici_cer cc on cc.codice_cer = ci.cod_cer_mat 
/* UNISCO AL CALCOLO ULTIMIO SVUOTAMENTO */
left join (select distinct s1.data_ora_last_sv, s2.riempimento, s1.targa_contenitore
			from
			(select max(data_ora_svuotamento) as data_ora_last_sv,
			targa_contenitore 
			from idea.svuotamenti s 
			group by targa_contenitore) s1
			join idea.svuotamenti s2 on s2.data_ora_svuotamento = s1.data_ora_last_sv and s2.targa_contenitore = s1.targa_contenitore
			where trim(s1.targa_contenitore) !='') sv
			on sv.targa_contenitore = ci.targa_contenitore
/* UNISCO AI PERCORSI */
left join (
	select e.id_elemento, e.id_piazzola, tr.codice_cer,
	trim(concat(p.cod_percorso, ' - ', p.descrizione)) as percorso 
	from elem.elementi e
	join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento 
	join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto 
	left join elem.elementi_aste_percorso eap on e.id_elemento =eap.id_elemento 
	left join elem.aste_percorso ap on ap.id_asta_percorso = eap.id_asta_percorso 
	left join elem.percorsi p on p.id_percorso = ap.id_percorso
	where p.id_categoria_uso = 3 and te.tipologia_elemento in ('B', 'R')
) pp on pp.id_piazzola = vpd.id_piazzola and pp.codice_cer=coalesce(cc.codice_cer_corretto, cc.codice_cer::varchar)
where ci.id_piazzola not like 'MAG%'
and trim(ci.tipo_contenitore) != 'ECOISOLA'
group by vpd.id_piazzola, 
vpd.via,
vpd.civ,
vpd.riferimento,
vpd.municipio, 
vpd.quartiere,
cc.descrizione,
ci.targa_contenitore, 
ci.volume_contenitore,
ci.data_ultimo_agg, 
ci.val_riemp, 
ci.val_bat_elettronica,
ci.val_bat_bocchetta, 
sv.data_ora_last_sv, 
sv.riempimento, mc.media_conf_giorno
order by val_riemp desc, data_ora_last_sv";
    

    //questa parte per ora non serve
    if($_GET['ut']) {
        $query= "select * from (".$query0.") a where $1 = any(id_uts) ".$filter ;  
    } else {
        $query= "select * from (".$query0.") a where 1=1 ".$filter ;
    }

    //print $query."<br>";

    $result = pg_prepare($conn, "my_query", $query);
?>