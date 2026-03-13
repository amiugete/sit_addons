<?php 


$query0 = "SELECT
au.ID_UO_GEST,
au.desc_uo,
per.NOMINATIVO,
per.cod_MATLIBROMAT AS MATRICOLA,
sum(hs.durata) as DURATA_SERVIZIO_EKOVISION, 
sum(vo.MINUTI_LAV) AS minuti_lavorati_esipert,
sum(va.MINUTI_ASS) AS minuti_assenze,
listagg( 
	CASE 
		WHEN  aspu.descrizione IS NOT NULL
		THEN au1.desc_uo || ' - ' || aspu.descrizione
		ELSE 
		NULL
	END
	, 
	'; '
	) WITHIN GROUP (ORDER BY nominativo) SERVIZI,
	COALESCE(sum(hs.durata),0) - COALESCE(sum(vo.MINUTI_LAV),0) AS anomalia_min
FROM T_ANAGR_PERS_EKOVISION per
LEFT JOIN V_anagr_ut au 
	ON per.COD_CDC = au.CDC 
	AND per.cod_SEDE = au.cod_SEDE 
	AND per.cod_unitaorg = au.cod_unitaorg
LEFT JOIN esipertbo.v_orelav@sipedb vo 
	ON vo.CDAZIEND = per.ID_AZIENDA 
	AND vo.cdDIPEND = per.cod_MATLIBROMAT 
	AND vo.DTA_COMPETENZA = to_date(:datav, 'YYYYMMDD')
LEFT JOIN esipertbo.v_oreass@sipedb va 
	ON va.CDAZIEND = per.ID_AZIENDA 
	AND va.cdDIPEND = per.cod_MATLIBROMAT 
	AND va.DTA_COMPETENZA = to_date(:datav, 'YYYYMMDD')
LEFT JOIN HIST_SERVIZI hs ON hs.COD_DIPENDENTE = concat(concat(lpad(per.COD_MATLIBROMAT, 5,'0'), '_'),per.id_azienda)
                    AND hs.dta_servizio BETWEEN per.dta_inizio
                                            AND per.dta_fine
                    AND hs.DTA_SERVIZIO = to_date(:datav, 'YYYYMMDD')
LEFT JOIN ANAGR_SER_PER_UO aspu ON aspu.ID_SER_PER_UO = hs.ID_SER_PER_UO
LEFT JOIN anagr_uo au1 ON au1.id_uo = aspu.id_uo";







//print $query."<br>";






?>