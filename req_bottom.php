<?php
?>


<!--script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script-->


<!--script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script-->

<!-- Bootstrap Core JavaScript -->
<!--script src="./vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script-->
<script src="./vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script> 

<!--script src="./vendor/components/bootstrap/js/bootstrap.min.js"></script>
<script src="./vendor/components/bootstrap/js/alert.js"></script>
<script src="./vendor/components/bootstrap/js/modal.js"></script>
<script src="./vendor/components/bootstrap/js/tooltip.js"></script>
<script src="./vendor/components/bootstrap/js/popover.js"></script>
<script src="./vendor/components/bootstrap/js/collapse.js"></script>
<script src="./vendor/components/bootstrap/js/dropdown.js"></script-->

<!--script src="./vendor/twbs/bootstrap/js/dist/popover.js"></script-->




<!-- Bootstrap Plugins -->
<!--script src="./bootstrap-table-1.18.3/dist/bootstrap-table.js"></script-->



<script src="./vendor/moment/moment/moment.js"></script>
<script src="./vendor/moment/moment/locale/it.js"></script>


<!--script src="./bootstrap-datepicker/js/bootstrap-datepicker.js"></script-->
<script src="./vendor/wenzhixin/bootstrap-table/dist/bootstrap-table.min.js"></script>

<script src="./vendor/wenzhixin/bootstrap-table/dist/locale/bootstrap-table-it-IT.min.js"></script>
<script src="./vendor/wenzhixin/bootstrap-table/dist/locale/bootstrap-table-en-US.min.js"></script>


<!--script src="./vendor/leaflet/leaflet.js"></script>
<script src="./vendor/leaflet-locatecontrol-0.79.0/dist/L.Control.Locate.min.js"></script-->



<script src="./vendor/hhurz/tableexport.jquery.plugin/tableExport.min.js" ></script>
<script src="./vendor/hhurz/tableexport.jquery.plugin/libs/jsPDF/jspdf.umd.min.js" ></script>
<!--script src="./vendor/hhurz/tableexport.jquery.plugin/libs/jsPDF-AutoTable/jspdf.plugin.autotable.js" ></script-->
<script src="./vendor/hhurz/tableexport.jquery.plugin/libs/FileSaver/FileSaver.min.js" ></script>
<script src="./vendor/hhurz/tableexport.jquery.plugin/libs/js-xlsx/xlsx.core.min.js" ></script>



<script src="./vendor/wenzhixin/bootstrap-table/dist/extensions/filter-control/bootstrap-table-filter-control.min.js" ></script>
<script src="./vendor/wenzhixin/bootstrap-table/dist/extensions/auto-refresh/bootstrap-table-auto-refresh.min.js"></script>
<script src="./vendor/wenzhixin/bootstrap-table/dist/extensions/group-by-v2/bootstrap-table-group-by.min.js"></script>
<script src="./vendor/wenzhixin/bootstrap-table/dist/extensions/i18n-enhance/bootstrap-table-i18n-enhance.min.js"></script>
<script src="./vendor/wenzhixin/bootstrap-table/dist/extensions/export/bootstrap-table-export.min.js"></script>




<!---------------------------------------------------------------------------------------------------------------------------->

<!-- BOOTSTRAP SELECT  problem version 1.13 with bootstrap 5 -->

<!-- Latest compiled and minified JavaScript -->
<!--script src="./vendor/snapappointments/bootstrap-select/dist/js/bootstrap-select.min.js"></script-->




<!-- Latest compiled and minified JavaScript -->

<script src="./vendor/bootstrap-select-beta/js/bootstrap-select.min.js"></script>
<script src="./vendor/bootstrap-select-beta/js/defaults-it_IT.min.js"></script>




<!--script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script-->
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<!--script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/i18n/defaults-it_IT.min.js"></script-->


<!---------------------------------------------------------------------------------------------------------------------------->

<!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"  charset="UTF-8" 
integrity="sha512-T/tUfKSV1bihCnd+MxKD0Hm1uBBroVYBOYSk1knyvQ9VyZJpc/ALb4P0r6ubwVPSGB2GvjeoMAJJImBG12TiaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script-->

<script src="./vendor/eternicode/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"  charset="UTF-8"></script>
<script src="./vendor/eternicode/bootstrap-datepicker/dist/locales/bootstrap-datepicker.it.min.js"  charset="UTF-8"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.1.1/list.min.js"></script>



<!-- Per questo non uso composer perchè il pacchetto è obsoleto -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>



<script>
function printClass(className) {
	//it is an array so i using only the first element
     var printContents = document.getElementsByClassName(className)[0].innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}


// funzione per stampa al volo 
function printDiv(divName) {
     var printContents = document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}



// prevent multiple submit
$("body").on("submit", "form", function () {
        $(this).submit(function () {
            return false;
        });
        return true;
    });

</script>