<?php
/*
Serve a reindirizzare alla pagina wip.php se l'ambiente non è di test, 
in modo da evitare che in produzione gli utenti possano accedere a pagine in sviluppo.
*/
    ?>
    <div class="comtainer-fluid">
    <?php
    if(($_ENV['APP_ENV'] ?? '') === 'test11111111') {
    $checkTest=1;
    } else {
        $checkTest=0; 
        //header("Location: ./wip.php"); ?>
            <?php require './modal_wip.php' ?>
        </div> <!-- chiusura del div container nella pagina principale --> 
    <?php
       require_once './includes/footer.php';
       exit();
    }
?>