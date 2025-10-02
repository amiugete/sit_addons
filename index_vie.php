<?php

//$id=pg_escape_string($_GET['id']);
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

    <title>Ricerca utenze</title>
<?php 
require_once('./req.php');

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
if (trim($check_utenze) != 't') { 
  require('./assenza_permessi.php');
  exit;
}
//************************************************************************************ */

?>
<div class="banner"> <div id="banner-image"></div> </div>

      <div class="container">
      

            <h2> Seleziona vie da cui recuperare le utenze <i class="fas fa-users"></i> </h2>
            <hr>
            <form name="openfile" id="getutenze" method="post" autocomplete="off" action="./backoffice/utenze_vie_output.php" >
            <div class="row">             
            <div class="col-md-4"> 
            <div class="form-group">
                <label for="mail">Prefisso file utenze (zona)</label><font color="red">*</font>
                <input type="text" class="form-control" id="zona" name="zona"  aria-describedby="emailHelp" placeholder="Inserisci il prefisso " required>
            <small class="form-text text-muted">Prefisso da usare per i file excel che avranno un formato di questo tipo YYYYMMGG_<b>prefisso</b>_<i>nomefile</i>.xlsx</small>
            </div>
            </div>

            <!--div class="col-md-4" style="display: flex; align-items: center; justify-content: center;"--> 
            <div class="col-md-4"> 
            <div class="form-group" >
                <label for="via">Utenze:</label> <font color="red">*</font>
                <!--select name="via-list" id="via-list" class="selectpicker show-tick form-control" 
                data-live-search="true" onChange="getCivico(this.value);" required=""-->
                <select name="ute-list" id="ute-list" class="selectpicker show-tick form-control" 
                data-live-search="true" required="">

                <option value="">Seleziona le utenze</option>
                <option name="ute" value="ute" >Utenze domestiche E non domestiche</option>
                <option name="uted" value="uted" >Solo utenze domestiche</option>
                <option name="utend" value="utend" >Solo utenze NON domestiche</option>

                </select>   
            </div>
            </div>

            <div class="col-md-4"> 
            <div class="form-group">
            <div class="form-check">
                <!--input type='hidden' value='0' name='consegne' id="consegne"-->
                <input class="form-check-input" type="checkbox" value="cons" name="consegne" id="consegne" disabled>
                <label class="form-check-label" for="flexCheckDefault">
                    Per portale consegne
                </label>
                </div>
            <small class="form-text text-muted">Cliccare sul check per l'inserimento sul portale consegne. 
                Verrà creato apposito file da importare sul portale consegne e inviato per conoscenza alla referente del progetto (Laura Calvello)
            </small>
            </div>
            </div>
            </div>

            <div class="row" style="padding-top: 2%;">

			<div class="col-md-6"> 
                <div class="form-group  ">
                <label for="via">Via:</label> <font color="red">*</font>
                <!--select name="via-list" id="via-list" class="selectpicker show-tick form-control" 
                data-live-search="true" onChange="getCivico(this.value);" required=""-->
                <select name="via-list" id="via-list" class="selectpicker show-tick form-control" 
                data-live-search="true" onchange="writelist();" required="">

                <option value="">Seleziona la via</option>
                <?php            
                $query2="SELECT id_via, nome From topo.vie where id_comune=1;";
                $result2 = pg_query($conn, $query2);
                //echo $query1;    
                while($r2 = pg_fetch_assoc($result2)) { 
                    $valore=  $r2['id_via']. ";".$r2['nome'];            
                ?>     
                    <option name="codvia" value="<?php echo $r2['id_via'];?>" ><?php echo $r2['nome'];?></option>
                <?php } ?>

                </select>            
            </div>
            </div>
            
            <div class="col-md-6"> 
            <div class="form-group">
            <label for="lista_vie">Elenco vie selezionate:</label>
            <textarea readonly id="lista_vie" name="lista_vie" rows="4"  class="form-control" >cod_via, nome_via</textarea>
            <div class="form-group" style="display: flex; margin-top:2%;">
            <small class="text-muted" ><!--Anteprima del file con l'elenco vie usato dall'applicativo per generare i file con le utenze. 
                In caso di errori con le vie clicca sul bottone per rimuovere l'ultima linea.<br-->
                <a href="#" class="btn btn-warning btn-sm" id="removeline" ><i class="far fa-trash-alt"></i>Elimina ultima via</a>
                <!--br> o riparti dall'inizio<br-->
                <a href="#" class="btn btn-danger btn-sm" id="aggiorna" ><i class="fas fa-redo"></i>Elimina intero elenco</a></small>
                <button type="submit" name="submit" id=submit value="invia_utenze" class="btn btn-success btn-sm" style="margin-left: auto;"><i class="fa-solid fa-file-arrow-down"></i>Sacrica utenze</button>
            </div>
            </div>
            </div>
            <script>
                $(document).ready(function() {
                    $('#removeline').click(function() {
                        // pulisco tutto
                        //$('#lista_vie').val('cod_via, nome_via');
                        // solo ultima riga
                        var txt = $('#lista_vie');
                        var text = txt.val().trim("\n");
                        var valuelist = text.split("\n");
                        //var string_to_replace = "";
                        //valuelist[valuelist.length-1] = string_to_replace;
                        console.log(valuelist);
                        console.log(valuelist.length);
                        var last = valuelist[valuelist.length - 1];
                        console.log(last);
                        pippo=text.replace(last, "").replace(/\n$/, "")
                        console.log(pippo)
                        txt.val(pippo)
                        //pippo=valuelist.pop()
                        //console.log(pippo)
                        //last.removeChild(last);
                        //console.log(valuelist);
                        //txt.val(pippo.join("\n"));
                    })
                });
                $(document).ready(function() {
                    $('#aggiorna').click(function() {
                        // pulisco tutto
                        $('#lista_vie').val('cod_via, nome_via');
                    })
                });

            </script>

            
            </div>
            <!--div class="row">

            <div class="form-group  ">
            <input type="submit" name="submit" id=submit class="btn btn-info" value="Recupera utenze">
            </div>
            </div-->
            </form>


</div>


</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>

<script type="text/javascript" >

$('#ute-list').change(function(e){
    var value = $(this).val();
   if(value == 'ute') {
     $('#consegne').removeAttr('disabled');
   }

})
/*
    document.getElementById("getutenze").addEventListener("submit", function(event) {
    let check1 = document.getElementById("uted").checked;
    let check2 = document.getElementById("utend").checked;

    if (!check1 && !check2) {
        alert("Devi selezionare almeno una opzione!");
        event.preventDefault(); // blocca l'invio del form
    } else {
        console.log("Form va inviato");
        // Non serve rimuovere preventDefault, perché non è stato chiamato
    }
});*/

// con questa parte scritta in JQuery si evita che 
// l'uso del tasto enter abbia effetto sul submit del form


$("input#zona").on({
  keydown: function(e) {
    if (e.which === 32)
      return false;
  },
  change: function() {
    this.value = this.value.replace(/\s/g, "");
  }
});


$(document).on("keydown", ":input:not(textarea)", function(event) {
    if (event.key == "Enter") {
        event.preventDefault();
    }
});




</script>









<script>
	function writelist(){
		var codvia_value=$("#via-list option:selected").val(); //get the value of the current selected option.
        console.log(codvia_value);
        var via_text=$("#via-list option:selected").text();
		console.log(via_text);


        document.getElementById("lista_vie").value +='\n'+ codvia_value+ ', '+via_text;

        //document.querySelector('#textlista_vie2').innerHTML = document.codvia_value

	} 

</script>
</body>

</html>