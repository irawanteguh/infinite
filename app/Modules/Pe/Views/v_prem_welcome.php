<?php
$startUrl = site_url('pe/prem') . '?' . http_build_query($tenant + [
    'instrument_id' => $instrument['instrument_id'], 'start' => '1',
]);
?>
<main class="prem-page">
    <header class="prem-header"><div class="prem-container"><img src="<?= base_url('assets/media/logos/rsudpasarminggu.png') ?>" alt="RSUD Pasar Minggu" class="prem-logo"></div></header>
    <div class="prem-container prem-main">
        <section class="prem-card prem-welcome">
            <div class="prem-welcome-copy">
                <span class="prem-eyebrow">PENGALAMAN PELAYANAN PASIEN</span>
                <h1>Pelayanan yang lebih baik,<br><span>dimulai dari pengalaman Anda.</span></h1>
                <p class="prem-lead">Ceritakan pengalaman Anda selama menerima pelayanan di rumah sakit. Penilaian Anda membantu kami memahami hal yang sudah baik dan yang perlu diperbaiki.</p>
                <div class="prem-facts"><span><i class="bi bi-list-check"></i> <?= count($questions) ?> pertanyaan</span><span><i class="bi bi-person-check"></i> Siapkan nomor rekam medis pasien</span></div>
                <a href="<?= esc($startUrl, 'attr') ?>" class="btn prem-btn-primary">Mulai mengisi <i class="bi bi-arrow-right ms-2"></i></a>
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
        <footer class="prem-footer">RSUD Pasar Minggu <span>·</span> Patient-Reported Experience Measures</footer>
    </div>
</main>