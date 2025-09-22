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

//************************************************************************************ */
// Controllo permessi
if (trim($check_coge) != 't') { 
  require('assenza_permessi.php');
  exit;
}
//************************************************************************************ */

?>


<div class="container">


<script type="text/javascript">


$(document).ready(function(){
  $('form#open_ut').submit(function() {
    console.log('Sono qua');
    $('#output_message').show(); 
    

    event.preventDefault();                  

    var formData = $(this).serialize();
    console.log(formData);

    $.ajax({ 
        url: './backoffice/download_driver_ekovision.php', 
        method: 'POST', 
        data: formData, 
        //processData: true, 
        //contentType: false, 
        xhrFields: {
        responseType: 'blob' // to avoid binary data being mangled on charset conversion
        },
        success: function(blob, status, xhr) {
            console.log('Finito di elaborare il file');
            //console.log(response);
          
            $('#output_message').hide(); 
            // check for a filename
            var filename = "";
            var disposition = xhr.getResponseHeader('Content-Disposition');
            if (disposition && disposition.indexOf('attachment') !== -1) {
                var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                var matches = filenameRegex.exec(disposition);
                if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
            }

            if (typeof window.navigator.msSaveBlob !== 'undefined') {
                // IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for which they were created. These URLs will no longer resolve as the data backing the URL has been freed."
                window.navigator.msSaveBlob(blob, filename);
            } else {
                var URL = window.URL || window.webkitURL;
                var downloadUrl = URL.createObjectURL(blob);

                if (filename) {
                    // use HTML5 a[download] attribute to specify filename
                    var a = document.createElement("a");
                    // safari doesn't support this yet
                    if (typeof a.download === 'undefined') {
                        window.location.href = downloadUrl;
                    } else {
                        a.href = downloadUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                    }
                } else {
                    window.location.href = downloadUrl;
                }

                setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 100); // cleanup
            }
            console.log('Sono arrivato qua');
        },
        error: function (jqXHR, textStatus, errorThrown) {                        
            alert('Your form was not sent successfully.'); 
            console.error(errorThrown); 
        } 
    }); 

    


    /*var seconds = 0;
    var el = document.getElementById('seconds-counter');

    function incrementSeconds() {
        seconds += 1;
        el.innerText = "Sto elaborando il file da " + seconds + " secondi. Attendi con pazienza";
    }

    var cancel = setInterval(incrementSeconds, 1000);*/




    return true;
    });
});



$(window).bind ("beforeunload",  function (zEvent) {
  console.log('Nascondo gif 2');
  //$('#output_message').hide();
} );


</script>

<h4>Controllo Gestione - Esportazione driver ekovision</h4>
<hr>

<!--form class="row" name="open_ut" method="post" id="open_ut" autocomplete="off" action="download_driver_ekovision.php" -->
<form class="row" name="open_ut" method="POST" id="open_ut" autocomplete="off" action="">
<input type="hidden" id="email" name="email" value="<?php echo $mail_user;?>">

<?php //echo $username;?>

<div class="form-group col-lg-4">
<label for="tipo_report" >Seleziona la tipologia di report</label><font color="red">*</font>
<select name="tipo_report" class="form-select form-select-sm" aria-label="Small select example">
  <option value="2">Raggruppate per Servizio Ekovision</option>
  <option value="3">Raggruppate solo per codice percorso Ekovision</option>
  <option value="1">Raggruppate per Servizio COGE</option>
</select>
</div>


<?php 
$dt= new DateTime();
$today = new DateTime();
$last_month = $dt->modify("-1 year");

?>


<div class="form-group col-lg-4">
<label for="data_inizio" >Da  (GG/MM/AAAA) - A (GG/MM/AAAA)</label><font color="red">*</font>
    <input type="text" class="form-control" name="daterange" value="<?php echo $last_month->format('d/m/Y');?> - <?php echo $today->format('d/m/Y');?>"/>
    <small>Massimo 1 anno e 6 mesi </small>
</div>


<script>
$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'left',
    maxSpan: {
        days: 548
    },
    showISOWeekNumbers: true,
    minDate: "<?php echo $partenza_ekovision;?>"

    
  }, function(start, end, label) {
    var data_inizio = start.format('YYYY-MM-DD') ;
    var data_fine= end.format('YYYY-MM-DD');
    console.log("A new date selection was made: " + data_inizio + ' to ' + data_fine);
    
  

    // Faccio refres della data-url
    //$table.bootstrapTable('refresh', {
    //  url: "./tables/report_consuntivazione_ekovision.php?ut=<?php echo $_POST['ut']?>&data_inizio="+data_inizio+"&data_fine="+data_fine+""
    //});
  });
});
</script>



<div class="form-group col-lg-4">
<button type="submit" id="sbtn" class="btn btn-primary"><i class="fa-solid fa-file-excel"></i> Esporta excel</button>
</div>


</form>





<div class="row align-items-center" style="display:none;" id="output_message">
  <hr>
  <img src="./img/loading.gif" alt="loader1" style="height:30px; width:auto;" class="img-fluid" id="loaderImg">
  L'operazione potrebbe impiegare un po' di tempo. Attendere, il file sarà presto disponibile per il download. 
  <img src="./img/loading.gif" alt="loader1" style="height:30px; width:auto;" class="img-fluid" id="loaderImg">

  <i class="fa-solid fa-envelopes-bulk"></i> Inoltre verrà inviato via mail all'indirizzo <?php echo $mail_user;?>
  <!--div id='seconds-counter'> </div-->
</div>


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