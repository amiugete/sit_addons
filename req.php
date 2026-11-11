<?php
// setcookie funziona solo se non ci sono degli output prima per cui l'ho spostato qua;
require_once('./check_utente.php');



require_once('./session.php');



require_once './env.php';


// parametri iniziali 
$partenza_ditte_terze = $_ENV['PARTENZA_DITTE_TERZE'] ?? null;
$partenza_ekovision = $_ENV['PARTENZA_EKOVISION'] ?? null;


//echo   "partenza_ditte_terze: " . $partenza_ditte_terze . "<br>";
//echo   "partenza_ekovision: " . $partenza_ekovision


?>


<!--link rel="stylesheet" href="https://kit-free.fontawesome.com/releases/latest/css/free.min.css"-->
<link rel="stylesheet" href="./vendor/fortawesome/font-awesome/css/all.min.css">

<!--script
  src="https://code.jquery.com/jquery-3.6.0.min.js"
  integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
  crossorigin="anonymous"></script-->


<!-- Production version -->
<!--script src="https://unpkg.com/@popperjs/core@2" crossorigin="anonymous"></script-->
  




<!--script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script-->

<!-- Bootstrap Core CSS -->
<link rel="stylesheet" href="./vendor/twbs/bootstrap/dist/css/bootstrap.min.css">


<!--ICONE -->
<!--link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css"-->
<link rel="stylesheet" href="./vendor/twbs/bootstrap-icons/font/bootstrap-icons.css">


<!-- Bootstrap Plugins -->
<!-- BOOTSTRAP TABLE -->
<!--link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css"-->
<link rel="stylesheet" href="./vendor/wenzhixin/bootstrap-table/dist/bootstrap-table.min.css">
<link rel="stylesheet" href="./vendor/wenzhixin/bootstrap-table/dist/extensions/filter-control/bootstrap-table-filter-control.css">
<link rel="stylesheet" href="./vendor/wenzhixin/bootstrap-table/dist/extensions/group-by-v2/bootstrap-table-group-by.min.css">


<!-- BOOTSTRAP SELECT  problem version 1.13 with bootstrap 5-->
<!-- Latest compiled and minified CSS -->
<!--link rel="stylesheet" href="./vendor/snapappointments/bootstrap-select/dist/css/bootstrap-select.min.css"-->

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="./vendor/bootstrap-select-beta//css/bootstrap-select.min.css">
<!--link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css"-->





<!-- BOOTSTRAP DATEPICKER -->
<link rel="stylesheet" href="./vendor/eternicode/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css"/>


<!-- select2 -->
<link rel="stylesheet" href="./node_modules/select2/dist/css/select2.min.css">
<link rel="stylesheet" href="./node_modules/select2-bootstrap-theme/dist/select2-bootstrap.min.css">


<!--link href="./bootstrap-table-1.18.3/dist/bootstrap-table.css" rel="stylesheet"-->

<!--link href="./bootstrap-datepicker/css/bootstrap-datepicker.css" rel="stylesheet"-->

<!-- FONT FAMILY -->
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">




<!-- MAPPA LEAFLET -->

<link rel="stylesheet" href="./node_modules/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="./node_modules/leaflet.markercluster/dist/MarkerCluster.css">
<link rel="stylesheet" href="./node_modules/leaflet.locatecontrol/dist/L.Control.Locate.css">


<!--link rel="stylesheet" href="./vendor/leaflet-locatecontrol-0.79.0/dist/L.Control.Locate.css"-->

<script>

var onResize = function() {
    console.log('Sono dentro la funzione onResize del req.php');
    var new_height= 10+$(".banner").height()+$(".navbar-sticky-top").height()
    console.log('new_height: '+ new_height);
    // apply dynamic padding at the top of the body according to the fixed navbar height
    $("body").css("margin-top", new_height);


    var new_width=$(".banner").width()
    console.log('new_width:' + new_width);
    if (new_width < 760) {
        $("#sit_btn1").css("display", 'none'); 
        $("#link_pc1").css("display", 'none');
        $("#link_pc2").css("display", 'none');
        //$("#intestazione").css("display", 'none');
        //$(".banner").css("height", 0);
        $(".pull-left").css("height", 0);
        //$(".navbar-sticky-top").css("top", 0);
    } else {
        $("#sit_btn1").css("display", 'inline-block'); 
        $("#link_pc1").css("display", 'inline-block');
        $("#link_pc2").css("display", 'inline-block');
        $("#intestazione").css("display", 'inline-block');
    }
    var h = window.innerHeight - new_height - 15; /* aggiungo 15 di padding  */
    console.log('h:' + h);

    $("#mainContainer").height(h);

    if (typeof map !== 'undefined') {
        setTimeout(() => map.invalidateSize(), 50);
    }

    /*$(".container-fluid.vh-100").css("height", h + "px");
    //$("#map-container").css("height", h+"px");  
    setTimeout(() => {
        map.invalidateSize();
    }, 100);*/
};

</script>




<!--script src="./jquery.js"></script-->


<?php 

$check_modal = $check_modal ?? 0;

if ($check_modal!=1){?>
<!-- jQuery -->
<script src="./vendor/components/jquery/jquery.min.js"></script>
<?php }?>

<!-- Per questo non uso composer perchè il pacchetto è obsoleto -->
<link rel="stylesheet" type="text/css" href="./node_modules/daterangepicker/daterangepicker.css" />

<!--link rel="stylesheet" type="text/css" href="./includes/main.css"-->

<link rel="stylesheet" type="text/css"  href="./includes/sit.css">

<?php if (!isset($check_modal)){
    $check_modal=0;
}
if ($check_modal!=1) { ?>


<link rel='icon' href='./favicon.ico' type='image/x-icon' sizes="16x16">

<script>
// attach the function to the window resize event
$(window).resize(onResize);

// call it also when the page is ready after load or reload
$(function() {
  onResize();
});
</script>


<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>






<?php
}
$problemi =  'contattare l\'<a href="mailto:assterritorio@amiu.genova.it">amministratore di sistema</a>';

function the_page_title()
{
    /*$page_name = getcwd(); // getcwd() gets the directory of the file you call the function from
    $each_page_name = explode('/', $page_name);
    $len_page_dir = count($each_page_name) -1;
    $temp = explode('_', $each_page_name[$len_page_dir]);
    $len_temp=count($temp)-1;
    */
    $app_env = $_ENV['APP_ENV'] ?? null;
    
    if ($app_env == 'prod') {
        $test=0;
    } else if($app_env =='test') {
        ?>
        <style>
            .navbar {
                background: #ff0000;
            }
        </style>
        <?php
        $test=1;
    } else {
        die("Non riconosco l'ambiente di esecuzione. 
        Controlla la variabile APP_ENV in .env 
        e assicurati che sia valorizzata correttamente 
        (prod o test).");
    }
    return $test;
}

?>

<!-- libreria per l'esportazione in excel -->
<!--script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script-->
<script src="./node_modules/xlsx/dist/xlsx.full.min.js"></script>

<!-- script JS per recuperare i dati della tabella filtrati e non e scaricarli in excel -->
<script src="./js/tableExport.js"></script>