<?php
//session_set_cookie_params($lifetime);
session_start();

    
?>
<!DOCTYPE html>
<html lang="it">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Pesi per UT</title>
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

<?php 

require_once("./filter_tables/filter_pesi.php");

?>

<div class="container-fluid">
  <h4 style="margin-bottom: 1%; display:inline;"><i class="fa-solid fa-scale-balanced"></i>Report Pesi per UT</h4>
  <button type="button" class="btn btn-sm" data-bs-container="body" 
  data-bs-toggle="popover" data-bs-placement="right">
  <i class="fa-regular fa-circle-question"></i>
</button>
  <?php 

#require_once("./last_update_ekovision.php");

?>

<div class="row justify-content-start" style="margin-top: 1%;">

  <div class="col-4">
  <!--FILTRO PER UT sulla base delle UT del mio profilo SIT-->
<div class="rfix">

<script>
  function utScelta(val) {
    document.getElementById('open_ut').submit();
  }
</script>

 


<form class="row" name="open_ut" method="post" id="open_ut" autocomplete="off" action="report_pesi_ut.php" >

<!--?php echo $username; 
echo 'ut: '.$_POST['ut'].'<br>';?-->

<div class="form-group">

<label for="ut" >Seleziona una UT</label>
  <select class="selectpicker show-tick form-control" 
  data-live-search="true" name="ut" id="ut" onchange="utScelta(this.value);" required="">
  
  
  <?php 
  if (intval($_POST['ut'])>=0) {
    $query0='select cmu.id_uo as ut, u.id_ut, descrizione
    from topo.ut u
    join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo_sit = u.id_ut 
    where cmu.id_uo = $1';

    $result0 = pg_prepare($conn, "my_query0", $query0);
    $result0 = pg_execute($conn, "my_query0", array($_POST['ut']));
    
    while($r0 = pg_fetch_assoc($result0)) { 
  ?>    
          <option name="ut" value="<?php echo $r0['ut']?>"> <?php echo $r0['descrizione']?></option>
  <?php }
  pg_free_result($result0); 
  ?>
  <option name="ut" value="0">Nessuna UT</option>
  <?php 
  } else {
  ?>
    <option name="ut" value="0">Nessuna UT</option>
  
  
  <?php            
  }

  require_once('query_ut.php');

  //echo "<br>". $query1;


  $result1 = pg_prepare($conn, "my_query1", $query_ut);
  $result1 = pg_execute($conn, "my_query1", array($_SESSION['username']));

  while($r1 = pg_fetch_assoc($result1)) { 
?>    
        <option name="ut0" value="<?php echo $r1['id_uo'];?>" ><?php echo $r1['descrizione']?></option>
<?php 
  }
  pg_free_result($result1); 
?>

  </select>  
       
</div>
  </form>

  </div>

<!--FINE FILTRO PER UT sulla base delle UT del mio profilo SIT -->

  </div>

<?php 
$dt= new DateTime();
$today = new DateTime();
$last_month = $dt->modify("-1 month");
$query_min_date = "SELECT MIN(data_percorso) as min_date FROM consunt.v_dettaglio_pesi_percorso";
$result_min_date = pg_prepare($conn, "my_query_min_date", $query_min_date);
$result_min_date = pg_execute($conn, "my_query_min_date", array());
while($r_min_date = pg_fetch_assoc($result_min_date)) { 
  $min_data = new DateTime($r_min_date['min_date']);
}
?>



<div class="form-group col-4">
<label for="data_inizio" >Da (GG/MM/AAAA) - A (GG/MM/AAAA) <!--small>Massimo 31 giorni </small></label><font color="red">*</font-->
    <!--input type="text" class="form-control" name="daterange" value="<?php echo $last_month->format('d/m/Y');?> - <?php echo $today->format('d/m/Y');?>"/-->
    <input type="text" class="form-control" name="daterange" placeholder="Filtra per intervallo date"/>

</div>


<script>

$(function() {
  $('input[name="daterange"]').daterangepicker({
    autoUpdateInput: false,  // ← NON compilare automaticamente l'input
    locale: {
      cancelLabel: 'Cancella',
      applyLabel: 'Applica'
    },
    opens: 'left',
    /*maxSpan: {
        days: 31
    },*/
    showISOWeekNumbers: true/*,
    minDate: "<?php echo $partenza_ekovision;?>"*/
    });
    
  /*}, function(start, end, label) {
    var data_inizio = start.format('YYYY-MM-DD') ;
    var data_fine= end.format('YYYY-MM-DD');
    console.log("A new date selection was made: " + data_inizio + ' to ' + data_fine);*/
    
  

    // Faccio refres della data-url
    /*$table.bootstrapTable('refresh', {
      url: "./tables/data_report_pesi_percorso.php?ut=<?php echo $_POST['ut']?>&data_inizio="+data_inizio+"&data_fine="+data_fine+""
    });*/

  // Lasciamo l’input vuoto e la tabella carica tutto
  $('input[name="daterange"]').val('');

  
  $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));

    // aggiorna i parametri e ricarica la tabella
    $('#pesi_ut').bootstrapTable('refresh', {
      query: {
        data_inizio: picker.startDate.format('YYYY-MM-DD'),
        data_fine: picker.endDate.format('YYYY-MM-DD')
      }
    });
  });

  // 
  $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
    $('#pesi_ut').bootstrapTable('refresh', {
      query: {
        data_inizio: '<?php echo $min_data->format("Y-m-d"); ?>',
        data_fine: '<?php echo $today->format("Y-m-d"); ?>'
      }
    });
  });
  });
//});
</script>

<div class="col-4" style="align-content: center;">
  <!--button id="export-btn" class="btn btn-primary" title="Esporta file Excel completo" style="width: 100%;"><i class="bi bi-file-earmark-excel" ></i>Esporta tabella</button-->
</div>

</div>
<!--hr-->




<div id="tabella">

        <div class="table-responsive-sm">
          
   
      <div id="toolbar"> 
        <!--button id="export-btn-filtered" class="btn btn-primary" title="Esporta file Excel filtrato"><i class="fa-solid fa-filter"></i>Esporta tabella filtrata</button-->
      </div>
        <table  id="pesi_ut" class="table-hover table-sm" 
        data-locale="it-IT"
        idfield="id" 
        data-show-columns="true"
        data-group-by="false"
        data-show-search-clear-button="true"   
        data-show-export="false" 
				data-search="false" 
        data-click-to-select="true" data-show-print="false"  
        data-virtual-scroll="false"
        data-show-pagination-switch="false"
				data-pagination="true" data-page-size=100 data-page-list=[10,25,50,75,100,200,500]
				data-side-pagination="server" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
        data-search-on-enter-key="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-export-data-type="all"
        data-url="./tables/data_report_pesi_ut.php?ut=<?php echo $_POST['ut'];?>&data_inizio=<?php echo $min_data->format("Y-m-d");?>&data_fine=<?php echo $today->format("Y-m-d");?>" 
        data-toolbar="#toolbar"
        data-show-footer="false"
        data-query-params="queryParams"
        >
        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="id" data-sortable="true" data-visible="false"  data-filter-control="select">ID</th>
        <th data-field="zona" data-sortable="true" data-visible="false"  data-filter-control="input">Zona</th>
        <th data-field="rimessa" data-sortable="true" data-visible="true" data-filter-control="false">UT titolare</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-filter-control="false">UT esecutrice</th> 
        <th data-field="cod_cer" data-sortable="true" data-visible="false" data-filter-control="false">CER</th>
        <th data-field="descr_rifiuto" data-sortable="true" data-visible="true" data-filter-data="var:rifiuto_filtro" data-filter-control="select">Rifiuto</th>
        <th data-field="data_percorso" data-formatter="dateFormat" data-sortable="true" data-visible="true" data-filter-control="false">Data</th>
        <th data-field="numero_pesate" data-sortable="true" data-visible="true" data-filter-control="false">N. Pesate</th>
        <th data-field="peso_totale" data-sortable="true" data-visible="true" data-filter-control="false">Peso totale</th>
        <th data-field="numero_percorsi" data-sortable="true" data-visible="true" data-filter-control="false">N. Servizi</th>
    </tr>
</thead>
</table>











<script type="text/javascript">


var $table = $('#pesi_ut');

/*
function filterActive() {
  options = $('#ek_cons').bootstrapTable('getOptions');
  console.log(options)
  const filters = options.filterControlValues || {};
  const hasFilters = Object.values(filters).some(val => val !== '' && val !== null);

  console.log(hasFilters)
  return hasFilters
};*/

$table.on('post-body.bs.table', function () {
  if ($('#export-btn-filtered').length === 0) {
    $('.fixed-table-toolbar .columns')
      .append('<button id="export-btn-filtered" class="btn btn-secondary ms-2" title="Esporta file Excel"><i class="bi bi-download"></i> Esporta tabella</button>');
  }
});


$(function() {
    $table.bootstrapTable();
    //console.log($table.bootstrapTable());
  });



  function queryParams(params) {
    const options = $table.bootstrapTable('getOptions')
    if (!options.pagination) {
      params.limit = options.totalRows
    }
    return params
  };


function dateFormat(value, row, index) {
   if (value){ 
    return moment(value).format('DD/MM/YYYY');
   } else {
    return '-';
   }
};



$(function() {
  initTableExport({
    tableId: "pesi_ut",
    exportAllBtn: "#export-btn",
    exportFilteredBtn: "#export-btn-filtered",
    baseUrl: "./tables/data_report_pesi_ut.php",
    extraParams: () => {
      // parametri extra della pagina
      const range = $('input[name="daterange"]').val().split(" - ");
      return {
        ut: $("#ut").val() == 0 ? "" : $("#ut").val(),
        data_inizio: range[0].split('/').reverse().join('-'),
        data_fine: range[1].split('/').reverse().join('-')
      };
    }
  });
});


</script>


</div>	<!--tabella-->










</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>


<script>
  var popoverContent = `La pagina mostra il peso totale di un determinato rifiuto conferito dai servizi svolti in una determinata data dall'UT. Di default vengono mostrati tutti i dati, 
  ma è possibile filtrare per intervallo di date e per UT.<br><br>
  <b>I dati dei pesisono estratti da Ekovision in caso di scarico in impianto terzo, mentre per scarichi in impianti AMIU sono estratti da ECOS per i pesi registrati utilizzando il foglio pesata scaricato da Ekovision.</b><br>`;


  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl, {
      html: true,
      content: popoverContent
    })
  })

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