<main class="os-page">
    <header class="app-geometric-banner rounded-4 p-6 p-lg-8 mb-7">
        <h1 class="mb-2">Operasional Sistem</h1>
        <p class="mb-0">Kelola penyiapan dan pemeliharaan layanan Infinite.</p>
    </header>

    <?php if ($message = session()->getFlashdata('os_success')): ?>
        <div class="alert alert-success" role="status"><?= esc($message) ?></div>
    <?php endif ?>
    <?php if ($message = session()->getFlashdata('os_error')): ?>
        <div class="alert alert-warning" role="alert"><?= esc($message) ?></div>
    <?php endif ?>

    <section class="card mb-6" aria-labelledby="os-modules-title">
        <div class="card-body w-100">
            <div class="os-card-heading">
                <i class="bi bi-arrow-repeat os-action-icon" aria-hidden="true"></i>
                <div class="os-action-description">
                    <h2 class="fs-4 mb-1" id="os-modules-title">Sinkronisasi Master Modules</h2>
                    <p class="text-muted mb-0">Export katalog dari development, lalu impor dan periksa preview di production.</p>
                </div>
            </div>
            <p class="mb-0">Katalog menu berlaku untuk seluruh organisasi. ID dan status aktif modul yang sudah ada di target dipertahankan. Modul khusus target tetap tersedia.</p>
            <?php if ($canSyncModules): ?>
                <div class="os-module-actions">
                    <form id="os-modules-import" method="post" enctype="multipart/form-data" action="<?= esc(site_url('developer/os/previewmodules'), 'attr') ?>">
                        <?= csrf_field() ?>
                        <div class="flex-grow-1">
                            <label for="os-module-file" class="form-label">File katalog JSON · maksimal 2 MB</label>
                            <input id="os-module-file" class="form-control" type="file" name="module_file" accept=".json,application/json" required aria-describedby="os-module-file-help">
                        </div>
                        <button type="submit" class="btn btn-primary">Import & Preview</button>
                        <a class="btn btn-light-primary" href="<?= esc(site_url('developer/os/exportmodules'), 'attr') ?>"><i class="bi bi-download me-1" aria-hidden="true"></i>Export Master Modules</a>
                    </form>
                </div>
                <p id="os-module-file-help" class="text-muted mb-0">Gunakan file hasil export Infinite. Modul baru yang controllernya belum tersedia akan disimpan nonaktif dan ditandai pada preview.</p>
                <?php if ($modulePreview): ?>
                    <button type="button" class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#os-module-preview">Lihat Preview Sinkronisasi</button>
                <?php endif ?>
            <?php else: ?>
                <div class="alert alert-warning w-100 mb-0" role="status">Akses Operasional Sistem belum tersedia atau belum dapat diperiksa. Muat ulang halaman atau hubungi pengelola akses.</div>
            <?php endif ?>
        </div>
    </section>

    <?php if ($modulePreview): ?>
        <?php
        $modulePlan = $modulePreview['plan'];
        $moduleSummary = $modulePlan['summary'];
        $moduleFieldLabels = ['modules_name' => 'Nama', 'modules_header_id' => 'ID induk', 'package' => 'Package', 'def_controller' => 'Controller', 'parent' => 'Jenis menu', 'icon' => 'Ikon', 'urut' => 'Urutan', 'active' => 'Aktif', 'status' => 'Status', 'version' => 'Versi'];
        ?>
        <div class="modal fade" id="os-module-preview" tabindex="-1" aria-labelledby="os-module-preview-title" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h2 class="modal-title fs-4" id="os-module-preview-title">Preview sinkronisasi</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
              </div>
              <div class="modal-body">
        <section class="card app-table-card">
            <div class="card-header app-table-heading">
                <div><h3 class="app-table-title">Daftar perubahan modul</h3><p class="app-table-description">Periksa perubahan metadata sebelum diterapkan pada katalog target.</p></div>
                <div class="app-table-search"><input id="os-module-search" type="search" class="form-control" placeholder="Cari modul…" aria-label="Cari modul pada preview"></div>
            </div>
            <div class="card-body w-100">
                <div class="d-flex flex-wrap gap-3" role="status">
                    <span class="badge badge-light-success">Baru <?= $moduleSummary['add'] ?></span>
                    <span class="badge badge-light-warning">Diperbarui <?= $moduleSummary['update'] ?></span>
                    <span class="badge badge-light-primary">Sesuai <?= $moduleSummary['unchanged'] ?></span>
                    <span class="badge badge-light">Khusus target <?= $moduleSummary['kept'] ?></span>
                </div>
                <?php if ($moduleSummary['activePreserved']): ?>
                    <div class="alert alert-info w-100 mb-0"><?= $moduleSummary['activePreserved'] ?> modul memiliki perbedaan status aktif. Sinkronisasi menggunakan status aktif target.</div>
                <?php endif ?>
                <div class="table-responsive w-100">
                    <table id="os-module-table" class="table table-row-dashed align-middle w-100 fs-8 gy-2" data-datatable="manual">
                        <thead><tr><th>Modul</th><th>Route</th><th>Hasil</th><th>Perubahan</th></tr></thead>
                        <tbody>
                        <?php foreach ($modulePlan['entries'] as $entry): ?>
                            <?php [$actionLabel, $actionColor] = ['add' => ['Tambah', 'success'], 'update' => ['Perbarui', 'warning'], 'unchanged' => ['Sesuai', 'primary']][$entry['action']]; ?>
                            <tr>
                                <td class="text-break"><?= esc($entry['name']) ?><br><code><?= esc($entry['id']) ?></code></td>
                                <td class="text-break"><?= esc($entry['route'] ?: 'Folder menu') ?></td>
                                <td><span class="badge badge-light-<?= $actionColor ?>"><?= $actionLabel ?></span>
                                    <?php if (!empty($entry['warning'])): ?>
                                        <p class="text-warning mt-2 mb-0"><?= esc($entry['warning']) ?></p>
                                    <?php endif ?>
                                </td>
                                <td>
                                    <?php if ($entry['changes']): ?>
                                        <details class="os-module-changes"><summary><?= count($entry['changes']) ?> kolom</summary><dl class="mt-2 mb-0">
                                            <?php foreach ($entry['changes'] as $field => $change): ?>
                                                <dt><?= esc($moduleFieldLabels[$field] ?? $field) ?></dt>
                                                <dd class="text-break"><span class="text-muted"><?= esc((string) ($change['before'] ?? '—')) ?></span> → <?= esc((string) ($change['after'] ?? '—')) ?></dd>
                                            <?php endforeach ?>
                                        </dl></details>
                                    <?php else: ?>Sudah sesuai<?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <form id="os-modules-apply" method="post" action="<?= esc(site_url('developer/os/applymodules'), 'attr') ?>" class="align-self-end">
                    <?= csrf_field() ?>
                    <input type="hidden" name="preview_token" value="<?= esc($modulePreview['token'], 'attr') ?>">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" <?= $moduleSummary['add'] + $moduleSummary['update'] === 0 ? 'disabled' : '' ?>>Terapkan Sinkronisasi</button>
                </form>
                <p class="text-muted mb-0">Preview berlaku 15 menit. Perubahan katalog target setelah preview memerlukan pemeriksaan ulang.</p>
            </div>
        </section>
              </div>
            </div>
          </div>
        </div>
    <?php endif ?>

    <section aria-label="Pemeliharaan aktivitas pengguna">
    <div class="row g-5">
        <div class="col-12 col-lg-6">
            <section class="card card-bordered h-100" aria-labelledby="os-setup-title">
                <div class="card-body">
                    <div class="os-card-heading">
                    <div class="os-action-description">
                        <h3 class="fs-4 mb-1" id="os-setup-title">Siapkan tabel aktivitas</h3>
                        <p class="text-muted mb-2">Buat atau lengkapi tabel riwayat aktivitas untuk sistem.</p>
                    </div>
                    <?php if ($tableReady === true): ?>
                        <span class="badge os-status os-status-ready" role="status"><i class="bi bi-check-circle me-2" aria-hidden="true"></i>Sudah siap</span>
                    <?php elseif ($tableReady === false): ?>
                        <span class="badge os-status os-status-pending" role="status">Perlu penyiapan</span>
                    <?php else: ?>
                        <span class="badge os-status os-status-error" role="alert">Status tabel belum dapat diperiksa. Muat ulang halaman.</span>
                    <?php endif ?>
                    </div>
                    <p class="os-table-name mb-0">Tabel <button class="btn btn-link p-0" type="button" data-bs-toggle="modal" data-bs-target="#os-table-schema"><code>dt01_gen_user_activity</code></button></p>
                    <div class="modal fade" id="os-table-schema" tabindex="-1" aria-labelledby="os-schema-title" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title fs-4" id="os-schema-title">Struktur kolom data</h2>
                                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                    <div class="table-responsive w-100">
                        <table class="table table-row-dashed align-middle w-100 mb-0 fs-8 gy-2" data-datatable="false" aria-label="Kolom tabel aktivitas pengguna">
                            <thead>
                                <tr><th scope="col">Nama kolom</th><th scope="col">Tipe data</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ([
                                    'activity_id' => 'BIGINT UNSIGNED · AUTO_INCREMENT',
                                    'group_id' => 'VARCHAR(64)',
                                    'org_id' => 'VARCHAR(64)',
                                    'user_id' => 'VARCHAR(64)',
                                    'user_name' => 'VARCHAR(255)',
                                    'event' => 'VARCHAR(20)',
                                    'route' => 'VARCHAR(200)',
                                    'http_method' => 'VARCHAR(16)',
                                    'http_status' => 'SMALLINT UNSIGNED',
                                    'created_at' => 'DATETIME',
                                    'details' => 'MEDIUMTEXT · NULL',
                                ] as $column => $type): ?>
                                    <tr>
                                        <td><code><?= esc($column) ?></code></td>
                                        <td><?= esc($type) ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form class="align-self-end" data-setup-table method="post" action="<?= esc(site_url('developer/os/setup'), 'attr') ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-primary" type="submit" <?= $tableReady !== false ? 'disabled' : '' ?>><i class="bi bi-database-gear me-1" aria-hidden="true"></i>Siapkan tabel</button>
                    </form>
                </div>
            </section>
        </div>
        <div class="col-12">
            <section class="card app-table-card h-100" aria-labelledby="os-prune-title">
                    <div class="card-header os-card-heading app-table-heading">
                        <div class="os-action-description">
                            <h3 class="fs-4 mb-1" id="os-prune-title">Bersihkan log kedaluwarsa</h3>
                            <p class="text-muted mb-2">Periksa log organisasi aktif yang berusia lebih dari <?= esc((string) $retentionDays) ?> hari.</p>
                        </div>
                        <label class="app-table-search">
                            <input id="os-log-search" type="search" class="form-control form-control-solid" placeholder="Cari log…" aria-label="Cari log aktivitas">
                        </label>
                    </div>
                <div class="card-body">
                    <?php if ($preview !== null): ?>
                        <p role="status"><strong><?= esc((string) $preview['count']) ?></strong> log kedaluwarsa.</p>
                    <?php endif ?>
                    <div class="table-responsive w-100">
                        <table id="os-log-table" class="table table-row-dashed align-middle w-100 mb-0 fs-8 gy-2" data-datatable="manual" aria-label="Log aktivitas organisasi aktif">
                            <thead>
                                <tr>
                                    <th scope="col">Waktu</th>
                                    <th scope="col">Pengguna</th>
                                    <th scope="col">Organisasi</th>
                                    <th scope="col">Aktivitas</th>
                                    <th scope="col">Rute</th>
                                    <th scope="col" class="text-end pe-4">Status HTTP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td class="text-nowrap"><?= esc($log['created_at']) ?></td>
                                        <td><?= esc($log['user_name']) ?></td>
                                        <td><?= esc($log['org_name'] ?? '—') ?></td>
                                        <td><?= esc(\Modules\Developer\Models\UserActivityModel::EVENTS[$log['event']][0] ?? $log['event']) ?></td>
                                        <td><?= esc($log['http_method'] . ' ' . $log['route']) ?></td>
                                        <td class="text-end pe-4"><?= esc((string) $log['http_status']) ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end align-self-end gap-2">
                        <button class="btn btn-light-danger" type="button" data-bs-toggle="modal" data-bs-target="#os-prune-expired">Hapus log lebih dari 30 hari</button>
                        <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#os-prune-all">Hapus semua log</button>
                    </div>
                </div>
            </section>
        </div>
    </div>
    </section>

    <?php foreach (['expired' => 'Hapus log lebih dari 30 hari', 'all' => 'Hapus semua log'] as $mode => $label): ?>
        <div class="modal fade" id="os-prune-<?= esc($mode) ?>" tabindex="-1" aria-labelledby="os-prune-title-<?= esc($mode) ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="post" action="<?= esc(site_url('developer/os/prune'), 'attr') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="mode" value="<?= esc($mode, 'attr') ?>">
                    <div class="modal-header">
                        <h2 class="modal-title fs-4" id="os-prune-title-<?= esc($mode) ?>"><?= esc($label) ?></h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label" for="os-org-<?= esc($mode) ?>">Organisasi</label>
                        <select class="form-select form-select-solid" name="org_id" id="os-org-<?= esc($mode) ?>"
                            data-control="select2" data-dropdown-parent="#os-prune-<?= esc($mode) ?>"
                            data-placeholder="Pilih organisasi" data-hide-search="true" required>
                            <option value="">Pilih organisasi</option>
                            <option value="<?= esc((string) session('orgid'), 'attr') ?>"><?= esc((string) session('orgname')) ?></option>
                        </select>
                        <p class="text-muted mt-4 mb-0"><?= $mode === 'expired' ? 'Log yang berusia lebih dari 30 hari pada organisasi terpilih akan dihapus permanen.' : 'Seluruh log pada organisasi terpilih akan dihapus permanen.' ?></p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-danger" type="submit">Hapus log</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach ?>
</main>
