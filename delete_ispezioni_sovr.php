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

<?php 
require_once('./navbar_up.php');
$name=dirname(__FILE__);


#per ora solo superedit
if ($check_superedit==0) { 
  require('assenza_permessi.php');
  exit;
}


?>






<div class="container">

Da questa pagina è possibile forzare la rimozione di un'ispezione 



<hr>
<!--form id="update_elem" method="post" autocomplete="off" action="" onsubmit="return clickButton();"-->
<form id="dlete_isp">
<div class="row g-3 align-items-center">
  <div class="form-group col-md-6">
    <label for="id_isp"> Id ispezione </label> <font color="red">*</font>
    <input type="number" name="id_isp" id="id_isp" class="form-control" required="">
  </div>

  <div class="form-group col-md-6">
    <label for="motivazione"> Motivazione </label> <font color="red">*</font>
    <input type="text" name="motivazione" id="motivazione" class="form-control" required="">
  </div>


</div>
<br>
<div class="row g-3 align-items-center">
  <?php if ($check_edit==1){?>
    <div class="form-group col-md-6">
      <button type="submit" class="btn btn-info">
      <i class="fa-solid fa-arrows-rotate"></i> Procedi
      </button>
    </div>
  <?php }?>
</div>
</form>
<hr>
<p><div id="results"></div></p>
<script> 
        $(document).ready(function () {                 
            $('#dlete_isp').submit(function (event) { 
                console.log('Bottone cliccato e finito qua');
                event.preventDefault();     
                var id_isp=document.getElementById('id_isp').value;
            
         
                console.log(id_isp);
                //var form = document.getElementById('update_elem'); 
                //var formData = new FormData(form); 
                var formData = $(this).serialize();
                console.log(formData);
                if (confirm("Sei sicuro di voler eliminare l'ispezione "+ id_isp+"?") == true) {
             
                console.log('Hai confermato');
                $.ajax({ 
                      url: 'backoffice/delete_ispezioni_sovr.php', 
                      method: 'POST', 
                      data: formData, 
                      //processData: true, 
                      //contentType: false, 
                      success: function (response) {                       
                          //alert('Your form has been sent successfully.'); 
                          console.log(response);
                          $("#results").html(response).fadeIn("slow");
                      }, 
                      error: function (jqXHR, textStatus, errorThrown) {                        
                          alert('Your form was not sent successfully.'); 
                          console.error(errorThrown); 
                      } 
                  });
                } else {
                  $('#results').html("<h2>Hai bloccato la rimozione</h2>").fadeIn("slow");
              } 
                  
              
            }); 
        }); 
    </script>


</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>





</body>

</html>