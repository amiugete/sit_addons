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




<script>
  function utScelta(val) {
    document.getElementById('open_ut').submit();
  }


</script>

<div class="rfix">

<form class="row" name="open_ut" method="post" id="open_ut" autocomplete="off" action="percorsi.php" >

<?php //echo $username;?>

<div class="form-group col-lg-4">
  <select class="selectpicker show-tick form-control" 
  data-live-search="true" name="ut0" id="ut0" onchange="utScelta(this.value);" required="">
  
  <?php 
  if ($_POST['ut0']) {
    $query0='select id_ut, descrizione
    from topo.ut where id_ut = $1';

    $result0 = pg_prepare($conn, "my_query0", $query0);
    $result0 = pg_execute($conn, "my_query0", array($_POST['ut0']));
    
    while($r0 = pg_fetch_assoc($result0)) { 
  ?>    
          <option name="ut0" value="<?php echo $_POST['ut0'];?>" ><?php echo $r0['descrizione']?></option>
  <?php }
  pg_free_result($result0); 
  } else{
  ?>
    <option name="ut0" value="NO">Seleziona una UT</option>
  
  
  <?php            
  }

  
  require_once('query_ut.php');

  //echo "<br>". $query1;


  $result1 = pg_prepare($conn, "my_query1", $query_ut);
  $result1 = pg_execute($conn, "my_query1", array($_SESSION['username']));

  while($r1 = pg_fetch_assoc($result1)) { 
?>    
        <option name="ut0" value="<?php echo $r1['id_ut'];?>" ><?php echo $r1['descrizione']?></option>
<?php 
  }
  pg_free_result($result1); 
?>

  </select>  
  <!--small>L'elenco delle piazzole..  </small-->        
</div>
<div class="form-group col-lg-4">
<a class="btn btn-primary" href="./percorsi.php">Tutte le mie UT</a>
</div>
  </form>

  </div>
  
  <hr>


<div id="tabella">
            
        <h4>Elenco percorsi</h4>




            <div class="row">

                  
  <div id="toolbar"> Per esportare i dati completi rimuovere la paginazione (primo tasto dopo la ricerca)
</div>
				<table  id="percorsi" class="table-hover" 
        idfield="id" 
        data-toolbar="#toolbar" 
        data-group-by="false"
        data-group-by-field='["cod_percorso", "descrizione", "famiglia", "tipo"]'
        data-show-search-clear-button="true"   
        data-show-export="true" 
        data-export-type="['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf']"
				data-search="true" data-click-to-select="true" data-show-print="false"  
				data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
        data-show-pagination-switch="true"
				data-side-pagination="false" 
        data-search-on-enter-key="true"  
        data-remember-order="true"
        data-search-highlight = "true" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false"
        data-query-params="queryParams"
        data-url="./tables/percorsi_raggruppati.php?ut=<?php echo $_POST['ut0'];?>">
        

        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="famiglia" data-sortable="true" data-visible="true" data-filter-control="select">Famiglia</th>
        <th data-field="tipo" data-sortable="true" data-visible="true"  data-filter-control="select">Tipo</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-filter-control="select">UT/Rimessa</th> 
        <th data-field="cod_percorso" data-sortable="true" data-visible="true" data-filter-control="input">Codice</th>
        <th data-field="descrizione" data-sortable="true" data-visible="true" data-filter-control="input">Descrizione</th>
        <th data-field="freq" data-sortable="true" data-visible="true" data-filter-control="input">Frequenza</th>
        <th data-field="turno" data-sortable="true" data-visible="true" data-filter-control="select">Turno</th>
        <th data-field="versione" data-sortable="true" data-visible="true" data-filter-control="select">Versione</th>
        <th data-field="flg_disattivo" data-sortable="true" data-visible="true" data-formatter="nameFormatterAtt" 
        data-filter-strict-search="true" data-search-formatter="false" data-filter-data="var:opzioni" data-filter-control="select"></th>
        <th data-field="cp_report" data-sortable="false" data-formatter="nameFormatterReport" data-visible="true" >Report</th>
        <?php if ($check_superedit == 1) { ?>
          <th data-field="cp_edit" data-sortable="true"  data-visible="true"  data-events="dpEvents" data-formatter="nameFormatterEdit_ok">Edit</th>
        <?php } ?>
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



  var $table = $('#percorsi');
  
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




 


    var opzioni = ['Attivo', 'In attivazione', 'In disattivazione', 'Disattivo'] ;

    function nameFormatterAtt(value) {
      if (value =='Attivo'){
        return '<span style="font-size: 1em; color: green;"> <i title="'+value+'" class="fa-solid fa-play"></i></span>';
      } else if (value =='In attivazione') {
        return '<span style="font-size: 1em; color: blue;"> <i title="'+value+'" class="fa-solid fa-pause"></i></span>';
      } else if (value =='In disattivazione') {
        return '<span style="font-size: 1em; color: red;"> <i title="'+value+'" class="fa-solid fa-pause"></i></span>';
      } else if (value =='Disattivo') {
        return '<span style="font-size: 1em; color: Tomato;"> <i title="'+value+'" class="fa-solid fa-stop"></i></span>';
      }
    };

    function nameFormatterEdit(value, row) {
        
        return '<a class="btn btn-warning" href="./dettagli_percorso.php?cp='+row.cod_percorso+'&v='+row.versione+'"><i class="fa-solid fa-pencil"></i></a>';
     
    };



    function nameFormatterEdit_ok(value, row, index) {
    return [
        '<button type="button" class="info btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#viewMemberModal">',
        '<i class="fa-solid fa-pencil"></i>',
        '</button>'
    ].join('');
};


window.dpEvents = {
    'click .info': function (e, value, row, index) {
      $('#ut0').attr('disabled',true);
        console.log('Sono qua');
        console.log(row.cod_percorso);
        var cp = row.cod_percorso;
        console.log('cp'+cp);
        var v = row.versione;
        console.log('v'+v);
        $.ajax({   
            type: "get",
            url: "dettagli_percorso_modal.php",
            data: {'cp': cp,
                    'v': v
                  },
            dataType: "text",                  
            success: function(response){                    
                $("#body_dettaglio").html(response);
                
            }, 
              
            
        });
      
    }
};
    





    function nameFormatterReport(value, row) {
      if (row.flg_disattivo == 'Attivo' && !row.tipo.includes('SOLO TESTATA')) {
        return [
          '<div class="btn-group btn-group-sm" role="group" aria-label="...">',
          '<a class="btn btn-success btn-sm" href="./download_report_percorso.php?cod='+row.cod_percorso+'&vers=s"><i title="Versione per operatore" class="fa-solid fa-clipboard-list"></i></a>',
          '<a class="btn btn-primary btn-sm" href="./download_report_percorso.php?cod='+row.cod_percorso+'&vers=c"><i title="Versione completa" class="fa-solid fa-list-check"></i></a>',
          '</div>'  
        ].join('');
      }  
    };


</script>


</div>	<!--tabella-->










</div>

<?php
require_once('req_bottom.php');
require_once('./footer.php');
?>


  </div>

</body>

</html>