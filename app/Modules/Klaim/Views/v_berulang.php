<main class="prem-page pe-dashboard klaim-case-page" data-case-module="berulang">
    <section class="ur-hero app-page-heading"><div><h1>Analisis Prosedur Berulang</h1><p>Telusuri klaim dengan catatan verifikasi terkait pengulangan prosedur atau pelayanan.</p></div></section>
    <section class="prem-card app-filter-card" aria-labelledby="case-filter-title">
        <div class="app-filter-heading"><h2 id="case-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter klaim</h2></div>
        <div class="app-filter-fields">
            <div><label for="case-month">Bulan pulang</label><select id="case-month" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true"><option value="">Semua bulan</option>
            <?php foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $index => $month): ?>
            <option value="<?= $index+1 ?>"><?= $month ?></option>
            <?php endforeach; ?></select></div>
            <div><label for="case-service">Jenis layanan</label><select id="case-service" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true"><option value="">Semua layanan</option><option value="1">Rawat inap</option><option value="2">Rawat jalan</option></select></div>
            <div><label for="case-status">Status klaim</label><select id="case-status" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true"><option value="">Semua status</option><option value="PENDING">Pending</option><option value="TIDAK LAYAK">Tidak layak</option></select></div>
        </div>
        <div class="app-filter-scope"><i class="bi bi-calendar-range" aria-hidden="true"></i><span>Tahun dan bulan mengikuti tanggal pulang.</span></div>
    </section>
    <div id="case-message" role="status" aria-live="polite" class="mb-5"></div>
    <div class="row g-5 mb-5">
        <div class="col-12 col-lg-6"><section class="prem-card klaim-case-panel h-100 mb-0">
            <div class="ur-panel-head"><div><h2>Tren klaim per bulan</h2><p>Jumlah klaim berdasarkan bulan pulang.</p></div></div>
            <div id="trencase" class="klaim-case-chart" role="img" aria-label="Grafik garis jumlah klaim per bulan"></div>
        </section></div>
        <div class="col-12 col-lg-6"><section class="prem-card klaim-case-panel h-100 mb-0">
            <div class="ur-panel-head"><div><h2>Nilai pengajuan klaim</h2><p>Total pengajuan berdasarkan bulan pulang. Setiap SEP dihitung sekali per bulan.</p></div></div>
            <div id="case-value-chart" class="klaim-case-chart" role="img" aria-label="Grafik garis nilai pengajuan per bulan"></div>
            <p id="case-value-note" class="text-muted small mb-0"></p>
        </section></div>
    </div>
    <section class="card app-table-card klaim-case-panel" aria-labelledby="klaim-case-title">
        <div class="app-table-heading"><div><h2 id="klaim-case-title" class="app-table-title">Daftar klaim</h2><p class="app-table-description">Telusuri pasien, nomor SEP, status klaim, dan catatan verifikasi.</p></div>
            <div class="app-table-search"><label for="searchtable" class="visually-hidden">Cari klaim</label><input id="searchtable" type="search" data-kt-customer-table-filter="search" class="form-control form-control-solid" placeholder="Cari klaim…" aria-label="Cari klaim"></div>
        </div>
        <div class="klaim-case-scroll"><table class="table align-middle table-row-dashed klaim-case-table" id="dataraweklaim_table" data-datatable="manual">
                <thead><tr><th>No</th><th>Nomor SEP</th><th>Nama pasien</th><th>Layanan</th><th>Tanggal pulang</th><th>Nilai pengajuan</th><th>Status klaim</th><th>Aksi</th></tr></thead>
                <tbody class="fw-bold text-gray-600" id="resultdataraweklaim"></tbody>
            </table></div>
    </section>
    <div class="klaim-case-info" role="note"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Dasar pembacaan.</strong> Hasil mengikuti data dan catatan verifikasi yang tersedia. Periksa tanggal pelayanan, indikasi klinis, episode perawatan, dan bukti tindakan untuk menilai pengulangan yang ditandai.</div></div>
</main>