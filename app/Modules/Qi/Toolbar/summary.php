<div class="d-flex align-items-center overflow-auto pt-3 pt-md-0">
    <div class="d-flex align-items-center">
        <label for="selectsummaryperiode" class="fs-7 fw-bolder text-gray-700 pe-4 text-nowrap">Periode:</label>
        <select data-control="select2" data-placeholder="Pilih periode" data-hide-search="true" class="form-select form-select-sm form-select-solid min-w-100px" name="tahun" id="selectsummaryperiode">
            <?php foreach (($summaryPeriods ?? []) as $period): ?>
                <?php $year = (string) $period->tahun; ?>
                <option value="<?= esc($year) ?>" <?= $year === (string) ($selectedSummaryPeriod ?? '') ? 'selected' : '' ?>><?= esc($year) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
