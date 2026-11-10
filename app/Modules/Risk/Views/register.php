<?php
use Modules\Risk\Libraries\RiskMatrix;
$base = site_url('risk/register');
$url = static function (array $extra = []) use ($base, $year, $department, $quarter) {
    return $base . '?' . http_build_query(array_merge(['year' => $year, 'department' => $department, 'quarter' => $quarter], $extra));
};
$failedForm = session()->getFlashdata('risk_form');
$profileInput = static function ($key, $default = '') use ($selected, $failedForm) {
    $value = $failedForm === 'profile' ? old($key, $selected[$key] ?? $default, false) : ($selected[$key] ?? $default);
    return is_scalar($value) ? (string) $value : '';
};
$value = static function ($key, $default = '') use ($profileInput) { return esc($profileInput($key, $default)); };
$priority = array_filter($risks, static function ($risk) { return $risk['assessment']['requires_action']; });
$monitored = 0;
foreach ($priority as $risk) {
    foreach ($risk['monitoring'] as $m) { if ((int) $m['quarter'] === $quarter) { $monitored++; break; } }
}
$monitor = [];
if ($selected) {
    foreach ($selected['monitoring'] as $m) { if ((int) $m['quarter'] === $quarter) { $monitor = $m; } }
}
$mv = static function ($key, $default = '') use ($monitor, $failedForm) {
    $value = $failedForm === 'monitor' ? old($key, $monitor[$key] ?? $default, false) : ($monitor[$key] ?? $default);
    return esc(is_scalar($value) ? (string) $value : '');
};
$likelihoodLabels = [1 => 'Hampir tidak terjadi', 2 => 'Jarang terjadi', 3 => 'Kadang terjadi', 4 => 'Sering terjadi', 5 => 'Hampir pasti terjadi'];
$impactLabels = [1 => 'Tidak signifikan', 2 => 'Minor', 3 => 'Moderat', 4 => 'Signifikan', 5 => 'Sangat signifikan'];
?>
<style>
.risk-score{display:inline-block;padding:6px 10px;border-radius:6px;font-weight:700;color:#141824;white-space:nowrap}.risk-blue{color:white!important}.risk-matrix td{height:66px;min-width:76px;text-align:center;color:#182230;border:4px solid white}.risk-matrix th{padding:8px;text-align:center}.risk-text{white-space:pre-wrap;min-width:180px;max-width:340px}.risk-section{scroll-margin-top:140px}.risk-form label{font-weight:600;margin-bottom:6px}.risk-form textarea{min-height:85px}.risk-kpi{font-size:30px;font-weight:700}.risk-matrix small{display:block}.risk-form summary{cursor:pointer}.risk-form summary h3{display:inline}
@media print{#kt_aside,#kt_header,#kt_toolbar,.risk-no-print,#kt_footer,#kt_scrolltop{display:none!important}#kt_wrapper{padding:0!important}#kt_content{padding:0!important}.card{break-inside:avoid}.risk-section{break-inside:auto}body{background:white!important}.table-responsive{overflow:visible!important}}
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
    <div><h1 class="fs-2x fw-bolder mb-2">Manajemen Risiko</h1><div class="text-muted">Register, peta risiko dan pemantauan triwulan</div></div>
    <div class="d-flex gap-2 risk-no-print"><a class="btn btn-light-primary" href="<?= esc(site_url('risk/register/peta') . '?' . http_build_query(['year' => $year, 'department' => $department])) ?>">Peta risiko per unit</a><a class="btn btn-light-primary" href="#risk-profile">Tambah risiko</a><button class="btn btn-light" type="button" id="risk-print">Cetak laporan</button></div>
</div>
<div class="mb-5 risk-no-print"><a href="<?= site_url('risk/masterrisk') ?>" class="text-primary">Master risiko</a></div>
<?php foreach (['risk_error' => 'danger', 'risk_success' => 'success'] as $key => $type): ?>
<?php if ($message = session()->getFlashdata($key)): ?><div class="alert alert-<?= $type ?>" role="alert"><?= esc($message) ?></div><?php endif ?>
<?php endforeach ?>
<?php if (!$ready): ?>
<div class="alert alert-warning"><h3>Modul belum diinisialisasi</h3>Tabel manajemen risiko belum tersedia. Jalankan migrasi manajemen risiko sesuai panduan instalasi modul.</div>
<?php else: ?>
<form class="card card-body mb-6 risk-no-print" method="get" action="<?= esc($base) ?>">
    <div class="row g-4 align-items-end">
        <div class="col-md-2"><label class="form-label" for="filter-year">Tahun</label><input id="filter-year" class="form-control" name="year" type="number" min="2000" max="2099" value="<?= $year ?>" required></div>
        <div class="col-md-4"><label class="form-label" for="filter-unit">Unit kerja</label><select id="filter-unit" class="form-select" name="department"><option value="">Semua unit</option><?php foreach ($departments as $d): ?><option value="<?= esc($d['department_id']) ?>" <?= $department === $d['department_id'] ? 'selected' : '' ?>><?= esc($d['department']) ?></option><?php endforeach ?></select></div>
        <div class="col-md-2"><label class="form-label" for="filter-quarter">Triwulan</label><select id="filter-quarter" class="form-select" name="quarter"><?php for ($q = 1; $q <= 4; $q++): ?><option value="<?= $q ?>" <?= $q === $quarter ? 'selected' : '' ?>>Triwulan <?= $q ?></option><?php endfor ?></select></div>
        <div class="col-md-4 d-flex gap-2"><button class="btn btn-primary">Tampilkan</button><a class="btn btn-light-primary" href="<?= esc(site_url('risk/register/export') . '?' . http_build_query(['year' => $year, 'department' => $department, 'quarter' => $quarter])) ?>">Ekspor CSV</a></div>
    </div>
</form>
<div class="row g-4 mb-6">
    <?php foreach (['Risiko terdaftar' => count($risks), 'Perlu penanganan (≥12)' => count($priority), 'Prioritas dipantau TW ' . $quarter => $monitored, 'Prioritas belum dipantau' => count($priority) - $monitored] as $label => $count): ?>
    <div class="col-sm-6 col-xl-3"><div class="card card-body"><div class="text-muted mb-2"><?= esc($label) ?></div><div class="risk-kpi"><?= $count ?></div></div></div>
    <?php endforeach ?>
</div>
<div class="row g-6 mb-6">
    <div class="col-xl-6"><div class="card card-body h-100"><h3 class="mb-4">Peta risiko register · <?= $year ?></h3><div class="table-responsive"><table class="risk-matrix w-100"><caption class="text-muted">Isi sel: skor matriks dan jumlah risiko. Ambang penanganan ≥12.</caption><thead><tr><th scope="col">Kemungkinan ↓ / Dampak →</th><?php for ($i = 1; $i <= 5; $i++): ?><th scope="col" title="<?= esc($impactLabels[$i]) ?>"><?= $i ?></th><?php endfor ?></tr></thead><tbody>
    <?php for ($l = 5; $l >= 1; $l--): ?><tr><th scope="row"><?= $l ?> · <?= esc($likelihoodLabels[$l]) ?></th><?php for ($i = 1; $i <= 5; $i++): $a = RiskMatrix::assess($l, $i); $count = count(array_filter($risks, static function ($r) use ($l, $i) { return (int) $r['likelihood'] === $l && (int) $r['impact_level'] === $i; })); ?><td style="background:<?= $a['hex'] ?>;<?= $a['color'] === 'Biru' ? 'color:white' : '' ?>" title="<?= esc($a['level']) ?>"><strong><?= $a['score'] ?></strong><small><?= $count ?> risiko</small></td><?php endfor ?></tr><?php endfor ?>
    </tbody></table></div></div></div>
    <div class="col-xl-6"><div class="card card-body h-100"><h3 class="mb-4">Pemantauan triwulan <?= $quarter ?></h3><p class="text-muted">Tahun <?= $year ?>. Jumlah berikut hanya mencakup risiko prioritas pada filter aktif.</p>
    <?php $trends = ['Menurun' => 0, 'Tetap' => 0, 'Meningkat' => 0]; $noEvidence = 0; foreach ($priority as $r) { foreach ($r['monitoring'] as $m) { if ((int) $m['quarter'] === $quarter) { $trends[$m['trend']]++; if ($m['evidence_url'] === '') { $noEvidence++; } } } } ?>
    <?php foreach ($trends as $label => $count): ?><div class="d-flex justify-content-between border-bottom py-4"><span>Risiko <?= strtolower($label) ?></span><strong><?= $count ?></strong></div><?php endforeach ?>
    <div class="d-flex justify-content-between py-4"><span>Sudah dipantau, bukti belum dicantumkan</span><strong><?= $noEvidence ?></strong></div><p class="text-muted mt-4 mb-0">Tren membandingkan skor outlook dengan skor awal yang tersimpan pada pemantauan tersebut. Matriks memakai pemetaan dari template Excel.</p></div></div>
</div>
<div class="card mb-6 risk-section" id="risk-list"><div class="card-header"><h3 class="card-title">Register risiko · <?= $year ?></h3></div><div class="card-body"><div class="table-responsive"><table class="table table-row-dashed align-middle"><thead><tr><th>Prioritas</th><th>Unit / Kode</th><th>Kejadian risiko</th><th>Kategori</th><th>LK / LD</th><th>Skor / Level</th><th>Penanganan</th><th class="risk-no-print">Aksi</th></tr></thead><tbody>
<?php if (!$risks): ?><tr><td colspan="8" class="text-center text-muted py-10">Belum ada risiko pada tahun dan unit ini. Tambahkan profil risiko di bawah.</td></tr><?php endif ?>
<?php foreach ($risks as $n => $risk): $a = $risk['assessment']; ?><tr><td><?= $n + 1 ?></td><td><?= esc($risk['department']) ?><div class="text-muted"><?= esc($risk['code']) ?></div></td><td class="risk-text"><?= esc($risk['event']) ?></td><td><?= esc($risk['category']) ?></td><td><?= (int) $risk['likelihood'] ?> / <?= (int) $risk['impact_level'] ?></td><td><span class="risk-score <?= $a['color'] === 'Biru' ? 'risk-blue' : '' ?>" style="background:<?= $a['hex'] ?>"><?= $a['score'] ?> · <?= esc($a['color']) ?></span></td><td><?= $a['requires_action'] ? 'Ya' : 'Tidak' ?></td><td class="risk-no-print"><a class="btn btn-sm btn-light-primary" href="<?= esc($url(['id' => $risk['risk_id']])) ?>#risk-profile">Kelola</a></td></tr><?php endforeach ?>
</tbody></table></div></div></div>

<details class="card card-body risk-form risk-no-print mb-6 risk-section" id="risk-profile" <?= $selected || $failedForm === 'profile' ? 'open' : '' ?>>
<summary class="mb-4"><h3><?= $selected ? 'Ubah profil risiko · ' . $value('code') : 'Tambah profil risiko' ?></h3></summary>
<form method="post" action="<?= esc(site_url('risk/register/save')) ?>" data-risk-assessment>
<?= csrf_field() ?><input type="hidden" name="risk_id" value="<?= $value('risk_id') ?>">
<div class="row g-5">
    <div class="col-md-4"><label for="profile-unit">Unit kerja *</label><select id="profile-unit" class="form-select" name="department_id" required><option value="">Pilih unit</option><?php foreach ($departments as $d): ?><option value="<?= esc($d['department_id']) ?>" <?= ($selected['department_id'] ?? $department) === $d['department_id'] ? 'selected' : '' ?>><?= esc($d['department']) ?></option><?php endforeach ?></select></div>
    <div class="col-md-2"><label for="profile-year">Tahun *</label><input id="profile-year" class="form-control" type="number" name="year" min="2000" max="2099" value="<?= $value('year', $year) ?>" required></div>
    <div class="col-md-3"><label for="profile-code">Kode risiko *</label><input id="profile-code" class="form-control" name="code" maxlength="60" value="<?= $value('code') ?>" placeholder="Contoh: R3.7" required></div>
    <div class="col-md-3"><label for="profile-category">Kategori risiko *</label><select id="profile-category" class="form-select" name="category" required><?php foreach ($categories as $c): ?><option <?= ($selected['category'] ?? '') === $c ? 'selected' : '' ?>><?= esc($c) ?></option><?php endforeach ?></select></div>
<?php foreach (['objective' => 'Sasaran organisasi', 'indicator' => 'Indikator sasaran', 'target' => 'Target sasaran', 'business_process' => 'Proses bisnis', 'event' => 'Kejadian risiko', 'cause' => 'Penyebab', 'consequence' => 'Dampak risiko', 'control' => 'Pengendalian yang sudah dilaksanakan'] as $key => $label): ?>
    <div class="col-md-6"><label for="profile-<?= $key ?>"><?= $label ?> *</label><textarea id="profile-<?= $key ?>" class="form-control" name="<?= $key ?>" maxlength="12000" required><?= $value($key) ?></textarea></div>
<?php endforeach ?>
    <?php foreach (['likelihood' => ['Level kemungkinan', $likelihoodLabels], 'impact_level' => ['Level dampak', $impactLabels]] as $key => $field): ?><div class="col-md-6"><label for="profile-<?= $key ?>"><?= $field[0] ?> *</label><select id="profile-<?= $key ?>" class="form-select" name="<?= $key ?>" required><?php foreach ($field[1] as $n => $label): ?><option value="<?= $n ?>" <?= (int) ($selected[$key] ?? 1) === $n ? 'selected' : '' ?>><?= $n ?> · <?= esc($label) ?></option><?php endforeach ?></select></div><?php endforeach ?>
    <?php foreach (['likelihood_reason' => 'Alasan penilaian kemungkinan', 'impact_reason' => 'Alasan penilaian dampak'] as $key => $label): ?><div class="col-md-6"><label for="profile-<?= $key ?>"><?= $label ?> *</label><textarea id="profile-<?= $key ?>" class="form-control" name="<?= $key ?>" maxlength="12000" required><?= $value($key) ?></textarea></div><?php endforeach ?>
    <div class="col-12"><div class="alert alert-primary mb-0" data-score-preview aria-live="polite">Skor dihitung otomatis berdasarkan matriks penilaian.</div></div>
    <div class="col-12"><h4>Rencana penanganan</h4><p class="text-muted">Aksi, output, target dan waktu wajib untuk risiko dengan skor ≥12.</p></div>
    <?php foreach (['action_plan' => 'Aksi / pengendalian', 'output' => 'Output', 'action_target' => 'Target penanganan', 'implementation' => 'Waktu implementasi'] as $key => $label): ?><div class="col-md-6"><label for="profile-<?= $key ?>"><?= $label ?></label><textarea id="profile-<?= $key ?>" class="form-control" name="<?= $key ?>" maxlength="12000" data-action-required><?= $value($key) ?></textarea></div><?php endforeach ?>
    <div class="col-md-6"><label for="profile-pic">Penanggung jawab / PIC *</label><input id="profile-pic" class="form-control" name="pic" maxlength="200" value="<?= $value('pic') ?>" required></div>
    <div class="col-12 d-flex gap-3"><button class="btn btn-primary">Simpan profil risiko</button><?php if ($selected): ?><a class="btn btn-light" href="<?= esc($url()) ?>#risk-profile">Tambah risiko baru</a><?php endif ?></div>
</div></form></details>

<?php if ($selected): ?>
<div class="card card-body risk-no-print risk-form mb-6 risk-section" id="risk-monitor"><h3 class="mb-3">Pemantauan · <?= $value('code') ?> · <?= $value('year') ?></h3><p class="risk-text mb-5" style="max-width:none"><?= $value('event') ?></p>
<div class="d-flex flex-wrap gap-3 mb-6"><?php for ($q = 1; $q <= 4; $q++): ?><a class="btn <?= $q === $quarter ? 'btn-primary' : 'btn-light' ?>" href="<?= esc($url(['id' => $selected['risk_id'], 'quarter' => $q])) ?>#risk-monitor">Triwulan <?= $q ?></a><?php endfor ?></div>
<form method="post" action="<?= esc(site_url('risk/register/monitor')) ?>" data-risk-assessment>
<?= csrf_field() ?><input type="hidden" name="risk_id" value="<?= $value('risk_id') ?>"><input type="hidden" name="year" value="<?= $value('year') ?>"><input type="hidden" name="quarter" value="<?= $quarter ?>">
<div class="row g-5">
<?php foreach (['action' => ['Aksi / pengendalian', 'action_plan'], 'output' => ['Output', 'output'], 'target' => ['Target', 'action_target'], 'realization' => ['Realisasi', ''], 'implementation' => ['Waktu implementasi', 'implementation']] as $key => $field): ?><div class="col-md-6"><label for="monitor-<?= $key ?>"><?= $field[0] ?> *</label><textarea id="monitor-<?= $key ?>" class="form-control" name="<?= $key ?>" maxlength="12000" required><?= $mv($key, $selected[$field[1]] ?? '') ?></textarea></div><?php endforeach ?>
<div class="col-md-6"><label for="monitor-pic">Penanggung jawab / PIC *</label><input id="monitor-pic" class="form-control" name="pic" maxlength="200" value="<?= $mv('pic', $selected['pic']) ?>" required></div>
<?php foreach (['likelihood' => ['Kemungkinan setelah pengendalian', $likelihoodLabels], 'impact_level' => ['Dampak setelah pengendalian', $impactLabels]] as $key => $field): ?><div class="col-md-6"><label for="monitor-<?= $key ?>"><?= $field[0] ?> *</label><select id="monitor-<?= $key ?>" class="form-select" name="<?= $key ?>"><?php foreach ($field[1] as $n => $label): ?><option value="<?= $n ?>" <?= (int) ($monitor[$key] ?? $selected[$key]) === $n ? 'selected' : '' ?>><?= $n ?> · <?= esc($label) ?></option><?php endforeach ?></select></div><?php endforeach ?>
<div class="col-12"><div class="alert alert-primary mb-0" data-score-preview data-baseline="<?= (int) ($monitor['baseline_score'] ?? $selected['assessment']['score']) ?>" aria-live="polite"></div></div>
<div class="col-md-6"><label for="monitor-evidence">Tautan bukti aksi</label><input id="monitor-evidence" class="form-control" name="evidence_url" type="url" maxlength="2000" placeholder="https://…" value="<?= $mv('evidence_url') ?>"><div class="text-muted mt-2">Tautan dokumen atau foto pada penyimpanan institusi. Bukti kosong tetap ditandai pada ringkasan.</div></div>
<div class="col-md-6"><label for="monitor-notes">Catatan evaluasi</label><textarea id="monitor-notes" class="form-control" name="notes" maxlength="12000"><?= $mv('notes') ?></textarea></div>
<div class="col-12"><button class="btn btn-primary"><?= $monitor ? 'Perbarui' : 'Simpan' ?> pemantauan triwulan <?= $quarter ?></button></div>
</div></form></div>
<?php endif ?>

<div class="card mb-6 risk-section"><div class="card-header"><h3 class="card-title">Laporan pemantauan triwulan <?= $quarter ?> · <?= $year ?></h3></div><div class="card-body"><div class="table-responsive"><table class="table table-row-dashed align-middle"><thead><tr><th>Unit / Risiko prioritas</th><th>Aksi / Output</th><th>Target / Realisasi</th><th>Waktu / PIC</th><th>Skor awal → Outlook</th><th>Tren</th><th>Bukti aksi</th></tr></thead><tbody>
<?php if (!$priority): ?><tr><td colspan="7" class="text-center text-muted py-8">Belum ada risiko prioritas pada filter ini.</td></tr><?php endif ?>
<?php foreach ($priority as $r): $m = null; foreach ($r['monitoring'] as $item) { if ((int) $item['quarter'] === $quarter) { $m = $item; } } ?>
<tr><td class="risk-text"><strong><?= esc($r['department']) ?> · <?= esc($r['code']) ?></strong><br><?= esc($r['event']) ?></td><td class="risk-text"><?= esc($m['action'] ?? $r['action_plan']) ?><hr><?= esc($m['output'] ?? $r['output']) ?></td><td class="risk-text"><strong>Target:</strong> <?= esc($m['target'] ?? $r['action_target']) ?><hr><strong>Realisasi:</strong> <?= esc($m['realization'] ?? 'Belum dipantau') ?></td><td class="risk-text"><?= esc($m['implementation'] ?? $r['implementation']) ?><hr><?= esc($m['pic'] ?? $r['pic']) ?></td><td><?= $m ? (int) $m['baseline_score'] . ' → ' . $m['assessment']['score'] . ' (' . esc($m['assessment']['color']) . ')' : 'Belum dipantau' ?></td><td><?= esc($m['trend'] ?? '—') ?></td><td><?php if ($m && $m['evidence_url'] !== ''): ?><a href="<?= esc($m['evidence_url']) ?>" target="_blank" rel="noopener noreferrer">Lihat bukti</a><?php else: ?><span class="text-muted">Belum dicantumkan</span><?php endif ?></td></tr>
<?php endforeach ?>
</tbody></table></div></div></div>
<?php endif ?>
