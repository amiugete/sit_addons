<?php 

?>

<div class="col-12">
        <div title="Dettagli elemento" class="btn-sm">Matr: <?php echo $re['matricola'] ;?> - Tag: <?php echo $re['tag'] ;?>
        </div>
      </div>
      

      <button type="button" class="info btn btn-warning btn-sm" title="Aggiungi informazioni su matricola / tag elemento <?php echo $re['id_elemento'];?>"
      data-bs-toggle="modal" data-bs-target="#edit_elemento" data-bs-whatever="<?php echo $re['id_elemento'];?>">
      <i class="fa-solid fa-pencil"></i>
      </button>




      <div class="modal fade" id="edit_elemento" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"> 
    <div class="modal-dialog modal-dialog-scrollable modal-xl" >
      <div class="modal-content">
        <div class="modal-header">
          <!--h5 class="modal-title" id="exampleModalLabel">Titolo</h5-->
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      <div class="modal-body" id="body_dettaglio">

      

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div> 
<?php ?>