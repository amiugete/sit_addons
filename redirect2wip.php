<?php
/*
Serve a reindirizzare alla pagina wip.php se l'ambiente non è di test, 
in modo da evitare che in produzione gli utenti possano accedere a pagine in sviluppo.
*/
    
    if(($_ENV['APP_ENV'] ?? '') === 'test') {
    $checkTest=1;
    } else {?>
        <div class="comtainer-fluid">
        <?php
        $checkTest=0; 
        //header("Location: ./wip.php"); ?>
            <?php require './modal_wip.php' ?>
        </div> <!-- chiusura del div container nella pagina principale --> 
    <?php
       require_once './includes/footer.php';
       exit();
    }
?>