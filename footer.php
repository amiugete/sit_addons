<?php 
#footer
?>




<!-- Sticky Footer -->
<footer class="sticky-footer">
<div class="text-center">
    
<span class="copyright">
<hr>
Applicativo realizzato da <a href="https://www.amiu.genova.it" target="_blank">AMIU Genova SPA</a> (Gestione applicativi SIGT) 
e distribuito con licenza open source GNU GPL 3.0. <br>Il codice dell'applicazione è disponibile su 
<a href="https://github.com//amiugete/sit_addons" targer="_new"> <i class="fab fa-github"></i> github</a>. 
In caso di problemi <?php echo $problemi;?>
<br>
<a href="https://www.amiu.genova.it" target="_blank"><img style="max-width:200px;" class="rounded" src="./img/logo_amiu.jpg" alt=""></a>
</span>
</div>
</footer>

<?php

pg_close($conn);
?>