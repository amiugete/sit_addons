<?php

require_once './session.php';

$pageTitle = 'Titolo della pagina';
$pageDescription = 'Descrizione della pagina per i meta tag';

require_once './includes/header.php';

$name = dirname(__FILE__);

if ((int)$id_role_SIT === 0) {
    ///echo'Non ho i permessi..';
    redirect('no_permessi.php');
    exit;
}

?>
<!-- FLUID è più esteso su tutto lo schermo -->
<div class="container-fluid">
<!--div class="container"-->
<?php require_once './redirect2wip.php'; ?>

<!-- CONTENUTO DELLA PAGINA -->
<main>
 <h1>Titolo H1</h1>
  <p>Qua c'è il contenuto principale della mia pagina web</p>
</main>
</!--div>
<?php require_once './includes/footer.php'; ?>