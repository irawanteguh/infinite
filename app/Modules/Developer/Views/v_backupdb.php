<div id="backup-config" data-base-url="<?= esc(site_url('developer/backupdb'), 'attr') ?>" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>" data-csrf-hash="<?= esc(csrf_hash(), 'attr') ?>"></div>
<div class="col-xl-12 mb-5">
    <div class="card card-flush">
        <div class="card-header border-0 pt-6 align-items-center">
            <div class="card-title"><div><div class="fs-3 fw-bold text-gray-800">Dump Database</div><div class="text-muted fs-7 mt-2">Buat, unduh, dan kelola file backup SQL.</div></div></div>
            <div class="card-toolbar"><button type="button" id="btnRefreshBackups" class="btn btn-sm btn-light-primary"><i class="bi bi-arrow-clockwise"></i> Muat ulang</button></div>
        </div>
        <div class="card-body pt-4">
            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-5 mb-6"><i class="bi bi-info-circle text-primary fs-2 me-4"></i><div class="fs-7 text-gray-700"><strong>Seluruh database:</strong> struktur, data, view, trigger, routine, dan event database aktif, termasuk seluruh organisasi.<br><strong>ICS:</strong> hanya tabel referensi ICS dan trigger terkait. Snapshot transaksi berlaku untuk tabel InnoDB.</div></div>
            <div id="backup-status" class="text-muted fs-7 mb-5" role="status" aria-live="polite">Memuat daftar backup...</div>
            <div class="d-flex justify-content-end mb-4"><input id="searchtable" type="search" class="form-control form-control-solid w-250px" placeholder="Cari file backup" aria-label="Cari file backup"></div>
            <div class="table-responsive"><table class="table align-middle table-row-dashed gy-3 fs-8" id="backup_table">
                <thead><tr class="fw-bolder text-muted bg-light"><th class="ps-4 rounded-start">#</th><th>File SQL</th><th>Jenis backup</th><th>Ukuran</th><th>Dibuat</th><th class="text-end rounded-end pe-4">Tindakan</th></tr></thead>
                <tbody class="fw-bold text-gray-600" id="resultbackup"></tbody>
            </table></div>
        </div>
    </div>
</div>
