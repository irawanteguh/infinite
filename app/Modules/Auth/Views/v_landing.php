<?php
$loginUrl = site_url('auth/login');
$adminPhone = 'tel:+6281288646630';
$modules = [
    'qi' => ['Quality Improvement', 'bi-clipboard2-pulse', 'primary', 'Pantau indikator mutu unit, analisis akar masalah dengan Fishbone, dan dokumentasikan perbaikan melalui PDSA.'],
    'pe' => ['Pengalaman Pasien', 'bi-chat-heart', 'info', 'Kelola survei SKM, PREM, dan PROM serta tinjau hasilnya dalam dashboard.'],
    'ur' => ['Utilization Review', 'bi-activity', 'success', 'Tinjau data e-Klaim, biaya, lama rawat, case mix index, serta audit medik dan koding.'],
    'klaim' => ['Klaim', 'bi-receipt-cutoff', 'warning', 'Pantau klaim pending, layanan berulang, dan data slit lamp.'],
    'ca' => ['Skrining Kanker', 'bi-heart-pulse', 'danger', 'Kelola kuesioner skrining dan tinjau hasil pengisian melalui dashboard skrining kanker.'],
    'risk' => ['Manajemen Risiko', 'bi-shield-exclamation', 'warning', 'Kelola master risiko dan dokumentasikan penilaian di register risiko.'],
    'hr' => ['Pengguna & Hak Akses', 'bi-people', 'primary', 'Kelola akun pengguna dan hak akses ke modul Infinite.'],
    'organization' => ['Organisasi', 'bi-diagram-3', 'info', 'Kelola departemen, pengaturan organisasi, dan indikator aplikasi.'],
    'farmasi' => ['Farmasi', 'bi-capsule', 'success', 'Kelola master obat yang digunakan di aplikasi.'],
    'ics' => ['Master MDC', 'bi-table', 'primary', 'Kelola dan lihat data referensi Major Diagnostic Category (MDC).'],
    'nti' => ['Pemantauan Lingkungan', 'bi-thermometer-half', 'info', 'Lihat kondisi perangkat pemantauan lingkungan melalui NTI.'],
    'developer' => ['Administrasi Sistem', 'bi-gear', 'dark', 'Kelola master modul, pantau log aktivitas pengguna, dan buat cadangan basis data.'],
];
?>

<div class="landing-page min-vh-100 d-flex flex-column" lang="id">
    <header class="landing-header sticky-top border-bottom">
        <nav class="container-xl d-flex flex-wrap align-items-center gap-4 py-4" aria-label="Navigasi utama">
            <a href="#beranda" class="landing-brand d-inline-flex align-items-center gap-3 me-auto">
                <img src="<?= base_url('assets/favicon/favicon.ico') ?>" alt="" width="36" height="36">
                <span class="fw-bolder fs-3">INFINITE</span>
            </a>
            <a href="#modul" class="landing-nav-link fw-semibold px-sm-3">Modul</a>
            <a href="#bantuan" class="landing-nav-link fw-semibold px-sm-3">Bantuan</a>
            <a href="<?= esc($loginUrl) ?>" class="btn btn-primary px-4 px-sm-6">
                Masuk<i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
            </a>
        </nav>
    </header>

    <main class="container-xl flex-grow-1 pt-5 pt-lg-8">
        <section id="beranda" class="landing-hero app-geometric-banner p-7 p-md-12 p-xl-16" aria-labelledby="landing-title">
            <div class="row align-items-center gy-8 gx-xl-16">
                <div class="col-lg-7">
                    <p class="landing-hero-label fw-bold mb-4">INFINITE · Platform data rumah sakit</p>
                    <h1 id="landing-title" class="landing-title fw-bolder mb-5">Ruang kerja terpadu untuk rumah sakit</h1>
                    <p class="landing-hero-copy fs-5 mb-7">Dari indikator mutu dan pengalaman pasien hingga skrining, risiko, dan klaim, temukan modul yang mendukung pekerjaan tim Anda.</p>
                    <a href="#modul" class="btn btn-light px-6 fw-bold">
                        Jelajahi modul<i class="bi bi-arrow-down ms-2" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="col-lg-5">
                    <div class="landing-preview p-5 p-sm-6">
                        <h2 class="fs-5 fw-bold mb-4">Modul unggulan</h2>
                        <div class="row g-3">
                            <?php foreach (array_slice($modules, 0, 4, true) as $id => [$name, $icon, $color]): ?>
                                <div class="col-6">
                                    <a href="#modul-<?= esc($id) ?>" class="landing-preview-link h-100 d-flex flex-column align-items-start gap-3 p-3 p-sm-4">
                                        <i class="bi <?= esc($icon) ?> text-<?= esc($color) ?> fs-2" aria-hidden="true"></i>
                                        <span class="fw-semibold"><?= esc($name) ?></span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="modul" class="py-12 py-lg-16" aria-labelledby="landing-module-title">
            <div class="mb-7 mb-lg-9">
                <h2 id="landing-module-title" class="display-6 fw-bolder mb-2">Jelajahi modul Infinite</h2>
                <p class="landing-muted fs-5 mb-0">Modul yang tampil setelah masuk mengikuti hak akses akun Anda.</p>
            </div>
            <div class="row g-4 g-lg-5">
                <?php foreach ($modules as $id => [$name, $icon, $color, $description]): ?>
                    <div class="col-sm-6 col-lg-4">
                        <article id="modul-<?= esc($id) ?>" class="landing-module card h-100">
                            <div class="card-body p-6 p-lg-7">
                                <span class="symbol symbol-45px mb-4">
                                    <span class="symbol-label bg-light-<?= esc($color) ?>">
                                        <i class="bi <?= esc($icon) ?> text-<?= esc($color) ?> fs-2" aria-hidden="true"></i>
                                    </span>
                                </span>
                                <h3 class="fs-4 fw-bold mb-2"><?= esc($name) ?></h3>
                                <p class="landing-muted mb-0"><?= esc($description) ?></p>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="bantuan" class="landing-help d-flex flex-wrap align-items-center justify-content-between gap-5 p-6 p-md-9 mb-12" aria-labelledby="landing-help-title">
            <div>
                <h2 id="landing-help-title" class="fs-2 fw-bolder mb-2">Butuh bantuan akses?</h2>
                <p class="landing-muted mb-0">Hubungi administrator untuk bantuan akun dan hak akses modul.</p>
            </div>
            <a href="<?= esc($adminPhone) ?>" class="btn btn-primary">
                <i class="bi bi-headset me-2" aria-hidden="true"></i>Hubungi Administrator
            </a>
        </section>
    </main>

    <footer class="border-top bg-white py-5">
        <div class="container-xl d-flex flex-wrap align-items-center justify-content-between gap-3">
            <span class="landing-muted">&copy; <?= date('Y') ?> INFINITE</span>
            <img src="<?= base_url('assets/media/logos/rsudpasarminggu.png') ?>" alt="RSUD Pasar Minggu" class="h-30px w-auto">
        </div>
    </footer>
</div>
