<main class="prem-page" id="prem-app">
    <header class="prem-header"><div class="prem-container"><img src="<?= base_url('assets/media/logos/rsudpasarminggu.png') ?>" alt="RSUD Pasar Minggu" class="prem-logo"></div></header>
    <div class="prem-container prem-main">
        <section class="prem-card prem-empty text-center">
            <span class="prem-success-icon"><i class="bi bi-check-lg"></i></span>
            <span class="prem-eyebrow d-block mt-7">SKM DAN PREM SELESAI</span>
            <h1 class="mt-3 mb-4">Terima kasih atas penilaian Anda.</h1>
            <p class="prem-lead mx-auto">Jawaban SKM dan PREM RSUD Pasar Minggu sudah tersimpan.</p>
            <a class="btn prem-btn-primary mt-5" href="<?= esc(site_url('pe/skm') . '?' . http_build_query($tenant), 'attr') ?>">Kembali ke halaman awal</a>
        </section>
    </div>
</main>