<?php
function fotoPiazzola($id_piazzola, $check_foto, $check_edit_piazzola, $piazzola_sospesa){
    ob_start();

    if ($check_foto == 1) {
    ?>
        <img src="../foto/sit/<?php echo $id_piazzola; ?>.jpg?hash=<?php echo filemtime('/foto_SIT/sit/' . $id_piazzola . '.jpg'); ?>"
             class="rounded img-fluid foto-piazzola" style="cursor: zoom-in" alt="Immagine piazzola <?php echo $id_piazzola; ?> non presente" onclick="apriFotoPiazzola(this)">
        <hr>
    <?php
    }

    if ($check_edit_piazzola == 1 and $piazzola_sospesa == 0) {
        ?>
        <form id="edit_foto_piazzola_<?php echo $id_piazzola; ?>" action="upload_foto.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="piazzola" value="<?php echo $id_piazzola; ?>">
            
            <div class="mb-3">
                <label for="fileToUpload" class="form-label"><?php echo ($check_foto == 1) ? 'Modifica immagine' : 'Aggiungi immagine:'; ?></label>
                <div class="mb-3 d-flex align-items-center gap-2">
                    <input type="file" class="form-control form-control-sm" name="fileToUpload" id="fileToUpload_<?php echo $id_piazzola; ?>" accept="image/*" required>
                    <input type="submit" value="Carica foto" class="btn btn-primary btn-sm">
                </div>
            </div>
        </form>
        <?php
    }

    return ob_get_clean();
}
?>