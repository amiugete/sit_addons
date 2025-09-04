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

require_once("./last_update_ekovision.php");

?>
  
</div>

<?php 
$dt= new DateTime();
$today = new DateTime();
$last_month = $dt->modify("-1 month");
?>


<div class="form-group col-lg-6">
<label for="data_inizio" >Da  (GG/MM/AAAA) - A (GG/MM/AAAA)</label><font color="red">*</font>
    <input type="text" class="form-control" name="daterange" value="<?php echo $last_month->format('d/m/Y');?> - <?php echo $today->format('d/m/Y');?>"/>
    <small>Massimo 31 giorni </small>
</div>


<script>
$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'left',
    maxSpan: {
        days: 31
    },
    showISOWeekNumbers: true,
    minDate: "<?php echo $partenza_ditte_terze;?>" 

    
  }, function(start, end, label) {
    var data_inizio = start.format('YYYY-MM-DD') ;
    var data_fine= end.format('YYYY-MM-DD');
    console.log("A new date selection was made: " + data_inizio + ' to ' + data_fine);
    
    // Faccio refres della data-url
    $table.bootstrapTable('refresh', {
      url: "./tables/report_fascia_oraria_esecuzione.php?s="+data_inizio+"&e="+data_fine+""
    }); 
  });
});
</script>


</div>
<hr>

<div id="tabella">
            
        <h4>Report fasce orarie consuntivazione (ditte terze)</h4>




        <div class="table-responsive-sm">

                  <!--div id="toolbar">
        <button id="showSelectedRows" class="btn btn-primary" type="button">Crea ordine di lavoro</button>
      </div-->
    
      <div id="toolbar" class="isDisabled"> 
      <!--a target="_new" class="btn btn-primary btn-sm"
         href="./export_consuntivazione_ekovision.php"><i class="fa-solid fa-file-excel"></i> Esporta xlsx completo</a-->
      </div>
				<table  id="fascia_ora" class="table-hover table-sm" 
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
				data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
				data-side-pagination="server" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false" 
        data-export-data-type="all"
        data-url="./tables/report_fascia_oraria_esecuzione.php?s=<?php echo $last_month->format('Y-m-d');?>&e=<?php echo $today->format('Y-m-d');?>" 
        data-toolbar="#toolbar" 
        data-show-footer="false"
        >
        
        
<thead>



 	  <tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="FAMIGLIA" data-sortable="true" data-visible="true"  data-filter-control="select">Fam.<br>servizio</th>
        <th data-field="DESC_SERVIZIO" data-sortable="true" data-visible="true" data-footer-formatter="idFormatter" data-filter-control="select">Tipo<br>Servizio</th>
        <th data-field="DESC_UO" data-sortable="true" data-visible="true" data-filter-control="select">Gruppo<br>Coordinamento</th> 
        <th data-field="ID_SCHEDA" data-sortable="true" data-visible="true" data-filter-control="input">ID scheda<br>Ekovision</th>
        <th data-field="CODICE_SERV_PRED"  data-sortable="true" data-visible="true" data-filter-control="input">Codice</th>
        <th data-field="DESCRIZIONE" data-sortable="true" data-visible="true" data-filter-control="input">Descr<br>percorso</th>
        <th data-field="DATA_PIANIF_INIZIALE" data-sortable="true" data-formatter="dateFormat" data-visible="true" data-filter-control="input">Data<br>pianificata</th>
        <th data-field="FASCIA_ORA_ESECUZIONE" data-sortable="true" data-visible="true" data-filter-control="input">Fascia orario<br>esecuzione</th>
    </tr>
</thead>
</table>





<script type="text/javascript">



var $table = $('#fascia_ora');

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


<script>

$('#js-date').datepicker({
    format: 'dd/mm/yyyy',
    //startDate: '+1d', 
    language:'it' 
});

$('#js-date1').datepicker({
    format: 'dd/mm/yyyy', 
    language:'it'
});
</script>


</body>

</html>