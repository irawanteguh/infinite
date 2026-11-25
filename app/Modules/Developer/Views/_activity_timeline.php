<?php $personalTimeline = $personalTimeline ?? false; ?>
<div class="user-activity-timeline<?= $personalTimeline ? ' user-activity-timeline-personal' : '' ?>">
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
                    $activityDateTime = str_replace(' ', 'T', $row['created_at']);
                    $activityTime = substr($row['created_at'], 11, 8);
                    $details = json_decode($row['details'] ?? '', true);
                    $detailId = 'userlog-details-' . (string) $row['activity_id'];
                    $statusColor = $status >= 400 || $row['event'] === 'denied' ? 'danger' : ($status >= 300 ? 'warning' : 'success');
                    ?>
                    <li class="timeline-item pb-5">
                        <?php if (!$personalTimeline): ?>
                            <time class="timeline-label text-muted fs-8" datetime="<?= esc($activityDateTime, 'attr') ?>"><?= esc($activityTime) ?></time>
                        <?php endif ?>
                        <div class="timeline-badge"><i class="bi bi-circle-fill user-activity-bullet user-activity-bullet-<?= $statusColor ?> fs-7" aria-hidden="true"></i></div>
                        <div class="timeline-content min-w-0 ps-3 flex-grow-1">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <?php if ($personalTimeline): ?>
                                    <time class="fw-semibold text-gray-900 text-nowrap" datetime="<?= esc($activityDateTime, 'attr') ?>"><?= esc($activityTime) ?></time>
                                <?php else: ?>
                                    <span class="fw-semibold text-gray-900 text-break"><?= esc($row['user_name'] ?: $row['user_id']) ?></span>
                                <?php endif ?>
                                <span class="badge badge-light-<?= $color ?>"><i class="bi <?= $icon ?> me-1 text-reset" aria-hidden="true"></i><?= esc($label) ?></span>
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
                                    <?php elseif (is_array($details)): ?>
                                        <dl class="mt-3 mb-0">
                                            <?php foreach ($details as $key => $value): ?>
                                                <dt><?= esc((string) $key) ?></dt>
                                                <dd class="text-break"><?= esc(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : (string) $value) ?></dd>
                                            <?php endforeach ?>
                                        </dl>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach ?>
            </ol>
        <?php endforeach ?>
    <?php endif ?>
</div>
