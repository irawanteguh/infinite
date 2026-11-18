<link rel="stylesheet" href="<?= base_url('assets/css/ur/tarifdetail.css?v=' . filemtime(FCPATH . 'assets/css/ur/tarifdetail.css')) ?>">
<div id="ur-executive" class="prem-page pe-dashboard ur-dashboard <?= $tariffType === 'positif' ? 'tp-page' : 'tn-page' ?>" data-tariff-type="<?= esc($tariffType) ?>">
    <section class="ur-hero app-page-heading">
        <div>
            <h1>Daftar kasus tarif <?= esc($tariffType) ?></h1>
            <p>Episode dengan tarif INA-CBG <?= $tariffType === 'positif' ? 'lebih tinggi' : 'lebih rendah' ?> dari tarif RS.</p>
        </div>
    </section>

    <section class="prem-card ur-filterbar app-filter-card" aria-labelledby="tp-cases-filter-title">
        <div class="ur-filter-heading app-filter-heading">
            <h2 id="tp-cases-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter daftar kasus</h2>
        </div>
        <div class="ur-filters app-filter-fields">
            <div class="ur-filter">
                <label for="tp-month">Bulan pulang</label>
                <select id="tp-month" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true">
                    <option value="">Semua bulan</option>
                    <?php foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $index => $month): ?>
                        <option value="<?= $index + 1 ?>"<?= $selectedMonth === $index + 1 ? ' selected' : '' ?>><?= $month ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ur-filter">
                <label for="tp-service">Jenis layanan</label>
                <select id="tp-service" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true">
                    <option value="">Semua layanan</option>
                    <option value="1"<?= $selectedService === '1' ? ' selected' : '' ?>>Rawat inap</option>
                    <option value="2"<?= $selectedService === '2' ? ' selected' : '' ?>>Rawat jalan</option>
                </select>
            </div>
            <div class="ur-filter">
                <label for="tp-sort">Urutkan berdasarkan</label>
                <select id="tp-sort" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true">
                    <option value="gap">Selisih terbesar</option>
                    <option value="date">Tanggal pulang terbaru</option>
                    <option value="tariff">Tarif INA-CBG tertinggi</option>
                </select>
            </div>
        </div>
        <div class="ur-scope app-filter-scope">
            <i class="bi bi-calendar-range" aria-hidden="true"></i><span>Tahun pulang mengikuti pilihan pada toolbar.</span>
        </div>
    </section>

    <section class="card card-flush ur-panel app-table-card" aria-labelledby="tp-cases-title">
        <div class="ur-panel-head app-table-heading">
            <div>
                <h2 id="tp-cases-title" class="app-table-title">Daftar episode</h2>
                <p class="app-table-description">Lihat selisih INA-CBG dan tarif RS per episode. Buka Detail untuk melihat diagnosis dan prosedur.</p>
            </div>
            <div class="app-table-search">
                <label for="tp-cases-search" class="visually-hidden">Cari kasus</label>
                <input id="tp-cases-search" type="search" class="form-control form-control-solid" placeholder="Cari pasien, SEP, MRN, DPJP…" maxlength="200">
            </div>
        </div>
        <div id="tp-cases-status" class="ur-status mb-3" role="status" aria-live="polite" hidden></div>
        <div class="ur-table-wrap">
            <table id="tp-cases-table" data-datatable="manual" class="table table-row-dashed align-middle ur-table tp-cases-table">
                <thead><tr>
                    <th scope="col">No.</th><th scope="col">Pasien</th><th scope="col">SEP / MRN</th>
                    <th scope="col">DPJP</th><th scope="col">Layanan</th><th scope="col">Tanggal pulang</th>
                    <th scope="col" class="text-end">Tarif RS</th><th scope="col" class="text-end">INA-CBG</th>
                    <th scope="col" class="text-end">Selisih <?= esc($tariffType) ?></th><th scope="col">Aksi</th>
                </tr></thead>
                <tbody id="tp-cases-rows"></tbody>
            </table>
        </div>
        <div class="app-table-footer app-table-footer-manual">
            <div class="app-table-meta">
                <div class="dataTables_length">
                    <label for="tp-case-limit" class="visually-hidden">Baris per halaman</label>
                    <select id="tp-case-limit" class="form-select form-select-sm form-select-solid">
                        <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
                    </select>
                </div>
                <span id="tp-case-count" class="dataTables_info" aria-live="polite"></span>
            </div>
            <nav aria-label="Halaman daftar kasus">
                <ul class="pagination">
                    <li class="page-item" id="tp-case-prev-item"><button id="tp-case-prev" type="button" class="page-link" disabled>Sebelumnya</button></li>
                    <li class="page-item"><button id="tp-case-next" type="button" class="page-link" disabled>Berikutnya</button></li>
                </ul>
            </nav>
        </div>
    </section>
</div>
