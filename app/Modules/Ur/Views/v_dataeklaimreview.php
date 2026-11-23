<?php
$reviewUrl = site_url('ur/dataeklaim/review');
$months = ['Semua bulan', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
    'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$services = ['' => 'Semua layanan', '1' => 'Rawat inap', '2' => 'Rawat jalan'];
$values = static fn($value) => $value === null || $value === '' ? '—' : esc((string) $value);
$amount = static fn($value) => is_numeric($value)
    ? esc(number_format((float) $value, 2, ',', '.')) : $values($value);
$date = static function ($value) use ($values) {
    if ($value === null || $value === '') return $values($value);
    $day = substr((string) $value, 0, 10);
    return $values(preg_match('/^\d{4}-\d{2}-\d{2}$/D', $day)
        ? implode('.', array_reverse(explode('-', $day))) : $day);
};
$tariffFields = ['tarif_rs', 'total_tarif'];
$pageLink = static fn($after) => $reviewUrl . '?' . http_build_query(array_replace($filters, ['after' => $after]));
$tones = ['data' => 'danger', 'koding' => 'warning', 'topup' => 'success', 'klinis' => 'info', 'klaim' => 'danger'];
?>
<div class="prem-page ur-dashboard ek-page ek-review">
    <header class="ur-hero app-page-heading">
        <div>
            <h1>Review Verifikator Internal</h1>
            <p>Tinjau temuan E-Klaim per SEP sebelum pengajuan.</p>
        </div>
    </header>
    <section class="prem-card ur-filterbar app-filter-card" aria-labelledby="review-filter-title">
        <div class="app-filter-heading">
            <h2 id="review-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter review</h2>
        </div>
        <form id="review-filters" method="get" action="<?= esc($reviewUrl, 'attr') ?>" class="app-filter-fields">
            <?php foreach ([
                'month' => ['Bulan pulang', $months],
                'service' => ['Jenis layanan', $services],
                'category' => ['Kategori temuan', ['' => 'Semua kategori'] + $categories],
            ] as $field => [$label, $options]): ?>
                <div class="ur-filter">
                    <label for="review-<?= esc($field) ?>" class="form-label"><?= esc($label) ?></label>
                    <select class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true" id="review-<?= esc($field) ?>" name="<?= esc($field) ?>">
                        <?php foreach ($options as $key => $option): ?>
                            <option value="<?= esc((string) $key) ?>" <?= (string) $filters[$field] === (string) $key ? 'selected' : '' ?>><?= esc((string) $option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endforeach; ?>
        </form>
    </section>
    <section class="card ek-panel app-table-card" aria-labelledby="queue-title">
        <div class="app-table-heading">
            <div>
                <h2 id="queue-title" class="app-table-title">Antrean temuan</h2>
                <p class="app-table-description">Daftar episode yang memerlukan peninjauan dari data UR terbaru.</p>
            </div>
            <?php if ($rows): ?>
                <span class="badge badge-light-primary"><?= count($rows) ?><?= isset($total) ? ' dari ' . number_format($total, 0, ',', '.') : '' ?> episode ditampilkan</span>
            <?php endif; ?>
        </div>
        <?php if ($error !== ''): ?>
            <div class="ek-status is-error mb-0" role="alert"><i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i><?= esc($error) ?></div>
        <?php elseif (!$rows): ?>
            <div class="ek-table-info mt-0" role="status"><i class="bi bi-info-circle" aria-hidden="true"></i><div><?= $next !== '' || $filters['after'] !== '' ? 'Tidak ada temuan pada bagian ini.' : 'Tidak ada episode yang perlu direview untuk filter ini.' ?></div></div>
        <?php else: ?>
            <div class="table-responsive ek-datatable-scroll" tabindex="0" role="region" aria-label="Tabel antrean temuan">
                <table class="table table-row-dashed align-middle gy-2 mb-0" data-datatable="manual">
                    <thead>
                        <tr>
                            <th scope="col">SEP / Pasien</th>
                            <th scope="col">No. RM</th>
                            <th scope="col">Tanggal masuk</th>
                            <th scope="col">Tanggal pulang</th>
                            <th scope="col">Layanan</th>
                            <th scope="col">DPJP</th>
                            <th scope="col">LOS (hari)</th>
                            <th scope="col">INA-CBG / Severity</th>
                            <th scope="col" class="text-end">Tarif RS</th>
                            <th scope="col" class="text-end">Total tarif klaim</th>
                            <th scope="col" class="text-end">Tarif iDRG</th>
                            <th scope="col" class="text-end">Temuan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $index => $row): ?>
                        <tr>
                            <td class="ek-sep"><strong class="ek-patient"><?= esc($row['sep']) ?></strong><span class="ek-mrn"><?= $values($row['nama_pasien']) ?></span></td>
                            <td class="ek-rm"><?= $values($row['mrn']) ?></td>
                            <td class="ek-date"><?= $date($row['admission_date']) ?></td>
                            <td class="ek-date"><?= $date($row['discharge_date']) ?></td>
                            <td>
                                <?php if (in_array((string) $row['ptd'], ['1', '2'], true)): ?>
                                    <span class="ek-service ek-service-<?= (string) $row['ptd'] === '1' ? 'inpatient' : 'outpatient' ?>"><?= esc($services[$row['ptd']]) ?></span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="ek-dpjp"><?= $values($row['dpjp']) ?></td>
                            <td><?= $values($row['los']) ?></td>
                            <td>
                                <span class="text-nowrap"><?= $values($row['inacbg']) ?></span>
                                <?php if (trim((string) $row['sl']) !== ''): ?>
                                    <span class="badge badge-light-primary d-table mt-1"><?= $values($row['sl']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= $amount($row['tarif_rs']) ?></td>
                            <td class="text-end"><?= $amount($row['total_tarif']) ?></td>
                            <td class="text-end"><?= $amount($row['idrg_total_tarif']) ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-light-primary ek-toggle collapsed"
                                    data-bs-toggle="collapse" data-bs-target="#review-detail-<?= $index ?>"
                                    aria-expanded="false" aria-controls="review-detail-<?= $index ?>">
                                    <?= count($row['findings']) ?> temuan <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="ek-review-detail-row">
                            <td colspan="12">
                                <div class="collapse" id="review-detail-<?= $index ?>">
                                    <div class="ek-detail my-2">
                                        <h3 class="ek-detail-heading fs-6"><i class="bi bi-file-earmark-medical" aria-hidden="true"></i>Data yang diperiksa</h3>
                                        <dl class="row g-3 mb-4">
                                            <?php foreach (['inacbg' => 'INA-CBG', 'sl' => 'Severity', 'los' => 'LOS (hari)', 'vent_hour' => 'Ventilator (jam)', 'icu_indikator' => 'Indikator ICU', 'icu_los' => 'LOS ICU (hari)', 'tarif_rs' => 'Tarif RS', 'total_tarif' => 'Total tarif klaim', 'diaglist' => 'Diagnosis', 'proclist' => 'Prosedur'] as $field => $label): ?>
                                                <div class="ek-detail-field <?= in_array($field, ['diaglist', 'proclist'], true) ? 'col-12 col-md-6' : 'col-6 col-md-3' ?>">
                                                    <dt><?= esc($label) ?></dt>
                                                    <dd><?= in_array($field, $tariffFields, true) ? $amount($row[$field]) : $values($row[$field]) ?></dd>
                                                </div>
                                            <?php endforeach; ?>
                                        </dl>
                                        <h3 class="ek-detail-heading fs-6"><i class="bi bi-clipboard-check" aria-hidden="true"></i>Temuan dan tindak lanjut</h3>
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($row['findings'] as $finding): ?>
                                                <li class="border bg-white rounded p-3 mb-2">
                                                    <span class="badge badge-light-<?= $tones[$finding['category']] ?>"><?= esc($categories[$finding['category']]) ?></span>
                                                    <div class="fw-semibold"><?= esc($finding['title']) ?></div>
                                                    <div><?= nl2br(esc($finding['note'])) ?></div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php if (!empty($history[$row['sep']])): ?>
                                            <h3 class="ek-detail-heading fs-6 mt-4"><i class="bi bi-clock-history" aria-hidden="true"></i>Riwayat BAHV</h3>
                                            <ul class="mb-0">
                                                <?php foreach ($history[$row['sep']] as $entry): ?>
                                                    <li><?= $values(substr((string) $entry['verified_at'], 0, 10)) ?> · <?= $values($entry['status']) ?> · <?= $values($entry['reason']) ?>
                                                        <?php if (trim((string) $entry['verification_note']) !== ''): ?>
                                                            <div class="text-muted"><?= $values($entry['verification_note']) ?></div>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php if ($error === '' && ($filters['after'] !== '' || $next !== '')): ?>
            <nav class="ek-table-footer d-flex justify-content-end" aria-label="Halaman antrean">
                <?php if ($filters['after'] !== ''): ?>
                    <a class="btn btn-sm btn-light-primary" href="<?= esc($pageLink(''), 'attr') ?>"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke awal</a>
                <?php endif; ?>
                <?php if ($next !== ''): ?>
                    <a rel="next" class="btn btn-sm btn-light-primary" href="<?= esc($pageLink($next), 'attr') ?>">Lanjutkan pemeriksaan <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
</div>
