<div id="backup-config" data-base-url="<?= esc(site_url('developer/backupdb'), 'attr') ?>" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>" data-csrf-hash="<?= esc(csrf_hash(), 'attr') ?>"></div>
<div class="backup-page">
    <?php include APPPATH . 'Modules/Developer/Pageheader/backupdb.php'; ?>

    <section class="card app-table-card mb-5" aria-labelledby="backup-full-title">
        <div class="card-header app-table-heading">
            <div>
                <h2 id="backup-full-title" class="app-table-title">Backup seluruh database</h2>
                <p class="app-table-description">Salin struktur, data, view, trigger, routine, dan event dari database aktif untuk seluruh organisasi.</p>
            </div>
            <button type="button" id="btnBackupDatabase" class="btn btn-sm btn-primary">
                <i class="bi bi-database-down me-1" aria-hidden="true"></i> Buat backup
            </button>
        </div>
        <div class="card-body">
            <div class="alert alert-info d-flex align-items-start gap-3 mb-0">
                <i class="bi bi-info-circle fs-5" aria-hidden="true"></i>
                <div>
                    <strong>Struktur saja, tanpa data</strong>
                    <ul class="mb-0 mt-1 ps-5">
                        <li><code class="text-reset text-break">dt01_bpjs_pending_klaim</code></li>
                        <li><code class="text-reset text-break">dt01_bpjs_ur_dt</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="card app-table-card mb-5" aria-labelledby="backup-files-title">
        <div class="card-header app-table-heading">
            <div>
                <h2 id="backup-files-title" class="app-table-title">File backup</h2>
                <p class="app-table-description">Unduh atau hapus file backup yang sudah tersimpan.</p>
            </div>
            <div class="app-table-search">
                <input id="searchtable" type="search" class="form-control" placeholder="Cari file backup…" aria-label="Cari file backup">
            </div>
        </div>
        <div class="card-body">
            <div id="backup-status" class="alert alert-danger" role="alert" hidden></div>
            <div class="table-responsive">
                <table class="table table-row-dashed align-middle w-100 fs-8 gy-2" id="backup_table" data-datatable="manual">
                    <thead>
                        <tr>
                            <th scope="col" class="text-end">#</th>
                            <th scope="col">File SQL</th>
                            <th scope="col">Jenis backup</th>
                            <th scope="col" class="text-end">Ukuran</th>
                            <th scope="col">Dibuat</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="resultbackup"></tbody>
                </table>
            </div>
        </div>
    </section>
</div>
