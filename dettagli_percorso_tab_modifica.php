<div class="row g-3 align-items-stretch" id="row-posizione"> 
  <div class="col-md-8" id="mappa_percorso"></div>
  <div class="col-md-4" id="dett_tappa">
    <div class="row align-items-center mb-2">
      <div class="col-md-6 d-flex justify-content-start gap-2">
        <button type="button" id="btnAddAsta" class="btn btn-sm btn-success">
          <i class="fa-solid fa-plus"></i>
        </button>
        <button type="button" id="btnDelAsta" class="btn btn-sm btn-danger">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="col-md-6 d-flex justify-content-end gap-2">
        <button type="button" id="btnAstaPrecedente" class="btn btn-sm btn-primary">
          <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button type="button" id="btnAstaSuccessiva" class="btn btn-sm btn-primary">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
    </div>
    <div id="info_tappa">
        <p class="alert alert-primary">Seleziona un'asta dalla tabella per visualizzarne i dettagli.</p>
    </div>
  </div>
</div>

<script> 
  cod_percorso = <?php echo json_encode($cod_percorso); ?>; 
  nome_servizio = <?php echo json_encode($nome_servizio); ?>;
  console.log('il cod percorso in javascript è '+ cod_percorso)
</script>
<script  src="./layers/mappa_dettagli_percorso.js"></script>
<script>
  $('#btnAstaPrecedente').on('click', function() {
    navigaAsta(-1);
  });

  $('#btnAstaSuccessiva').on('click', function() {
      navigaAsta(1);
  });
</script>