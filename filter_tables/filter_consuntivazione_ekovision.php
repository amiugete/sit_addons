  <?php 
  // filtro ambito
  $query_servizio="SELECT DISTINCT as2.DESC_SERVIZIO
FROM UNIOPE.V_REPORT_PERCORSI_EKOVISION vrpe
JOIN ANAGR_SER_PER_UO aspu ON vrpe.DATA_PIANIFICATA >=aspu.DTA_ATTIVAZIONE 
						AND vrpe.DATA_PIANIFICATA < aspu.DTA_DISATTIVAZIONE 
						AND trim(vrpe.COD_PERCORSO) = trim(aspu.ID_PERCORSO) 
JOIN ANAGR_UO au ON au.ID_UO = aspu.ID_UO 
JOIN ANAGR_SERVIZI as2 ON as2.ID_SERVIZIO = aspu.ID_SERVIZIO
ORDER BY 1";

  $result = oci_parse($oraconn, $query_servizio);

  oci_execute($result);

  #echo $result;

  ?>
  <script>
    var servizio_filtro = {

  <?php
  while($r = oci_fetch_assoc($result)) {
      echo '"'.$r["DESC_SERVIZIO"].'":"'.$r["DESC_SERVIZIO"].'",';
  }
  oci_free_statement($result);
  ?> 
  }
  </script>


  <?php 
  // filtro ambito
  $query_causale="SELECT DISTINCT cd.DESCRIZIONE AS DESCR_CAUSALE
FROM UNIOPE.V_REPORT_PERCORSI_EKOVISION vrpe
LEFT JOIN CAUSE_DISSERV cd ON CAST(vrpe.COD_CAUS_SRV_NON_ESEG_EXT AS integer) = cd.CODICE
ORDER BY 1";

  $result = oci_parse($oraconn, $query_causale);

  oci_execute($result);

  #echo $result;

  ?>
  <script>
    var causale_filtro = {

  <?php
  while($r = oci_fetch_assoc($result)) {
      echo '"'.$r["DESCR_CAUSALE"].'":"'.$r["DESCR_CAUSALE"].'",';
  }
  oci_free_statement($result);
  ?> 
  }
  </script>

