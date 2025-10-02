<?php
require_once('./conn.php');
echo "<option value=''>Scegli un'area</option>";
    $query="SELECT * From etl.aree_ecopunti_4326 where data_disegno::date>=(NOW() - INTERVAL '30' DAY) order by data_disegno desc;";
    $result = pg_query($conn, $query);

     while($r2 = pg_fetch_assoc($result)) {
        echo "<option value='{$r2['id']}'>{$r2['nome']} ({$r2['data_disegno']})</option>";
    }
?>
