<?php
require_once '../session.php';
#require('../validate_input.php');

require_once '../conn_ok.php';

$query_clona = "
select id_percorso,
       cod_percorso,
       descrizione,
       stagionalita
from elem.percorsi
where id_categoria_uso in (3,6)
  and id_servizio = $1
order by descrizione";

$result = pg_prepare($conn_sit, "query_clona_ajax", $query_clona);
$result = pg_execute($conn_sit, "query_clona_ajax", array($_POST['id_servizio_sit']));

echo '<option value="">Seleziona il percorso da clonare</option>';

while ($r = pg_fetch_assoc($result)) {

    echo '<option value="'.$r['id_percorso'].'">';
    echo $r['cod_percorso'].' - '.$r['descrizione'].' ('.$r['stagionalita'].')';
    echo '</option>';
}