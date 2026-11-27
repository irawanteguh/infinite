<div id="environment-page" data-storage-url="<?= esc(site_url('restapi/environment/storage'), 'attr') ?>" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>" data-csrf-hash="<?= esc(csrf_hash(), 'attr') ?>" data-storage-key="<?= esc('infinite.environments.' . hash('sha256', json_encode([session()->get('groupid'), session()->get('orgid'), session()->get('userid')])), 'attr') ?>">
    <header class="mb-6"><h1>Environment</h1><p class="text-muted">Kelola variabel untuk konfigurasi REST API.</p></header>
    <div class="alert alert-info">Environment disimpan di server Infinite per organisasi. Nilai secret disamarkan pada layar. File ekspor memuat seluruh nilai variabel.</div>
    <div class="card mb-5"><div class="card-body d-flex flex-wrap gap-3 align-items-end">
        <div class="flex-grow-1"><label for="env-select" class="form-label">Environment aktif</label><select id="env-select" class="form-select"></select></div>
        <div class="btn-group">
            <button id="env-new" class="btn btn-primary" type="button">Tambah environment</button>
            <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi environment"></button>
            <div class="dropdown-menu dropdown-menu-end">
                <label class="dropdown-item mb-0" role="button" tabindex="0" for="env-import"><i class="bi bi-upload me-2" aria-hidden="true"></i>Impor JSON</label>
                <button id="env-export" class="dropdown-item" type="button"><i class="bi bi-download me-2" aria-hidden="true"></i>Ekspor JSON</button>
                <button id="env-edit" class="dropdown-item" type="button"><i class="bi bi-pencil me-2" aria-hidden="true"></i>Edit environment</button>
                <div class="dropdown-divider"></div>
                <button id="env-delete" class="dropdown-item text-danger" type="button"><i class="bi bi-trash me-2" aria-hidden="true"></i>Hapus environment</button>
            </div>
        </div>
        <input id="env-import" type="file" accept=".json,application/json" class="d-none">
    </div></div>
    <section class="card app-table-card">
        <div class="card-header app-table-heading">
            <div>
                <h2 class="app-table-title">Variabel environment</h2>
                <p class="app-table-description">Kelola nama, tipe, dan nilai variabel pada <span id="env-title">environment terpilih</span>.</p>
            </div>
            <div class="app-table-search"><input id="env-search" type="search" class="form-control form-control-solid" placeholder="Cari variabel…" aria-label="Cari variabel"></div>
        </div>
        <div class="card-body">
            <div id="env-status" class="alert alert-info" role="status" hidden></div>
            <div class="table-responsive"><table class="table table-row-dashed align-middle w-100 fs-8 gy-2" id="env-table" data-datatable="manual">
                <thead><tr><th>Aktif</th><th>Variable</th><th>Type</th><th>Value</th><th class="text-end">Actions</th></tr></thead><tbody id="env-rows"></tbody>
            </table></div>
            <button id="env-add" class="btn btn-sm btn-light-primary mt-4" type="button"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah variabel</button>
            <button id="env-save" class="btn btn-primary mt-4 ms-2" type="button">Simpan variable</button>
            <p class="text-muted mt-4 mb-0">Gunakan nama variabel melalui sintaks <code>{{variable}}</code> saat menyusun konfigurasi request.</p>
        </div>
    </section>
</div>

<div class="modal fade" id="env-name-modal" tabindex="-1" aria-labelledby="env-name-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="env-name-form">
            <div class="modal-header">
                <h2 class="modal-title fs-4" id="env-name-title">Nama environment</h2>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <label class="form-label" for="env-name">Nama environment</label>
                <input id="env-name" class="form-control form-control-solid" maxlength="120" required placeholder="Contoh Production">
                <p id="env-name-error" class="text-danger mt-2 mb-0" role="alert"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
<script src="<?= base_url('assets/js/restapi/storage.js?v=' . filemtime(FCPATH . 'assets/js/restapi/storage.js')) ?>"></script>
