<div class="ur-toolbar" role="group" aria-label="Periode skrining">
    <div class="ur-toolbar-period">
        <label for="ca-year"><i class="bi bi-calendar3" aria-hidden="true"></i><span>Tahun skrining</span></label>
        <select id="ca-year" name="year" form="ca-dashboard-filter" data-control="select2" data-hide-search="true" class="form-select form-select-sm form-select-solid">
            <?php foreach ($caYears as $year): ?>
                <option value="<?= esc((string) $year, 'attr') ?>" <?= (string) ($caFilters['year'] ?? date('Y')) === (string) $year ? 'selected' : '' ?>><?= esc((string) $year) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="module-toolbar-actions">
    <a href="<?= esc(site_url('ca/masterqusioner'), 'attr') ?>" class="btn btn-light-primary"><i class="bi bi-list-check"></i> Master kuesioner</a>
    <a href="<?= esc(site_url('ca/caskrining') . '?' . http_build_query(['group_id' => session()->get('groupid'), 'org_id' => session()->get('orgid')]), 'attr') ?>" class="btn btn-light-primary" target="_blank" rel="noopener"><i class="bi bi-clipboard-heart"></i> Pengisian skrining</a>
</div>
