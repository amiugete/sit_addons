<?php


// troppo lento. Pagina non utilizzata e sostituita da esportazione python  (vedi backoffice/download_driver_ekovision.php) 

ob_start();
ini_set('memory_limit','4096M');

require_once('./req.php');

//the_page_title();

if ($_SESSION['test']==1) {
  require_once ('./conn_test.php');
} else {
  require_once ('./conn.php');
}






$query_personale1="
SELECT 
ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE,
mese,
id_comune,  
comune,
id_municipio,
municipio,
id_uo,
desc_uo,
id_uo_lavoro,
desc_uo_lavoro,
MANSIONE,
round(sum(COALESCE(perc,1)*durata)/60,2) AS ore
FROM (
SELECT 
	DISTINCT per.cod_postoorg AS MANSIONE, 
	COALESCE (asc2.ID_SERVIZIO_COGE,'SRV0000') AS ID_SERVIZIO_COGE,
	COALESCE (asc2.DESCR_SERVIZIO_COGE,'Ore non assegnate') AS DESCR_SERVIZIO_COGE,
	hs.DURATA, 
	aspu.ID_PERCORSO, 
	hs.ID_SER_PER_UO,
	to_char(hs.DTA_SERVIZIO, 'YYYY/MM') AS mese,
	id_comune,  
	comune,
	id_municipio,
	municipio,
	au1.ID_UO, 
	au1.DESC_UO, -- da cahiamare DESC_UO_SERVIZIO
	au2.id_uo AS id_uo_lavoro, 
	au2.DESC_UO AS desc_uo_lavoro,-- da cahiamare DESC_UO_UOMO
	perc
FROM HIST_SERVIZI hs
	JOIN ANAGR_SER_PER_UO aspu 
		ON aspu.ID_SER_PER_UO = hs.ID_SER_PER_UO
	JOIN anagr_servizi as2 ON aspu.id_servizio = as2. id_servizio
	LEFT JOIN ANAGR_SERVIZI_COGE asc2 
		ON asc2.id_servizio_COGE = as2.id_servizio_coge		
	LEFT JOIN PERCORSI_X_COMUNE_UO_GIORNO pxcuo 
		ON pxcuo.id_percorso = aspu.ID_PERCORSO 
		AND pxcuo.giorno = hs.dta_servizio 
		AND pxcuo.giorno BETWEEN aspu.DTA_ATTIVAZIONE AND aspu.DTA_DISATTIVAZIONE
	JOIN anagr_uo au1 ON aspu.ID_UO = au1.ID_UO
	INNER JOIN anagr_pers_uo per
	 ON     (per.id_persona = hs.id_persona OR hs.COD_DIPENDENTE = concat(concat(per.COD_MATLIBROMAT, '_'),per.id_azienda))
	    AND hs.dta_servizio BETWEEN per.dta_inizio
	                            AND per.dta_fine
	/*JOIN HCMDB9.hrhistory@cezanne8 h 
		ON h.ID_PERSONA = hs.ID_PERSONA 
		AND hs.dta_servizio BETWEEN h.DTA_INIZIO AND h.DTA_FINE*/
	LEFT JOIN UNIOPE.V_AFFERENZE_PERSONALE vap 
		ON per.COD_SEDE=vap.ID_SEDE_TRASPORTO AND per.COD_CDC = vap.CODICE_CDC AND per.COD_UNITAORG = vap.COD_UNITAORG
	LEFT JOIN ANAGR_UO au2 ON vap.ID_UO_GEST = au2.ID_UO
	--JOIN anagr_uo au2 ON hs.ID_UO_LAVORO = au2.ID_UO
WHERE  
hs.DTA_SERVIZIO BETWEEN TO_DATE(:d1, 'DD/MM/YYYY') AND to_date(:d2, 'DD/MM/YYYY')
AND hs.durata > 0 AND coalesce(perc,1) > 0 
) pp
WHERE durata > 0
GROUP BY 
ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE, 
mese,
id_comune,  
comune,
id_municipio,
municipio,
id_uo,
desc_uo, id_uo_lavoro,
desc_uo_lavoro, mansione
ORDER BY ID_SERVIZIO_COGE, comune, mese 
";



$query_personale2="
SELECT 
ID_SERVIZIO, DESC_SERVIZIO,
mese,
id_comune,  
comune,
id_municipio,
municipio,
id_uo,
desc_uo,
id_uo_lavoro,
desc_uo_lavoro,
MANSIONE,
round(sum(COALESCE(perc,1)*durata)/60,2) AS ore
FROM (
SELECT 
	DISTINCT per.cod_postoorg AS MANSIONE, 
	COALESCE (asc2.ID_SERVIZIO_COGE,'SRV0000') AS ID_SERVIZIO_COGE,
	COALESCE (asc2.DESCR_SERVIZIO_COGE,'Ore non assegnate') AS DESCR_SERVIZIO_COGE,
	as2.ID_SERVIZIO,
	as2.DESC_SERVIZIO, 
  hs.DURATA, 
	aspu.ID_PERCORSO, 
	hs.ID_SER_PER_UO,
	to_char(hs.DTA_SERVIZIO, 'YYYY/MM') AS mese,
	id_comune,  
	comune,
	id_municipio,
	municipio,
	au1.ID_UO, 
	au1.DESC_UO, -- da cahiamare DESC_UO_SERVIZIO
	au2.id_uo AS id_uo_lavoro, 
	au2.DESC_UO AS desc_uo_lavoro,-- da cahiamare DESC_UO_UOMO
	perc
FROM HIST_SERVIZI hs
	JOIN ANAGR_SER_PER_UO aspu 
		ON aspu.ID_SER_PER_UO = hs.ID_SER_PER_UO
	JOIN anagr_servizi as2 ON aspu.id_servizio = as2. id_servizio
	LEFT JOIN ANAGR_SERVIZI_COGE asc2 
		ON asc2.id_servizio_COGE = as2.id_servizio_coge		
	LEFT JOIN PERCORSI_X_COMUNE_UO_GIORNO pxcuo 
		ON pxcuo.id_percorso = aspu.ID_PERCORSO 
		AND pxcuo.giorno = hs.dta_servizio 
		AND pxcuo.giorno BETWEEN aspu.DTA_ATTIVAZIONE AND aspu.DTA_DISATTIVAZIONE
	JOIN anagr_uo au1 ON aspu.ID_UO = au1.ID_UO
	INNER JOIN anagr_pers_uo per
	 ON     (per.id_persona = hs.id_persona OR hs.COD_DIPENDENTE = concat(concat(per.COD_MATLIBROMAT, '_'),per.id_azienda))
	    AND hs.dta_servizio BETWEEN per.dta_inizio
	                            AND per.dta_fine
	/*JOIN HCMDB9.hrhistory@cezanne8 h 
		ON h.ID_PERSONA = hs.ID_PERSONA 
		AND hs.dta_servizio BETWEEN h.DTA_INIZIO AND h.DTA_FINE*/
	LEFT JOIN UNIOPE.V_AFFERENZE_PERSONALE vap 
		ON per.COD_SEDE=vap.ID_SEDE_TRASPORTO AND per.COD_CDC = vap.CODICE_CDC AND per.COD_UNITAORG = vap.COD_UNITAORG
	LEFT JOIN ANAGR_UO au2 ON vap.ID_UO_GEST = au2.ID_UO
	--JOIN anagr_uo au2 ON hs.ID_UO_LAVORO = au2.ID_UO
WHERE  
hs.DTA_SERVIZIO BETWEEN TO_DATE(:d1, 'DD/MM/YYYY') AND to_date(:d2, 'DD/MM/YYYY')
AND hs.durata > 0 AND coalesce(perc,1) > 0 
) pp
WHERE durata > 0
GROUP BY 
ID_SERVIZIO, DESC_SERVIZIO,
mese,
id_comune,  
comune,
id_municipio,
municipio,
id_uo,
desc_uo, id_uo_lavoro,
desc_uo_lavoro, mansione
ORDER BY ID_SERVIZIO_COGE, comune, mese 
";



$query_mezzi1="SELECT 
ID_SERVIZIO_COGE,
DESCR_SERVIZIO_COGE,
giorno,
id_comune,  
comune,
id_municipio,
municipio,
id_uo,
desc_uo,
TIPO_MEZZO,
sportello,
sum(COALESCE(perc,1)*durata/60) AS ore
FROM (
	SELECT 
	DISTINCT hsm.sportello,
	COALESCE (asc2.ID_SERVIZIO_COGE,'SRV0000') AS ID_SERVIZIO_COGE,
	COALESCE (asc2.DESCR_SERVIZIO_COGE,'Ore non assegnate') AS DESCR_SERVIZIO_COGE,
	hsm.DURATA, 
	aspu.ID_PERCORSO, 
	TO_DATE(see.DATA_PIANIF_INIZIALE,'YYYYMMDD') AS giorno,
	id_comune,  
	comune,
	id_municipio,
	municipio,
	/*au.id_uo,
	au.desc_uo,*/
	/*Correggo i mezzi grandi*/
	CASE 
		WHEN /*trim(a.CDAOG3) NOT IN ('08','09', 'I1')*/
		COALESCE(a.SEDE_PRESA_SERV, 'ND') LIKE 'RIMESSA%' AND au.ID_ZONATERRITORIALE IN (1,2,3) THEN 
			(SELECT DISTINCT au1.id_uo FROM ANAGR_SER_PER_UO aspu1 
				JOIN anagr_uo au1 ON au1.ID_UO = aspu1.ID_UO 
				WHERE au1.ID_ZONATERRITORIALE = 5 AND aspu1.ID_PERCORSO = aspu.ID_PERCORSO)
		ELSE /*au.ID_UO*/
		 (SELECT DISTINCT aspu1.ID_UO FROM ANAGR_SER_PER_UO aspu1 
		WHERE id_squadra <> 15 AND aspu1.ID_SER_PER_UO = aspu.ID_SER_PER_UO)
	END AS id_uo, 
	CASE 
		WHEN /*trim(a.CDAOG3) NOT IN ('08','09', 'I1') */
		COALESCE(a.SEDE_PRESA_SERV, 'ND') LIKE 'RIMESSA%' AND au.ID_ZONATERRITORIALE IN (1,2,3) THEN 
			(SELECT DISTINCT au1.DESC_UO FROM ANAGR_SER_PER_UO aspu1 
				JOIN anagr_uo au1 ON au1.ID_UO = aspu1.ID_UO 
				WHERE au1.ID_ZONATERRITORIALE = 5 AND aspu1.ID_PERCORSO = aspu.ID_PERCORSO)
		ELSE (SELECT DISTINCT au1.DESC_UO FROM ANAGR_SER_PER_UO aspu1
		JOIN anagr_uo au1 ON au1.ID_UO = aspu1.ID_UO
		WHERE id_squadra <> 15 AND aspu1.ID_SER_PER_UO = aspu.ID_SER_PER_UO)
	END AS desc_uo,
	codice_tipologia_mezzo,
	descrizione_tipologia_mezzo AS tipo_mezzo,
	perc     
	FROM HIST_SERVIZI_MEZZI_OK hsm
	JOIN SCHEDE_ESEGUITE_EKOVISION see 
		ON see.ID_SCHEDA = hsm.ID_SCHEDA_EKOVISION AND see.RECORD_VALIDO='S'
		AND see.COD_CAUS_SRV_NON_ESEG_EXT IS null
	JOIN ANAGR_SER_PER_UO aspu 
		ON aspu.ID_PERCORSO = see.CODICE_SERV_PRED 
		AND to_date(see.DATA_ESECUZIONE_PREVISTA, 'YYYYMMDD') BETWEEN aspu.DTA_ATTIVAZIONE AND aspu.DTA_DISATTIVAZIONE 
	JOIN anagr_servizi as2 ON aspu.id_servizio = as2. id_servizio
	LEFT JOIN ANAGR_SERVIZI_COGE asc2 
		ON asc2.id_servizio_COGE = as2.id_servizio_coge	
	LEFT JOIN PERCORSI_X_COMUNE_UO_GIORNO pxcuo 
		ON pxcuo.id_percorso = aspu.ID_PERCORSO AND pxcuo.giorno = to_date(see.DATA_ESECUZIONE_PREVISTA, 'YYYYMMDD')   
		AND pxcuo.giorno BETWEEN aspu.DTA_ATTIVAZIONE AND aspu.DTA_DISATTIVAZIONE 
	JOIN anagr_uo au
		ON au.ID_UO= aspu.ID_UO
	/*LEFT JOIN (SELECT ma.numatr AS sportello, ma.CDAOG3, oa.DSAOG3 FROM MAC_AMIUAUTO@info ma
JOIN OG3_AMIUAUTO@info oa ON oa.CDAOG3 =ma.CDAOG3) a ON trim(a.sportello) = lpad(hsm.sportello, 5,'0') */
	LEFT JOIN v_AUTO_EKOVISION@info a ON  trim(a.sportello) = lpad(hsm.sportello, 5,'0')
	WHERE  /*id_comune = 2 AND au.id_uo = 10 AND*/
	/*trim(a.sportello)= '03754' and*/
	TO_DATE(see.DATA_PIANIF_INIZIALE,'YYYYMMDD') BETWEEN TO_DATE(:d1, 'DD/MM/YYYY') AND to_date(:d2, 'DD/MM/YYYY')
	ORDER BY ID_PERCORSO, giorno
) pp
WHERE sportello IS NOT NULL 
GROUP BY
ID_SERVIZIO_COGE,
DESCR_SERVIZIO_COGE,
giorno,
id_comune,  
comune,
id_municipio,
municipio,
id_uo,
desc_uo, tipo_mezzo, sportello
ORDER BY 1, 3, 4, 6, 8, 9";




$query_mezzi2="SELECT 
ID_SERVIZIO,
DESC_SERVIZIO,
giorno,
id_comune,  
comune,
id_municipio,
municipio,
id_uo,
desc_uo,
TIPO_MEZZO,
sportello,
sum(COALESCE(perc,1)*durata/60) AS ore
FROM (
	SELECT 
	DISTINCT hsm.sportello,
	COALESCE (asc2.ID_SERVIZIO_COGE,'SRV0000') AS ID_SERVIZIO_COGE,
	COALESCE (asc2.DESCR_SERVIZIO_COGE,'Ore non assegnate') AS DESCR_SERVIZIO_COGE,
	as2.id_SERVIZIO,
	as2.DESC_SERVIZIO,
  hsm.DURATA, 
	aspu.ID_PERCORSO, 
	TO_DATE(see.DATA_PIANIF_INIZIALE,'YYYYMMDD') AS giorno,
	id_comune,  
	comune,
	id_municipio,
	municipio,
	/*au.id_uo,
	au.desc_uo,*/
	/*Correggo i mezzi grandi*/
	CASE 
		WHEN /*trim(a.CDAOG3) NOT IN ('08','09', 'I1')*/
		COALESCE(a.SEDE_PRESA_SERV, 'ND') LIKE 'RIMESSA%' AND au.ID_ZONATERRITORIALE IN (1,2,3) THEN 
			(SELECT DISTINCT au1.id_uo FROM ANAGR_SER_PER_UO aspu1 
				JOIN anagr_uo au1 ON au1.ID_UO = aspu1.ID_UO 
				WHERE au1.ID_ZONATERRITORIALE = 5 AND aspu1.ID_PERCORSO = aspu.ID_PERCORSO)
		ELSE /*au.ID_UO*/
		 (SELECT DISTINCT aspu1.ID_UO FROM ANAGR_SER_PER_UO aspu1 
		WHERE id_squadra <> 15 AND aspu1.ID_SER_PER_UO = aspu.ID_SER_PER_UO)
	END AS id_uo, 
	CASE 
		WHEN /*trim(a.CDAOG3) NOT IN ('08','09', 'I1') */
		COALESCE(a.SEDE_PRESA_SERV, 'ND') LIKE 'RIMESSA%' AND au.ID_ZONATERRITORIALE IN (1,2,3) THEN 
			(SELECT DISTINCT au1.DESC_UO FROM ANAGR_SER_PER_UO aspu1 
				JOIN anagr_uo au1 ON au1.ID_UO = aspu1.ID_UO 
				WHERE au1.ID_ZONATERRITORIALE = 5 AND aspu1.ID_PERCORSO = aspu.ID_PERCORSO)
		ELSE (SELECT DISTINCT au1.DESC_UO FROM ANAGR_SER_PER_UO aspu1
		JOIN anagr_uo au1 ON au1.ID_UO = aspu1.ID_UO
		WHERE id_squadra <> 15 AND aspu1.ID_SER_PER_UO = aspu.ID_SER_PER_UO)
	END AS desc_uo,
	codice_tipologia_mezzo,
	descrizione_tipologia_mezzo AS tipo_mezzo,
	perc     
	FROM HIST_SERVIZI_MEZZI_OK hsm
	JOIN SCHEDE_ESEGUITE_EKOVISION see 
		ON see.ID_SCHEDA = hsm.ID_SCHEDA_EKOVISION AND see.RECORD_VALIDO='S'
		AND see.COD_CAUS_SRV_NON_ESEG_EXT IS null
	JOIN ANAGR_SER_PER_UO aspu 
		ON aspu.ID_PERCORSO = see.CODICE_SERV_PRED 
		AND to_date(see.DATA_ESECUZIONE_PREVISTA, 'YYYYMMDD') BETWEEN aspu.DTA_ATTIVAZIONE AND aspu.DTA_DISATTIVAZIONE 
	JOIN anagr_servizi as2 ON aspu.id_servizio = as2. id_servizio
	LEFT JOIN ANAGR_SERVIZI_COGE asc2 
		ON asc2.id_servizio_COGE = as2.id_servizio_coge	
	LEFT JOIN PERCORSI_X_COMUNE_UO_GIORNO pxcuo 
		ON pxcuo.id_percorso = aspu.ID_PERCORSO AND pxcuo.giorno = to_date(see.DATA_ESECUZIONE_PREVISTA, 'YYYYMMDD')   
		AND pxcuo.giorno BETWEEN aspu.DTA_ATTIVAZIONE AND aspu.DTA_DISATTIVAZIONE 
	JOIN anagr_uo au
		ON au.ID_UO= aspu.ID_UO
	/*LEFT JOIN (SELECT ma.numatr AS sportello, ma.CDAOG3, oa.DSAOG3 FROM MAC_AMIUAUTO@info ma
JOIN OG3_AMIUAUTO@info oa ON oa.CDAOG3 =ma.CDAOG3) a ON trim(a.sportello) = lpad(hsm.sportello, 5,'0') */
	LEFT JOIN v_AUTO_EKOVISION@info a ON  trim(a.sportello) = lpad(hsm.sportello, 5,'0')
	WHERE  /*id_comune = 2 AND au.id_uo = 10 AND*/
	/*trim(a.sportello)= '03754' and*/
	TO_DATE(see.DATA_PIANIF_INIZIALE,'YYYYMMDD') BETWEEN TO_DATE(:d1, 'DD/MM/YYYY') AND to_date(:d2, 'DD/MM/YYYY')
	ORDER BY ID_PERCORSO, giorno
) pp
WHERE sportello IS NOT NULL 
GROUP BY
ID_SERVIZIO,
DESC_SERVIZIO,
giorno,
id_comune,  
comune,
id_municipio,
municipio,
id_uo,
desc_uo, tipo_mezzo, sportello
ORDER BY 1, 3, 4, 6, 8, 9";







require_once "vendor/autoload.php";
//  output buffering per non fare stampe


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
#use PhpOffice\PhpSpreadsheet\IOFactory;

//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');





#print di tutte le variabili
#foreach ($_POST as $key => $value){
#  echo "{$key} = {$value}<br>";
#}


#echo "OK 1<br>";
$data_start = explode("-", $_POST['daterange'])[0]; 
$data_end = explode("-", $_POST['daterange'])[1];
#echo "OK 2<br>";
$tipo_report = $_POST['tipo_report'];






#echo $tipo_report. "<br>";
#echo $data_start. "<br>";
#echo $data_end. "<br>";
#echo "OK 3<br>";
#exit();
 

# esportazione personale
$spreadsheet = new Spreadsheet('');
$Excel_writer = new Xlsx($spreadsheet);

# il primo foglio lo faccio del personale (index 0 e titolo)
$spreadsheet->setActiveSheetIndex(0);
$activeSheet = $spreadsheet->getActiveSheet()->setTitle("Personale");
#echo "OK 4<br>";

# provo a usare la cache per velocizzare
# composer require cache/simple-cache-bridge cache/apcu-adapter -W

$pool = new \Cache\Adapter\Apcu\ApcuCachePool();
$simpleCache = new \Cache\Bridge\SimpleCache\SimpleCacheBridge($pool);

\PhpOffice\PhpSpreadsheet\Settings::setCache($simpleCache);



if ($tipo_report ==1){
  $activeSheet->setCellValue('A1', 'ID servizio COGE');
  $activeSheet->setCellValue('B1', 'Desc servizio COGE');
} else if ($tipo_report ==2){
  $activeSheet->setCellValue('A1', 'ID servizio');
  $activeSheet->setCellValue('B1', 'Desc servizio');
}
 
$activeSheet->setCellValue('C1', 'Mese');
$activeSheet->setCellValue('D1', 'ID Comune');
$activeSheet->setCellValue('E1', 'Comune');
$activeSheet->setCellValue('F1', 'ID Municipio');
$activeSheet->setCellValue('G1', 'Municipio');
$activeSheet->setCellValue('H1', 'ID UO');
$activeSheet->setCellValue('I1', 'Desc UO');
$activeSheet->setCellValue('J1', 'ID UO Lavoro');
$activeSheet->setCellValue('K1', 'DESC UO Lavoro');
$activeSheet->setCellValue('L1', 'Mansione');
$activeSheet->setCellValue('M1', 'Ore');


#echo "OK 5<br>";
#exit;
if ($tipo_report ==1){
  $result_personale = oci_parse($oraconn, $query_personale1);
} else if ($tipo_report ==2){
  $result_personale = oci_parse($oraconn, $query_personale2);
}
oci_bind_by_name($result_personale, ':d1', $data_start);
oci_bind_by_name($result_personale, ':d2', $data_end);
oci_execute($result_personale);
$e = oci_error($oraconn);

print htmlentities($e['message']);
$i = 2;
while($r = oci_fetch_assoc($result_personale)) { 
    #echo $i.'<br>';
    if ($i >= 63459){
      $row = $activeSheet->getHighestRow()+1;
      $activeSheet->insertNewRowBefore($row);
    }
    if ($tipo_report ==1){
      $activeSheet->setCellValue('A'.$i , $r['ID_SERVIZIO_COGE']);
      $activeSheet->setCellValue('B'.$i , $r['DESCR_SERVIZIO_COGE']);
    } else if ($tipo_report ==2){
      $activeSheet->setCellValue('A'.$i , $r['ID_SERVIZIO']);
      $activeSheet->setCellValue('B'.$i , $r['DESC_SERVIZIO']);
    }
    $activeSheet->setCellValue('C'.$i , $r['MESE']);
    $activeSheet->setCellValue('D'.$i , $r['ID_COMUNE']);
    $activeSheet->setCellValue('E'.$i , $r['COMUNE']);
    $activeSheet->setCellValue('F'.$i , $r['ID_MUNICIPIO']);
    $activeSheet->setCellValue('G'.$i , $r['MUNICIPIO']);
    $activeSheet->setCellValue('H'.$i , $r['ID_UO']);
    $activeSheet->setCellValue('I'.$i , $r['DESC_UO']);
    $activeSheet->setCellValue('J'.$i , $r['ID_UO_LAVORO']);
    $activeSheet->setCellValue('K'.$i , $r['DESC_UO_LAVORO']);
    $activeSheet->setCellValue('L'.$i , $r['MANSIONE']);
    $activeSheet->setCellValue('M'.$i , $r['ORE']);
    $i++;
}
//echo $i;
//exit;
oci_free_statement($result_personale);


// autosize
foreach (range('A', $activeSheet->getHighestColumn()) as $col) {
    $activeSheet->getColumnDimension($col)->setAutoSize(true);
 }


 // autofilter
 // definisco prima e ultima riga
 $firstrow=1;
 $lastrow=$i-1;
// set autofilter
$activeSheet->setAutoFilter("A".$firstrow.":M".$lastrow);


echo "personale esportato<br>";




#################################################################################################
# MEZZI
# il secondo foglio lo faccio per i mezzi (index 1 e titolo)
$spreadsheet->createSheet();

$spreadsheet->setActiveSheetIndex(1);
$activeSheet = $spreadsheet->getActiveSheet()->setTitle("Mezzi");


# provo a usare la cache per velocizzare
# composer require cache/simple-cache-bridge cache/apcu-adapter -W

$pool = new \Cache\Adapter\Apcu\ApcuCachePool();
$simpleCache = new \Cache\Bridge\SimpleCache\SimpleCacheBridge($pool);

\PhpOffice\PhpSpreadsheet\Settings::setCache($simpleCache);



if ($tipo_report ==1){
  $activeSheet->setCellValue('A1', 'ID servizio COGE');
  $activeSheet->setCellValue('B1', 'Desc servizio COGE');
} else if ($tipo_report ==2){
  $activeSheet->setCellValue('A1', 'ID servizio');
  $activeSheet->setCellValue('B1', 'Desc servizio');
}
$activeSheet->setCellValue('C1', 'Giorno');
$activeSheet->setCellValue('D1', 'ID Comune');
$activeSheet->setCellValue('E1', 'Comune');
$activeSheet->setCellValue('F1', 'ID Municipio');
$activeSheet->setCellValue('G1', 'Municipio');
$activeSheet->setCellValue('H1', 'ID UO');
$activeSheet->setCellValue('I1', 'Desc UO');
$activeSheet->setCellValue('J1', 'Tipo mezzo');
$activeSheet->setCellValue('K1', 'Sportello');
$activeSheet->setCellValue('L1', 'Ore');



if ($tipo_report ==1){
  $result_mezzi = oci_parse($oraconn, $query_mezzi1);
} else if ($tipo_report ==2){
  $result_mezzi = oci_parse($oraconn, $query_mezzi2);
}

//echo "Qua ci arrivo 2<br>";

oci_bind_by_name($result_mezzi, ':d1', $data_start);
oci_bind_by_name($result_mezzi, ':d2', $data_end);
oci_execute($result_mezzi);
$e = oci_error($oraconn);
print htmlentities($e['message']);

$i = 2;
while($rm = oci_fetch_assoc($result_mezzi)) { 
    //echo $i.'<br>';
    if ($i >= 63459){
      echo $i.'<br>';
      //echo 'Sono qua e provo a inserire una riga<br>';
      $row = $activeSheet->getHighestRow()+1;
      $activeSheet->insertNewRowBefore($row);
      //echo 'OK<br>';
      //exit();
    }
    if ($tipo_report ==1){
      $activeSheet->setCellValue('A'.$i , $r['ID_SERVIZIO_COGE']);
      $activeSheet->setCellValue('B'.$i , $r['DESCR_SERVIZIO_COGE']);
    } else if ($tipo_report ==2){
      $activeSheet->setCellValue('A'.$i , $r['ID_SERVIZIO']);
      $activeSheet->setCellValue('B'.$i , $r['DESC_SERVIZIO']);
    }
    $activeSheet->setCellValue('C'.$i , $r['GIORNO']);
    $activeSheet->setCellValue('D'.$i , $r['ID_COMUNE']);
    $activeSheet->setCellValue('E'.$i , $r['COMUNE']);
    $activeSheet->setCellValue('F'.$i , $r['ID_MUNICIPIO']);
    $activeSheet->setCellValue('G'.$i , $r['MUNICIPIO']);
    $activeSheet->setCellValue('H'.$i , $r['ID_UO']);
    $activeSheet->setCellValue('I'.$i , $r['DESC_UO']);
    $activeSheet->setCellValue('J'.$i , $r['TIPO_MEZZO']);
    $activeSheet->setCellValue('K'.$i , $r['SPORTELLO']);
    $activeSheet->setCellValue('L'.$i , $r['ORE']);
    $i++;
}
echo $i;
oci_free_statement($result_mezzi);
oci_close($oraconn);
exit();
// autosize
foreach (range('A', $activeSheet->getHighestColumn()) as $col) {
    $activeSheet->getColumnDimension($col)->setAutoSize(true);
 }


 // autofilter
 // definisco prima e ultima riga
 $firstrow=1;
 $lastrow=$i-1;
// set autofilter
$activeSheet->setAutoFilter("A".$firstrow.":L".$lastrow);





$filename = 'driver_ekovision.xlsx';
 
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='. $filename);
header('Cache-Control: max-age=0');
ob_end_clean();
$Excel_writer->save('php://output');
//require_once('./req_bottom.php');
?>