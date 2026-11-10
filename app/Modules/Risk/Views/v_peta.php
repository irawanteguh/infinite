<?php
use Modules\Risk\Libraries\RiskMatrix;
$cells = [];
foreach ($risks as $risk) {
    $cells[(int) $risk['likelihood']][(int) $risk['impact_level']][] = $risk;
}
$likelihoodLabels = [1 => 'Hampir tidak terjadi', 2 => 'Jarang terjadi', 3 => 'Kadang terjadi', 4 => 'Sering terjadi', 5 => 'Hampir pasti terjadi'];
$impactLabels = [1 => 'Tidak signifikan', 2 => 'Minor', 3 => 'Moderat', 4 => 'Signifikan', 5 => 'Sangat signifikan'];
?>
<style>
.peta-grid{min-width:720px;width:100%;border-collapse:separate;border-spacing:5px}.peta-grid th{text-align:center;padding:10px}.peta-grid td{vertical-align:top;padding:12px;border-radius:6px;width:16%;color:#182230}.peta-grid td.peta-blue,.peta-grid td.peta-blue a{color:white}.peta-grid a{display:block;color:#182230;margin-top:6px;text-decoration:underline}.peta-event{white-space:pre-wrap;min-width:220px;max-width:450px}
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6"><div><h1 class="fs-2x mb-2">Peta Risiko</h1><div class="text-muted"><?= esc($unitName) ?> · <?= $year ?></div></div><a class="btn btn-light-primary" href="<?= esc(site_url('risk/register') . '?' . http_build_query(['year' => $year, 'department' => $department])) ?>">Kembali ke register</a></div>
<form method="get" action="<?= site_url('risk/register/peta') ?>" class="card card-body mb-6"><div class="row g-4 align-items-end">
<div class="col-md-3"><label class="form-label" for="peta-year">Tahun</label><input class="form-control" id="peta-year" name="year" type="number" min="2000" max="2099" value="<?= $year ?>" required></div>
<div class="col-md-6"><label class="form-label" for="peta-unit">Unit kerja</label><select id="peta-unit" class="form-select" name="department"><option value="">Semua unit</option><?php foreach ($departments as $unit): ?><option value="<?= esc($unit['department_id']) ?>" <?= $department === $unit['department_id'] ? 'selected' : '' ?>><?= esc($unit['department']) ?></option><?php endforeach ?></select></div>
<div class="col-md-3"><button class="btn btn-primary">Tampilkan peta</button></div></div></form>
<?php if (!$ready): ?>
<div class="alert alert-warning">Tabel manajemen risiko belum tersedia. Jalankan migrasi modul terlebih dahulu.</div>
<?php else: ?>
<div class="card mb-6"><div class="card-body"><h3>Matriks risiko 5 × 5</h3><p class="text-muted">Penilaian register untuk <?= esc($unitName) ?>. Klik kode risiko untuk membuka profilnya.</p><div class="table-responsive"><table class="peta-grid"><caption>Skor mengikuti matriks institusi. Risiko dengan skor ≥12 memerlukan penanganan.</caption><thead><tr><th scope="col">Kemungkinan ↓ / Dampak →</th><?php foreach ($impactLabels as $i => $label): ?><th scope="col"><?= $i ?> · <?= esc($label) ?></th><?php endforeach ?></tr></thead><tbody>
<?php for ($l = 5; $l >= 1; $l--): ?><tr><th scope="row"><?= $l ?> · <?= esc($likelihoodLabels[$l]) ?></th><?php for ($i = 1; $i <= 5; $i++): $a = RiskMatrix::assess($l, $i); $items = $cells[$l][$i] ?? []; ?>
<td class="<?= $a['color'] === 'Biru' ? 'peta-blue' : '' ?>" style="background:<?= $a['hex'] ?>"><strong><?= $a['score'] ?> · <?= esc($a['color']) ?></strong><div><?= count($items) ?> risiko</div><?php foreach ($items as $risk): ?><a href="<?= esc(site_url('risk/register') . '?' . http_build_query(['year' => $year, 'department' => $risk['department_id'], 'id' => $risk['risk_id']]) . '#risk-profile') ?>" title="<?= esc($risk['event']) ?>"><?= esc($risk['code']) ?> · <?= esc($risk['department']) ?></a><?php endforeach ?></td>
<?php endfor ?></tr><?php endfor ?></tbody></table></div></div></div>
<div class="card"><div class="card-header"><h3 class="card-title">Daftar risiko · <?= count($risks) ?> risiko</h3></div><div class="card-body"><div class="table-responsive"><table class="table table-row-dashed align-middle"><thead><tr><th>Unit / Kode</th><th>Kejadian risiko</th><th>Kemungkinan</th><th>Dampak</th><th>Skor / Warna</th><th>Penanganan</th></tr></thead><tbody>
<?php if (!$risks): ?><tr><td colspan="6" class="text-center text-muted py-10">Belum ada risiko pada tahun dan unit yang dipilih.</td></tr><?php endif ?>
<?php foreach ($risks as $risk): ?><tr><td><?= esc($risk['department']) ?><br><a href="<?= esc(site_url('risk/register') . '?' . http_build_query(['year' => $year, 'id' => $risk['risk_id']]) . '#risk-profile') ?>"><?= esc($risk['code']) ?></a></td><td class="peta-event"><?= esc($risk['event']) ?></td><td><?= (int) $risk['likelihood'] ?></td><td><?= (int) $risk['impact_level'] ?></td><td><?= $risk['assessment']['score'] ?> · <?= esc($risk['assessment']['color']) ?></td><td><?= $risk['assessment']['requires_action'] ? 'Ya' : 'Tidak' ?></td></tr><?php endforeach ?>
</tbody></table></div></div></div>
<?php endif ?>
