<?php
$displayName = trim((string) ($name ?? session()->get('name'))) ?: 'Pengguna';
$organization = trim((string) ($orgname ?? session()->get('orgname'))) ?: 'Organisasi belum tersedia';
$accountEmail = trim((string) ($email ?? session()->get('email')));
$organizationAddress = trim((string) ($address ?? session()->get('address')));
$organizationEmail = trim((string) ($emailorg ?? session()->get('emailorg')));
$hour = (int) date('G');
if ($hour >= 5 && $hour < 12) { $greeting = 'Selamat pagi'; }
elseif ($hour >= 12 && $hour < 16) { $greeting = 'Selamat siang'; }
elseif ($hour >= 16 && $hour < 19) { $greeting = 'Selamat sore'; }
else { $greeting = 'Selamat malam'; }
$days = [1=>'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
$months = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<main class="welcome-page">
    <section class="welcome-banner app-geometric-banner" aria-label="Selamat datang di INFINITE">
        <div class="welcome-banner-content">
            <span class="welcome-kicker"><span class="welcome-status-dot" aria-hidden="true"></span> INFINITE WORKSPACE</span>
            <p class="welcome-greeting"><?= esc($greeting) ?>,</p>
            <h1 class="welcome-title"><?= esc($displayName) ?></h1>
            <p class="welcome-subtitle">Selamat datang kembali. Mulai aktivitas Anda dan akses informasi yang dibutuhkan dalam satu ruang kerja.</p>
        </div>
        <aside class="welcome-date-panel" aria-label="Tanggal hari ini">
            <span class="welcome-calendar-icon"><i class="bi bi-calendar3" aria-hidden="true"></i></span>
            <span class="welcome-date-label">HARI INI</span>
            <p class="welcome-day"><?= esc($days[(int)date('N')]) ?></p>
            <p class="welcome-date"><?= date('d') ?> <?= esc($months[(int)date('n')]) ?> <?= date('Y') ?></p>
            <span class="welcome-date-note">Semoga hari Anda produktif.</span>
        </aside>
    </section>

    <section class="row g-4 mt-1 mb-4" aria-label="Ringkasan organisasi dan profil">
        <div class="col-12 col-md-6">
            <article class="card welcome-summary-card welcome-summary-organization h-100">
                <div class="card-body p-5 p-lg-6">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <span class="fw-semibold text-gray-700">Organisasi aktif</span>
                        <span class="welcome-summary-icon d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0">
                            <i class="bi bi-hospital fs-3" aria-hidden="true"></i>
                        </span>
                    </div>
                    <h2 class="welcome-summary-title fs-3 fw-bold mb-0"><?= esc($organization) ?></h2>
                    <?php if ($organizationAddress !== '' || $organizationEmail !== ''): ?>
                        <ul class="welcome-summary-details list-unstyled border-top pt-3 mt-3 mb-0 d-flex flex-column gap-2">
                            <?php if ($organizationAddress !== ''): ?>
                                <li class="d-flex align-items-baseline gap-2">
                                    <i class="bi bi-geo-alt flex-shrink-0" aria-hidden="true"></i>
                                    <span><span class="visually-hidden">Alamat </span><?= esc($organizationAddress) ?></span>
                                </li>
                            <?php endif ?>
                            <?php if ($organizationEmail !== ''): ?>
                                <li class="d-flex align-items-baseline gap-2">
                                    <i class="bi bi-envelope flex-shrink-0" aria-hidden="true"></i>
                                    <span><span class="visually-hidden">Email organisasi </span><?= esc($organizationEmail) ?></span>
                                </li>
                            <?php endif ?>
                        </ul>
                    <?php endif ?>
                </div>
            </article>
        </div>
        <div class="col-12 col-md-6">
            <article class="card welcome-summary-card h-100">
                <div class="card-body p-5 p-lg-6">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <span class="fw-semibold text-gray-700">Profil pengguna</span>
                        <span class="welcome-summary-icon d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0">
                            <i class="bi bi-person fs-3" aria-hidden="true"></i>
                        </span>
                    </div>
                    <h2 class="welcome-summary-title fs-3 fw-bold mb-0"><?= esc($displayName) ?></h2>
                    <?php if ($accountEmail !== ''): ?>
                        <p class="welcome-summary-details border-top pt-3 mt-3 mb-0 d-flex align-items-baseline gap-2">
                            <i class="bi bi-envelope flex-shrink-0" aria-hidden="true"></i>
                            <span><span class="visually-hidden">Email pengguna </span><?= esc($accountEmail) ?></span>
                        </p>
                    <?php endif ?>
                </div>
            </article>
        </div>
    </section>
    <aside class="notice d-flex align-items-center bg-light-primary rounded border-primary border border-dashed p-4 mb-4" aria-label="Retensi aktivitas">
        <i class="bi bi-info-circle fs-3 text-primary me-3" aria-hidden="true"></i>
        <span>Riwayat aktivitas hanya menampilkan <strong><?= \Modules\Developer\Models\UserActivityModel::RETENTION_DAYS ?> hari terakhir</strong>.</span>
    </aside>
    <section id="welcome-activity" class="activity-log row g-4 align-items-start" aria-labelledby="welcome-activity-title">
        <div class="col-12 col-lg-8 activity-table-column">
            <div class="card welcome-summary-card app-table-card">
                    <div class="card-header app-table-heading">
                        <div>
                            <h2 id="welcome-activity-title" class="app-table-title">Aktivitas saya</h2>
                            <p class="app-table-description">Riwayat akun Anda di organisasi aktif selama <?= \Modules\Developer\Models\UserActivityModel::RETENTION_DAYS ?> hari terakhir.</p>
                        </div>
                    </div>
                <div class="card-body">
                    <?php if ($activity['message'] !== ''): ?>
                        <div class="alert alert-danger mb-0" role="alert">
                            <strong class="d-block"><i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>Gagal memuat</strong>
                            <?= esc($activity['message']) ?>
                        </div>
                    <?php elseif (!$activity['dailyActivities']): ?>
                        <p class="text-muted mb-0">Belum ada aktivitas.</p>
                    <?php else: ?>
                        <?= view('Modules\Developer\Views\_daily_activity_table', $activity + [
                            'headingId' => 'welcome-activity-title',
                            'showUser' => false,
                        ]) ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <section class="card welcome-summary-card app-table-card" aria-labelledby="welcome-activity-timeline-title">
                    <div class="card-header app-table-heading">
                        <div>
                            <h3 id="welcome-activity-timeline-title" class="activity-timeline-title app-table-title">Timeline aktivitas</h3>
                            <p class="app-table-description">Rincian aktivitas akun Anda pada tanggal yang dipilih.</p>
                        </div>
                    </div>
                <div class="card-body">
                    <?php if ($activity['message'] !== ''): ?>
                        <div class="alert alert-danger mb-0" role="alert">
                            <strong class="d-block"><i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>Gagal memuat</strong>
                            <?= esc($activity['message']) ?>
                        </div>
                    <?php else: ?>
                        <div class="mt-3">
                            <?= view('Modules\Developer\Views\_activity_timeline', $activity + ['personalTimeline' => true]) ?>
                        </div>
                    <?php endif ?>
                </div>
            </section>
        </div>
    </section>
</main>
