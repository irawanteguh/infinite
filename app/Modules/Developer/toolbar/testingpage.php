<link rel="stylesheet" href="<?= base_url('assets/css/ur/toolbar.css?v=' . filemtime(FCPATH . 'assets/css/ur/toolbar.css')) ?>">
<div class="ur-toolbar" role="group" aria-label="Periode dan impor contoh">
    <div class="ur-toolbar-period">
        <label for="testing-year"><i class="bi bi-calendar3" aria-hidden="true"></i><span>Tahun</span></label>
        <select id="testing-year" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true">
            <option value="2026">2026</option>
            <option value="2025">2025</option>
        </select>
    </div>
    <button type="button" class="btn btn-sm ur-toolbar-import" data-bs-toggle="modal" data-bs-target="#testing-import-modal">
        <i class="bi bi-filetype-txt" aria-hidden="true"></i><span>Impor</span>
    </button>
</div>
<div class="module-toolbar-actions">
    <button type="button" class="btn btn-sm btn-light-primary ur-button" id="testing-refresh">
        <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Perbarui
    </button>
    <button type="button" class="btn btn-sm btn-light-primary ur-button" id="testing-print">
        <i class="bi bi-printer" aria-hidden="true"></i> Cetak
    </button>
</div>
