<?php $loginUrl = site_url('auth/login'); ?>

<div class="landing-page min-vh-100 d-flex flex-column bg-body">
    <header class="landing-header border-bottom sticky-top" data-section="beranda">
        <nav class="landing-navigation container-xxl d-flex align-items-center justify-content-between py-3 px-4" aria-label="Navigasi utama">
            <a href="#beranda" class="landing-brand d-inline-flex align-items-center text-decoration-none" aria-label="INFINITE beranda">
                <img src="<?= base_url('assets/media/logos/rsudpasarminggu.png') ?>" alt="Logo RSUD Pasar Minggu" class="h-40px w-auto">
            </a>
            <div class="landing-section-links" aria-label="Bagian halaman">
                <a class="landing-section-link is-active" href="#beranda" aria-current="location">Beranda</a>
                <a class="landing-section-link" href="#platform">Platform</a>
                <a class="landing-section-link" href="#mutu">Mutu &amp; PDSA</a>
                <a class="landing-section-link" href="#ekosistem">Ekosistem</a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="<?= esc($loginUrl) ?>" class="landing-login btn btn-primary px-5"><i class="bi bi-box-arrow-in-right me-2"></i>Masuk</a>
            </div>
        </nav>
    </header>

    <main class="flex-grow-1">
        <section id="beranda" class="landing-hero position-relative overflow-hidden py-15 py-lg-20">
            <svg class="landing-medical-pattern" viewBox="0 0 900 520" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <path d="M-20 300 H170 L208 300 L235 258 L275 345 L320 205 L360 300 H500 L535 300 L565 270 L595 330 L630 285 H920" fill="none" stroke="#75e6df" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <g fill="none" stroke="#a6edff" stroke-width="3" opacity=".8"><path d="M720 100v56m-28-28h56"/><path d="M815 205v40m-20-20h40"/><path d="M700 390v42m-21-21h42"/></g>
                <circle cx="760" cy="320" r="92" fill="none" stroke="#a6edff" stroke-width="1.5" stroke-dasharray="5 10"/>
                <circle cx="760" cy="320" r="62" fill="none" stroke="#75e6df" stroke-width="1.5"/>
            </svg>
            <div class="container-xxl px-4 position-relative">
                <div class="row align-items-center gy-12 gx-xl-15">
                    <div class="col-lg-6">
                        <span class="badge bg-white bg-opacity-10 text-info border border-white border-opacity-10 rounded-pill px-4 py-3 mb-6">
                            <i class="bi bi-stars me-2"></i>Integrated Healthcare Data Platform
                        </span>
                        <h1 class="text-white display-4 fw-bolder lh-sm mb-5">
                            Data rumah sakit,<br>
                            <span class="landing-gradient-text">keputusan lebih cerdas.</span>
                        </h1>
                        <p class="text-white text-opacity-75 fs-4 fw-normal mb-8 mw-600px">
                            INFINITE menyatukan informasi klinis, operasional, mutu, organisasi, SDM, dan keuangan dalam satu ruang kerja untuk mendukung layanan yang terus berkembang.
                        </p>
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-10">
                            <a href="<?= esc($loginUrl) ?>" class="btn btn-info btn-lg text-white px-7"><i class="bi bi-arrow-right-circle me-2"></i>Masuk ke INFINITE</a>
                            <a href="tel:+6281288646630" class="btn btn-outline-light btn-lg px-6"><i class="bi bi-headset me-2"></i>Hubungi Admin</a>
                        </div>
                        <div class="d-flex flex-wrap gap-6 gap-xl-8">
                            <div class="d-flex align-items-center text-white text-opacity-75 fs-7"><span class="symbol symbol-30px symbol-circle bg-white bg-opacity-10 me-3"><i class="bi bi-hospital text-info fs-5"></i></span>Terhubung lintas unit</div>
                            <div class="d-flex align-items-center text-white text-opacity-75 fs-7"><span class="symbol symbol-30px symbol-circle bg-white bg-opacity-10 me-3"><i class="bi bi-shield-check text-success fs-5"></i></span>Akses sesuai peran</div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card border-0 shadow-lg overflow-hidden">
                            <div class="card-header border-0 pt-6 px-6">
                                <div class="d-flex align-items-center">
                                    <span class="symbol symbol-45px symbol-circle bg-light-primary me-3"><span class="symbol-label"><i class="bi bi-heart-pulse-fill text-primary fs-3"></i></span></span>
                                    <div>
                                        <h2 class="fs-5 fw-bolder text-gray-900 mb-1">Hospital Performance</h2>
                                        <span class="text-gray-500 fs-8">Ringkasan indikator dan mutu</span>
                                    </div>
                                </div>
                                <span class="badge badge-light-success ms-auto"><i class="bi bi-circle-fill fs-9 me-2"></i>Data terintegrasi</span>
                            </div>
                            <div class="card-body px-6 pt-2 pb-6">
                                <div class="row g-4 mb-5">
                                    <div class="col-6">
                                        <div class="rounded-3 bg-light-primary p-4 h-100">
                                            <div class="d-flex align-items-center justify-content-between mb-3"><span class="text-primary fw-bold fs-8">INDIKATOR MUTU</span><i class="bi bi-activity text-primary fs-4"></i></div>
                                            <div class="fs-2 fw-bolder text-gray-900">Kinerja</div>
                                            <div class="text-gray-600 fs-8 mt-1">Pantau target per unit</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="rounded-3 bg-light-success p-4 h-100">
                                            <div class="d-flex align-items-center justify-content-between mb-3"><span class="text-success fw-bold fs-8">PERBAIKAN</span><i class="bi bi-arrow-repeat text-success fs-4"></i></div>
                                            <div class="fs-2 fw-bolder text-gray-900">PDSA</div>
                                            <div class="text-gray-600 fs-8 mt-1">Rencana sampai tindak lanjut</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="border rounded-3 p-4 mb-5">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div><div class="fw-bolder text-gray-800">Patient safety &amp; quality</div><div class="text-gray-500 fs-8">Visualisasi tren dan capaian</div></div>
                                        <span class="badge badge-light-info">ANALYTICS</span>
                                    </div>
                                    <svg class="w-100" height="112" viewBox="0 0 560 112" role="img" aria-label="Ilustrasi tren indikator mutu">
                                        <defs><linearGradient id="qualityArea" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#009ef7" stop-opacity=".2"/><stop offset="1" stop-color="#009ef7" stop-opacity="0"/></linearGradient></defs>
                                        <path d="M0 84 C48 78 58 50 106 58 S167 84 212 57 S273 31 318 47 S377 68 420 36 S493 42 560 14 L560 112 L0 112Z" fill="url(#qualityArea)"/>
                                        <path d="M0 84 C48 78 58 50 106 58 S167 84 212 57 S273 31 318 47 S377 68 420 36 S493 42 560 14" fill="none" stroke="#009ef7" stroke-width="4" stroke-linecap="round"/>
                                        <path d="M0 99 H560 M0 65 H560 M0 31 H560" stroke="#e4e6ef" stroke-width="1" stroke-dasharray="4 6"/>
                                        <circle cx="420" cy="36" r="5" fill="#20c997" stroke="#fff" stroke-width="3"/><circle cx="560" cy="14" r="5" fill="#20c997" stroke="#fff" stroke-width="3"/>
                                    </svg>
                                    <div class="d-flex justify-content-between text-gray-500 fs-9"><span>Jan</span><span>Mar</span><span>Mei</span><span>Jul</span><span>Sep</span><span>Des</span></div>
                                </div>
                                <div class="rounded-3 bg-light-info p-4 d-flex align-items-center gap-4">
                                    <span class="symbol symbol-45px symbol-circle bg-white"><span class="symbol-label"><i class="bi bi-stars text-info fs-2"></i></span></span>
                                    <div><div class="fw-bolder text-gray-800">Insight berbasis data</div><div class="text-gray-600 fs-8">Temukan pola untuk mendukung evaluasi dan keputusan klinis maupun operasional.</div></div>
                                    <i class="bi bi-cpu text-info fs-2 ms-auto d-none d-sm-block"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="platform" class="landing-perspectives py-15 py-lg-20">
            <div class="container-xxl px-4">
                <div class="text-center mw-750px mx-auto mb-12">
                    <span class="badge badge-light-primary rounded-pill px-4 py-3 mb-4">SATU PLATFORM, BANYAK PERSPEKTIF</span>
                    <h2 class="display-6 fw-bolder text-gray-900 mb-4">Mendukung rumah sakit<br class="d-none d-md-block"> bekerja <span class="landing-perspectives-highlight">lebih terarah</span></h2>
                    <p class="text-gray-600 fs-5 mb-0">Satukan proses, pahami kinerja, dan fokuskan perbaikan berdasarkan kebutuhan layanan dan unit kerja.</p>
                </div>
                <div class="row g-5">
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-bordered h-100 shadow-sm"><div class="card-body p-7"><span class="symbol symbol-55px symbol-circle bg-light-primary mb-6"><span class="symbol-label"><i class="bi bi-bar-chart-line-fill text-primary fs-2"></i></span></span><h3 class="fs-4 fw-bolder text-gray-900 mb-3">Business Intelligence</h3><p class="text-gray-600 mb-0">Ringkas informasi operasional, kinerja, dan finansial untuk mendukung pemantauan manajemen.</p></div></div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-bordered h-100 shadow-sm"><div class="card-body p-7"><span class="symbol symbol-55px symbol-circle bg-light-success mb-6"><span class="symbol-label"><i class="bi bi-clipboard2-pulse-fill text-success fs-2"></i></span></span><h3 class="fs-4 fw-bolder text-gray-900 mb-3">Quality Improvement</h3><p class="text-gray-600 mb-0">Kelola indikator mutu, analisis sebab-akibat, dan dokumentasikan perbaikan melalui PDSA.</p></div></div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-bordered h-100 shadow-sm"><div class="card-body p-7"><span class="symbol symbol-55px symbol-circle bg-light-info mb-6"><span class="symbol-label"><i class="bi bi-cpu-fill text-info fs-2"></i></span></span><h3 class="fs-4 fw-bolder text-gray-900 mb-3">Analitik &amp; teknologi</h3><p class="text-gray-600 mb-0">Ubah data terstruktur menjadi insight yang lebih mudah ditinjau, dievaluasi, dan ditindaklanjuti.</p></div></div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-bordered h-100 shadow-sm"><div class="card-body p-7"><span class="symbol symbol-55px symbol-circle bg-light-danger mb-6"><span class="symbol-label"><i class="bi bi-diagram-3-fill text-danger fs-2"></i></span></span><h3 class="fs-4 fw-bolder text-gray-900 mb-3">Organisasi &amp; SDM</h3><p class="text-gray-600 mb-0">Hubungkan struktur, departemen, dan tanggung jawab unit dengan alur kerja organisasi.</p></div></div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-bordered h-100 shadow-sm"><div class="card-body p-7"><span class="symbol symbol-55px symbol-circle bg-light-warning mb-6"><span class="symbol-label"><i class="bi bi-receipt-cutoff text-warning fs-2"></i></span></span><h3 class="fs-4 fw-bolder text-gray-900 mb-3">Klaim &amp; integrasi</h3><p class="text-gray-600 mb-0">Dukung koordinasi proses klaim dan informasi layanan yang berhubungan dengan BPJS.</p></div></div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-bordered h-100 shadow-sm"><div class="card-body p-7"><span class="symbol symbol-55px symbol-circle bg-light-dark mb-6"><span class="symbol-label"><i class="bi bi-person-lock text-dark fs-2"></i></span></span><h3 class="fs-4 fw-bolder text-gray-900 mb-3">Akses berbasis peran</h3><p class="text-gray-600 mb-0">Pengguna bekerja pada informasi dan fungsi yang sesuai dengan hak aksesnya.</p></div></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="mutu" class="landing-quality py-15 py-lg-20">
            <div class="container-xxl px-4">
                <div class="text-center mw-750px mx-auto mb-12">
                    <span class="badge badge-light-success rounded-pill px-4 py-3 mb-4">QUALITY IMPROVEMENT</span>
                    <h2 class="display-6 fw-bolder text-gray-900 mb-4">Visualisasikan analisis dan <span class="landing-quality-highlight">siklus mutu</span></h2>
                    <p class="text-gray-600 fs-5 mb-0">Dari pemetaan akar penyebab hingga pengujian perubahan, setiap langkah membantu tim belajar dari data layanan.</p>
                </div>
                <div class="row g-6 align-items-stretch">
                    <div class="col-xl-6">
                        <article class="card card-bordered shadow-sm h-100 overflow-hidden">
                            <div class="card-header border-0 pt-7 px-7">
                                <div class="d-flex align-items-center gap-4">
                                    <span class="symbol symbol-45px symbol-circle bg-light-primary"><span class="symbol-label"><i class="bi bi-bezier2 text-primary fs-2"></i></span></span>
                                    <div><h3 class="fs-4 fw-bolder text-gray-900 mb-1">Diagram Fishbone</h3><p class="text-gray-600 fs-7 mb-0">Petakan faktor penyebab dalam kategori 6M</p></div>
                                </div>
                                <span class="badge badge-light-primary ms-auto">ANALISIS AKAR MASALAH</span>
                            </div>
                            <div class="card-body px-4 px-md-7 pt-2 pb-7">
                                <div class="bg-light rounded-3 p-3 p-md-5">
                                    <svg class="w-100" viewBox="0 0 780 390" role="img" aria-label="Diagram fishbone dengan enam kategori penyebab">
                                        <defs><marker id="landingFishArrow" markerWidth="10" markerHeight="10" refX="8" refY="4" orient="auto"><path d="M0 0 L0 8 L9 4Z" fill="#315b80"/></marker></defs>
                                        <line x1="42" y1="195" x2="622" y2="195" stroke="#315b80" stroke-width="5" stroke-linecap="round" marker-end="url(#landingFishArrow)"/>
                                        <rect x="630" y="151" width="132" height="88" rx="16" fill="#fff" stroke="#009ef7" stroke-width="3"/>
                                        <text x="696" y="188" text-anchor="middle" font-size="16" font-weight="700" fill="#181c32">Masalah</text><text x="696" y="210" text-anchor="middle" font-size="12" fill="#7e8299">mutu layanan</text>
                                        <g stroke-width="3" fill="none" stroke-linecap="round">
                                            <path d="M135 195 L180 80" stroke="#009ef7"/><path d="M300 195 L330 80" stroke="#50cd89"/><path d="M465 195 L490 80" stroke="#7239ea"/>
                                            <path d="M135 195 L180 310" stroke="#f1416c"/><path d="M300 195 L330 310" stroke="#ffc700"/><path d="M465 195 L490 310" stroke="#17c653"/>
                                        </g>
                                        <g fill="#fff" stroke-width="3"><circle cx="135" cy="195" r="6" stroke="#009ef7"/><circle cx="300" cy="195" r="6" stroke="#50cd89"/><circle cx="465" cy="195" r="6" stroke="#7239ea"/><circle cx="135" cy="195" r="6" stroke="#f1416c"/><circle cx="300" cy="195" r="6" stroke="#ffc700"/><circle cx="465" cy="195" r="6" stroke="#17c653"/></g>
                                        <g font-family="Arial,sans-serif" font-size="15" font-weight="700">
                                            <text x="148" y="57" fill="#009ef7">Man (SDM)</text><text x="294" y="57" fill="#37a96f">Method</text><text x="443" y="57" fill="#7239ea">Machine</text>
                                            <text x="148" y="342" fill="#d6335b">Material</text><text x="278" y="342" fill="#a88300">Measurement</text><text x="445" y="342" fill="#119447">Environment</text>
                                        </g>
                                        <g stroke="#a1a5b7" stroke-width="2" stroke-linecap="round"><path d="M171 106h68m-55 18h45m90-18h65m-56 18h43m82-18h71m-55 18h42"/><path d="M171 282h62m-48-18h46m94 18h67m-56-18h44m80 18h68m-52-18h42"/></g>
                                    </svg>
                                </div>
                                <div class="d-flex flex-wrap gap-3 mt-5">
                                    <?php foreach ([['Man (SDM)', 'primary'], ['Method', 'success'], ['Machine', 'info'], ['Material', 'danger'], ['Measurement', 'warning'], ['Environment', 'dark']] as $category): ?>
                                        <span class="badge badge-light-<?= esc($category[1]) ?> py-2 px-3"><?= esc($category[0]) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-xl-6">
                        <article class="card card-bordered shadow-sm h-100 overflow-hidden">
                            <div class="card-header border-0 pt-7 px-7">
                                <div class="d-flex align-items-center gap-4">
                                    <span class="symbol symbol-45px symbol-circle bg-light-success"><span class="symbol-label"><i class="bi bi-arrow-repeat text-success fs-2"></i></span></span>
                                    <div><h3 class="fs-4 fw-bolder text-gray-900 mb-1">Siklus PDSA</h3><p class="text-gray-600 fs-7 mb-0">Uji perubahan, pelajari hasil, lalu tindak lanjuti</p></div>
                                </div>
                                <span class="badge badge-light-success ms-auto">PERBAIKAN BERKELANJUTAN</span>
                            </div>
                            <div class="card-body px-4 px-md-7 pt-2 pb-7 d-flex flex-column align-items-center">
                                <svg class="landing-pdsa-diagram" viewBox="0 0 480 480" role="img" aria-label="Diagram siklus PDSA berbentuk lingkaran">
                                    <defs>
                                        <marker id="landingPdsaArrowBlue" markerWidth="11" markerHeight="11" refX="8" refY="4" orient="auto"><path d="M0 0 L0 8 L9 4Z" fill="#009ef7"/></marker>
                                        <marker id="landingPdsaArrowGreen" markerWidth="11" markerHeight="11" refX="8" refY="4" orient="auto"><path d="M0 0 L0 8 L9 4Z" fill="#50cd89"/></marker>
                                        <marker id="landingPdsaArrowYellow" markerWidth="11" markerHeight="11" refX="8" refY="4" orient="auto"><path d="M0 0 L0 8 L9 4Z" fill="#e6b400"/></marker>
                                        <marker id="landingPdsaArrowRed" markerWidth="11" markerHeight="11" refX="8" refY="4" orient="auto"><path d="M0 0 L0 8 L9 4Z" fill="#f1416c"/></marker>
                                    </defs>
                                    <circle cx="240" cy="240" r="202" fill="#f8fbff" stroke="#e4e6ef" stroke-width="2" stroke-dasharray="5 9"/>
                                    <path d="M300 151 A108 108 0 0 1 329 180" fill="none" stroke="#009ef7" stroke-width="5" stroke-linecap="round" marker-end="url(#landingPdsaArrowBlue)"/>
                                    <path d="M329 300 A108 108 0 0 1 300 329" fill="none" stroke="#50cd89" stroke-width="5" stroke-linecap="round" marker-end="url(#landingPdsaArrowGreen)"/>
                                    <path d="M180 329 A108 108 0 0 1 151 300" fill="none" stroke="#e6b400" stroke-width="5" stroke-linecap="round" marker-end="url(#landingPdsaArrowYellow)"/>
                                    <path d="M151 180 A108 108 0 0 1 180 151" fill="none" stroke="#f1416c" stroke-width="5" stroke-linecap="round" marker-end="url(#landingPdsaArrowRed)"/>
                                    <circle cx="240" cy="240" r="72" fill="#fff" stroke="#e4e6ef" stroke-width="3"/>
                                    <text x="240" y="233" text-anchor="middle" font-size="25" font-weight="800" fill="#181c32">PDSA</text><text x="240" y="258" text-anchor="middle" font-size="13" fill="#7e8299">siklus mutu</text>
                                    <g font-family="Arial,sans-serif" text-anchor="middle">
                                        <circle cx="240" cy="78" r="59" fill="#fff" stroke="#009ef7" stroke-width="3"/><text x="240" y="75" font-size="18" font-weight="800" fill="#009ef7">PLAN</text><text x="240" y="96" font-size="11" fill="#7e8299">Rencanakan</text>
                                        <circle cx="402" cy="240" r="59" fill="#fff" stroke="#50cd89" stroke-width="3"/><text x="402" y="237" font-size="18" font-weight="800" fill="#37a96f">DO</text><text x="402" y="258" font-size="11" fill="#7e8299">Uji perubahan</text>
                                        <circle cx="240" cy="402" r="59" fill="#fff" stroke="#ffc700" stroke-width="3"/><text x="240" y="399" font-size="18" font-weight="800" fill="#a88300">STUDY</text><text x="240" y="420" font-size="11" fill="#7e8299">Pelajari hasil</text>
                                        <circle cx="78" cy="240" r="59" fill="#fff" stroke="#f1416c" stroke-width="3"/><text x="78" y="237" font-size="18" font-weight="800" fill="#d6335b">ACT</text><text x="78" y="258" font-size="11" fill="#7e8299">Tindak lanjuti</text>
                                    </g>
                                </svg>
                                <div class="d-flex align-items-center gap-3 text-gray-600 fs-7 mt-2"><i class="bi bi-shield-check text-success fs-3"></i><span>Siklus pembelajaran untuk mutu layanan yang lebih baik.</span></div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section id="ekosistem" class="landing-connect py-12 py-lg-15">
            <div class="container-xxl px-4">
                <div class="landing-connect-banner p-8 p-lg-12 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-6">
                    <div class="d-flex align-items-center gap-5">
                        <span class="symbol symbol-60px symbol-circle bg-white bg-opacity-10"><span class="symbol-label"><i class="bi bi-hospital text-white fs-2"></i></span></span>
                        <div><h2 class="text-white fw-bolder mb-2">Terhubung dalam satu ekosistem layanan</h2><p class="text-white text-opacity-75 mb-0">INFINITE — Integrated Healthcare Data Analytics Platform</p></div>
                    </div>
                    <a href="<?= esc($loginUrl) ?>" class="btn btn-light btn-lg text-primary px-7 flex-shrink-0">Masuk sekarang<i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-top bg-white py-7 mt-auto">
        <div class="container-xxl px-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <span class="text-gray-600 fs-7">&copy; <?= date('Y') ?> INFINITE — Integrated Healthcare Data Analytics Platform</span>
            <a href="tel:+6281288646630" class="text-gray-600 text-hover-primary text-decoration-none fs-7"><i class="bi bi-headset me-2"></i>Hubungi Administrator</a>
        </div>
    </footer>
</div>
<script defer src="<?= base_url('assets/js/auth/landing.js?v=' . filemtime(FCPATH . 'assets/js/auth/landing.js')) ?>"></script>
