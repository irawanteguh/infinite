<?php $url = site_url('developer/userlog'); ?>
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
        <p class="alert alert-warning" role="alert"><?= esc($message) ?></p>
    <?php endif ?>

    <div class="row g-5 align-items-start">
        <div class="col-12 col-lg-8">
            <section class="prem-card pe-panel" aria-labelledby="userlog-latest-title">
                <div class="pe-panel-heading">
                    <div>
                        <h2 id="userlog-latest-title"><i class="bi bi-person-lines-fill me-2" aria-hidden="true"></i>Aktivitas terakhir per pengguna</h2>
                        <p>Satu aktivitas terbaru untuk setiap pengguna.</p>
                    </div>
                </div>
                <?php if ($message === '' && !$latestUsers): ?>
                    <p class="text-muted mb-0">Belum ada aktivitas.</p>
                <?php elseif ($latestUsers): ?>
                    <div class="table-responsive" tabindex="0" role="region" aria-labelledby="userlog-latest-title">
                        <table class="table table-row-dashed pe-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Pengguna</th>
                                    <th scope="col">Waktu</th>
                                    <th scope="col">Aktivitas</th>
                                    <th scope="col">Melakukan</th>
                                    <th scope="col">Rute</th>
                                    <th scope="col">HTTP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latestUsers as $row): ?>
                                    <?php [$label, $icon, $color] = $events[$row['event']]; ?>
                                    <tr>
                                        <td class="fw-semibold text-break"><?= esc($row['user_name'] ?: $row['user_id']) ?></td>
                                        <td class="text-nowrap"><time datetime="<?= esc(str_replace(' ', 'T', $row['created_at']), 'attr') ?>"><?= esc(\CodeIgniter\I18n\Time::parse($row['created_at'], null, 'id')->toLocalizedString('d MMM yyyy, HH:mm:ss')) ?></time></td>
                                        <td><span class="badge badge-light-<?= $color ?>"><i class="bi <?= $icon ?> me-1 text-<?= $color ?>" aria-hidden="true"></i><?= esc($label) ?></span></td>
                                        <td class="text-break"><?= esc(\App\Libraries\UserActivity::describe($row, $moduleNames)) ?></td>
                                        <td class="text-break"><?= esc($row['http_method'] . ' ' . $row['route']) ?></td>
                                        <td><?= (int) $row['http_status'] ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif ?>
            </section>
        </div>
        <div class="col-12 col-lg-4">
            <section class="prem-card pe-panel" aria-labelledby="userlog-title">
                <div class="pe-panel-heading">
                    <div>
                        <h2 id="userlog-title"><i class="bi bi-clock-history me-2" aria-hidden="true"></i>Timeline aktivitas</h2>
                        <p>Status HTTP menunjukkan respons aplikasi.</p>
                    </div>
                </div>

                <?php if ($message === '' && !$rows): ?>
                    <p class="text-muted mb-0">Belum ada aktivitas.</p>
                <?php elseif ($rows): ?>
                    <?php
                    $days = [];
                    foreach ($rows as $row) {
                        $days[substr($row['created_at'], 0, 10)][] = $row;
                    }
                    ?>
                    <?php foreach ($days as $day => $entries): ?>
                        <h3 class="fs-6 text-gray-700 mt-5 mb-4">
                            <time datetime="<?= esc($day, 'attr') ?>"><?= esc(\CodeIgniter\I18n\Time::parse($day, null, 'id')->toLocalizedString('EEEE, d MMMM yyyy')) ?></time>
                        </h3>
                        <ol class="timeline-label list-unstyled mb-0">
                            <?php foreach ($entries as $row): ?>
                                <?php
                                [$label, $icon, $color] = $events[$row['event']];
                                $status = (int) $row['http_status'];
                                $details = $row['route'] === '/developer/mastermodules/reorder'
                                    ? json_decode($row['details'] ?? '', true) : null;
                                $detailId = 'userlog-details-' . (string) $row['activity_id'];
                                $statusColor = $status >= 400 || $row['event'] === 'denied' ? 'danger' : ($status >= 300 ? 'warning' : 'success');
                                ?>
                                <li class="timeline-item pb-5">
                                    <time class="timeline-label text-muted fs-8" datetime="<?= esc(str_replace(' ', 'T', $row['created_at']), 'attr') ?>"><?= esc(substr($row['created_at'], 11, 8)) ?></time>
                                    <div class="timeline-badge"><i class="bi bi-circle-fill text-<?= $color ?> fs-7" aria-hidden="true"></i></div>
                                    <div class="timeline-content min-w-0 ps-3 flex-grow-1">
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            <span class="fw-semibold text-gray-900 text-break"><?= esc($row['user_name'] ?: $row['user_id']) ?></span>
                                            <span class="badge badge-light-<?= $color ?>"><i class="bi <?= $icon ?> me-1 text-<?= $color ?>" aria-hidden="true"></i><?= esc($label) ?></span>
                                            <span class="badge badge-light-<?= $statusColor ?>">HTTP <?= $status ?></span>
                                        </div>
                                        <div class="mt-1 text-break"><?= esc(\App\Libraries\UserActivity::describe($row, $moduleNames)) ?></div>
                                        <button type="button" class="btn btn-sm btn-light-primary mt-2"
                                            data-bs-toggle="collapse" data-bs-target="#<?= esc($detailId, 'attr') ?>"
                                            aria-expanded="false" aria-controls="<?= esc($detailId, 'attr') ?>">
                                            Lihat detail
                                        </button>
                                        <div class="collapse" id="<?= esc($detailId, 'attr') ?>">
                                            <div class="mt-3 rounded bg-light p-3">
                                                <dl class="mb-0">
                                                    <dt>ID aktivitas</dt>
                                                    <dd><?= esc($row['activity_id']) ?></dd>
                                                    <dt>ID pengguna</dt>
                                                    <dd class="text-break"><?= esc($row['user_id']) ?></dd>
                                                    <dt>Rute</dt>
                                                    <dd class="text-break mb-0"><?= esc($row['http_method'] . ' ' . $row['route']) ?></dd>
                                                </dl>
                                                <?php if (isset($details['parent'], $details['changes'])): ?>
                                                    <div class="fw-semibold text-break mt-3"><?= esc($details['parent']) ?></div>
                                                    <?php if (!$details['changes']): ?>
                                                        <p class="mb-0 mt-2">Urutan modul tetap sama.</p>
                                                    <?php else: ?>
                                                        <table class="table table-sm align-middle mb-0 mt-2">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">Modul</th>
                                                                    <th scope="col">Sebelum</th>
                                                                    <th scope="col">Sesudah</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($details['changes'] as $change): ?>
                                                                    <tr>
                                                                        <th scope="row" class="fw-normal text-break"><?= esc($change['module']) ?></th>
                                                                        <td><?= (int) $change['before'] ?></td>
                                                                        <td><?= (int) $change['after'] ?></td>
                                                                    </tr>
                                                                <?php endforeach ?>
                                                            </tbody>
                                                        </table>
                                                    <?php endif ?>
                                                <?php endif ?>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach ?>
                        </ol>
                    <?php endforeach ?>
                <?php endif ?>

                <?php if ($message === '' && ($before !== '' || $next !== null)): ?>
                    <nav class="d-flex flex-wrap gap-2 mt-3" aria-label="Halaman timeline">
                        <?php if ($before !== ''): ?>
                            <a class="btn btn-sm btn-light" href="<?= esc($url, 'attr') ?>">Aktivitas terbaru</a>
                        <?php endif ?>
                        <?php if ($next !== null): ?>
                            <a class="btn btn-sm btn-light-primary" href="<?= esc($url . '?' . http_build_query(['before' => $next]), 'attr') ?>">Aktivitas sebelumnya <i class="bi bi-chevron-down ms-1" aria-hidden="true"></i></a>
                        <?php endif ?>
                    </nav>
                <?php endif ?>
            </section>
        </div>
    </div>
</main>
