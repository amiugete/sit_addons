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




<script>
var date_filtro = {
  "202401": "2024/01",
  "202402": "2024/02",
  "202403": "2024/03",
  "202404": "2024/04",
  "202405": "2024/05",
  "202406": "2024/06",
  "202407": "2024/07",
  "202408": "2024/08",
  "202409": "2024/09",
  "202410": "2024/10",
  "202411": "2024/11",
  "202412": "2024/12",
};
</script>

<div class="container">



  
 


<div id="tabella_raccolta">
            
        <h4>Indicatori raccolta
        <a href="#tabella_spazzamento" class="btn btn-sm btn-info"> Vai allo spazzamento </a></h4>
        NOTE: (i) per servizi si intende il numero di elementi per cui è pianificato lo svuotamento, (ii) sono considerati come effettuati tutti i servizi effettuati regolarmente o con un recupero entro le 24 h



            <div class="row">

                  
  <!--div id="toolbar"> Per esportare i dati completi rimuovere la paginazione (primo tasto dopo la ricerca)
</div-->
      <table  id="raccolta" class="table-hover" 
        data-show-columns="true"
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
        data-url="./tables/report_indicatori_arera_raccolta.php" 
        data-toolbar="#toolbar" 
        data-show-footer="false"
        >

<!--Per i filtri guardare report_fascia_oraria_esecuzione -->

        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="AMBITO" data-sortable="true" data-visible="true" data-filter-control="select">Ambito</th>
        <th data-field="COMUNE" data-sortable="true" data-visible="true"  data-filter-control="select">Comune</th>
        <th data-field="ANNOMESE" data-sortable="true" data-visible="true" data-formatter="dateFormat" 
        data-filter-control="select" data-filter-data="var:date_filtro" >Mese anno</th>
        <!--th data-field="ANNOMESE" data-sortable="true" data-visible="true" data-formatter="dateFormat" data-filter-control="input" >Mese anno</th-->  
        <th data-field="SERVIZI_PIANIFICATI"  data-sortable="true" data-visible="true" data-filter-control="input">Servizi pianificati</th>
        <th data-field="SERVIZI_NON_EFFETTUATI"  data-sortable="true" data-visible="true" data-filter-control="input">Servizi non effettuati</th>
        <th data-field="CAUSA_FORZA_MAGGIORE"   data-sortable="true" data-visible="true" data-filter-control="input">Causa di<br>forza maggiore</th>
        <th data-field="IMPUTABILI_UTENTE"   data-sortable="true" data-visible="true" data-filter-control="input">Cause<br>imputabili<br>all'utente</th>
        <th data-field="IMPUTABILI_GESTORE"   data-sortable="true" data-visible="true" data-filter-control="input">Cause<br>imputabili<br>al gestore</th>
        <th data-field="ALTRO"   data-sortable="true" data-visible="false" data-filter-control="input">Altro</th>
        <th data-field="PERC_SERV_EFFETTUATI" data-formatter="realFormat_pc"  data-sortable="true" data-visible="true" data-filter-control="input">Indicatore<br>puntualità</th>
        <th data-field="PERC_SERV_NON_EFFETTUATI" data-formatter="realFormat_pc"  data-sortable="true" data-visible="false" data-filter-control="input">Perc non<br>effettuati</th>
        <!--th data-field="ALTRO" data-sortable="true" data-visible="false" data-formatter="nameFormatterAtt" 
        data-filter-strict-search="true" data-search-formatter="false" data-filter-data="var:opzioni" data-filter-control="select"></th-->
        <!--th data-field="cp_report" data-sortable="false" data-formatter="nameFormatterReport" data-visible="true" >Report</th-->
      
          <!--th data-field="quartiere" data-sortable="true" data-visible="true" data-filter-control="select">Quartiere<br>/Comune</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-filter-control="select">UT</th>
        <th data-field="tipo" data-sortable="true" data-visible="true" data-formatter="dateFormatter" data-filter-control="input">Data<br>apertura</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-formatter="dateFormatter" data-filter-control="input">Data<br>chiusura</th>
        <th data-field="desc_intervento" data-sortable="true" data-visible="true" data-filter-control="input">Descrizione</th-->
    </tr>
</thead>
</table>



<div class="modal fade" id="viewMemberModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"> 
    <div class="modal-dialog modal-dialog-scrollable modal-xl" >
      <div class="modal-content">
        <div class="modal-header">
          <!--h5 class="modal-title" id="exampleModalLabel">Titolo</h5-->
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      <div class="modal-body" id="body_dettaglio">
                <!-- output data here-->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">



  var $table = $('#raccolta');
  
  $(function() {
    $table.bootstrapTable()
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
    return moment(value).format('MMM YYYY');
   } else {
    return '-';
   }
};



function realFormat(value, row, index) {
   if (value){ 
    return parseFloat(value);
   } else {
    return '-';
   }
};
 
function realFormat_pc(value, row, index) {
   if (value){ 
    return parseFloat(value)+'%';
   } else {
    return '-';
   }
};


</script>


</div>	<!--tabella-->



<div id="tabella_spazzamento">
            
        <h4>Indicatori spazzamento
        <a href="#tabella_raccolta" class="btn btn-sm btn-info"> Vai alla raccolta </a>
        </h4>
        NOTE: (i) per servizi si intende il valore in km lineari da spazzare / lavare (art 46.3 TQRIF) (ii) sono considerati come effettuati tutti i servizi effettuati regolarmente o con un recupero entro le 24 h



            <div class="row">

                  
  <!--div id="toolbar"> Per esportare i dati completi rimuovere la paginazione (primo tasto dopo la ricerca)
</div-->
      <table  id="spazzamento" class="table-hover" 
        data-show-columns="true"
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
        data-url="./tables/report_indicatori_arera_spazzamento.php" 
        data-toolbar="#toolbar" 
        data-show-footer="false"
        >

<!--Per i filtri guardare report_fascia_oraria_esecuzione -->

        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="AMBITO" data-sortable="true" data-visible="true" data-filter-control="select">Ambito</th>
        <th data-field="COMUNE" data-sortable="true" data-visible="true"  data-filter-control="select">Comune</th>
        <th data-field="ANNOMESE" data-sortable="true" data-visible="true" data-formatter="dateFormat" 
        data-filter-control="select" data-filter-data="var:date_filtro" >Mese anno</th>
        <!--th data-field="ANNOMESE" data-sortable="true" data-visible="true" data-formatter="dateFormat" data-filter-control="input" >Mese anno</th-->  
        <th data-field="SERVIZI_PIANIFICATI" data-formatter="realFormat" data-sortable="true" data-visible="true" data-filter-control="input">Servizi pianificati</th>
        <th data-field="SERVIZI_NON_EFFETTUATI" data-formatter="realFormat" data-sortable="true" data-visible="true" data-filter-control="input">Servizi non effettuati</th>
        <th data-field="CAUSA_FORZA_MAGGIORE" data-formatter="realFormat" data-sortable="true" data-visible="true" data-filter-control="input">Causa di<br>forza maggiore</th>
        <th data-field="IMPUTABILI_UTENTE" data-formatter="realFormat" data-sortable="true" data-visible="true" data-filter-control="input">Cause<br>imputabili<br>all'utente</th>
        <th data-field="IMPUTABILI_GESTORE" data-formatter="realFormat" data-sortable="true" data-visible="true" data-filter-control="input">Cause<br>imputabili<br>al gestore</th>
        <th data-field="ALTRO" data-formatter="realFormat" data-sortable="true" data-visible="false" data-filter-control="input">Altro</th>
        <th data-field="PERC_SERV_EFFETTUATI" data-formatter="realFormat_pc" data-sortable="true" data-visible="true" data-filter-control="input">Indicatore<br>puntualità</th>
        <th data-field="PERC_SERV_NON_EFFETTUATI" data-formatter="realFormat_pc" data-sortable="true" data-visible="false" data-filter-control="input">Perc non<br>effettuati</th>
        <!--th data-field="ALTRO" data-sortable="true" data-visible="false" data-formatter="nameFormatterAtt" 
        data-filter-strict-search="true" data-search-formatter="false" data-filter-data="var:opzioni" data-filter-control="select"></th-->
        <!--th data-field="cp_report" data-sortable="false" data-formatter="nameFormatterReport" data-visible="true" >Report</th-->
      
          <!--th data-field="quartiere" data-sortable="true" data-visible="true" data-filter-control="select">Quartiere<br>/Comune</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-filter-control="select">UT</th>
        <th data-field="tipo" data-sortable="true" data-visible="true" data-formatter="dateFormatter" data-filter-control="input">Data<br>apertura</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-formatter="dateFormatter" data-filter-control="input">Data<br>chiusura</th>
        <th data-field="desc_intervento" data-sortable="true" data-visible="true" data-filter-control="input">Descrizione</th-->
    </tr>
</thead>
</table>





<script type="text/javascript">



  var $tables = $('#spazzamento');
  
  $(function() {
    $tables.bootstrapTable()
  });
  


  function queryParams(params) {
    var options = $tables.bootstrapTable('getOptions')
    if (!options.pagination) {
      params.limit = options.totalRows
    }
    return params
  };



function dateFormat(value, row, index) {
   if (value){ 
    return moment(value).format('MMM YYYY');
   } else {
    return '-';
   }
};

function realFormat(value, row, index) {
   if (value){ 
    return parseFloat(value);
   } else {
    return '-';
   }
};
 
function realFormat_pc(value, row, index) {
   if (value){ 
    return parseFloat(value)+'%';
   } else {
    return '-';
   }
};


</script>


</div>






</div>

<?php
require_once('req_bottom.php');
require_once('./footer.php');
?>


  </div>

</body>

</html>