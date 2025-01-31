<?php

//echo '<br>ok<br>';
$lu = $_POST['lu'] ?? '0';
$ma = $_POST['ma'] ?? '0';
$me = $_POST['me'] ?? '0';
$gi = $_POST['gi'] ?? '0';
$ve = $_POST['ve'] ?? '0';
$sa = $_POST['sa'] ?? '0';
$do = $_POST['do'] ?? '0';

$I = $_POST['I'] ?? '0';
$II = $_POST['II'] ?? '0';
$III = $_POST['III'] ?? '0';
$IV = $_POST['IV'] ?? '0';
$frequenza_binaria = $do.''.$sa .''. $ve.''.$gi.''.$me .''. $ma.''.$lu.''.$IV .''. $III.''.$II.''.$I .'0';
//echo '<br>'.$frequenza_binaria.'<br>';

?>