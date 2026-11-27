<div class="table-responsive" tabindex="0" role="region" aria-labelledby="<?= esc($headingId, 'attr') ?>">
    <table<?= isset($tableId) ? ' id="' . esc($tableId, 'attr') . '"' : '' ?> class="table table-row-dashed align-middle mb-0 fs-8 gy-2" data-datatable="false">
        <thead>
            <tr>
                <th scope="col">Tanggal aktivitas</th>
                <th scope="col">Aktivitas terakhir</th>
                <th scope="col">Jam</th>
                <?php if ($showUser): ?>
                    <th scope="col">Pengguna</th>
                <?php endif ?>
                <th scope="col">Melakukan</th>
                <th scope="col" class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dailyActivities as $latest): ?>
                <?php
                $date = $latest['activity_date'];
                $selected = $date === $selectedDate;
                [$label, $icon, $color] = $events[$latest['event']];
                ?>
                <tr<?= $selected ? ' class="activity-date-selected"' : '' ?>>
                    <td class="text-nowrap">
                        <time datetime="<?= esc($date, 'attr') ?>"><?= esc(\CodeIgniter\I18n\Time::parse($date, null, 'id')->toLocalizedString('EEEE, d MMMM yyyy')) ?></time>
                    </td>
                    <td>
                        <span class="badge badge-light-<?= $color ?>"><i class="bi <?= $icon ?> me-1 text-reset" aria-hidden="true"></i><?= esc($label) ?></span>
                    </td>
                    <td class="text-nowrap">
                        <time datetime="<?= esc(str_replace(' ', 'T', $latest['created_at']), 'attr') ?>"><?= esc(substr($latest['created_at'], 11, 8)) ?></time>
                    </td>
                    <?php if ($showUser): ?>
                        <td class="text-break"><?= esc($latest['user_name'] ?: $latest['user_id']) ?></td>
                    <?php endif ?>
                    <td class="text-break"><?= esc(\App\Libraries\UserActivity::describe($latest, $moduleNames)) ?></td>
                    <td class="text-end ps-4 pe-4">
                        <a class="btn btn-sm text-nowrap <?= $selected ? 'btn-primary' : 'btn-light-primary' ?>"
                            href="<?= esc($url . '?' . http_build_query(['date' => $date]) . $anchor, 'attr') ?>"<?= $selected ? ' aria-current="true"' : '' ?>>
                            View Timeline
                        </a>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
