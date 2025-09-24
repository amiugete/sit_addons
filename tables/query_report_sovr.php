<?php

$query0= "
    /*report sovrariempimenti*/
select a.*, 
case
	when a.contenitori_presenti_su_sit > a.contenitori_ispezionati
	then 'Oggi ci sono più contenitori su SIT'
	when a.contenitori_presenti_su_sit < a.contenitori_ispezionati
	then 'Oggi ci sono meno contenitori su SIT'
	else 'OK'
end as congruenza_sit,  
case 
	when a.contenitori_ispezionati > 0
	then round((100*(a.contenitori_ispezionati::real-a.contenitori_sovrariempiti::real)/a.contenitori_ispezionati)::numeric, 2) 
	else NULL
end as indicatore
from  
( 
	select c.cod_istat, c.descr_comune, concat(p.id_piazzola, ' - ', v.nome, ', ', p.numero_civico, ' - Rif. ', p.riferimento) as piazzola,
	za.cod_zona as zona, u.descrizione as ut, q.nome as quartiere,
	string_agg(distinct pe.id_segnalazione::text, ' - ') as id_segnalazione,
	string_agg(distinct to_char(pe.data_ora_segnalazione,  'DD/MM/YYYY HH24.MI'), ' - ') as data_ora_segnalazione,
	count(distinct e.id_elemento) as contenitori_presenti_su_sit,
	i.id as id_ispezione,
	to_char(i.data_ora , 'DD/MM/YYYY HH24.MI') as data_ora_verifica, 
	i.ispettore as ispezione_eseguita_da,
	count(distinct ie.id_elemento) as contenitori_ispezionati, 
	count(distinct ie.id_elemento) filter (where ie.sovrariempito) as contenitori_sovrariempiti, 
	string_agg(distinct te.descrizione, ', ') filter (where ie.sovrariempito) as dettagli_sovrariempiti,
	string_agg(distinct ie.dettagli_svuotamenti, ', ') as dettagli_svuotamenti,
	(select count(id) from sovrariempimenti.ispezioni i2 where i2.id_piazzola = p.id_piazzola) as num_ispezioni_effettuate,
	pe.num_ispezioni as num_ispezioni_previste
	 from sovrariempimenti.programmazione_ispezioni pe 
	 join sovrariempimenti.ispezioni i on i.id_piazzola = pe.id_piazzola
	 left join sovrariempimenti.ispezione_elementi ie on ie.id_ispezione = i.id 
	 left join (select id_elemento, id_asta, tipo_elemento from elem.elementi
   		union 
		select id_elemento, id_asta, tipo_elemento from history.elementi) e on ie.id_elemento = e.id_elemento
	 left join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento  
	 left join elem.piazzole p on p.id_piazzola = i.id_piazzola 
	 --join elem.elementi e on e.id_piazzola = p.id_piazzola 
	 left join elem.aste a on a.id_asta = coalesce(p.id_asta, e.id_asta) 
	 left join topo.vie v on v.id_via = a.id_via 
	 left join topo.ut u on u.id_ut=a.id_ut 
	 left join topo.quartieri q on q.id_quartiere = a.id_quartiere
	 left join topo.zone_amiu za on za.id_zona = u.id_zona 
	 left join topo.comuni c on c.id_comune = v.id_comune 
	 group by 
	 c.cod_istat, c.descr_comune, concat(p.id_piazzola, ' - ', v.nome, ', ', p.numero_civico, ' - Rif. ', p.riferimento) ,
	 za.cod_zona , u.descrizione , q.nome ,
	 /*pe.id_segnalazione, pe.data_ora_segnalazione,*/ i.data_ora,
	 i.ispettore, i.id, pe.num_ispezioni, p.id_piazzola
	 )a
order by id_ispezione";
    

    //questa parte per ora non serve
    if($_GET['ut']) {
        $query= "select * from (".$query0.") a where $1 = any(id_uts) " ;  
    } else {
        $query= $query0;
    }

    //print $query."<br>";

    $result = pg_prepare($conn_sovr, "report_sovr", $query);
?>