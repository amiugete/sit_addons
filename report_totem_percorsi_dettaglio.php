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

# filtro sulle UT
$filter_totem="OK";


?> 





</head>

<body>



<div class="container">
<?php 




//require_once("select_ut.php");

?>

<div id="tabella">
            
        <h4>Dettaglio percorso <?php echo $_POST['id'];?> del <?php echo $_POST['datalav'];?></h4>




        <div class="table-responsive-sm">

                  <!--div id="toolbar">
        <button id="showSelectedRows" class="btn btn-primary" type="button">Crea ordine di lavoro</button>
      </div-->
    
      <div id="toolbar" class="isDisabled"> 
      <!--a target="_new" class="btn btn-primary btn-sm"
         href="./export_consuntivazione_ekovision.php"><i class="fa-solid fa-file-excel"></i> Esporta xlsx completo</a-->
      </div>
				<table  id="totem_percorsi_dettaglio" class="table-hover" 
        idfield="ID_SCHEDA" 
        data-show-columns="true"
        data-group-by="false"
        data-group-by-field='["indirizzo", "frazione"]'
        data-show-search-clear-button="true"   
        data-show-export="true" 
        data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'pdf', 'excel',  'doc'] 
				data-search="false" data-click-to-select="true" data-show-print="false"  
        data-virtual-scroll="false"
        data-show-pagination-switch="false"
				data-pagination="false" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
				data-side-pagination="false" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false" 
        data-export-data-type="all"
        data-url="./tables/report_totem_percorsi_dettaglio.php?id=<?php echo $_POST['id'];?>&datalav=<?php echo $_POST['datalav'];?>" 
        data-toolbar="#toolbar" 
        data-show-footer="false"
        >
        
        
<thead>



 	  <tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="id_piazzola" data-sortable="true" data-visible="true"  data-filter-control="input">Piazzola</th>
        <th data-field="nome_via" data-sortable="true" data-visible="true" data-filter-control="select">Indirizzo</th>
        <th data-field="elem_non_fatti" data-sortable="true" data-visible="true" data-filter-control="input">Nr.</th> 
        <th data-field="causale" data-sortable="true" data-visible="true" data-filter-control="input">Causale</th> 
        <th data-field="operatore"  data-sortable="true" data-visible="true" data-filter-control="select">Operatore</th>

    </tr>
</thead>
</table>





<script type="text/javascript">



var $table = $('#totem_percorsi_dettaglio');

$(function() {
    $table.bootstrapTable();
  });
  




  var opzioni = ['OK', 'NON CONSUNTIVATO', 'ANOMALIA'] ;

function nameFormatterStato(value, row, index) {
  if (value =='OK'){
    return '<span style="font-size: 1em; color: green;"> <i title="'+row.descr_causale+'" class="fa-solid fa-circle"></i></span>';
  } else if (value =='NON CONSUNTIVATO') {
    return '<span style="font-size: 1em; color: black;"> <i title="'+row.descr_causale+'" class="fa-solid fa-circle"></i></span>';
  } else if (value =='ANOMALIA') {
    return '<span style="font-size: 1em; color: red;"> <i title="'+row.descr_causale+'" class="fa-solid fa-circle"></i></span>';
  } 
};




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


function dateFormat2(value, row, index) {
   if (value){ 
    return moment(value).format('DD/MM/YYYY HH:mm');
   }
};




  /*data.forEach(d=>{
       data_creazione = moment(d.data_creazione).format('DD/MM/YYYY HH24:MI')
    });*/
    
    function dateFormatter(value) {
      return moment(value).format('DD/MM/YYYY HH:mm')
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