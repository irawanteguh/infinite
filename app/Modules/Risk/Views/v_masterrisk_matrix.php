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
?>
<section class="card card-flush" id="master-matrix">
    <div class="card-header border-0 pt-6 align-items-center gap-3"><div><h3 class="mb-1">Master Matriks Analisis Risiko 5 × 5</h3><span class="text-muted">Pemetaan tingkat kemungkinan dan dampak sesuai matriks Excel</span></div><span class="badge badge-light-primary"><?= count($matrixCells) ?> kombinasi</span></div>
    <div class="card-body pt-5">
        <?php if (!$matrixReady || count($matrixCells) !== 25): ?>
            <div class="alert alert-warning">Master matriks belum lengkap. Diperlukan 25 kombinasi kemungkinan dan dampak.</div>
        <?php else: ?>
        <div class="table-responsive"><table id="datamasterriskmatrix_table" class="table table-bordered align-middle text-center gy-2 min-w-700px w-100" style="table-layout:fixed">
            <caption>Skor berdasarkan pemetaan matriks, bukan perkalian. Ambang penanganan ≥12.</caption>
            <thead class="fw-bolder text-muted bg-light"><tr><th scope="col" rowspan="2" class="bg-light fw-bold text-center">Level Kemungkinan</th><th colspan="5" scope="colgroup" class="bg-light-primary fw-bold text-primary text-center">Level Dampak</th></tr><tr><?php foreach ($matrixImpacts as $i => $label): ?><th scope="col" class="bg-light text-center"><?= $i ?><div class="fw-normal mt-1"><?= esc($label) ?></div></th><?php endforeach ?></tr></thead>
            <tbody class="fw-bold text-gray-600"><?php for ($l = 5; $l >= 1; $l--): ?><tr class="h-75px"><th scope="row" class="bg-light text-center px-4"><?= $l ?> · <?= esc($matrixLikelihoods[$l]) ?></th><?php for ($i = 1; $i <= 5; $i++): $cell = $grid[$l][$i]; $hex = preg_match('/^#[0-9a-fA-F]{6}$/D', $cell['color_hex']) ? $cell['color_hex'] : '#ffffff'; ?><td style="background-color:<?= $hex ?>;color:<?= strtolower($hex) === '#0070c0' ? '#ffffff' : '#182230' ?>" title="<?= esc($cell['risk_level']) ?>"><strong><?= (int) $cell['score'] ?></strong></td><?php endfor ?></tr><?php endfor ?></tbody>
        </table></div>
        <?php endif ?>
    </div>
</section>
