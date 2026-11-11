<?php
/*
Da richiamare  subito dopo require_once './conn_ok.php'; e prima che venga inviato qualsiasi contenuto html al browser
(piuttosto spostare require_once './conn_ok.php'; subito dopo require_once './session.php';)

Serve a reindirizzare alla pagina wip.php se l'ambiente non è di test, 
in modo da evitare che utenti esterni possano accedere a pagine in sviluppo.
*/
    if(($_ENV['APP_ENV'] ?? '') === 'test') {
    $checkTest=1;
    } else {
        $checkTest=0; 
        //header("Location: ./wip.php"); ?>
            <div class="row justify-content-center align-items-center" style="width: 70%;">
                <div class="col-auto">
                    <h1 style="text-align:center;">PAGINA IN COSTRUZIONE</h1>
                    <img class="img-fluid" src="./img/wip.png">
                </div>
            </div>
        </div> <!-- chiusura del div container nella pagina principale --> 
    <?php
       require_once './includes/footer.php';
       exit();
    }
?>