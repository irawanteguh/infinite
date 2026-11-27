<div id="environment-page" data-storage-url="<?= esc(site_url('restapi/environment/storage'), 'attr') ?>" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>" data-csrf-hash="<?= esc(csrf_hash(), 'attr') ?>" data-storage-key="<?= esc('infinite.environments.' . hash('sha256', json_encode([session()->get('groupid'), session()->get('orgid'), session()->get('userid')])), 'attr') ?>">
    <header class="mb-6"><h1>Environment</h1><p class="text-muted">Kelola variabel untuk konfigurasi REST API.</p></header>
    <div class="alert alert-info">Environment disimpan di server Infinite per organisasi. Nilai secret disamarkan pada layar. File ekspor memuat seluruh nilai variabel.</div>
    <div class="card mb-5"><div class="card-body d-flex flex-wrap gap-3 align-items-end">
        <div class="flex-grow-1"><label for="env-select" class="form-label">Environment aktif</label><select id="env-select" class="form-select"></select></div>
        <button id="env-new" class="btn btn-primary" type="button">Tambah environment</button>
        <label class="btn btn-light-primary mb-0">Impor JSON<input id="env-import" type="file" accept=".json,application/json" class="d-none"></label>
        <button id="env-export" class="btn btn-light-primary" type="button">Ekspor JSON</button>
        <button id="env-delete" class="btn btn-light-danger" type="button">Hapus environment</button>
    </div></div>
    <section class="card app-table-card">
        <div class="card-header app-table-heading">
            <div class="w-100">
                <label for="env-name" class="form-label">Nama environment</label>
                <div class="input-group">
                    <input id="env-name" class="form-control" maxlength="120" placeholder="Contoh Production">
                    <button id="env-save" class="btn btn-primary" type="button">Simpan</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="env-status" class="alert alert-info" role="status" hidden></div>
            <div class="table-responsive"><table class="table table-row-dashed align-middle w-100" id="env-table" data-datatable="manual">
                <thead><tr><th>Aktif</th><th>Variable</th><th>Type</th><th>Value</th><th class="text-end">Actions</th></tr></thead><tbody id="env-rows"></tbody>
            </table></div>
            <button id="env-add" class="btn btn-sm btn-light-primary mt-4" type="button"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah variabel</button>
            <p class="text-muted mt-4 mb-0">Gunakan nama variabel melalui sintaks <code>{{variable}}</code> saat menyusun konfigurasi request.</p>
        </div>
    </section>
</div>

<script src="<?= base_url('assets/js/restapi/storage.js?v=' . filemtime(FCPATH . 'assets/js/restapi/storage.js')) ?>"></script>
