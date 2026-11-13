<div id="pe-master" class="prem-page pe-master-page" data-endpoint="<?= esc(site_url('pe/masterqusioner'), 'attr') ?>" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>" data-csrf-hash="<?= esc(csrf_hash(), 'attr') ?>">
    <div class="pe-master-heading">
        <div><span class="prem-eyebrow">PATIENT EXPERIENCE</span><h1>Master Kuesioner</h1><p>Kelola instrumen, domain, dan pertanyaan SKM, PREM, serta PROM.</p></div>
        <div class="pe-master-actions"><button type="button" class="btn btn-light-primary" id="pe-manage-domains"><i class="bi bi-list-ul"></i> Master domain</button><button type="button" class="btn prem-btn-primary" id="pe-add-instrument"><i class="bi bi-plus-lg"></i> Tambah instrumen</button></div>
    </div>
    <div id="pe-message" class="alert d-none" role="alert" aria-live="polite"></div>
    <div class="pe-master-stats" id="pe-summary" aria-label="Ringkasan master kuesioner">
        <article class="prem-card pe-master-stat"><div><span>Instrumen kuesioner</span><strong id="pe-count-instruments">0</strong><p>Instrumen pada rumah sakit aktif</p></div><i class="bi bi-clipboard-data"></i></article>
        <article class="prem-card pe-master-stat"><div><span>Total pertanyaan</span><strong id="pe-count-questions">0</strong><p>Seluruh pertanyaan yang terdaftar</p></div><i class="bi bi-list-check"></i></article>
        <article class="prem-card pe-master-stat"><div><span>Pertanyaan aktif</span><strong id="pe-count-active">0</strong><p>Pertanyaan dalam instrumen aktif</p></div><i class="bi bi-check2-circle"></i></article>
    </div>
    <section class="prem-card pe-master-workspace" aria-label="Kelola pertanyaan kuesioner">
        <div class="pe-master-filter">
            <div class="pe-master-instrument"><label for="pe-instrument-filter" class="form-label">Instrumen kuesioner</label><select id="pe-instrument-filter" class="form-select form-select-solid"><option value="">Pilih instrumen</option></select></div>
            <button type="button" id="pe-edit-instrument" class="btn btn-light" disabled><i class="bi bi-pencil"></i> Edit instrumen</button>
            <div class="pe-master-actions"><button type="button" id="pe-preview" class="btn btn-light-primary" disabled><i class="bi bi-eye"></i> Pratinjau</button><button type="button" id="pe-add-question" class="btn prem-btn-primary" disabled><i class="bi bi-plus-lg"></i> Tambah pertanyaan</button></div>
        </div>
        <div class="pe-master-body">
            <div class="pe-master-instrument-info d-none" id="pe-instrument-info">
                <div class="pe-master-info-title"><span id="pe-type" class="badge badge-light-primary"></span><h2 id="pe-name"></h2><span id="pe-instrument-status" class="badge"></span></div>
                <div class="pe-master-info-grid"><div><span>Responden</span><p id="pe-respondent"></p></div><div><span>Waktu pengukuran</span><p id="pe-measurement"></p></div></div>
                <p class="pe-master-info-note"><i class="bi bi-info-circle"></i> Pilihan jawaban mengikuti masing-masing pertanyaan pada instrumen.</p>
                <div id="pe-source-note" class="text-warning mt-3 d-none"></div>
            </div>
            <div class="pe-master-table-heading"><div><span class="prem-eyebrow">DAFTAR PERTANYAAN</span><h2>Pertanyaan kuesioner</h2></div><div class="pe-master-table-filters"><div class="pe-master-search"><i class="bi bi-search"></i><input type="search" id="pe-search" class="form-control form-control-solid" placeholder="Cari pertanyaan atau domain…" aria-label="Cari pertanyaan atau domain"></div><select id="pe-status-filter" class="form-select form-select-solid" aria-label="Filter status"><option value="">Semua status</option><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div></div>
            <p class="pe-master-scroll-note">Geser tabel ke samping untuk melihat semua kolom.</p><div class="table-responsive pe-master-table-scroll" tabindex="0" role="region" aria-label="Tabel pertanyaan kuesioner, dapat digulir"><table class="table pe-master-table align-middle gy-2"><thead><tr><th>Urutan</th><th>Domain</th><th>Pertanyaan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody id="pe-questions"><tr><td colspan="5" class="text-center text-muted">Memuat master pertanyaan…</td></tr></tbody></table></div>
            <div id="pe-result-count" class="pe-master-result" aria-live="polite"></div>
        </div>
    </section>
</div>
