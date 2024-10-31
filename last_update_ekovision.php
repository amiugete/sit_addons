<?php 



$query_max1="SELECT TO_CHAR(max(DATA_ORA_INSER),'DD/MM/YYYY HH24:MI') as MAX_DATA_AGG_JSON 
            FROM EKOVISION_LETTURA_CONSUNT elc";

$result_max1 = oci_parse($oraconn, $query_max1);
//oci_bind_by_name($result0, ':s1', $scheda);
oci_execute($result_max1);


while($rmax1 = oci_fetch_assoc($result_max1)) {
  echo '<i class="fa-solid fa-stopwatch"></i> ';
  /*if($rmax1['max_data_agg_api'] ){

  }*/
  echo "<b>Ultimo aggiornamento da EKOVISION</b>: ".$rmax1['MAX_DATA_AGG_JSON'] ."  ";
}


oci_free_statement($result_max1);
oci_close($oraconn);

?>