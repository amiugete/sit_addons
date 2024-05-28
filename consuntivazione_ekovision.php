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
<div class="row justify-content-start">
<div class="col-6">
<?php 



$query_max1="SELECT TO_CHAR(max(DATA_ORA_INSER),'DD/MM/YYYY HH:MI') as MAX_DATA_AGG_JSON 
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
  </div>
  <div class="col-6">
  FILTRO PER UT sulla base delle UT del mio profilo SIT (TODO)
  </div>
</div>
<div class="row justify-content-start">

  <div class="col-6">
  FILTRO PER DATA DA (TODO in questo momento dal 15/04/2024 ) 
  </div>
  <div class="col-6">
  FILTRO PER DATA A (TODO in questo momento fino ad oggi) 
  </div>
</div>
<hr>

<div id="tabella">
            
        <h4>Report consuntivazione Ekovision</h4>




        <div class="table-responsive-sm">

                  <!--div id="toolbar">
        <button id="showSelectedRows" class="btn btn-primary" type="button">Crea ordine di lavoro</button>
      </div-->
    
      <div id="toolbar" class="isDisabled"> 
      <!--a target="_new" class="btn btn-primary btn-sm"
         href="./export_consuntivazione_ekovision.php"><i class="fa-solid fa-file-excel"></i> Esporta xlsx completo</a-->
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
        data-url="./tables/report_consuntivazione_ekovision.php" 
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
        <th data-field="DATA_PIANIFICATA" data-formatter="dateFormat" data-sortable="true" data-visible="true" data-filter-control="select">Data<br>pianificata</th>
        <th data-field="DATA_ESECUZIONE" data-formatter="dateFormat" data-sortable="true" data-visible="true" data-filter-control="select">Data<br>esecuzione</th>
        <th data-field="COD_PERCORSO" data-sortable="true" data-visible="false" data-filter-control="input">Cod<br>percorso</th>
        <th data-field="DESCRIZIONE" data-sortable="true" data-visible="false" data-filter-control="input">Descr<br>percorso</th>
        <th data-field="PERCORSO" data-sortable="true" data-visible="true" data-filter-control="input">Percorso</th>
        <th data-field="PREVISTO"  data-sortable="true" data-visible="true" data-formatter="prevFormat" 
        data-filter-strict-search="true" data-search-formatter="false" data-filter-data="var:opzioni_prev" data-filter-control="select">Prev</th>
        <th data-field="STATO" data-sortable="true" data-visible="true" data-formatter="statoFormat"  
        data-filter-strict-search="true" data-search-formatter="false" 
        data-filter-data="var:opzioni_stato"  data-filter-control="select">Stato</th>
        <th data-field="ORARIO_ESECUZIONE" data-sortable="true" data-visible="false" data-filter-control="input">Orario</th>
        <th data-field="FASCIA_TURNO" data-sortable="true" data-visible="true" data-filter-control="select">Turno</th>
        <th data-field="FLG_SEGN_SRV_NON_COMPL" data-sortable="true" data-visible="false" data-filter-control="select">Non<br>completato</th>
        <th data-field="FLG_SEGN_SRV_NON_EFFETT" data-sortable="true" data-visible="true" 
        data-formatter="nonEseguitoFormat" data-filter-control="select">Causale non<br>effettuato</th>
        
        <th data-field="ID_SCHEDA" data-sortable="true" data-visible="true" data-events="operateEvents" data-formatter="operateFormatter" data-filter-control="input"></th>
    </tr>
</thead>
</table>



<div class="modal fade" id="viewMemberModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"> 
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <!--h5 class="modal-title" id="exampleModalLabel">Titolo</h5-->
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      <div class="modal-body">
                <!-- output data here-->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">



var opzioni_prev = ['Previsto', 'Non previsto'] ;

function prevFormat(value) {
  if (value =='Previsto'){
    return '<span style="font-size: 1em; color: grey;"><i  title="'+value+'" class="fa-regular fa-calendar-check"></i></span>';
  } else if (value =='Non previsto') {
    return '<span style="font-size: 1em; color: blue;"> <i title="'+value+'" class="fa-regular fa-calendar-plus"></i></span>';
  }
};


function operateFormatter(value, row, index) {
    return [
        '<a class="info btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewMemberModal">',
        '<i class="fa-regular fa-square-caret-down"></i>',
        '</a>'
    ].join('');
};


window.operateEvents = {
    'click .info': function (e, value, row, index) {
        console.log('Sono qua');
        console.log(row.ID_SCHEDA);
        var id = row.ID_SCHEDA;
        console.log('id'+id);
        $.ajax({   
            type: "post",
            url: "consuntivazione_ekovision_dettaglio.php",
            data: 'id=' + id,
            dataType: "text",                  
            success: function(response){                    
                $(".modal-body").html(response); 
            }
        });
        $('#viewMemberModal').modal('show')
        	/*.find('.modal-body').html('<p> '+row.ID_SCHEDA+' </p><pre>' + 
            JSON.stringify(row) + '</pre>');*/
    }
};

// Modal con dettagli consuntivazione percorso 





var opzioni_stato = ['COMPLETATO', 'NON COMPLETATO', 'NON EFFETTUATO', 'NON ESEGUITO' ] ;

function statoFormat(value) {
  if (value =='COMPLETATO'){
    return '<span style="font-size: 1em; color: blue;"><i  title="'+value+'" class="fa-solid fa-check-double"></i></span>';
  }  else if (value =='NON ESEGUITO') {
    return '<span style="font-size: 1em; color: red;"> <i title="'+value+'" class="fa-solid fa-pencil"></i></span>';
  } else if (value =='NON COMPLETATO') {
    return '<span style="font-size: 1em; color: orange;"> <i title="'+value+'" class="fa-solid fa-circle-exclamation"></i></span>';
  } else if (value =='NON EFFETTUATO') {
    return '<span style="font-size: 1em; color: red;"> <i title="'+value+'" class="fa-solid fa-triangle-exclamation"></i></span>';
  }
};


function nonEseguitoFormat(value,row) {
  if (value == 0){
    return ''; 
  } else if (value ==1) {
    return '<span style="font-size: 1em; color: red;"> '+row.DESCR_CAUSALE+'</span>';
  } 
};

function idFormatter() {
    return 'Tot contenitori:'
  };

  function countFormatter(data) {
    return data.length
  };


var $table = $('#ek_cons');

$(function() {
    $table.bootstrapTable();
  });
  


  function queryParams(params) {
    var options = $table.bootstrapTable('getOptions')
    if (!options.pagination) {
      params.limit = options.totalRows
    }
    return params
  };



function dateFormat(value, row, index) {
   if (value){ 
    return moment(value).format('DD/MM/YYYY (ddd)');
   } else {
    return '-';
   }
};



  /*data.forEach(d=>{
       data_creazione = moment(d.data_creazione).format('DD/MM/YYYY HH24:MI')
    });*/
    
    function dateFormatter(date) {
      return moment(date).format('DD/MM/YYYY HH:mm')
    };

</script>


</div>	<!--tabella-->










</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>





</body>

</html>