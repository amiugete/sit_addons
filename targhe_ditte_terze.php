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





<div id="tabella">
            
        <h4>Elenco targhe</h4>




  <div class="row">

                  
  <div id="toolbar"> Per esportare i dati completi rimuovere la paginazione (primo tasto dopo la ricerca)
</div>
				<table  id="targhe" class="table-hover" 
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
        data-auto-refresh="true"
        data-auto-refresh-interval = 60
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false"
        data-query-params="queryParams"
        data-url="./tables/targhe_ditte_terze.php">
        

        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="ut" data-sortable="true" data-visible="true" data-filter-control="select">Gruppo di<br>Coordinamento</th>
        <th data-field="id_uo" data-sortable="true" data-visible="false"  data-filter-control="select">Id UO</th>
        <th data-field="targa" data-sortable="true" data-visible="true" data-filter-control="input">Targa</th> 
        <th data-field="quintali" data-sortable="true" data-visible="true" data-filter-control="select">Quintali</th>
        <th data-field="in_uso" data-sortable="true" data-visible="true" data-filter-control="select">In uso</th>
        <th data-field="data_inserimento" data-sortable="true" data-visible="true" data-filter-control="input">Data Inserimento</th>

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



  var $table = $('#targhe');
  
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

<hr>

<div class="row">

<form  autocomplete="off"  class="row g-3" id="inserimento_targa" >



<div class="form-group col-lg-3">
<label for="ut0" class="form-label">Gruppo di coordinamento</label>
<select class="selectpicker show-tick form-control" 
data-live-search="true" placeholder="Seleziona un Gruppo di Coordinamento" name="ut0" id="ut0" required="">
<?php 

  $query0="  select cmu.id_uo, descrizione
  from topo.ut u
join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo_sit = u.id_ut 
where u.id_zona = 7 
and u.data_disattivazione is null 
and u.ekovision = 't'
and mail is not null
order by 2";

  $result0 = pg_prepare($conn, "my_query0", $query0);
  $result0 = pg_execute($conn, "my_query0", array());
  
  while($r0 = pg_fetch_assoc($result0)) { 
?>    
        <option name="ut0" id="ut0" value="<?php echo $r0['id_uo'];?>" ><?php echo $r0['descrizione']?></option>
<?php }
pg_free_result($result0); 

?> 
</select>
</div> 


<div class="form-group col-md-3"><font color="red">*</font>
    <label for="targa" class="form-label">Targa</label>
    <input type="text"  placeholder="Targa"
    onkeypress="return event.charCode != 32" class="form-control" name="targa" id="targa" value="" required>
</div>



<div class="form-group col-md-3">
    <label for="quintali" class="form-label">Quintali</label>
    <input type="number"  min="0" placeholder="XX" class="form-control" name="quintali" id="quintali" value="">
</div>

<div class="form-group col-md-3"> 
  <button type="submit" class="btn btn-info">
      <i class="fa-solid fa-arrow-up-from-bracket"></i> Salva
  </button>
</div>

</form>
<p><div id="results_desc"></div></p>
<script> 
  $(document).ready(function () {                 
      $('#inserimento_targa').submit(function (event) { 
          console.log('Bottone form targa cliccato e finito qua');
          event.preventDefault();                  
          var formData = $(this).serialize();
          console.log(formData);
          $.ajax({ 
              url: 'backoffice/inserimento_targhe_ditte_terze.php', 
              method: 'POST', 
              data: formData, 
              //processData: true, 
              //contentType: false, 
              success: function (response) {                       
                  //alert('Your form has been sent successfully.'); 
                  console.log(response);
                  $("#results_desc").html(response).fadeIn("slow");
              }, 
              error: function (jqXHR, textStatus, errorThrown) {                        
                  alert('Your form was not sent successfully.'); 
                  console.error(errorThrown); 
              } 
          }); 
      }); 
  }); 
</script>

</div>


<?php
require_once('req_bottom.php');
require_once('./footer.php');
?>


  </div>

</body>

</html>