<div class="ur-toolbar" role="group" aria-label="Periode dan impor data UR">
    <div class="ur-toolbar-period">
        <label for="selectperiode"><i class="bi bi-calendar3" aria-hidden="true"></i><span>Tahun pulang</span></label>
        <select data-control="select2" data-placeholder="Pilih tahun" class="form-select form-select-sm form-select-solid" data-hide-search="true" name="selectperiode" id="selectperiode">
            <?= $periode ?>
        </select>
    </div>
    <button type="button" class="btn btn-sm ur-toolbar-import" data-bs-toggle="modal" data-bs-target="#modal_upload_txt_eklaim"><i class="bi bi-filetype-txt" aria-hidden="true"></i><span>Impor E-Klaim</span></button>
</div>
