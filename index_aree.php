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
if (trim($check_utenze) != 't') { 
  require('./assenza_permessi.php');
  exit;
}
//************************************************************************************ */

?>

<div class="banner"> <div id="banner-image"></div> </div>
      <div class="container-fluid">
<!--script type="text/javascript">


$(document).ready(function(){
  $('form#open_file').submit(function() {
    console.log('Sono qua');
    $('#output_message').show(); 
    

    event.preventDefault();                  

    var formData = $(this).serialize();
    console.log(formData);

    $.ajax({ 
        url: './backoffice/utenze_aree_output.php', 
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

     return true;
    });
});

$(window).bind ("beforeunload",  function (zEvent) {
  console.log('Nascondo gif 2');
  //$('#output_message').hide();
} );

</script-->
      

            <h3> Utenze Piazzole <i class="fas fa-users"></i> </h3>
            <hr>
            <h5> Eseguire il login (credenziali del proprio PC) e disegnare l'area sulla mappa prima di inviare la richiesta di estrazione utenze</h5>
            <div class="row row-small">
              <iframe src="https://amiugis.amiu.genova.it/mappe/lizmap/www/index.php/view/map?repository=repository1&project=utenze_piazzole_iframe" 
              title="Disegna area per estrazione utenze\" style="height:500px;" allowfullscreen></iframe>
            </div>
              <hr>  
            <!--form name="openfile" method="post" autocomplete="off" action="<?php echo $_SERVER['PHP_SELF'] ?>" -->
            <div class = "row row-small">
            <form class="tag-small" name="openfile" id="open_file" method="post" autocomplete="off" action="" >

            <div class="row">
            
            

            <div class="col-md-4"> 
            <div class="form-group">
            <label for="eco">Area:</label> <font color="red">*</font>
            <select class="selectpicker show-tick form-control" name="eco" id="eco" data-live-search="true">
            <option value="" > Scegli un'area </option>
            <?php            
            //$query2="SELECT * From etl.aree_4326 where data_disegno::date>=(NOW() - INTERVAL '30' DAY) order by data_disegno desc;";
            $query2= "SELECT id,
                      CASE 
                        WHEN ecopunto = true THEN 'ecopunto_' || nome
                        ELSE nome
                      END AS nome,
                      to_char(data_disegno, 'dd/mm/yyyy - HH24:MI:SS') as data_disegno2
                      from etl.aree_4326 where (data_disegno::date>=(NOW() - INTERVAL '30' DAY) and ecopunto is not true) or 
                      ecopunto = true order by data_disegno desc;";
	          $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
            ?>    
                    <option name="eco" value="<?php echo $r2['id'];?>" ><?php echo $r2['nome']. "(".$r2['data_disegno2']. ")";?></option>
             <?php } ?>
             </select>
                
             </div>
            </div>

            
            <div class="col-md-4"> 
            <div class="form-group">
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


            <!--div class="col-md-2 d-flex justify-content-center align-items-center">

            <div class="form-group  ">
              <label for="ecop">Ecopunto?</label>
            <input class="form-check-input" type="checkbox" value="ecop" name="ecop" id="ecop">
            </div>
            </div-->

            
            <div class="col-md-4 d-flex justify-content-center align-items-end">

            <div class="form-group  ">
            <button type="submit" name="submit" id=submit value="invia_utenze" class="btn btn-success"><i class="fa-solid fa-file-arrow-down"></i>Scarica utenze</button>
            </div>
            </div>
            </div>
            </form>

        <div class="row justify-content-center" style="margin-top:2%; display:none;" id="output_message">
          <img src="./img/loading.gif" alt="loader1" style="height:30px; width:auto;" class="img-fluid" id="loaderImg">
          L'operazione potrebbe impiegare un po' di tempo. Attendere, il file sarà presto disponibile per il download. 
          <img src="./img/loading.gif" alt="loader1" style="height:30px; width:auto;" class="img-fluid" id="loaderImg">
        </div>
            </div>
      </div>


</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>


<!--script>
/*funzione senza classi selectpicker ecc sul select*/
function refreshSelect() {
  let select = document.getElementById("eco");
  let selectedValue = select.value; // salvo l'opzione selezionata

  fetch("./index_aree_refresh_select.php")
    .then(response => response.text())
    .then(data => {
      select.innerHTML = data;

      // ripristino la selezione se ancora valida
      if (selectedValue) {
        let optionToSelect = select.querySelector("option[value='" + selectedValue + "']");
        if (optionToSelect) {
          optionToSelect.selected = true;
        }
      }
    });
}

// refresh ogni 5 secondi
setInterval(refreshSelect, 5000);
</script-->

<!--script>
/*funzione con classi selectpicker ecc sul select che chiude il menù ogni 5 sec*/
function refreshSelect() {
  const $select = $('#eco');
  const select = document.getElementById('eco');
  const selectedValue = select.value;

  fetch('./index_aree_refresh_select.php')
    .then(r => r.text())
    .then(optionsHtml => {
      // Distruggo completamente la UI generata da bootstrap-select
      $select.selectpicker('destroy');

      // Sovrascrivo le option
      select.innerHTML = optionsHtml;

      // Ripristino selezione se ancora valida
      if (selectedValue) {
        const optionToSelect = select.querySelector("option[value='" + selectedValue + "']");
        if (optionToSelect) {
          optionToSelect.selected = true;
        }
      }

      // Reinizializzo da zero (una UI nuova, senza duplicati)
      $select.selectpicker();
    })
    .catch(err => console.error(err));
}

// refresh ogni 5 secondi
setInterval(refreshSelect, 5000);
</script-->
<script>
document.getElementById('open_file').addEventListener('submit', function (e) {
  e.preventDefault();
  const msg = document.getElementById('output_message');
  msg.style.display = 'flex';

  fetch('./backoffice/utenze_aree_output.php', {
    method: 'POST',
    body: new FormData(this)
  })
  .then(res => {
  if (!res.ok) throw new Error("Errore server: " + res.status);
  
  // leggo l'header Content-Disposition
  let filename = "download.zip";
  const disposition = res.headers.get("Content-Disposition");
  if (disposition && disposition.indexOf("filename=") !== -1) {
    filename = disposition.split("filename=")[1].replace(/['"]/g, '');
  }
  
  return res.blob().then(blob => ({ blob, filename }));
  })
  .then(({ blob, filename }) => {
    msg.style.display = 'none';
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename; // usa quello del PHP!
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(url);
  })
  .catch(err => {
    msg.style.display = 'none';
    alert("Errore: " + err.message);
    console.error(err);
  });
});
</script>

<script>
function refreshSelect() {
  const $select = $('#eco');
  const select = document.getElementById('eco');
  if (!select) return;

  // Se il menu è aperto, non aggiorniamo
  /*if ($select.parent().hasClass('open')) {
    console.log("Menu aperto, skip refresh");
    return;
  }*/
  if ($select.nextAll('.dropdown-menu').hasClass('show')) {
    console.log("Menu aperto, skip refresh");
    return;
}

  const selectedValue = select.value;

  fetch('./index_aree_refresh_select.php')
    .then(r => r.text())
    .then(optionsHtml => {
      $select.selectpicker('destroy');   // distruggo UI corrente
      select.innerHTML = optionsHtml;   // sostituisco opzioni

      if (selectedValue) {
        const optionToSelect = select.querySelector("option[value='" + selectedValue + "']");
        if (optionToSelect) {
          optionToSelect.selected = true;
        }
      }

      $select.selectpicker();  // ricreo UI
    })
    .catch(err => console.error(err));
}

// refresh ogni 5 secondi
setInterval(refreshSelect, 5000);
</script>





</body>

</html>