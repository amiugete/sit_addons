<?php
require_once("req.php");
session_start();
#require('../validate_input.php');

if ($_POST['id']){
	$scheda = $_POST['id'];
} else {
	echo 'Manca il paramentro post';
	$scheda=262322;
}


if ($_SESSION['test']==1) {
    require_once ('./conn_test.php');
} else {
    require_once ('./conn.php');
}
//echo "OK";



// per prima cosa cerco di capire il tipo di servizio 
$select_tipo="SELECT ID_SERVIZIO, 
TIPO_SERVIZIO, TIPO_RIFIUTO, CER, TIPO_SERVIZIO_FILTRO  FROM ANAGR_SERVIZI as2 WHERE ID_SERVIZIO  = (
	SELECT DISTINCT id_servizio FROM ANAGR_SER_PER_UO aspu 
	WHERE ID_PERCORSO = 
	(SELECT distinct CODICE_SERV_PRED 
	FROM SCHEDE_ESEGUITE_EKOVISION see WHERE ID_SCHEDA =:s1 and RECORD_VALIDO='S')
	AND to_date((SELECT distinct DATA_ESECUZIONE_PREVISTA 
	FROM SCHEDE_ESEGUITE_EKOVISION see WHERE ID_SCHEDA =:s2 and RECORD_VALIDO='S'), 'YYYYMMDD') BETWEEN 
	DTA_ATTIVAZIONE AND (DTA_DISATTIVAZIONE-1)
)";

$result0 = oci_parse($oraconn, $select_tipo);

oci_bind_by_name($result0, ':s1', $scheda);
oci_bind_by_name($result0, ':s2', $scheda);
oci_execute($result0);


while($r0 = oci_fetch_assoc($result0)) { 
    $ts=$r0['TIPO_SERVIZIO'];
}
oci_free_statement($result0);
oci_close($oraconn);

if ($ts=='RACCOLTA'){
	//echo $ts.'<br>';
	?>
	<div id="tabella_raccolta_<?php echo $scheda;?>">
            
    

        <div class="table-responsive-sm">

		<table  id="ek_cons_dett" class="table-hover" 
        data-cache="true"
        idfield="ID_PIAZZOLA" 
        data-show-columns="true"
        data-group-by="false"
        data-group-by-field='["indirizzo", "frazione"]'
        data-show-search-clear-button="true"   
		data-search="true" data-click-to-select="true" data-show-print="false"  
        data-virtual-scroll="false"
        data-show-pagination-switch="false"
		data-pagination="true" data-page-size=200 data-page-list=[10,25,50,75,100,200,500]
		data-side-pagination="false" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
		data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false"
        data-query-params="queryParams"
        data-url="./tables/report_consuntivazione_ekovision_raccolta.php?s=<?php echo $scheda;?>" 
        data-show-footer="false"
        >
        
        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
		<th data-field="POSIZIONE" data-sortable="true" data-visible="false"  data-filter-control="input">Piazzola</th>
        <th data-field="ID_PIAZZOLA" data-sortable="true" data-visible="true"  data-filter-control="input">Piazzola</th>
        <th data-field="VIA" data-sortable="true" data-visible="true" data-filter-control="select">Via</th>
        <th data-field="NUM_CIV" data-sortable="true" data-visible="true"  data-filter-control="input">Civ</th>
		<th data-field="RIFERIMENTO" data-sortable="true" data-visible="true"  data-filter-control="input">Riferimento</th>
		<th data-field="TIPO_ELEMENTO" data-sortable="true" data-visible="true"  data-filter-control="select">Tipo<br>elem</th>
		<th data-field="NUM_ELEMENTI" data-sortable="true" data-visible="true"  data-filter-control="input">Num<br>elem</th>
		<th data-field="TOTEM" data-sortable="true" data-visible="true"  data-filter-control="select">Consuntivato<br>su totem</th>
		<th data-field="RIPROGRAMMATO" data-sortable="true" data-visible="true"  data-filter-control="input">Soccorso</th>
		<th data-field="CAUSALE" data-sortable="true" data-visible="false"  data-filter-control="input">Id Causale</th>
		<th data-field="DESCR_CAUSALE" data-sortable="true" data-visible="true"  data-filter-control="select">Causale</th>
    </tr>
</thead>
</table>

</div>
<script type="text/javascript">


var $table = $('#ek_cons_dett');

$(function() {
    $table.bootstrapTable();
  })

</script>


</div>
<?php

} else if ($ts=='SPAZZAMENTO'){
	//echo 'Id scheda Ekovision: '.$scheda.'<br>Servizio di tipo '.$ts;
?>

<div id="tabella_raccolta_<?php echo $scheda;?>">
            
    

        <div class="table-responsive-sm">

		<table  id="ek_cons_dett" class="table-hover" 
        data-cache="true"
        idfield="ID_PIAZZOLA" 
        data-show-columns="true"
        data-group-by="false"
        data-group-by-field='["indirizzo", "frazione"]'
        data-show-search-clear-button="true"   
		data-search="true" data-click-to-select="true" data-show-print="false"  
        data-virtual-scroll="false"
        data-show-pagination-switch="false"
		data-pagination="true" data-page-size=200 data-page-list=[10,25,50,75,100,200,500]
		data-side-pagination="false" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
		data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false"
        data-query-params="queryParams"
        data-url="./tables/report_consuntivazione_ekovision_spazzamento.php?s=<?php echo $scheda;?>" 
        data-show-footer="false"
        >
        
        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
		<th data-field="POS" data-sortable="true" data-visible="false"  data-filter-control="input">Piazzola</th>
        <th data-field="VIA" data-sortable="true" data-visible="true" data-filter-control="select">Via</th>
        <th data-field="NOTA_VIA" data-sortable="true" data-visible="true"  data-filter-control="input">Nota via</th>
		<th data-field="NUM_TRATTI" data-sortable="true" data-visible="true"  data-filter-control="input">Num<br>tratti via</th>
		<th data-field="MQ_DA_SPAZZARE" data-sortable="true" data-visible="true"  data-filter-control="input">MQ<br>previsti</th>
		<th data-field="RIPASSO" data-sortable="true" data-visible="false"  data-filter-control="select">Ripasso</th>
		<th data-field="TOTEM" data-sortable="true" data-visible="true"  data-filter-control="select">Consuntivato<br>su totem</th>
		<th data-field="RIPROGRAMMATO" data-sortable="true" data-visible="true"  data-filter-control="select">Soccorso</th>
		<th data-field="QUALITA" data-sortable="true" data-visible="true"  data-filter-control="select">Qualità</th>
		<th data-field="CAUSALE" data-sortable="true" data-visible="false"  data-filter-control="select">Id Causale</th>
		<th data-field="DESCR_CAUSALE" data-sortable="true" data-visible="true"  data-filter-control="select">Causale</th>
    </tr>
</thead>
</table>

</div>
<script type="text/javascript">


var $table = $('#ek_cons_dett');

$(function() {
    $table.bootstrapTable();
  })

</script>
<?php 
} else {
	echo 'Id scheda Ekovision: '.$scheda.'<br>Servizio di tipo '.$ts.'. Non sono previsti maggiori dettagli.';
}

require_once("req_bottom.php");

?>