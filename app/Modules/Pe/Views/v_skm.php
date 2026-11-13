<?php
$publicUrl = site_url('pe/skm') . '?' . http_build_query($tenant);
$selecting = request()->getGet('select') === '1';
?>
<main class="prem-page">
    <header class="prem-header"><div class="prem-container"><img src="<?= base_url('assets/media/logos/rsudpasarminggu.png') ?>" alt="RSUD Pasar Minggu" class="prem-logo"></div></header>
    <div class="prem-container prem-main">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= esc($error) ?></div>
        <?php elseif (!$instruments): ?>
            <section class="prem-card prem-empty text-center"><span class="prem-hero-icon"><i class="bi bi-clipboard-heart"></i></span><h1 class="mt-6 mb-4">Kuesioner belum tersedia</h1><p class="text-muted">SKM belum disiapkan untuk rumah sakit ini. Silakan hubungi petugas.</p></section>
        <?php elseif (!$selecting): ?>
            <section class="prem-card prem-welcome">
                <div class="prem-welcome-copy">
                    <span class="prem-eyebrow">SURVEI KEPUASAN MASYARAKAT · PENGALAMAN PELAYANAN PASIEN</span>
                    <h1>Pelayanan yang lebih baik,<br><span>dimulai dari pengalaman Anda.</span></h1>
                    <p class="prem-lead">Ceritakan pengalaman Anda selama menerima pelayanan IGD, rawat jalan, atau rawat inap. Penilaian Anda membantu kami memahami hal yang sudah baik dan yang perlu diperbaiki.</p>
                    <div class="prem-facts"><span><i class="bi bi-list-check"></i> <?= count($instruments) ?> pilihan kuesioner</span><span><i class="bi bi-person-check"></i> Siapkan nomor rekam medis pasien</span></div>
                    <a href="<?= esc($publicUrl . '&select=1', 'attr') ?>" class="btn prem-btn-primary">Mulai mengisi <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
                <aside class="prem-welcome-aside">
                    <span class="prem-hero-icon"><i class="bi bi-chat-heart"></i></span><h2>Pengalaman Anda berarti.</h2><p>Berikan penilaian sesuai pengalaman pelayanan yang Anda terima.</p>
                    <div class="prem-guide"><span>01</span><div>Pilih pelayanan yang ingin Anda nilai.</div></div>
                    <div class="prem-guide"><span>02</span><div>Isi data responden, lalu pilih satu jawaban yang tersedia untuk setiap pernyataan.</div></div>
                    <div class="prem-guide"><span>03</span><div>Periksa jawaban sebelum mengirim.</div></div>
                </aside>
            </section>
        <?php else: ?>
            <div class="prem-heading"><span class="prem-eyebrow">SURVEI KEPUASAN MASYARAKAT</span><h1>Penilaian pengalaman pelayanan pasien</h1><p>Pilih kuesioner sesuai pelayanan yang Anda terima.</p></div>
            <section class="prem-card prom-selection">
                <div class="skm-grid">
                    <?php foreach ($instruments as $item): ?>
                        <a class="skm-choice" href="<?= esc($publicUrl . '&instrument_id=' . rawurlencode($item['instrument_id']), 'attr') ?>">
                            <span class="prem-hero-icon"><i class="bi <?= esc($item['icon'], 'attr') ?>"></i></span>
                            <h2><?= esc($item['name']) ?></h2><p><?= esc($item['description']) ?></p>
                            <div><span><i class="bi bi-list-check"></i> <?= $item['question_count'] ?> pertanyaan</span><span>Isi kuesioner <i class="bi bi-arrow-right"></i></span></div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="prom-selection-footer"><span><i class="bi bi-info-circle"></i> Pilih satu pelayanan. Data kunjungan diisi di halaman berikutnya.</span><a href="<?= esc($publicUrl, 'attr') ?>" class="btn btn-light">Kembali</a></div>
            </section>
        <?php endif; ?>
        <footer class="prem-footer">RSUD Pasar Minggu <span>·</span> Survei Kepuasan Masyarakat</footer>
    </div>
</main>