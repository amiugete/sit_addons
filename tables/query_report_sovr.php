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
round((100*(a.contenitori_ispezionati::real-a.contenitori_sovrariempiti::real)/a.contenitori_ispezionati)::numeric, 2) as indicatore
from  
( 
	select c.descr_comune, concat(p.id_piazzola, ' - ', v.nome, ', ', p.numero_civico, ' - Rif. ', p.riferimento) as piazzola,
	za.cod_zona as zona, u.descrizione as ut, q.nome as quartiere,
	string_agg(pe.id_segnalazione::text, ' - ') as id_segnalazione,
	string_agg(to_char(pe.data_ora_segnalazione,  'DD/MM/YYYY HH24.MI'), ' - ') as data_ora_segnalazione,
	count(distinct e.id_elemento) as contenitori_presenti_su_sit,
	i.id as id_ispezione,
	to_char(i.data_ora , 'DD/MM/YYYY HH24.MI') as data_ora_verifica, 
	i.ispettore as ispezione_eseguita_da,
	count(distinct ie.id_elemento) as contenitori_ispezionati, 
	count(distinct ie.id_elemento) filter (where ie.sovrariempito) as contenitori_sovrariempiti, 
	string_agg(distinct te.descrizione, ', ') filter (where ie.sovrariempito) as dettagli_sovrariempiti,
	string_agg(distinct ie.dettagli_svuotamenti, ', ') as dettagli_svuotamenti
	 from sovrariempimenti.programmazione_ispezioni pe 
	 join sovrariempimenti.ispezioni i on i.id_piazzola = pe.id_piazzola
	 inner join sovrariempimenti.ispezione_elementi ie on ie.id_ispezione = i.id 
	 join (select id_elemento, id_asta, tipo_elemento from elem.elementi
   		union 
		select id_elemento, id_asta, tipo_elemento from history.elementi) e on ie.id_elemento = e.id_elemento
	 join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento  
	 left join elem.piazzole p on p.id_piazzola = i.id_piazzola 
	 --join elem.elementi e on e.id_piazzola = p.id_piazzola 
	 join elem.aste a on a.id_asta = coalesce(p.id_asta, e.id_asta) 
	 join topo.vie v on v.id_via = a.id_via 
	 join topo.ut u on u.id_ut=a.id_ut 
	 join topo.quartieri q on q.id_quartiere = a.id_quartiere
	 join topo.zone_amiu za on za.id_zona = u.id_zona 
	 join topo.comuni c on c.id_comune = v.id_comune 
	 group by 
	 c.descr_comune, concat(p.id_piazzola, ' - ', v.nome, ', ', p.numero_civico, ' - Rif. ', p.riferimento) ,
	 za.cod_zona , u.descrizione , q.nome ,
	 /*pe.id_segnalazione, pe.data_ora_segnalazione,*/ i.data_ora,
	 i.ispettore, i.id 
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