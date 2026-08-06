<?php 
$res_ok = 0;
  // filtro targa
  $query_sportello="SELECT DISTINCT sportello
FROM consunt.tb_pesi_percorsi
ORDER BY 1";

  $resultt = pg_prepare($conn, "my_query_sportello", $query_sportello);
  if (pg_last_error($conn)){
        echo pg_last_error($conn);
        $res_ok=$res_ok+1;
  }
  $resultt = pg_execute($conn, "my_query_sportello", array());
  if (pg_last_error($conn)){
        echo pg_last_error($conn);
        $res_ok=$res_ok+1;
  }
  #echo $result;

  ?>
  <script>
    var sportello_filtro = {

  <?php
  while($rt = pg_fetch_assoc($resultt)) {
      echo '"'.$rt["sportello"].'":"'.$rt["sportello"].'",';
  }
  pg_free_result($resultt);
  ?> 
  }
  </script>

  <?php 
  // filtro targa
  $query_rifiuto="SELECT DISTINCT descr_rifiuto
  FROM consunt.tb_pesi_percorsi
  ORDER BY 1";

  $resultr = pg_prepare($conn, "my_query_rifiuto", $query_rifiuto);
  if (pg_last_error($conn)){
        echo pg_last_error($conn);
        $res_ok=$res_ok+1;
  }
  $resultr = pg_execute($conn, "my_query_rifiuto", array());
  if (pg_last_error($conn)){
        echo pg_last_error($conn);
        $res_ok=$res_ok+1;
  }

  #echo $result;

  ?>
  <script>
    var rifiuto_filtro = {

  <?php
  while($rr = pg_fetch_assoc($resultr)) {
      echo '"'.$rr["descr_rifiuto"].'":"'.$rr["descr_rifiuto"].'",';
  }
  pg_free_result($resultr);
  ?> 
  }
  </script>


  <?php 
  // filtro percorso
  $query_descrizione="SELECT DISTINCT descrizione 
  FROM elem.fascia_turni ft
  ORDER BY 1";

  $resultd = pg_prepare($conn, "my_query_descrizione", $query_descrizione);
  if (pg_last_error($conn)){
        echo pg_last_error($conn);
        $res_ok=$res_ok+1;
  }
  $resultd = pg_execute($conn, "my_query_descrizione", array());
  if (pg_last_error($conn)){
        echo pg_last_error($conn);
        $res_ok=$res_ok+1;
  }

  #echo $result;

  ?>
  <script>
    var descrizione_filtro = {

  <?php
  while($rd = pg_fetch_assoc($resultd)) {
      echo '"'.$rd["descrizione"].'":"'.$rd["descrizione"].'",';
  }
  pg_free_result($resultd);
  ?> 
  }
  </script>

    <?php 
  // filtro servizio
  $query_servizio="SELECT DISTINCT servizio
FROM consunt.v_dettaglio_pesi_percorso_ok
ORDER BY 1";

  $results = pg_prepare($conn, "my_query_servizio", $query_servizio);
  if (pg_last_error($conn)){
        echo pg_last_error($conn);
        $res_ok=$res_ok+1;
  }
  $results = pg_execute($conn, "my_query_servizio", array());
  if (pg_last_error($conn)){
        echo pg_last_error($conn);
        $res_ok=$res_ok+1;
  }

  #echo $result;

  ?>
  <script>
    var servizio_filtro = {

  <?php
  while($rs = pg_fetch_assoc($results)) {
      echo '"'.$rs["servizio"].'":"'.$rs["servizio"].'",';
  }
  pg_free_result($results);
  ?> 
  }
  </script>


