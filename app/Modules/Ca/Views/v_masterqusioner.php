<link href="<?= esc(base_url('assets/css/pe/masterqusioner.css?v=' . filemtime(FCPATH . 'assets/css/pe/masterqusioner.css')), 'attr') ?>" rel="stylesheet">
<div class="pe-master-page" id="ca-master" data-endpoint="<?= esc(site_url('ca/masterqusioner'), 'attr') ?>" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>" data-csrf-hash="<?= esc(csrf_hash(), 'attr') ?>">
    <div class="app-page-heading">
        <div><h1>Master Kuesioner Skrining Kanker</h1><p>Kelola instrumen, pertanyaan, pilihan jawaban, dan skor.</p></div>
    </div>
    <div id="ca-message" class="alert d-none" role="status"></div>

    <section class="app-filter-card mb-5" aria-labelledby="ca-filter-title">
        <div class="app-filter-heading">
            <h2 id="ca-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter pertanyaan</h2>
            <p>Pilih status pertanyaan yang ingin ditampilkan.</p>
        </div>
        <div class="app-filter-fields">
            <div id="ca-status" class="app-filter-options" role="group" aria-label="Status pertanyaan">
                <button type="button" class="btn btn-sm app-filter-option active" data-status="" aria-pressed="true">Semua</button>
                <button type="button" class="btn btn-sm app-filter-option" data-status="1" aria-pressed="false">Aktif</button>
                <button type="button" class="btn btn-sm app-filter-option" data-status="0" aria-pressed="false">Nonaktif</button>
            </div>
        </div>
    </section>

    <section class="card mb-5">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-end gap-3 mb-5">
                <div class="flex-grow-1">
                    <label for="ca-instrument" class="form-label">Instrumen</label>
                    <select id="ca-instrument" class="form-select form-select-solid" disabled></select>
                </div>
                <button type="button" class="btn btn-primary" id="ca-add-instrument" disabled><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Instrumen</button>
                <button type="button" class="btn btn-primary d-none" id="ca-initialize">Simpan master awal</button>
                <button type="button" class="btn btn-light d-none" id="ca-edit-instrument">Edit instrumen</button>
                <button type="button" class="btn btn-light-primary" id="ca-preview" disabled><i class="bi bi-eye"></i> Pratinjau</button>
                <button type="button" class="btn prem-btn-primary" id="ca-add-question" disabled><i class="bi bi-plus-lg"></i> Tambah pertanyaan</button>
            </div>
            <div id="ca-risks" class="d-flex flex-wrap gap-2"></div>
        </div>
    </section>

    <section class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table pe-master-table align-middle gy-2" data-datatable="false">
                    <thead><tr><th scope="col">Urutan</th><th scope="col">Pertanyaan</th><th scope="col">Pilihan dan skor</th><th scope="col">Status</th><th scope="col" class="text-end">Aksi</th></tr></thead>
                    <tbody id="ca-questions"><tr><td colspan="5" class="text-center text-muted">Memuat master pertanyaan…</td></tr></tbody>
                </table>
            </div>
        </div>
    </section>
</div>
