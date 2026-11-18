<link rel="stylesheet" href="<?= base_url('assets/css/ur/malnutrisi.css?v=' . filemtime(FCPATH . 'assets/css/ur/malnutrisi.css')) ?>">
<div id="ur-executive" class="prem-page pe-dashboard ur-dashboard mal-page vent-detail-page">
    <section class="ur-hero app-page-heading">
        <div><h1>Daftar kasus ventilator</h1><p>Rawat inap dengan prosedur 96.71 atau 96.72.</p></div>
    </section>
    <section class="prem-card ur-filterbar app-filter-card" aria-labelledby="vent-detail-filter-title">
        <div class="ur-filter-heading app-filter-heading">
            <h2 id="vent-detail-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter daftar kasus</h2>
        </div>
        <div class="ur-filters app-filter-fields">
            <div class="ur-filter">
                <label for="vent-month">Bulan pulang</label>
                <select id="vent-month" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true">
                    <option value="">Semua bulan</option>
                    <?php foreach (['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $month): ?>
                        <option value="<?= $i + 1 ?>"<?= $selectedMonth === $i + 1 ? ' selected' : '' ?>><?= $month ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ur-filter vent-review-filter">
                <label for="vent-review">Status pemeriksaan</label>
                <select id="vent-review" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true">
                    <option value="">Semua kasus</option>
                    <option value="danger">Diag · 96.71, jam ≥ 96 atau 0</option>
                    <option value="warning">Diag · 96.72, jam &lt; 96 atau 0</option>
                    <option value="normal">Tanpa peringatan</option>
                </select>
            </div>
        </div>
        <div class="ur-scope app-filter-scope">
            <i class="bi bi-calendar-range" aria-hidden="true"></i><span>Tahun pulang mengikuti pilihan pada toolbar.</span>
        </div>
    </section>
    <div id="vent-status" class="ur-status" role="status" aria-live="polite" hidden></div>
    <div id="vent-content" hidden>
        <section class="card card-flush ur-panel app-table-card">
            <div class="ur-panel-head app-table-heading"><div><h2 class="app-table-title">Daftar kasus ventilator</h2><p class="app-table-description">Merah untuk 96.71 dengan jam ≥ 96 atau 0. Kuning untuk 96.72 dengan jam &lt; 96 atau 0. Bila kedua kode tercatat, pemeriksaan mengikuti 96.71.</p></div></div>
            <div class="ur-table-wrap"><table id="vent-cases-table" data-datatable="manual" class="table table-row-dashed align-middle ur-table">
                <thead><tr><th>No</th><th>SEP</th><th>MRN</th><th>Nama pasien</th><th>DPJP</th><th>Tgl masuk</th><th>Tgl keluar</th><th>LOS</th><th>Aksi</th></tr></thead>
                <tbody id="vent-cases"></tbody>
            </table></div>
        </section>

    </div>
</div>
