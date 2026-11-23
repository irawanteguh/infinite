<?php if (isset($reviewFilters)): ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/ur/toolbar.css?v=' . filemtime(FCPATH . 'assets/css/ur/toolbar.css')) ?>">
    <div class="ur-toolbar">
        <div class="ur-toolbar-period">
            <label for="review-year"><i class="bi bi-calendar3" aria-hidden="true"></i><span>Discharge Years</span></label>
            <select id="review-year" name="year" form="review-filters" class="form-select form-select-sm form-select-solid"
                data-control="select2" data-minimum-results-for-search="0">
                <?php foreach (range($currentYear, 2014) as $year): ?>
                    <option value="<?= $year ?>" <?= $reviewFilters['year'] === $year ? 'selected' : '' ?>><?= $year ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
<?php else: ?>
    <?php include APPPATH . 'Modules/Ur/Toolbar/_common.php'; ?>
    <div class="module-toolbar-actions d-flex gap-2">
        <a class="btn btn-sm btn-light-primary" id="ek-review-link" href="<?= esc(site_url('ur/dataeklaim/review')) ?>">
            <i class="bi bi-clipboard-check" aria-hidden="true"></i> Review Verifikator Internal
        </a>
        <button type="button" class="btn btn-sm btn-light-primary ur-button" id="ek-refresh">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Perbarui
        </button>
    </div>
<?php endif; ?>
