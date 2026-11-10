<?php $periodRows = $periodRows ?? []; ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-6">
    <div>
        <h2 class="fw-bolder text-gray-900 mb-1">Perbandingan indikator antarperiode</h2>
        <span class="text-muted">Capaian indikator yang sama pada seluruh periode tersedia</span>
    </div>
</div>

<?php if ($periodRows): ?>
    <?php $indicator = $periodRows[0]; ?>
    <div class="card border-0 border-start border-4 border-primary bg-light-primary mb-6 overflow-hidden">
        <div class="card-body p-6 p-lg-8">
            <div class="d-flex flex-wrap align-items-center gap-5">
                <span class="symbol symbol-60px"><span class="symbol-label bg-white text-primary"><i class="bi bi-graph-up-arrow fs-2x"></i></span></span>
                <div class="flex-grow-1 min-w-200px">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge badge-primary">Indikator <?= esc($indicator->indikator_code ?? '-') ?></span>
                        <span class="badge badge-light"> <?= esc($indicator->department ?? 'Lintas departemen') ?></span>
                    </div>
                    <h3 class="fw-bolder text-gray-900 mb-1"><?= esc($indicator->indikator ?? '-') ?></h3>
                    <span class="text-muted">Ringkasan perbandingan capaian berdasarkan periode pelaporan</span>
                </div>
                <div class="rounded bg-white px-5 py-3 text-center">
                    <span class="text-muted fs-8 fw-bold text-uppercase d-block">Periode tersedia</span>
                    <span class="text-primary fs-2 fw-bolder"><?= count($periodRows) ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-5">
        <div class="col-12">
            <div class="card mb-6">
                <div class="card-header border-0 pt-6"><div class="card-title flex-column"><h3 class="fw-bolder text-gray-900 mb-1">Tren capaian per periode</h3><span class="text-muted fs-7">Rata-rata capaian tahunan indikator</span></div></div>
                <div class="card-body pt-0"><div id="summaryCompareChart"></div></div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-6"><div class="card-title"><h3 class="fw-bolder text-gray-900 mb-0">Rincian setiap periode</h3></div></div>
                <div class="card-body pt-0 table-responsive">
                    <table class="table align-middle table-row-dashed gy-3">
                        <thead><tr class="fw-bolder text-muted bg-light"><th class="rounded-start">Periode</th><th>Target</th><th class="text-center">Bulan terisi</th><th class="text-end">Numerator</th><th class="text-end">Denominator</th><th class="text-center rounded-end">Rata-rata capaian</th></tr></thead>
                        <tbody>
                            <?php foreach ($periodRows as $row): ?>
                                <tr>
                                    <td class="fw-bold"><?= esc($row->tahun) ?></td>
                                    <td><?= esc($row->target ?? '-') ?> <?= esc($row->satuan ?? '') ?></td>
                                    <td class="text-center"><?= esc($row->jumlah_bulan) ?> / 12</td>
                                    <td class="text-end"><?= number_format((float) ($row->total_numerator ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float) ($row->total_denominator ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <?php if ($row->rata_pencapaian === null): ?><span class="text-muted">Belum dinilai</span>
                                        <?php else: ?><span class="badge badge-light-success"><?= number_format((float) $row->rata_pencapaian, 2, ',', '.') ?>%</span><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>window.summaryCompareRows = <?= json_encode($periodRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;</script>
<?php else: ?>
    <div class="card"><div class="card-body text-center py-15"><i class="bi bi-clipboard2-x fs-3x text-muted mb-4"></i><h3 class="text-gray-800">Data indikator tidak ditemukan</h3><p class="text-muted mb-0">Indikator ini tidak memiliki data pada periode yang dapat dibandingkan. Gunakan breadcrumb Summary untuk kembali.</p></div></div>
<?php endif; ?>
