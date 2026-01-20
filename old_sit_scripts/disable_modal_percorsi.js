// javascript per disabilitare sul vecchio SIT il modal con le modifiche ai percorsi 
// accrocchio temporaneo un po' rude ma funzionante 
// va  salvato nella cartella scripts e
// richiamato dentro index.html con la seguente riga
// <script src="scripts/disable_modal_percorso.js"></script>

// in caso di deploy va rifatta la procedura a mano

$(document).on('focusin', 'sit-percorso-data-details', function () {
    const $modal = $(this);
    $modal.find("input, select, textarea, button, search").prop("disabled", true);
    $modal.find("sit-frequnzy, [disabled], [ng-disabled]").attr("disabled", true);
	$modal.find("button").hide();
});