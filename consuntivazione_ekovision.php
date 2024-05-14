<?php
//session_set_cookie_params($lifetime);
session_start();

    
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Gestione servizi</title>
<?php 
require_once('./req.php');

the_page_title();

if ($_SESSION['test']==1) {
  require_once ('./conn_test.php');
} else {
  require_once ('./conn.php');
}
?> 





</head>

<body>

<?php require_once('./navbar_up.php');
$name=dirname(__FILE__);
if ((int)$id_role_SIT = 0) {
  redirect('no_permessi.php');
  //exit;
}
?>


<div class="container">

<?php 

$query_min="select ci.id_piazzola,
concat(vpd.id_piazzola, ' - ', vpd.via, ' ',vpd.civ,' ', vpd.riferimento) as indirizzo,
vls.targa_contenitore, data_ora_last_sv
from idea.v_last_svuotamenti vls
left join idea.censimento_idea ci on ci.targa_contenitore = vls.targa_contenitore
left join elem.v_piazzole_dwh vpd on vpd.id_piazzola = ci.id_piazzola::int
left join idea.codici_cer cc on cc.codice_cer = ci.cod_cer_mat 
where data_ora_last_sv = (select min(data_ora_last_sv) as data_min
		from idea.v_last_svuotamenti 
		where targa_contenitore in (select targa_contenitore from idea.censimento_idea ci)
)";

$result_min = pg_prepare($conn, "query_min", $query_min);   
$result_min = pg_execute($conn, "query_min", array());


while($rmin = pg_fetch_assoc($result_min)) {
  echo "";
}



$query_max1="select date_trunc('minute',max(ci.data_agg_api)) as max_data_agg_api
from idea.censimento_idea ci 
left join elem.v_piazzole_dwh vpd on vpd.id_piazzola = ci.id_piazzola::int";

$result_max1 = pg_prepare($conn, "query_max1", $query_max1);   
$result_max1 = pg_execute($conn, "query_max1", array());


while($rmax1 = pg_fetch_assoc($result_max1)) {
  echo '<i class="fa-solid fa-stopwatch"></i> ';
  /*if($rmax1['max_data_agg_api'] ){

  }*/
  echo "<b>Ultimo aggiornamento AMIU</b>: ".$rmax1['max_data_agg_api'] ." / ";
}


$query_max0="select date_trunc('minute',max(ci.data_ultimo_agg)) as max_data_ultimo_agg
from idea.censimento_idea ci 
left join elem.v_piazzole_dwh vpd on vpd.id_piazzola = ci.id_piazzola::int";

$result_max0 = pg_prepare($conn, "query_max0", $query_max0);   
$result_max0 = pg_execute($conn, "query_max0", array());


while($rmax0 = pg_fetch_assoc($result_max0)) {
  echo '<i class="fa-solid fa-stopwatch-20"></i>';
  echo "<b>Ultimo aggiornamento contenitore</b>: ".$rmax0['max_data_ultimo_agg'] ." <br>";
}



$query_max="select ci.id_piazzola,
concat(vpd.id_piazzola, ' - ', vpd.via, ' ',vpd.civ,' ', vpd.riferimento) as indirizzo,
vls.targa_contenitore, data_ora_last_sv, cc.descrizione as frazione
from idea.v_last_svuotamenti vls
left join idea.censimento_idea ci on ci.targa_contenitore = vls.targa_contenitore
left join elem.v_piazzole_dwh vpd on vpd.id_piazzola = ci.id_piazzola::int
left join idea.codici_cer cc on cc.codice_cer = ci.cod_cer_mat 
where data_ora_last_sv = (select max(data_ora_last_sv) as data_min
		from idea.v_last_svuotamenti 
		where targa_contenitore in (select targa_contenitore from idea.censimento_idea ci)
)";

$result_max = pg_prepare($conn, "query_max", $query_max);   
$result_max = pg_execute($conn, "query_max", array());


while($rmax = pg_fetch_assoc($result_max)) {
  echo '<i class="fa-solid fa-truck-droplet"></i>';
  echo "<b>Ultimo contenitore svuotato registrato a sistema</b>: ".$rmax['indirizzo']." ".$rmax['frazione']." alle ore ".$rmax['data_ora_last_sv'] ."<br>";
}

?>



<hr>

<div id="tabella">
            
        <h4>Report consuntivazione ekovision</h4>




        <div class="table-responsive-sm">

                  <!--div id="toolbar">
        <button id="showSelectedRows" class="btn btn-primary" type="button">Crea ordine di lavoro</button>
      </div-->
    
      <div id="toolbar" class="isDisabled"> 
      <!--a target="_new" class="btn btn-primary btn-sm"
         href="./export_contenitori_bilaterali.php"><i class="fa-solid fa-file-excel"></i> Esporta xlsx completo</a-->
      </div>
				<table  id="ek_cons" class="table-hover" 
        data-cache="true"
        idfield="ID_SCHEDA" 
        data-show-columns="true"
        data-group-by="false"
        data-group-by-field='["indirizzo", "frazione"]'
        data-show-search-clear-button="true"   
        data-show-export="false" 
        data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf'] 
				data-search="true" data-click-to-select="true" data-show-print="false"  
        data-virtual-scroll="false"
        data-show-pagination-switch="false"
				data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
				data-side-pagination="false" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false"
        data-query-params="queryParams"
        data-url="./tables/consuntivazione_ekovision.php" 
        data-toolbar="#toolbar" 
        data-show-footer="false"
        >
        
        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="FAM_SERVIZIO" data-sortable="true" data-visible="false"  data-filter-control="select">Fam.<br>servizio</th>
        <th data-field="DESC_SERVIZIO" data-sortable="true" data-visible="true" data-footer-formatter="idFormatter" data-filter-control="select">Tipo<br>Servizio</th>
        <th data-field="UT" data-sortable="true" data-visible="true" data-filter-control="select">UT</th> 
        <th data-field="DATA_PIANIFICATA" data-formatter="dateFormat" data-sortable="true" data-visible="false" data-filter-control="input">Data<br>pianificata</th>
        <th data-field="DATA_ESECUZIONE" data-formatter="dateFormat" data-sortable="true" data-visible="true" data-filter-control="select">Data<br>esecuzione</th>
        <th data-field="COD_PERCORSO" data-sortable="true" data-visible="false" data-filter-control="input">Cod<br>percorso</th>
        <th data-field="DESCRIZIONE" data-sortable="true" data-visible="false" data-filter-control="input">Descr<br>percorso</th>
        <th data-field="PREVISTO"  data-sortable="true" data-visible="true" data-formatter="prevFormat" 
        data-filter-strict-search="true" data-search-formatter="false" data-filter-data="var:opzioni_prev" data-filter-control="select">Previsto</th>
        <th data-field="ORARIO_ESECUZIONE" data-sortable="true" data-visible="true" data-filter-control="input">Orario</th>
        <th data-field="FASCIA_TURNO" data-sortable="true" data-visible="true" data-filter-control="select">Turno</th>
        <th data-field="FLG_SEGN_SRV_NON_COMPL" data-sortable="true" data-visible="true" data-filter-control="select">Non<br>completato</th>
        <th data-field="FLG_SEGN_SRV_NON_EFFETT" data-sortable="true" data-visible="true" data-filter-control="select">Non<br>effettuato</th>
        <th data-field="STATO" data-sortable="true" data-visible="true" data-formatter="statoFormat"  
        data-filter-strict-search="true" data-search-formatter="false" data-filter-data="var:opzioni_stato"  data-filter-control="select">Stato</th>
        <th data-field="ID_SCHEDA" data-sortable="true" data-visible="true" data-filter-control="input"></th>
    </tr>
</thead>
</table>


<script type="text/javascript">



var opzioni_prev = ['Previsto', 'Non previsto'] 

function prevFormat(value) {
  if (value =='Previsto'){
    return '<span style="font-size: 1em; color: grey;"><i  title='+value+' class="fa-regular fa-calendar-check"></i></span>';
  } else if (value =='Non previsto') {
    return '<span style="font-size: 1em; color: blue;"> <i title='+value+' class="fa-regular fa-calendar-plus"></i></span>';
  }
}


var opzioni_stato = ['COMPLETATO', 'NON COMPLETATO', 'NON EFFETTUATO', 'NON ESEGUITO' ] 

function statoFormat(value) {
  if (value =='COMPLETATO'){
    return '<span style="font-size: 1em; color: blue;"><i  title='+value+' class="fa-solid fa-check-double"></i></span>';
  } else if (value =='NON COMPLETATO') {
    return '<span style="font-size: 1em; color: orange;"> <i title='+value+' class="fa-solid fa-circle-exclamation"></i></span>';
  } else if (value =='NON EFFETTUATO') {
    return '<span style="font-size: 1em; color: red;"> <i title='+value+' class="fa-solid fa-triangle-exclamation"></i></span>';
  } else if (value =='NON ESEGUITO') {
    return '<span style="font-size: 1em; color: red;"> <i title='+value+' class="fa-solid fa-pencil"></i></span>';
  }
}


function idFormatter() {
    return 'Tot contenitori:'
  }

  function countFormatter(data) {
    return data.length
  }


var $table = $('#ek_cons')

$(function() {
    $table.bootstrapTable()
  })
  


  function queryParams(params) {
    var options = $table.bootstrapTable('getOptions')
    if (!options.pagination) {
      params.limit = options.totalRows
    }
    return params
  }



function dateFormat(value, row, index) {
   if (value){ 
    return moment(value).format('DD/MM/YYYY');
   } else {
    return '-';
   }
}



  /*data.forEach(d=>{
       data_creazione = moment(d.data_creazione).format('DD/MM/YYYY HH24:MI')
    });*/
    
    function dateFormatter(date) {
      return moment(date).format('DD/MM/YYYY HH:mm')
    }

    function nameFormatterEdit(value, row) {
        
        return '<a class="btn btn-warning" href=./dettagli_percorso.php?cp='+row.cod_percorso+'&v='+row.versione+'><i class="fa-solid fa-pencil"></i></a>';
     
        }

</script>


</div>	<!--tabella-->










</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>





</body>

</html>