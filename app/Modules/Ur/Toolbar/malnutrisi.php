<div class="d-flex align-items-center overflow-auto pt-3 pt-md-0 me-2">
    <div class="d-flex align-items-center">
        <span class="fs-7 fw-bolder text-gray-700 pe-4 text-nowrap">Tahun pulang :</span>
        <select data-control="select2" class="form-select form-select-sm form-select-solid" data-hide-search="true" name="selectperiode" id="selectperiode">
            <?= $periode ?>
        </select>
    </div>
</div>
<a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modal_upload_txt_eklaim"><i class="bi bi-filetype-txt"></i> Impor E-Klaim</a>
<div class="module-toolbar-actions"><button id="mal-refresh" type="button" class="btn btn-sm btn-light-primary ur-button"><i class="bi bi-arrow-clockwise"></i> Perbarui</button><button id="mal-print" type="button" class="btn btn-sm btn-light-primary ur-button"><i class="bi bi-printer"></i> Cetak ringkasan</button></div>
