<link rel="stylesheet" href="<?= base_url('assets/css/ur/toolbar.css?v=' . filemtime(FCPATH . 'assets/css/ur/toolbar.css')) ?>">
<div class="ur-toolbar" role="group" aria-label="Periode dan impor data UR">
    <?php if (isset($periode) && (string) $periode !== ''): ?>
    <div class="ur-toolbar-period">
        <label for="selectperiode"><i class="bi bi-calendar3" aria-hidden="true"></i><span>Discharge Years</span></label>
        <select id="selectperiode" name="selectperiode" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="Pilih tahun" data-minimum-results-for-search="0">
            <?= $periode ?>
        </select>
    </div>
    <?php endif; ?>
    <button type="button" class="btn btn-sm ur-toolbar-import" data-bs-toggle="modal" data-bs-target="#modal_upload_txt_eklaim">
        <i class="bi bi-filetype-txt" aria-hidden="true"></i><span>Impor E-Klaim</span>
    </button>
</div>
