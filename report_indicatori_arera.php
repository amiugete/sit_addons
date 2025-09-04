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



<?php 

// time_update
$query_lr="select to_char(AGGIORNAMENTO, 'DD/MM/YYYY HH24:MI') as TIME_UPDATE
from INDICATORI_ARERA_RACCOLTA WHERE ROWNUM = 1";

$result = oci_parse($oraconn, $query_lr);

oci_execute($result);


while($r = oci_fetch_assoc($result)) {
    $time_update_r= $r['TIME_UPDATE'];
}

oci_free_statement($result);



// filtro date
$query_date="SELECT DISTINCT ANNOMESE, 
to_char(to_date(ANNOMESE, 'YYYYMM'), 'YYYY/MM') AS ANNOMESE2 
FROM UNIOPE.INDICATORI_ARERA_RACCOLTA viar 
ORDER BY ANNOMESE
";

$result = oci_parse($oraconn, $query_date);

oci_execute($result);
?>
<script>
  var date_filtro = {

<?php
while($r = oci_fetch_assoc($result)) {
    echo '"'.$r["ANNOMESE"].'":"'.$r["ANNOMESE2"].'",';
}
oci_free_statement($result);
?> 
}
</script>



<?php
// filtro anno
$query_anno="SELECT DISTINCT substr(ANNOMESE,1,4) as ANNO 
FROM UNIOPE.INDICATORI_ARERA_RACCOLTA viar 
ORDER BY ANNO
";

$result = oci_parse($oraconn, $query_anno);

oci_execute($result);
?>
<script>
  var anno_filtro = {

<?php
while($r = oci_fetch_assoc($result)) {
    echo '"'.$r["ANNO"].'":"'.$r["ANNO"].'",';
}
oci_free_statement($result);
?> 
}
</script>



<?php
// filtro mese
$query_mese="SELECT DISTINCT substr(ANNOMESE,5,2) as MESE 
FROM UNIOPE.INDICATORI_ARERA_RACCOLTA viar 
ORDER BY MESE
";

$result = oci_parse($oraconn, $query_mese);

oci_execute($result);
?>
<script>
  var mese_filtro = {

<?php
while($r = oci_fetch_assoc($result)) {
    echo '"'.$r["MESE"].'":"'.$r["MESE"].'",';
}
oci_free_statement($result);
?> 
}
</script>

<?php 
// filtro ambito
$query_ambito="SELECT DISTINCT AMBITO 
FROM UNIOPE.INDICATORI_ARERA_RACCOLTA viar 
ORDER BY AMBITO
";

$result = oci_parse($oraconn, $query_ambito);

oci_execute($result);
?>
<script>
  var ambito_filtro = {

<?php
while($r = oci_fetch_assoc($result)) {
    echo '"'.$r["AMBITO"].'":"'.$r["AMBITO"].'",';
}
oci_free_statement($result);
?> 
}
</script>



<?php 
// filtro comune
$query_comune="SELECT DISTINCT COMUNE 
FROM UNIOPE.INDICATORI_ARERA_RACCOLTA viar 
ORDER BY COMUNE
";

$result = oci_parse($oraconn, $query_comune);

oci_execute($result);
?>
<script>
  var comune_filtro = {

<?php
while($r = oci_fetch_assoc($result)) {
    echo '"'.$r["COMUNE"].'":"'.$r["COMUNE"].'",';
}
oci_free_statement($result);
?> 
}
</script>


<div class="container">



  
 


<div id="tabella_raccolta">
            
        <h4>Indicatori raccolta
        <a href="#tabella_spazzamento" class="btn btn-sm btn-info"> Vai allo spazzamento </a></h4>
        AGGIORNAMENTO DATI: <?php echo $time_update_r;?><br> 
        NOTE: (i) per servizi si intende il numero di elementi per cui è pianificato lo svuotamento, (ii) sono considerati come effettuati tutti i servizi effettuati regolarmente o con un recupero entro le 24 h



            <div class="row">

                  
  <!--div id="toolbar"> Per esportare i dati completi rimuovere la paginazione (primo tasto dopo la ricerca)
</div-->
      <table  id="raccolta" class="table-hover table-sm" 
        data-locale="it-IT"
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
        <th data-field="AMBITO" data-sortable="true" data-visible="true" 
        data-filter-control="select" data-filter-data="var:ambito_filtro">Ambito</th>
        <th data-field="COMUNE" data-sortable="true" data-visible="true"  
        data-filter-control="select" data-filter-data="var:comune_filtro">Comune</th>
        <!--th data-field="ANNOMESE" data-sortable="true" data-visible="true" data-formatter="dateFormat" 
        data-filter-control="select" data-filter-data="var:date_filtro" >Mese anno</th-->
        <th data-field="ANNO" data-sortable="true" data-visible="true" 
        data-filter-control="select" data-filter-data="var:anno_filtro" >Anno</th>
        <th data-field="MESE" data-sortable="true" data-visible="true" data-formatter="dateFormat2" 
        data-filter-control="select" data-filter-data="var:mese_filtro" >Mese</th>
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

function dateFormat2(value, row, index) {
   if (value){ 
    return moment(value).format('MMM');
   } else {
    return '-';
   }
};


function realFormat(value, row, index) {
   if (value){ 
    return parseFloat(value).toLocaleString("it-IT");
   } else {
    return '-';
   }
};
 
function realFormat_pc(value, row, index) {
   if (value){ 
    return parseFloat(value).toLocaleString("it-IT")+'%';
   } else {
    return '-';
   }
};


</script>


</div>	<!--tabella-->






<hr>
<div id="tabella_spazzamento">
            


<?php 
// filtro ambito
$query_ambito="SELECT DISTINCT AMBITO 
FROM UNIOPE.INDICATORI_ARERA_SPAZZAMENTO viar 
ORDER BY AMBITO
";

$result = oci_parse($oraconn, $query_ambito);

oci_execute($result);
?>
<script>
  var ambito_filtro = {

<?php
while($r = oci_fetch_assoc($result)) {
    echo '"'.$r["AMBITO"].'":"'.$r["AMBITO"].'",';
}
oci_free_statement($result);
?> 
}
</script>



<?php 
$query_lr="select to_char(AGGIORNAMENTO, 'DD/MM/YYYY HH24:MI') as TIME_UPDATE
from INDICATORI_ARERA_SPAZZAMENTO WHERE ROWNUM = 1";

$result = oci_parse($oraconn, $query_lr);

oci_execute($result);


while($r = oci_fetch_assoc($result)) {
    $time_update_s= $r['TIME_UPDATE'];
}

oci_free_statement($result);




// filtro anno
$query_anno="SELECT DISTINCT substr(ANNOMESE,1,4) as ANNO 
FROM UNIOPE.INDICATORI_ARERA_RACCOLTA viar 
ORDER BY ANNO
";

$result = oci_parse($oraconn, $query_anno);

oci_execute($result);
?>
<script>
  var anno_filtro = {

<?php
while($r = oci_fetch_assoc($result)) {
    echo '"'.$r["ANNO"].'":"'.$r["ANNO"].'",';
}
oci_free_statement($result);
?> 
}
</script>



<?php
// filtro mese
$query_mese="SELECT DISTINCT substr(ANNOMESE,5,2) as MESE 
FROM UNIOPE.INDICATORI_ARERA_RACCOLTA viar 
ORDER BY MESE
";

$result = oci_parse($oraconn, $query_mese);

oci_execute($result);
?>
<script>
  var mese_filtro = {

<?php
while($r = oci_fetch_assoc($result)) {
    echo '"'.$r["MESE"].'":"'.$r["MESE"].'",';
}
oci_free_statement($result);
?> 
}
</script>

<?php 
// filtro ambito
$query_ambito="SELECT DISTINCT AMBITO 
FROM UNIOPE.INDICATORI_ARERA_RACCOLTA viar 
ORDER BY AMBITO
";

$result = oci_parse($oraconn, $query_ambito);

oci_execute($result);
?>
<script>
  var ambito_filtro = {

<?php
while($r = oci_fetch_assoc($result)) {
    echo '"'.$r["AMBITO"].'":"'.$r["AMBITO"].'",';
}
oci_free_statement($result);
?> 
}
</script>

<?php
// filtro comune
$query_comune="SELECT DISTINCT COMUNE 
FROM UNIOPE.INDICATORI_ARERA_SPAZZAMENTO viar 
ORDER BY COMUNE
";

$result = oci_parse($oraconn, $query_comune);

oci_execute($result);
?>
<script>
  var comune_filtro = {

<?php
while($r = oci_fetch_assoc($result)) {
    echo '"'.$r["COMUNE"].'":"'.$r["COMUNE"].'",';
}
oci_free_statement($result);
?> 
}
</script>



        <h4>Indicatori spazzamento
        <a href="#tabella_raccolta" class="btn btn-sm btn-info"> Vai alla raccolta </a>
        </h4>
        AGGIORNAMENTO DATI: <?php echo $time_update_s;?><br>
        NOTE: (i) per servizi si intende il valore in km lineari da spazzare / lavare (art 46.3 TQRIF) (ii) sono considerati come effettuati tutti i servizi effettuati regolarmente o con un recupero entro le 24 h



            <div class="row">

                  
  <!--div id="toolbar"> Per esportare i dati completi rimuovere la paginazione (primo tasto dopo la ricerca)
</div-->
      <table  id="spazzamento" 
        class="table-hover table-sm" 
        data-locale="it-IT"
        data-show-columns="true"
        data-show-search-clear-button="true"   
        data-show-export="true" 
        data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'pdf', 'excel',  'doc'] 
				data-search="false" 
        data-click-to-select="true" 
        data-show-print="false"  
        data-virtual-scroll="false"
        data-show-pagination-switch="false"
				data-pagination="true" 
        data-page-size=75 
        data-page-list=[10,25,50,75,100,200,500]
				data-side-pagination="server" 
        data-show-refresh="true" 
        data-show-toggle="true"
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
        <th data-field="AMBITO" data-sortable="true" data-visible="true" 
        data-filter-control="select" data-filter-data="var:ambito_filtro">Ambito</th>
        <th data-field="COMUNE" data-sortable="true" data-visible="true"  
        data-filter-control="select" data-filter-data="var:comune_filtro">Comune</th>
        <!--th data-field="ANNOMESE" data-sortable="true" data-visible="true" data-formatter="dateFormat" 
        data-filter-control="select" data-filter-data="var:date_filtro" >Mese anno</th-->
        <th data-field="ANNO" data-sortable="true" data-visible="true" 
        data-filter-control="select" data-filter-data="var:anno_filtro" >Anno</th>
        <th data-field="MESE" data-sortable="true" data-visible="true" data-formatter="dateFormat2" 
        data-filter-control="select" data-filter-data="var:mese_filtro" >Mese</th>
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
    $tables.bootstrapTable();
    /*$tables.bootstrapTable('changeLocale', 'it_IT');*/
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
    return parseFloat(value).toLocaleString("it-IT");
   } else {
    return '-';
   }
};
 
function realFormat_pc(value, row, index) {
   if (value){ 
    return parseFloat(value).toLocaleString("it-IT")+'%';
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