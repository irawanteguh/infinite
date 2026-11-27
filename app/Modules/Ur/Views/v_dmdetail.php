<link rel="stylesheet" href="<?= base_url('assets/css/ur/malnutrisi.css?v=' . filemtime(FCPATH . 'assets/css/ur/malnutrisi.css')) ?>">
<div id="ur-executive" class="prem-page pe-dashboard ur-dashboard mal-page dm-detail-page">
    <section class="ur-hero app-page-heading">
        <div><h1>Daftar kasus dm</h1><p>Episode rawat inap dengan diagnosis E11.9 dan prosedur 96.71 atau 96.72.</p></div>
    </section>
    <section class="prem-card ur-filterbar app-filter-card" aria-labelledby="dm-detail-filter-title">
        <div class="ur-filter-heading app-filter-heading">
            <h2 id="dm-detail-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter daftar kasus</h2>
        </div>
        <div class="ur-filters app-filter-fields">
            <div class="ur-filter">
                <label for="dm-month">Bulan pulang</label>
                <select id="dm-month" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true">
                    <option value="">Semua bulan</option>
                    <?php foreach (['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $month): ?>
                        <option value="<?= $i + 1 ?>"<?= $selectedMonth === $i + 1 ? ' selected' : '' ?>><?= $month ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="ur-scope app-filter-scope">
            <i class="bi bi-calendar-range" aria-hidden="true"></i><span>Tahun pulang mengikuti pilihan pada toolbar.</span>
        </div>
    </section>
    <div id="dm-status" class="ur-status" role="status" aria-live="polite" hidden></div>
    <div id="dm-content" hidden>
        <section class="card card-flush ur-panel app-table-card mb-5"><div class="ur-panel-head"><div><h2>Daftar episode</h2><p>Diurutkan dari lama rawat tertinggi.</p></div><div class="app-table-search"><label for="dm-case-search" class="visually-hidden">Cari kasus</label><input id="dm-case-search" type="search" class="form-control form-control-solid" placeholder="Cari nama, SEP, MRN, DPJP..." maxlength="200"></div></div><div class="ur-table-wrap"><table id="dm-case-table" data-datatable="manual" class="table table-row-dashed align-middle ur-table mal-cases-table fs-8 gy-2"><thead><tr><th>No</th><th>SEP</th><th>MRN</th><th>Nama pasien</th><th>DPJP</th><th>Tgl masuk</th><th>Tgl keluar</th><th>LOS</th><th>Aksi</th></tr></thead><tbody id="dm-cases"></tbody></table></div></section>
    </div>
</div>
