<main class="prem-page pe-dashboard userlog-page">
    <div class="ur-hero app-page-heading">
        <div>
            <h1>Aktivitas pengguna</h1>
            <p>Telusuri login, logout, dan aktivitas aplikasi di organisasi Anda.</p>
        </div>
    </div>

    <aside class="notice d-flex align-items-center bg-light-primary rounded border-primary border border-dashed p-4 mb-5" aria-label="Retensi aktivitas">
        <i class="bi bi-info-circle fs-3 text-primary me-3" aria-hidden="true"></i>
        <span>Halaman ini hanya menampilkan aktivitas <strong><?= \Modules\Developer\Models\UserActivityModel::RETENTION_DAYS ?> hari terakhir</strong>.</span>
    </aside>

    <?php if ($message !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <strong class="d-block"><i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>Gagal memuat</strong>
            <?= esc($message) ?>
        </div>
    <?php endif ?>

    <div class="activity-log row g-4 align-items-start">
        <div class="col-12 col-lg-8 activity-table-column">
            <section class="card border" aria-labelledby="userlog-latest-title">
                <div class="card-body p-5 p-lg-6">
                    <div class="app-table-heading d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h2 id="userlog-latest-title" class="fs-3 fw-bold"><i class="bi bi-calendar3 me-2" aria-hidden="true"></i>Aktivitas harian pengguna</h2>
                            <p class="text-gray-700 mb-0">Aktivitas terakhir setiap pengguna per tanggal.</p>
                        </div>
                        <?php if ($message === '' && $dailyActivities): ?>
                            <div id="userlog-search-control" class="position-relative" hidden>
                                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-gray-600" aria-hidden="true"></i>
                                <label for="userlog-search" class="visually-hidden">Cari aktivitas harian pengguna</label>
                                <input id="userlog-search" type="search" class="form-control form-control-solid form-control-sm ps-9" placeholder="Cari aktivitas…" aria-controls="userlog-daily-table">
                            </div>
                        <?php endif ?>
                    </div>
                    <?php if ($message === '' && !$dailyActivities): ?>
                        <p class="text-muted mb-0">Belum ada aktivitas.</p>
                    <?php elseif ($message === ''): ?>
                        <?= view('Modules\Developer\Views\_daily_activity_table',
                            compact('dailyActivities', 'selectedDate', 'events', 'moduleNames', 'url', 'anchor') + [
                                'headingId' => 'userlog-latest-title',
                                'showUser' => true,
                                'tableId' => 'userlog-daily-table',
                            ]) ?>
                        <p id="userlog-search-empty" class="text-muted mt-3 mb-0" role="status" hidden>Tidak ada aktivitas yang sesuai dengan pencarian.</p>
                    <?php endif ?>
                </div>
            </section>
        </div>
        <div class="col-12 col-lg-4">
            <section class="card border" aria-labelledby="userlog-title">
                <div class="card-body p-5 p-lg-6">
                    <h2 id="userlog-title" class="activity-timeline-title fs-3 fw-bold"><i class="bi bi-clock-history me-2" aria-hidden="true"></i>Timeline aktivitas</h2>
                    <?= view('Modules\Developer\Views\_activity_timeline', compact('rows', 'moduleNames', 'events', 'message')) ?>
                </div>
            </section>
        </div>
    </div>
</main>
