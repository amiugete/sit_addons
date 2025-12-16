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
if ($check_superedit==1) {
  redirect('no_permessi.php');
  //exit;
}


?>

<script type="text/javascript">
        
        function clickButton() {
            console.log("Bottone  form cliccato");

            var id_elem=document.getElementById('id_elem').value;
            //var materiale= document.getElementById('materiale').text();
         
            console.log(id_elem);
            
            //organize the data properly
    

            //start the ajax
            $.ajax({
              url: "backoffice/update_elemento.php",  
              type: "GET",
                //pass data like this 
              data: {id_elem:id_elem,},    
              cache: false,
              success: function(data) {  
              if (data=="1")
                $('#message').html("<h2>Elemento aggiornato!</h2>") 
              } 

              });             



            

            //window.location.href = "ordini.php";
            return false;

        }
      </script>





<div class="container">

Da questa pagina è possibile forzare l'update di elementi SIT per risolvere l'errore del connector Ekovision

<hr>
<!--form id="update_elem" method="post" autocomplete="off" action="" onsubmit="return clickButton();"-->
<form id="update_elem">
<div class="row g-3 align-items-center">
  <div class="form-group col-md-6">
    <label for="id_elem"> Id elemento </label> <font color="red">*</font>
    <input type="text" name="id_elem" id="id_elem" class="form-control" required="">
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
            $('#update_elem').submit(function (event) { 
                console.log('Bottone cliccato e finito qua');
                event.preventDefault();                 
                //var form = document.getElementById('update_elem'); 
                //var formData = new FormData(form); 
                var formData = $(this).serialize();
                console.log(formData);
                $.ajax({ 
                    url: 'backoffice/update_elemento.php', 
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