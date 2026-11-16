<div id="backup-config" data-base-url="<?= esc(site_url('developer/backupdb'), 'attr') ?>" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>" data-csrf-hash="<?= esc(csrf_hash(), 'attr') ?>"></div>
<div class="prem-page backup-page">
    <header class="backup-heading">
        <div><span class="prem-eyebrow">PENGELOLAAN DATABASE</span><h1>Backup Database</h1><p>Buat salinan database dan kelola file SQL yang tersimpan di server.</p></div>
        <button type="button" id="btnRefreshBackups" class="btn backup-btn-light"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Muat ulang</button>
    </header>
    <div class="backup-options">
        <section class="prem-card backup-option" aria-labelledby="backup-full-title">
            <div class="backup-option-heading"><span class="backup-icon"><i class="bi bi-database" aria-hidden="true"></i></span><span class="backup-tag">SELURUH ORGANISASI</span></div>
            <h2 id="backup-full-title">Seluruh database</h2><p>Salin struktur, data, view, trigger, routine, dan event pada database aktif.</p>
            <div class="backup-note"><i class="bi bi-info-circle" aria-hidden="true"></i><span><strong>Struktur saja, tanpa data:</strong><code>dt01_bpjs_pending_klaim</code><code>dt01_bpjs_ur_dt</code></span></div>
            <button type="button" id="btnBackupDatabase" class="btn prem-btn-primary"><i class="bi bi-database-down" aria-hidden="true"></i> Backup seluruh database</button>
        </section>
        <section class="prem-card backup-option" aria-labelledby="backup-ics-title">
            <div class="backup-option-heading"><span class="backup-icon"><i class="bi bi-journal-medical" aria-hidden="true"></i></span><span class="backup-tag">REFERENSI ICS</span></div>
            <h2 id="backup-ics-title">Database ICS</h2><p>Salin tabel referensi ICS beserta struktur, data, dan trigger terkait.</p>
            <div class="backup-note"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Gunakan pilihan ini untuk backup khusus referensi ICS. Snapshot transaksi berlaku untuk tabel InnoDB.</span></div>
            <button type="button" id="btnBackupDatabaseICS" class="btn backup-btn-light"><i class="bi bi-database-down" aria-hidden="true"></i> Backup ICS</button>
        </section>
    </div>
    <section class="prem-card backup-files" aria-labelledby="backup-files-title">
        <div class="backup-files-heading"><div><span class="prem-eyebrow">ARSIP SQL</span><h2 id="backup-files-title">File backup</h2><p>Unduh atau hapus file backup yang sudah tersimpan.</p></div><div class="backup-search"><i class="bi bi-search" aria-hidden="true"></i><input id="searchtable" type="search" class="form-control" placeholder="Cari file backup..." aria-label="Cari file backup"></div></div>
        <div id="backup-status" class="backup-status" role="status" aria-live="polite">Memuat daftar backup...</div>
        <div class="table-responsive backup-table-scroll" tabindex="0" role="region" aria-label="Daftar file backup, dapat digulir"><table class="table align-middle gy-2" id="backup_table">
            <thead><tr><th class="ps-4">#</th><th>File SQL</th><th>Jenis backup</th><th>Ukuran</th><th>Dibuat</th><th class="text-end pe-4">Tindakan</th></tr></thead>
            <tbody id="resultbackup"></tbody>
        </table></div>
    </section>
</div>