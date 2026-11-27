<?php
$number = static function ($value, int $decimals = 0): string {
    return $value === null ? '—' : number_format((float) $value, $decimals, ',', '.');
};
$monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$domicileLabels = \Modules\Ca\Models\CaskriningModel::DOMICILES + ['UNKNOWN' => 'Tidak diketahui'];
$colors = ['Ringan' => 'success', 'Rata-rata' => 'success', 'Sedang' => 'warning', 'Berat' => 'danger', 'Tinggi' => 'danger', 'Average Risk (Risiko ringan)' => 'success', 'Moderate Risk (Risiko Sedang)' => 'warning', 'High Risk (Risiko Tinggi)' => 'danger'];
$riskCounts = ['success' => 0, 'warning' => 0, 'danger' => 0];
foreach ($data['risks'] ?? [] as $risk) {
    $category = $colors[$risk['risk_level']] ?? null;
    if (isset($riskCounts[$category])) {
        $riskCounts[$category] += (int) $risk['total'];
    }
}
$chart = $data ? [
    'months' => array_keys($data['monthly']),
    'risks' => $data['risks'],
    'series' => array_map(static function ($code) use ($data) {
        return ['name' => $data['instrumentNames'][$code], 'data' => array_column($data['monthly'], $code)];
    }, array_keys($data['instrumentNames'])),
    'domiciles' => array_map(static function ($row) use ($domicileLabels) {
        return ['label' => $domicileLabels[$row['domicile']] ?? 'Tidak diketahui', 'total' => (int) $row['total']];
    }, $data['domiciles']),
] : null;
?>
<main class="prem-page pe-dashboard ca-dashboard">
    <div class="prem-container prem-main">
        <div class="app-page-heading">
            <div><h1>Dashboard Skrining Kanker</h1><p>Oncology centre · AURA — A Place of Care, A Space for Hope</p></div>
        </div>
        <section class="prem-card pe-filter-card app-filter-card" aria-label="Filter dashboard">
            <div class="app-filter-heading"><h2><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter dashboard</h2><p>Tentukan periode dan cakupan data skrining.</p></div>
            <form method="get" action="<?= esc(site_url('ca/dashboard'), 'attr') ?>" id="ca-dashboard-filter" class="pe-filter app-filter-fields">
                <div><label for="ca-instrument" class="form-label">Instrumen</label><select id="ca-instrument" name="instrument" class="form-select form-select-solid"><option value="">Semua instrumen</option><?php foreach ($instruments as $item): ?><option value="<?= esc((string) $item['instrument_id'], 'attr') ?>" <?= $filters['instrument'] === (string) $item['instrument_id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option><?php endforeach; ?></select></div>
                <div><label for="ca-domicile" class="form-label">Domisili</label><select id="ca-domicile" name="domicile" class="form-select form-select-solid"><option value="">Semua domisili</option><?php foreach ($domicileLabels as $key => $label): ?><option value="<?= esc($key, 'attr') ?>" <?= $filters['domicile'] === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
            </form>
        </section>

        <?php if ($error): ?><div class="alert alert-danger mt-5" role="alert"><?= esc($error) ?></div><?php endif; ?>
        <?php if ($data): ?>
            <?php if (!$data['total']): ?><div class="alert alert-info mt-5">Belum ada pengisian skrining pada filter ini.</div><?php endif; ?>

            <div class="row g-5 mt-1">
                <div class="col-lg-8"><section class="prem-card p-6 h-100"><h2 class="fs-4">Tren pengisian bulanan</h2><p class="text-muted fs-7">Jumlah pengisian setiap instrumen sesuai filter aktif.</p><div id="ca-monthly-chart"></div></section></div>
                <div class="col-lg-4"><section class="prem-card p-6 h-100"><h2 class="fs-4">Domisili pasien</h2><p class="text-muted fs-7">Sebaran pengisian berdasarkan domisili yang dicantumkan pasien.</p><div id="ca-domicile-chart"></div><ul class="list-unstyled mb-0" id="ca-domicile-legend">
                    <?php $domicileTotal = array_sum(array_column($data['domiciles'], 'total')); ?>
                    <?php foreach ($data['domiciles'] as $index => $row): ?>
                        <li class="d-flex justify-content-between align-items-center gap-3 py-3 border-bottom">
                            <span class="d-flex align-items-center gap-2"><span class="ca-domicile-dot ca-domicile-dot-<?= $index ?>" aria-hidden="true"></span><?= esc($domicileLabels[$row['domicile']] ?? 'Tidak diketahui') ?></span>
                            <span class="text-end text-nowrap"><strong><?= $number($row['total']) ?></strong><span class="text-muted ms-2"><?= $number($domicileTotal > 0 ? $row['total'] / $domicileTotal * 100 : null, 1) ?>%</span></span>
                        </li>
                    <?php endforeach; ?>
                </ul></section></div>
            </div>

            <div class="row g-5 mt-1">
                <div class="col-lg-6">
                    <section class="prem-card p-6 h-100">
                        <h2 class="fs-4">Komposisi risiko</h2>
                        <p class="text-muted fs-7">Distribusi pengisian berkategori sesuai filter aktif.</p>
                        <?php foreach ([
                            ['Average Risk', $riskCounts['success'], '#7fbea9'],
                            ['Moderate Risk', $riskCounts['warning'], '#dfc384'],
                            ['High Risk', $riskCounts['danger'], '#c7838e'],
                        ] as [$label, $value, $color]): ?>
                            <?php $distribution = array_sum($riskCounts) > 0 ? $value / array_sum($riskCounts) * 100 : null; ?>
                            <div class="ur-meter-row">
                                <div class="ur-meter-label">
                                    <span><?= $label ?></span>
                                    <strong><?= $number($value) ?> · <?= $number($distribution, 1) ?><?= $distribution !== null ? '%' : '' ?></strong>
                                </div>
                                <div class="ur-meter" role="img" aria-label="<?= esc($label . ': ' . $number($value) . ' pengisian, ' . ($distribution === null ? 'persentase belum tersedia' : $number($distribution, 1) . ' persen'), 'attr') ?>">
                                    <span style="width: <?= (float) ($distribution ?? 0) ?>%; background: <?= $color ?>"></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                </div>
                <div class="col-lg-6">
                    <section class="prem-card p-6 h-100">
                        <h2 class="fs-4">Distribusi kategori risiko</h2>
                        <p class="text-muted fs-7">Jumlah pengisian menurut rentang penilaian instrumen saat ini.</p>
                        <div id="ca-risk-chart"></div>
                    </section>
                </div>
            </div>

            <section id="ca-instrument-summary" class="prem-card ca-table-card mt-5">
                <div class="card-header app-table-heading"><div><h2 class="app-table-title">Ringkasan instrumen dan kategori risiko</h2><p class="app-table-description">Kategori dihitung dari skor menggunakan rentang penilaian instrumen saat ini. Rata-rata dihitung dari pengisian yang memiliki skor.</p></div></div>
                <div class="card-body">
                <div class="table-responsive"><table class="table table-row-dashed align-middle fs-8 gy-2"><thead><tr><th>Instrumen</th><th>Pengisian</th><th>Memiliki skor</th><th>Rata-rata skor</th><th>Kategori risiko</th></tr></thead><tbody>
                    <?php foreach ($data['summaries'] as $summary): ?>
                        <tr><td><?= esc($summary['name']) ?></td><td><?= $number($summary['total']) ?></td><td><?= $number($summary['scored']) ?></td><td><?= $number($summary['average'], 1) ?></td><td>
                            <?php foreach ($data['risks'] as $risk): if ($risk['code'] !== $summary['code']) continue; ?>
                                <span class="badge badge-light-<?= $colors[$risk['risk_level']] ?? 'secondary' ?> m-1"><?= esc($risk['risk_level'] ?: 'Belum ditentukan') ?> · <?= $number($risk['total']) ?></span>
                            <?php endforeach; ?>
                        </td></tr>
                    <?php endforeach; ?>
                    <?php if (!$data['summaries']): ?><tr><td colspan="5" class="text-center text-muted">Belum ada data.</td></tr><?php endif; ?>
                </tbody></table></div>
                </div>
            </section>

            <section class="prem-card ca-table-card mt-5">
                <div class="card-header app-table-heading"><div><h2 class="app-table-title">Jumlah pengisian per bulan</h2><p class="app-table-description">Jumlah pengisian setiap instrumen pada setiap bulan dalam tahun terpilih.</p></div></div>
                <div class="card-body">
                <div class="table-responsive"><table class="table table-row-dashed fs-8 gy-2" data-datatable="false"><thead><tr><th>Bulan</th><?php foreach ($data['instrumentNames'] as $name): ?><th><?= esc($name) ?></th><?php endforeach; ?><th>Total</th></tr></thead><tbody><?php foreach ($data['monthly'] as $month => $counts): ?><tr><td><?= esc($monthNames[(int) substr($month, 5, 2)]) ?></td><?php foreach ($data['instrumentNames'] as $code => $name): ?><td><?= $number($counts[$code]) ?></td><?php endforeach; ?><td><?= $number(array_sum($counts)) ?></td></tr><?php endforeach; ?></tbody></table></div>
                </div>
            </section>

            <section id="ca-response-history" class="prem-card ca-table-card mt-5">
                <div class="card-header app-table-heading"><div><h2 class="app-table-title">Riwayat pengisian</h2><p class="app-table-description">Rincian waktu pengisian, instrumen, pasien, skor, dan kategori risiko sesuai filter aktif.</p></div></div>
                <div class="card-body">
                <div class="table-responsive"><table class="table table-row-dashed align-middle fs-8 gy-2"><thead><tr><th>Tanggal</th><th>Instrumen</th><th>Nama pasien</th><th>Domisili</th><th>Skor</th><th>Kategori risiko</th></tr></thead><tbody>
                    <?php foreach ($data['details'] as $row): ?><tr>
                        <td><?= esc(date('d.m.Y H:i:s', strtotime($row['screened_at']))) ?></td><td><?= esc($row['instrument_name']) ?></td><td><?= esc($row['respondent_name'] ?: '—') ?></td><td><?= esc($domicileLabels[$row['domicile']] ?? 'Tidak diketahui') ?></td><td><?= $number($row['total_score']) ?></td>
                        <td><span class="badge badge-light-<?= $colors[$row['risk_level']] ?? 'secondary' ?>"><?= esc($row['risk_level'] ?: 'Belum ditentukan') ?></span></td>
                    </tr><?php endforeach; ?>
                    <?php if (!$data['details']): ?><tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr><?php endif; ?>
                </tbody></table></div>
                <?php if ($data['total'] > 20): ?>
                <nav class="d-flex justify-content-end align-items-center mt-4" aria-label="Halaman riwayat">
                    <div class="d-flex gap-2"><?php foreach ([-1 => 'Sebelumnya', 1 => 'Selanjutnya'] as $offset => $label): $page = $data['page'] + $offset; if ($page < 1 || $page > (int) ceil($data['total'] / 20)) continue; ?><a class="btn btn-sm btn-light-primary" href="<?= esc(site_url('ca/dashboard') . '?' . http_build_query($filters + ['page' => $page]), 'attr') ?>"><?= $label ?></a><?php endforeach; ?></div>
                </nav>
                <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>
<script type="application/json" id="ca-dashboard-data"><?= json_encode($chart, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
