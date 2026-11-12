<div id="pe-master" data-endpoint="<?= esc(site_url('pe/masterqusioner'), 'attr') ?>" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>" data-csrf-hash="<?= esc(csrf_hash(), 'attr') ?>">
    <div class="card mb-6">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-4">
            <div>
                <div class="text-primary fw-bold mb-2">PATIENT EXPERIENCE</div>
                <h2 class="mb-2">Master Pertanyaan PROM &amp; PREM</h2>
                <div class="text-muted">Kelola instrumen, domain, dan pertanyaan penilaian pasien.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-light-primary btn-sm" id="pe-manage-domains"><i class="bi bi-list-ul"></i> Master domain</button>
                <button type="button" class="btn btn-primary btn-sm" id="pe-add-instrument"><i class="bi bi-plus-lg"></i> Tambah instrumen</button>
            </div>
        </div>
    </div>
    <div id="pe-message" class="alert d-none" role="alert" aria-live="polite"></div>
    <div class="row g-5 mb-6" id="pe-summary">
        <div class="col-md-4"><div class="card card-flush"><div class="card-body py-5"><div class="text-muted mb-2">Instrumen</div><div class="fs-2 fw-bolder" id="pe-count-instruments">0</div></div></div></div>
        <div class="col-md-4"><div class="card card-flush"><div class="card-body py-5"><div class="text-muted mb-2">Total pertanyaan</div><div class="fs-2 fw-bolder" id="pe-count-questions">0</div></div></div></div>
        <div class="col-md-4"><div class="card card-flush"><div class="card-body py-5"><div class="text-muted mb-2">Pertanyaan aktif</div><div class="fs-2 fw-bolder text-success" id="pe-count-active">0</div></div></div></div>
    </div>
    <div class="card">
        <div class="card-header py-5 align-items-center gap-3">
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <div class="w-300px mw-100"><label for="pe-instrument-filter" class="form-label">Instrumen kuesioner</label><select id="pe-instrument-filter" class="form-select form-select-solid"><option value="">Pilih instrumen</option></select></div>
                <button type="button" id="pe-edit-instrument" class="btn btn-sm btn-light mt-7" disabled><i class="bi bi-pencil"></i> Edit instrumen</button>
            </div>
            <div class="d-flex gap-2 align-items-end">
                <button type="button" id="pe-preview" class="btn btn-sm btn-light-primary" disabled><i class="bi bi-eye"></i> Pratinjau</button>
                <button type="button" id="pe-add-question" class="btn btn-sm btn-primary" disabled><i class="bi bi-plus-lg"></i> Tambah pertanyaan</button>
            </div>
        </div>
        <div class="card-body">
            <div class="bg-light rounded p-5 mb-5 d-none" id="pe-instrument-info">
                <div class="d-flex align-items-center gap-3 mb-3"><span id="pe-type" class="badge badge-light-primary"></span><h3 id="pe-name" class="mb-0 fs-5"></h3><span id="pe-instrument-status" class="badge"></span></div>
                <div class="text-gray-600 mb-2"><span class="fw-bold">Responden:</span> <span id="pe-respondent"></span></div>
                <div class="text-gray-600"><span class="fw-bold">Pengukuran:</span> <span id="pe-measurement"></span></div>
                <div class="text-muted mt-3">Pilihan jawaban: 1 Sangat tidak setuju · 2 Tidak setuju · 3 Ragu-ragu · 4 Setuju · 5 Sangat setuju</div>
                <div id="pe-source-note" class="text-warning mt-3 d-none"></div>
            </div>
            <div class="d-flex flex-wrap gap-3 justify-content-between mb-5">
                <input type="search" id="pe-search" class="form-control form-control-solid w-auto mw-100" placeholder="Cari pertanyaan atau domain…" aria-label="Cari pertanyaan atau domain">
                <div class="w-150px"><select id="pe-status-filter" class="form-select form-select-solid" aria-label="Filter status"><option value="">Semua status</option><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-2">
                    <thead><tr class="fw-bolder text-muted bg-light"><th class="text-start ps-4 rounded-start">Urutan</th><th>Domain</th><th style="min-width:280px">Pertanyaan</th><th>Status</th><th class="text-end pe-4 rounded-end">Aksi</th></tr></thead>
                    <tbody class="fw-bold text-gray-600" id="pe-questions"><tr><td colspan="5" class="text-center text-muted">Memuat master pertanyaan…</td></tr></tbody>
                </table>
            </div>
            <div class="text-muted mt-4" id="pe-result-count" aria-live="polite"></div>
        </div>
    </div>
</div>
