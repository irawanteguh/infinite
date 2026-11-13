<?php
$tenant = $tenant ?? ['group_id' => '', 'org_id' => ''];
$publicUrl = site_url('pe/prom') . '?' . http_build_query($tenant);
?>
<?php if ($error === '' && $instrument && $phase !== null): ?>
    <?= view('Modules\Pe\Views\v_prem', [
        'surveyType' => 'PROM', 'instrument' => $instrument, 'questions' => $questions,
        'error' => '', 'phase' => $phase, 'submissionToken' => $submissionToken, 'revision' => $revision,
    ]) ?>
<?php else: ?>
<main class="prem-page">
    <header class="prem-header"><div class="prem-container"><img src="<?= base_url('assets/media/logos/rsudpasarminggu.png') ?>" alt="RSUD Pasar Minggu" class="prem-logo"></div></header>
    <div class="prem-container prem-main">
        <?php if ($error === '' && !$instrument && request()->getGet('select') !== '1' && $instruments): ?>
            <section class="prem-card prem-welcome">
                <div class="prem-welcome-copy">
                    <span class="prem-eyebrow">PENGALAMAN PELAYANAN PASIEN</span>
                    <h1>Pelayanan yang lebih baik,<br><span>dimulai dari pengalaman Anda.</span></h1>
                    <p class="prem-lead">Ceritakan pengalaman Anda selama menerima pelayanan di rumah sakit. Penilaian Anda membantu kami memahami hal yang sudah baik dan yang perlu diperbaiki.</p>
                    <div class="prem-facts"><span><i class="bi bi-list-check"></i> <?= count($instruments) ?> pilihan kuesioner</span><span><i class="bi bi-person-check"></i> Siapkan nomor rekam medis pasien</span></div>
                    <a href="<?= esc($publicUrl . '&select=1', 'attr') ?>" class="btn prem-btn-primary">Mulai mengisi <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
                <aside class="prem-welcome-aside">
                    <span class="prem-hero-icon"><i class="bi bi-chat-heart"></i></span>
                    <h2>Pengalaman Anda berarti.</h2>
                    <p>Berikan penilaian sesuai pengalaman pelayanan yang Anda terima hari ini.</p>
                    <div class="prem-guide"><span>01</span><div>Pilih satu jawaban untuk setiap pernyataan.</div></div>
                    <div class="prem-guide"><span>02</span><div>Gunakan skala 1 sampai 5 sesuai pengalaman Anda.</div></div>
                    <div class="prem-guide"><span>03</span><div>Periksa jawaban sebelum mengirim.</div></div>
                </aside>
            </section>
        <?php else: ?>
        <div class="prem-heading"><span class="prem-eyebrow">KUESIONER PROM</span><h1>Penilaian kondisi kesehatan pasien</h1><p>Pilih kuesioner yang sesuai dengan perawatan pasien.</p></div>
        <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?= esc($error) ?> <a href="<?= esc($publicUrl, 'attr') ?>" class="fw-bold">Kembali ke pilihan kuesioner</a></div><?php endif; ?>
        <?php if ($instruments): ?>
            <form action="<?= esc(site_url('pe/prom'), 'attr') ?>" method="get" class="prem-card prom-selection">
                <input type="hidden" name="group_id" value="<?= esc($tenant['group_id'], 'attr') ?>">
                <input type="hidden" name="org_id" value="<?= esc($tenant['org_id'], 'attr') ?>">
                <fieldset class="border-0 p-0 m-0">
                    <legend class="float-none fs-4 fw-bold mb-2">1. Pilih jenis PROM</legend>
                    <p class="text-muted fs-7 mb-6">Kuesioner diisi oleh pasien atau pendamping sesuai keterangan responden.</p>
                    <div class="prom-instrument-grid">
                        <?php foreach ($instruments as $item): ?>
                            <label class="prom-instrument-choice">
                                <input type="radio" name="instrument_id" value="<?= esc($item['instrument_id'], 'attr') ?>" required<?= $instrument && $instrument['instrument_id'] === $item['instrument_id'] ? ' checked' : '' ?>>
                                <span class="prom-instrument-body"><span class="d-flex justify-content-between align-items-center"><span class="prem-hero-icon"><i class="bi bi-clipboard2-pulse"></i></span><i class="bi bi-check-circle-fill prom-selected"></i></span><strong class="prom-instrument-name"><?= esc($item['name']) ?></strong><span class="prom-respondent"><?= esc($item['respondent']) ?></span><span class="prom-measurement"><?= esc($item['measurement']) ?></span><span class="prom-question-count"><i class="bi bi-list-check"></i> <?= esc($item['question_count']) ?> pertanyaan</span></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <fieldset class="border-0 p-0 mt-8 mb-0">
                    <legend class="float-none fs-4 fw-bold mb-2">2. Pilih tahap pengukuran</legend>
                    <p class="text-muted fs-7 mb-5">Sesuaikan dengan waktu pengisian yang diarahkan petugas.</p>
                    <div class="prom-phase-grid">
                        <label class="prom-phase-choice"><input type="radio" name="phase" value="T0" required<?= $phase === 'T0' ? ' checked' : '' ?>><span><strong>PRE / T0</strong><span>Sebelum atau awal perawatan</span><i class="bi bi-check-circle-fill prom-selected"></i></span></label>
                        <label class="prom-phase-choice"><input type="radio" name="phase" value="T1" required<?= $phase === 'T1' ? ' checked' : '' ?>><span><strong>POST / T1</strong><span>Saat pulang perawatan</span><i class="bi bi-check-circle-fill prom-selected"></i></span></label>
                    </div>
                </fieldset>
                <div class="prom-selection-footer"><span><i class="bi bi-info-circle"></i> Pilih jenis PROM dan tahap pengukuran untuk melanjutkan.</span><button type="submit" class="btn prem-btn-primary">Lanjutkan <i class="bi bi-arrow-right ms-2"></i></button></div>
            </form>
        <?php else: ?>
            <section class="prem-card prem-empty text-center"><h2>Belum ada kuesioner PROM aktif</h2><p class="text-muted mb-0">Silakan hubungi petugas untuk informasi lebih lanjut.</p></section>
        <?php endif; ?>
        <?php endif; ?>
        <footer class="prem-footer">RSUD Pasar Minggu <span>·</span> Patient-Reported Outcome Measures</footer>
    </div>
</main>
<?php endif; ?>
