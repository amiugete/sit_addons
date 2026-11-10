<?php
$pageTitle = $pageTitle ?? 'SIT - AMIU';
$pageDescription = $pageDescription ?? 'Sistema Informativo Territoriale AMIU';
?>

<!DOCTYPE html>
<html lang="it">

<head>

    <meta charset="utf-8">
    <!-- Questo è il meta tag più importante per il responsive design.
        width=device-width
    Dice al browser di utilizzare come larghezza della pagina la larghezza effettiva del dispositivo.
        initial-scale=1
    Imposta lo zoom iniziale al 100%.--> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
      content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">


     <!--
    Controlla quante informazioni sulla pagina di provenienza vengono trasmesse quando il browser effettua richieste verso altri siti.
    Con questa impostazione:
    - Le richieste verso il tuo stesso dominio possono contenere l'URL completo.
    - Le richieste verso altri domini ricevono soltanto l'origine.
    - Non vengono trasmesse informazioni quando si passa da HTTPS a HTTP.
    È una buona impostazione per privacy e sicurezza
    -->
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="author" content="APTE - Assterritorio">


    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <?php

    require_once __DIR__ . '/../req.php';

    the_page_title();
    
    require_once __DIR__ . '/../conn_ok.php';
    ?>

</head>

<body>

    <?php require_once __DIR__ . '/../navbar_up.php'; ?>

    