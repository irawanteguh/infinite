<?php
$loginUrl = site_url('auth/login');
// Public capability descriptions are grounded in the corresponding module source.
$modules = [
    ['id' => 'qi', 'name' => 'Quality Improvement', 'icon' => 'bi-clipboard2-pulse', 'group' => 'Mutu & pelayanan', 'description' => 'Dari pengukuran hingga perbaikan yang terarah.', 'features' => ['Indikator mutu & periode pelaporan', 'Fishbone, PDSA & rencana tindak lanjut', 'Ringkasan dan laporan capaian']],
    ['id' => 'ur', 'name' => 'Utilization Review', 'icon' => 'bi-graph-up', 'group' => 'Analitik & klaim', 'description' => 'Pahami pola pelayanan, pemanfaatan, dan biaya.', 'features' => ['Analisis e-Klaim & case mix index', 'Lama rawat, severity & perbandingan tarif', 'Audit medik dan audit koding']],
    ['id' => 'pe', 'name' => 'Pengalaman Pasien', 'icon' => 'bi-chat-heart', 'group' => 'Mutu & pelayanan', 'description' => 'Dengarkan pengalaman dan hasil pelayanan melalui SKM, PREM, serta PROM.', 'features' => ['Instrumen & kuesioner', 'Dashboard survei & laporan']],
    ['id' => 'risk', 'name' => 'Manajemen Risiko', 'icon' => 'bi-shield-check', 'group' => 'Mutu & pelayanan', 'description' => 'Dokumentasikan penilaian risiko dan pantau tindak lanjut secara terstruktur.', 'features' => ['Register & matriks risiko', 'Pemantauan & peta risiko']],
    ['id' => 'satusehat', 'name' => 'SATUSEHAT', 'icon' => 'bi-intersect', 'group' => 'Integrasi', 'description' => 'Kelola resource FHIR dan alur integrasi data pelayanan kesehatan.', 'features' => ['Patient, Organization & Location', 'Encounter & alur data kelahiran']],
    ['id' => 'restapi', 'name' => 'API Workspace', 'icon' => 'bi-braces', 'group' => 'Integrasi', 'description' => 'Susun request, kelola environment, dan periksa response dalam satu ruang kerja.', 'features' => ['Collection & environment', 'Signature dan dekripsi BPJS']],
    ['id' => 'klaim', 'name' => 'Pemantauan Klaim', 'icon' => 'bi-receipt', 'group' => 'Analitik & klaim', 'description' => 'Tinjau klaim pending, layanan berulang, dan data slit lamp.', 'features' => ['Analisis pending', 'Penelusuran kasus pelayanan']],
    ['id' => 'ca', 'name' => 'Skrining Kanker', 'icon' => 'bi-heart-pulse', 'group' => 'Mutu & pelayanan', 'description' => 'Kelola instrumen skrining dan tinjau hasil pengisian pada dashboard.', 'features' => ['Kuesioner skrining', 'Ringkasan hasil']],
];
$supportingModules = [
    ['bi-diagram-3', 'Organisasi', 'Departemen, pengaturan organisasi, dan indikator aplikasi.'],
    ['bi-people', 'Pengguna & hak akses', 'Akun pengguna dan pemberian akses modul per organisasi.'],
    ['bi-capsule', 'Farmasi', 'Pengelolaan master obat untuk kebutuhan aplikasi.'],
    ['bi-journal-medical', 'Referensi MDC', 'Data referensi Major Diagnostic Category.'],
    ['bi-thermometer-half', 'Pemantauan lingkungan', 'Pembacaan sensor internal, eksternal, dan digital melalui NTI.'],
    ['bi-gear', 'Administrasi sistem', 'Master modul, log aktivitas pengguna, dan cadangan basis data.'],
];
$ecosystem = [
    ['qi', 'bi-clipboard2-pulse', 'Mutu & perbaikan', 'Indikator mutu, analisis Fishbone, dan siklus PDSA membantu tim meninjau capaian serta mendokumentasikan perbaikan.'],
    ['pe', 'bi-chat-heart', 'Pengalaman pasien', 'SKM, PREM, dan PROM mengolah masukan serta hasil pelayanan menjadi bahan evaluasi tim.'],
    ['ur', 'bi-graph-up', 'Analitik & klaim', 'Utilization Review dan pemantauan klaim membantu menelusuri biaya, lama rawat, severity, dan pola klaim.'],
    ['satusehat', 'bi-intersect', 'Interoperabilitas', 'Workspace SATUSEHAT mendukung resource FHIR, registrasi Encounter, dan alur data kelahiran.'],
    ['restapi', 'bi-braces', 'API & integrasi', 'Collection, environment, request, response, dan dukungan BPJS tersedia bagi tim integrasi.'],
    ['risk', 'bi-shield-check', 'Risiko & tata kelola', 'Register risiko, pengaturan organisasi, hak akses modul, dan log aktivitas mendukung pengelolaan kerja.'],
];
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$demo = [48, 61, 56, 70, 64, 78, 73, 84, 76, 89, 83, 94];
?>
<div id="infinite-landing" class="landing-page" lang="id">
    <a class="lp-skip" href="#konten">Lewati navigasi</a>
    <header class="lp-header">
        <div class="lp-container lp-nav">
            <a class="lp-brand" href="#beranda" aria-label="INFINITE beranda">
                <span>INFINITE<small>Healthcare enterprise platform</small></span>
            </a>
            <button class="lp-menu-toggle" type="button" aria-expanded="false" aria-controls="lp-navigation" aria-label="Buka navigasi"><i class="bi bi-list" aria-hidden="true"></i></button>
            <nav id="lp-navigation" class="lp-navigation" aria-label="Navigasi utama">
                <a href="#platform">Platform</a><a href="#ekosistem">Ekosistem</a><a href="#modul">Modul</a><a href="#integrasi">Integrasi</a>
                <a class="lp-button lp-button-primary" href="<?= esc($loginUrl) ?>">Masuk platform</a>
            </nav>
        </div>
    </header>

    <main id="konten">
        <section id="beranda" class="lp-hero app-geometric-banner" aria-labelledby="lp-title">
            <div class="lp-container lp-hero-grid">
                <div class="lp-hero-copy">
                    <p class="lp-eyebrow">Modern Integrated Healthcare Enterprise Platform</p>
                    <h1 id="lp-title">Teknologi terhubung.<br><em>Pelayanan lebih terarah.</em></h1>
                    <p class="lp-lead">Satukan perspektif mutu, pengalaman pasien, klaim, dan integrasi data dalam satu ekosistem digital rumah sakit.</p>
                    <div class="lp-actions">
                        <a class="lp-button lp-button-white" href="#platform">Jelajahi platform</a>
                        <a class="lp-text-link" href="#ekosistem">Kenali ekosistem Infinite</a>
                    </div>
                    <div class="lp-hero-domains"><span><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i>Mutu pelayanan</span><span><i class="bi bi-bar-chart" aria-hidden="true"></i>Healthcare intelligence</span><span><i class="bi bi-intersect" aria-hidden="true"></i>Interoperabilitas</span></div>
                </div>
                <figure class="lp-product-preview">
                    <div class="lp-preview-top"><span>INFINITE</span><span>Healthcare intelligence</span></div>
                    <div class="lp-preview-body">
                        <div class="lp-preview-heading"><div><span class="lp-caption">Quality overview</span><h2>Melihat gambaran besarnya.</h2></div><i class="bi bi-grid-1x2" aria-hidden="true"></i></div>
                        <div class="lp-preview-metrics"><div><span>Capaian indikator</span><strong>94<small>%</small></strong><span class="lp-preview-note">Contoh capaian bulanan</span></div><div><span>Siklus perbaikan</span><strong>PDSA</strong><span class="lp-preview-note">Plan · Do · Study · Act</span></div></div>
                        <div class="lp-preview-chart" aria-hidden="true">
                            <div class="lp-chart-label"><span>Tren capaian</span><span>Jan–Des</span></div>
                            <svg viewBox="0 0 460 150" fill="none"><path d="M0 25H460M0 70H460M0 115H460" stroke="#dce9e5"/><path d="M5 120L45 100L85 107L125 80L165 89L205 59L245 68L285 41L325 57L365 29L405 40L455 14L455 148H5Z" fill="#eaf6f4"/><path class="lp-chart-line" d="M5 120L45 100L85 107L125 80L165 89L205 59L245 68L285 41L325 57L365 29L405 40L455 14" stroke="#087e82" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <div class="lp-mini-months"><?php foreach ($months as $month): ?><span><?= $month ?></span><?php endforeach ?></div>
                        </div>
                        <div class="lp-preview-bottom"><span><i class="bi bi-check2-circle" aria-hidden="true"></i>Pengukuran</span><span><i class="bi bi-search" aria-hidden="true"></i>Evaluasi</span><span><i class="bi bi-repeat" aria-hidden="true"></i>Perbaikan</span></div>
                    </div>
                    <figcaption>Pratinjau ilustratif · Data simulasi</figcaption>
                </figure>
            </div>
        </section>

        <section id="platform" class="lp-section lp-container" aria-labelledby="lp-platform-title">
            <div class="lp-section-heading lp-reveal"><div><p class="lp-eyebrow">Satu platform, berbagai perspektif</p><h2 id="lp-platform-title">Ruang kerja untuk<br>tantangan rumah sakit.</h2></div><p>Infinite membantu tim mengelola informasi, meninjau kinerja, dan menjalankan integrasi dengan konteks kerja yang terstruktur.</p></div>
            <div class="lp-values">
                <article class="lp-reveal"><i class="bi bi-layers" aria-hidden="true"></i><h3>Informasi dalam konteks</h3><p>Modul pelayanan, mutu, dan analitik tersedia dalam satu platform dengan pengelolaan organisasi.</p></article>
                <article class="lp-reveal"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><h3>Data menjadi pemahaman</h3><p>Telusuri tren indikator, pengalaman pasien, pemanfaatan layanan, dan data klaim.</p></article>
                <article class="lp-reveal"><i class="bi bi-plug" aria-hidden="true"></i><h3>Integrasi lebih terstruktur</h3><p>Kelola resource SATUSEHAT dan kebutuhan pengujian REST API melalui workspace khusus.</p></article>
            </div>
        </section>

        <section id="ekosistem" class="lp-ecosystem-section lp-section" aria-labelledby="lp-ecosystem-title">
            <div class="lp-container">
                <div class="lp-section-heading lp-reveal"><div><p class="lp-eyebrow">Digital healthcare ecosystem</p><h2 id="lp-ecosystem-title">Beragam kebutuhan.<br>Satu titik temu.</h2></div><p>Jelajahi kelompok kapabilitas Infinite. Diagram ini menggambarkan pengelompokan modul dalam platform.</p></div>
                <div class="lp-ecosystem-layout">
                    <div class="lp-ecosystem-map lp-reveal">
                        <svg class="lp-connections" viewBox="0 0 720 410" preserveAspectRatio="none" aria-hidden="true">
                            <?php foreach (['M180 70H280Q360 70 360 205', 'M180 205H360', 'M180 340H280Q360 340 360 205', 'M540 70H440Q360 70 360 205', 'M540 205H360', 'M540 340H440Q360 340 360 205'] as $index => $path): ?>
                                <path data-connection="<?= $index ?>" d="<?= $path ?>"/>
                            <?php endforeach ?>
                        </svg>
                        <div class="lp-ecosystem-center"><strong>INFINITE</strong><span>Healthcare platform</span></div>
                        <?php foreach ($ecosystem as $index => [$id, $icon, $name, $description]): ?>
                            <button type="button" class="lp-ecosystem-node lp-node-<?= $index ?>" data-ecosystem="<?= $index ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="lp-ecosystem-detail">
                                <i class="bi <?= $icon ?>" aria-hidden="true"></i><span><?= esc($name) ?></span>
                            </button>
                        <?php endforeach ?>
                    </div>
                    <div class="lp-ecosystem-detail lp-reveal" id="lp-ecosystem-detail" aria-live="polite">
                        <?php foreach ($ecosystem as $index => [$id, $icon, $name, $description]): ?>
                            <article data-ecosystem-detail="<?= $index ?>" <?= $index ? 'hidden' : '' ?>>
                                <span class="lp-feature-icon"><i class="bi <?= $icon ?>" aria-hidden="true"></i></span>
                                <h3><?= esc($name) ?></h3><p><?= esc($description) ?></p>
                                <a class="lp-inline-link" href="#modul-<?= $id ?>">Kenali modul</a>
                            </article>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </section>

        <section id="perbaikan" class="lp-section lp-container" aria-labelledby="lp-method-title">
            <div class="lp-section-heading lp-reveal"><div><p class="lp-eyebrow">Dari analisis menuju perbaikan</p><h2 id="lp-method-title">Temukan akar masalah.<br>Bangun siklus perubahan.</h2></div><p>Fishbone membantu memetakan kemungkinan penyebab. PDSA membantu tim merencanakan, menguji, dan mengevaluasi perbaikan.</p></div>
            <div class="lp-methods">
                <figure class="lp-method-card lp-reveal" data-method-diagram>
                    <figcaption><h3>Siklus PDSA</h3><p>Pilih tahap untuk mengenali proses perbaikan.</p></figcaption>
                    <div class="lp-pdsa-diagram">
                        <svg viewBox="0 0 420 320" aria-hidden="true">
                            <path id="lp-pdsa-orbit" class="lp-method-flow" d="M210 55C286 55 334 92 334 160C334 228 286 266 210 266C134 266 87 228 87 160C87 92 134 55 210 55Z" fill="none" stroke="#087e82" stroke-width="2"/>
                            <g class="lp-pdsa-traveler">
                                <path d="M-10 -8L10 0L-10 8L-5 0Z" fill="#087e82" stroke="#fff" stroke-width="1.25"/>
                                <animateMotion dur="16s" repeatCount="indefinite" rotate="auto" begin="indefinite"><mpath href="#lp-pdsa-orbit"/></animateMotion>
                            </g>
                        </svg>
                        <div class="lp-method-center"><strong>PDSA</strong><span>Perbaikan berkelanjutan</span></div>
                        <?php foreach ([
                            ['Plan', 'Rencanakan', 'Tentukan masalah, tujuan, dan perubahan yang akan diuji.'],
                            ['Do', 'Laksanakan', 'Jalankan uji perubahan dan dokumentasikan pelaksanaannya.'],
                            ['Study', 'Pelajari', 'Tinjau hasil dan bandingkan dengan tujuan perbaikan.'],
                            ['Act', 'Tindak lanjuti', 'Sesuaikan rencana dan gunakan pembelajaran untuk siklus berikutnya.'],
                        ] as $index => [$stage, $label, $description]): ?>
                            <button type="button" class="lp-pdsa-stage lp-pdsa-stage-<?= $index ?>" data-method-option data-method-description="<?= esc($description, 'attr') ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="lp-pdsa-detail"><strong><?= $stage ?></strong><span><?= $label ?></span></button>
                        <?php endforeach ?>
                    </div>
                    <p id="lp-pdsa-detail" class="lp-method-detail" aria-live="polite">Tentukan masalah, tujuan, dan perubahan yang akan diuji.</p>
                </figure>
                <figure class="lp-method-card lp-reveal" data-method-diagram>
                    <figcaption><h3>Analisis Fishbone</h3><p>Pilih kategori untuk menelusuri kemungkinan penyebab.</p></figcaption>
                    <div class="lp-fishbone-diagram">
                        <svg viewBox="0 0 620 320" preserveAspectRatio="none" aria-hidden="true">
                            <path class="lp-method-flow" d="M22 160H500" fill="none" stroke="#087e82" stroke-width="2"/>
                            <?php foreach ([170, 320, 470] as $index => $end): ?>
                                <path class="lp-fishbone-branch" data-method-branch="<?= $index ?>" d="M<?= $end - 75 ?> 65L<?= $end ?> 160M<?= $end - 46 ?> 100H<?= $end - 85 ?>M<?= $end - 20 ?> 135H<?= $end - 55 ?>" />
                                <path class="lp-fishbone-branch" data-method-branch="<?= $index + 3 ?>" d="M<?= $end - 75 ?> 255L<?= $end ?> 160M<?= $end - 46 ?> 220H<?= $end - 85 ?>M<?= $end - 20 ?> 185H<?= $end - 55 ?>" />
                            <?php endforeach ?>
                        </svg>
                        <div class="lp-fishbone-effect"><strong>Masalah</strong><span>yang dianalisis</span></div>
                        <?php foreach ([
                            ['Manusia', 'Tinjau peran, kompetensi, komunikasi, dan pembagian tanggung jawab tim.'],
                            ['Metode', 'Telusuri prosedur, alur kerja, dan konsistensi pelaksanaannya.'],
                            ['Peralatan', 'Tinjau ketersediaan, kondisi, dan penggunaan peralatan pendukung.'],
                            ['Material', 'Tinjau ketersediaan dan kesesuaian bahan yang digunakan.'],
                            ['Pengukuran', 'Periksa definisi indikator, pencatatan, dan cara pengukuran hasil.'],
                            ['Lingkungan', 'Tinjau kondisi ruang kerja serta faktor lingkungan pelayanan.'],
                        ] as $index => [$label, $description]): ?>
                            <button type="button" class="lp-fishbone-category lp-fishbone-category-<?= $index ?>" data-method-option data-method-description="<?= esc($description, 'attr') ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="lp-fishbone-detail"><?= $label ?></button>
                        <?php endforeach ?>
                    </div>
                    <p id="lp-fishbone-detail" class="lp-method-detail" aria-live="polite">Tinjau peran, kompetensi, komunikasi, dan pembagian tanggung jawab tim.</p>
                </figure>
            </div>
        </section>

        <section id="modul" class="lp-section lp-container" aria-labelledby="lp-module-title">
            <div class="lp-section-heading lp-reveal"><div><p class="lp-eyebrow">Dirancang untuk pekerjaan nyata</p><h2 id="lp-module-title">Kapabilitas yang tumbuh<br>bersama kebutuhan tim.</h2></div><p>Temukan ruang kerja yang sesuai, dari evaluasi pelayanan hingga analisis klaim dan pertukaran data kesehatan.</p></div>
            <div class="lp-module-grid">
                <?php foreach ($modules as $index => $module): ?>
                    <article id="modul-<?= $module['id'] ?>" class="lp-module lp-reveal <?= $index < 2 ? 'lp-module-featured' : '' ?> <?= $index === 0 ? 'lp-module-dark' : '' ?>">
                        <div class="lp-module-top"><span class="lp-feature-icon"><i class="bi <?= $module['icon'] ?>" aria-hidden="true"></i></span><span class="lp-caption"><?= $module['group'] ?></span></div>
                        <h3><?= esc($module['name']) ?></h3><p><?= esc($module['description']) ?></p>
                        <?php if ($index === 0): ?><div class="lp-cycle" aria-label="Siklus PDSA"><span>Plan</span><span>Do</span><span>Study</span><span>Act</span></div><?php endif ?>
                        <?php if ($index === 1): ?><div class="lp-claim-lenses"><span>Biaya</span><span>LOS</span><span>Severity</span><span>Case mix</span></div><?php endif ?>
                        <ul><?php foreach ($module['features'] as $feature): ?><li><?= esc($feature) ?></li><?php endforeach ?></ul>
                    </article>
                <?php endforeach ?>
            </div>
            <section class="lp-supporting lp-reveal" aria-labelledby="lp-supporting-title">
                <div class="lp-supporting-heading"><h3 id="lp-supporting-title">Modul pendukung operasional</h3><span>Organisasi, referensi, dan administrasi</span></div>
                <div class="lp-supporting-grid"><?php foreach ($supportingModules as [$icon, $name, $description]): ?><article><i class="bi <?= $icon ?>" aria-hidden="true"></i><div><h3><?= esc($name) ?></h3><p><?= esc($description) ?></p></div></article><?php endforeach ?></div>
            </section>
            <p class="lp-access-note"><i class="bi bi-lock" aria-hidden="true"></i>Modul yang dapat digunakan mengikuti hak akses akun dan organisasi.</p>
        </section>

        <section id="intelligence" class="lp-intelligence-section lp-section" aria-labelledby="lp-intelligence-title">
            <div class="lp-container lp-intelligence-grid">
                <div class="lp-reveal"><p class="lp-eyebrow">Healthcare intelligence</p><h2 id="lp-intelligence-title">Lihat polanya.<br>Pahami konteksnya.<br><em>Tentukan langkahnya.</em></h2><p>Grafik dan ringkasan membantu tim mengevaluasi kinerja secara berkala, menelusuri pola pelayanan, dan menyusun tindak lanjut.</p>
                    <div class="lp-intelligence-tabs" role="group" aria-label="Contoh analitik">
                        <button type="button" data-insight="quality" aria-pressed="true">Mutu</button><button type="button" data-insight="claims" aria-pressed="false">Klaim</button><button type="button" data-insight="experience" aria-pressed="false">Pengalaman pasien</button>
                    </div>
                </div>
                <figure class="lp-analytics lp-reveal">
                    <figcaption><span id="lp-insight-title">Capaian indikator mutu</span><small>Pratinjau ilustratif · Data simulasi</small></figcaption>
                    <p id="lp-insight-description">Contoh perbandingan capaian bulanan untuk mendukung evaluasi indikator.</p>
                    <div class="lp-bars" role="img" aria-label="Grafik simulasi capaian bulanan Januari hingga Desember.">
                        <?php foreach ($months as $index => $month): ?><div class="lp-bar-column"><div class="lp-bar-track"><span class="lp-bar" data-chart-bar="<?= $index ?>" style="--bar-height:<?= $demo[$index] ?>%"></span></div><span><?= $month ?></span></div><?php endforeach ?>
                    </div>
                    <div class="lp-analytics-footer"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Ilustrasi kapabilitas analitik, menggunakan data sintetis.</span></div>
                </figure>
            </div>
        </section>

        <section id="integrasi" class="lp-section lp-container" aria-labelledby="lp-integration-title">
            <div class="lp-section-heading lp-reveal"><div><p class="lp-eyebrow">Integration & interoperability</p><h2 id="lp-integration-title">Siap berdialog<br>dengan sistem lain.</h2></div><p>Dukung kebutuhan pertukaran data kesehatan dengan resource FHIR, lingkungan API, dan pengujian request yang terkelola.</p></div>
            <div class="lp-integration-grid">
                <article class="lp-integration-feature lp-reveal"><div class="lp-integration-symbol" aria-hidden="true"><i class="bi bi-hospital"></i><span></span><strong>INFINITE</strong><span></span><i class="bi bi-cloud-check"></i></div><h3>Workspace untuk tim integrasi.</h3><p>Mulai dari environment dan susunan request hingga pembacaan response, tim dapat menyiapkan serta memeriksa pertukaran data dari satu ruang kerja.</p><a href="#modul-restapi" class="lp-inline-link">Kenali API Workspace</a></article>
                <div class="lp-integration-list lp-reveal">
                    <article><span class="lp-feature-icon"><i class="bi bi-intersect" aria-hidden="true"></i></span><div><h3>SATUSEHAT · FHIR</h3><p>Patient, Organization, Location, Encounter, serta alur data kelahiran.</p></div></article>
                    <article><span class="lp-feature-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><h3>BPJS API</h3><p>Pembuatan signature, pengiriman request, dan dekripsi response pada mode BPJS.</p></div></article>
                    <article><span class="lp-feature-icon"><i class="bi bi-code-slash" aria-hidden="true"></i></span><div><h3>REST API & ClaimGuard</h3><p>Collection, environment, serta layanan evaluasi aturan klaim melalui API.</p></div></article>
                </div>
            </div>
        </section>

        <section id="tata-kelola" class="lp-governance lp-section" aria-labelledby="lp-governance-title">
            <div class="lp-container lp-governance-grid">
                <div class="lp-reveal"><p class="lp-eyebrow">Pengelolaan yang terarah</p><h2 id="lp-governance-title">Setiap tim bekerja<br>dengan konteks aksesnya.</h2><p>Pengelolaan akun, organisasi, dan akses modul membantu menata penggunaan platform sesuai tanggung jawab pengguna.</p></div>
                <div class="lp-governance-list lp-reveal"><article><i class="bi bi-person-check" aria-hidden="true"></i><div><h3>Autentikasi pengguna</h3><p>Akses platform melalui akun pengguna Infinite.</p></div></article><article><i class="bi bi-building" aria-hidden="true"></i><div><h3>Konteks organisasi</h3><p>Pengaturan organisasi dan departemen untuk mendukung pengelolaan tim.</p></div></article><article><i class="bi bi-key" aria-hidden="true"></i><div><h3>Hak akses & aktivitas</h3><p>Pemberian akses modul serta pencatatan aktivitas pengguna.</p></div></article></div>
            </div>
        </section>

        <section class="lp-container lp-closing-wrap" aria-labelledby="lp-closing-title">
            <div class="lp-closing app-geometric-banner lp-reveal"><div><p class="lp-eyebrow">Infinite · Connected healthcare</p><h2 id="lp-closing-title">Langkah berikutnya<br>dimulai dari sini.</h2><p>Masuk ke ruang kerja Anda dan jelajahi kapabilitas yang tersedia untuk tim.</p></div><a class="lp-button lp-button-white" href="<?= esc($loginUrl) ?>">Akses platform Infinite</a></div>
        </section>
    </main>
    <footer class="lp-footer">
        <div class="lp-container lp-footer-grid">
            <div><a class="lp-brand" href="#beranda"><span>INFINITE</span></a><p>Modern Integrated Healthcare<br>Enterprise Platform.</p></div>
            <nav aria-label="Navigasi footer"><a href="#platform">Platform</a><a href="#ekosistem">Ekosistem</a><a href="#modul">Modul</a><a href="#integrasi">Integrasi</a></nav>
            <div class="lp-footer-identity"><span>RSUD Pasar Minggu</span><small>&copy; <?= date('Y') ?> INFINITE</small></div>
        </div>
    </footer>
</div>
<script src="<?= base_url('assets/js/auth/landing.js?v=' . filemtime(FCPATH . 'assets/js/auth/landing.js')) ?>" defer></script>
