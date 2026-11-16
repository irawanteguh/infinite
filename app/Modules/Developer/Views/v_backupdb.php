<div id="backup-config" data-base-url="<?= esc(site_url('developer/backupdb'), 'attr') ?>" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>" data-csrf-hash="<?= esc(csrf_hash(), 'attr') ?>"></div>
<div class="prem-page backup-page">
    <?php include APPPATH . 'Modules/Developer/Pageheader/backupdb.php'; ?>
    <div class="backup-options">
        <section class="prem-card backup-option" aria-labelledby="backup-full-title">
            <div class="backup-option-heading"><span class="backup-icon"><i class="bi bi-database" aria-hidden="true"></i></span><span class="backup-tag">SELURUH ORGANISASI</span></div>
            <h2 id="backup-full-title">Seluruh database</h2><p>Salin struktur, data, view, trigger, routine, dan event pada database aktif.</p>
            <div class="backup-note"><i class="bi bi-info-circle" aria-hidden="true"></i><span><strong>Struktur saja, tanpa data:</strong><code>dt01_bpjs_pending_klaim</code><code>dt01_bpjs_ur_dt</code></span></div>
            <button type="button" id="btnBackupDatabase" class="btn prem-btn-primary"><i class="bi bi-database-down" aria-hidden="true"></i> Backup seluruh database</button>
        </section>
    </div>
    <section class="prem-card backup-files" aria-labelledby="backup-files-title">
        <div class="backup-files-heading"><div><h2 id="backup-files-title">File backup</h2><p>Unduh atau hapus file backup yang sudah tersimpan.</p></div><div class="backup-search"><i class="bi bi-search" aria-hidden="true"></i><input id="searchtable" type="search" class="form-control" placeholder="Cari file backup..." aria-label="Cari file backup"></div></div>
        <div id="backup-status" class="backup-status" role="status" aria-live="polite">Memuat daftar backup...</div>
        <div class="table-responsive backup-table-container" tabindex="0" role="region" aria-label="Daftar file backup, dapat digulir"><table class="table align-middle gy-2" id="backup_table">
            <thead><tr><th class="ps-4">#</th><th>File SQL</th><th>Jenis backup</th><th>Ukuran</th><th>Dibuat</th><th class="text-end pe-4">Tindakan</th></tr></thead>
            <tbody id="resultbackup"></tbody>
        </table></div>
    </section>
</div>