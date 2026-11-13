<?php
$displayName = trim((string) ($name ?? session()->get('name'))) ?: 'Pengguna';
$organization = trim((string) ($orgname ?? session()->get('orgname'))) ?: 'Organisasi belum tersedia';
$accountEmail = trim((string) ($email ?? session()->get('email')));
$hour = (int) date('G');
if ($hour >= 5 && $hour < 12) { $greeting = 'Selamat pagi'; $background = 'morning-city.png'; }
elseif ($hour >= 12 && $hour < 16) { $greeting = 'Selamat siang'; $background = 'sunrise-city.png'; }
elseif ($hour >= 16 && $hour < 19) { $greeting = 'Selamat sore'; $background = 'sunset-city.png'; }
else { $greeting = 'Selamat malam'; $background = 'night-city.png'; }
$days = [1=>'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
$months = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<main class="welcome-page">
    <section class="welcome-banner" style="--welcome-background: url('<?= esc(base_url('assets/media/ambient/' . $background), 'attr') ?>');" aria-label="Selamat datang di INFINITE">
        <div class="welcome-banner-content">
            <span class="welcome-kicker"><span class="welcome-status-dot" aria-hidden="true"></span> INFINITE WORKSPACE</span>
            <p class="welcome-greeting"><?= esc($greeting) ?>,</p>
            <h1 class="welcome-title"><?= esc($displayName) ?></h1>
            <p class="welcome-subtitle">Selamat datang kembali. Mulai aktivitas Anda dan akses informasi yang dibutuhkan dalam satu ruang kerja.</p>
            <div class="welcome-banner-note"><i class="bi bi-grid-1x2" aria-hidden="true"></i><span>Pilih modul melalui menu navigasi untuk memulai.</span></div>
        </div>
        <aside class="welcome-date-panel" aria-label="Tanggal hari ini">
            <span class="welcome-calendar-icon"><i class="bi bi-calendar3" aria-hidden="true"></i></span>
            <span class="welcome-date-label">HARI INI</span>
            <p class="welcome-day"><?= esc($days[(int)date('N')]) ?></p>
            <p class="welcome-date"><?= date('d') ?> <?= esc($months[(int)date('n')]) ?> <?= date('Y') ?></p>
            <span class="welcome-date-note">Semoga hari Anda produktif.</span>
        </aside>
    </section>

    <section class="welcome-overview" aria-label="Informasi workspace">
        <article class="welcome-info-card">
            <div class="welcome-card-header"><span class="welcome-card-icon"><i class="bi bi-hospital" aria-hidden="true"></i></span><span>Organisasi aktif</span></div>
            <h2><?= esc($organization) ?></h2><p>Ruang kerja mengikuti organisasi yang sedang Anda gunakan.</p>
        </article>
        <article class="welcome-info-card">
            <div class="welcome-card-header"><span class="welcome-card-icon welcome-icon-blue"><i class="bi bi-person" aria-hidden="true"></i></span><span>Akun Anda</span></div>
            <h2><?= esc($displayName) ?></h2><p><?= $accountEmail !== '' ? esc($accountEmail) : 'Anda sudah masuk ke workspace INFINITE.' ?></p>
        </article>
        <article class="welcome-info-card welcome-guide-card">
            <div class="welcome-card-header"><span class="welcome-card-icon welcome-icon-slate"><i class="bi bi-compass" aria-hidden="true"></i></span><span>Mulai bekerja</span></div>
            <h2>Temukan modul Anda</h2><p>Gunakan menu navigasi untuk membuka modul sesuai kebutuhan dan hak akses Anda.</p><span class="welcome-mobile-note"><i class="bi bi-list" aria-hidden="true"></i> Pada ponsel, buka tombol menu di header.</span>
        </article>
    </section>
</main>
