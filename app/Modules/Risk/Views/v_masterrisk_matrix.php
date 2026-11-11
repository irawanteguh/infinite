<?php
$grid = [];
$matrixLikelihoods = [];
$matrixImpacts = [];
foreach ($matrixCells as $cell) {
    $l = (int) $cell['likelihood'];
    $i = (int) $cell['impact_level'];
    $grid[$l][$i] = $cell;
    $matrixLikelihoods[$l] = $cell['likelihood_label'];
    $matrixImpacts[$i] = $cell['impact_label'];
}
ksort($matrixImpacts);
$matrixComplete = count($matrixCells) === 25;
for ($l = 1; $l <= 5; $l++) {
    for ($i = 1; $i <= 5; $i++) {
        if (!isset($grid[$l][$i])) { $matrixComplete = false; }
    }
}
?>
<section class="card card-flush border shadow-sm" id="master-matrix" style="border-top:3px solid #10b981 !important">
    <div class="card-header border-0 pt-6 align-items-center gap-3"><div><h2 class="fs-5 mb-2"><i class="bi bi-table text-success me-2" aria-hidden="true"></i>Matriks analisis risiko 5 × 5</h2><span class="text-muted fs-7">Pemetaan tingkat kemungkinan dan dampak sesuai matriks referensi.</span></div><span class="badge badge-light-primary"><?= count($matrixCells) ?> kombinasi</span></div>
    <div class="card-body pt-5">
        <?php if (!$matrixReady || !$matrixComplete): ?>
            <div class="alert alert-warning">Master matriks belum lengkap. Diperlukan 25 kombinasi kemungkinan dan dampak.</div>
        <?php else: ?>
        <div class="table-responsive"><table id="datamasterriskmatrix_table" class="table table-bordered align-middle text-center gy-2 min-w-700px w-100" style="table-layout:fixed">
            <caption>Baris menunjukkan kemungkinan, kolom menunjukkan dampak. Skor mengikuti pemetaan matriks referensi.</caption>
            <thead class="fw-bolder text-muted bg-light"><tr><th scope="col" rowspan="2" class="bg-light fw-bold text-center">Level Kemungkinan</th><th colspan="5" scope="colgroup" class="bg-light-primary fw-bold text-primary text-center">Level Dampak</th></tr><tr><?php foreach ($matrixImpacts as $i => $label): ?><th scope="col" class="bg-light text-center"><?= $i ?><div class="fw-normal mt-1"><?= esc($label) ?></div></th><?php endforeach ?></tr></thead>
            <tbody class="fw-bold text-gray-600"><?php for ($l = 5; $l >= 1; $l--): ?><tr class="h-100px"><th scope="row" class="bg-light text-center px-4"><?= $l ?> · <?= esc($matrixLikelihoods[$l]) ?></th><?php for ($i = 1; $i <= 5; $i++): $cell = $grid[$l][$i]; $hex = preg_match('/^#[0-9a-fA-F]{6}$/D', $cell['color_hex']) ? $cell['color_hex'] : '#ffffff'; $brightness = (hexdec(substr($hex, 1, 2)) * 299 + hexdec(substr($hex, 3, 2)) * 587 + hexdec(substr($hex, 5, 2)) * 114) / 1000; ?><td style="background-color:<?= $hex ?>;color:<?= $brightness < 145 ? '#ffffff' : '#182230' ?>" title="<?= esc($cell['risk_level']) ?>"><strong class="fs-2"><?= (int) $cell['score'] ?></strong><div class="fs-8 mt-1"><?= esc($cell['risk_level']) ?></div></td><?php endfor ?></tr><?php endfor ?></tbody>
        </table></div>
        <?php endif ?>
    </div>
</section>
