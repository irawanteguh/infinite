<main id="pending-dashboard" class="prem-page pe-dashboard pending-page" data-endpoint="<?= esc(site_url('klaim/pending'),'attr') ?>">
    <div class="ur-hero app-page-heading"><div><h1>Pending &amp; tidak layak</h1><p>Pahami jumlah klaim, nilai pengajuan, dan perubahan antarbulan pada data BAHV.</p></div></div>
    <section class="prem-card pending-filter app-filter-card" aria-labelledby="pending-filter-title"><div class="pending-filter-heading app-filter-heading"><h2 id="pending-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter klaim</h2><p>Tahun mengikuti toolbar. Gunakan semua bulan untuk melihat tren tahunan.</p></div><div class="pending-filter-fields app-filter-fields">
        <div><label for="pending-month" class="form-label">Bulan pulang</label><select id="pending-month" data-control="select2" data-hide-search="true" class="form-select form-select-sm form-select-solid"><option value="">Semua bulan</option><?php foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i=>$month): ?><option value="<?= $i+1 ?>"><?= $month ?></option><?php endforeach; ?></select></div>
        <div><label for="pending-service" class="form-label">Pelayanan</label><select id="pending-service" data-control="select2" data-hide-search="true" class="form-select form-select-sm form-select-solid"><option value="">Semua pelayanan</option><option value="1">Rawat inap</option><option value="2">Rawat jalan</option></select></div>
        <div><label for="pending-status" class="form-label">Status klaim</label><select id="pending-status" data-control="select2" data-hide-search="true" class="form-select form-select-sm form-select-solid"><option value="">Semua status tercatat</option><option value="PENDING">Pending</option><option value="TIDAK LAYAK">Tidak layak</option></select></div>
        <button type="button" id="pending-reset" class="btn pending-soft-button"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Reset filter</button>
    </div><div id="pending-scope" class="pending-scope app-filter-scope"><i class="bi bi-calendar-range" aria-hidden="true"></i><span>Memuat cakupan data…</span></div></section>
    <div id="pending-message" class="alert d-none mt-6" role="status" aria-live="polite"></div>
    <div id="pending-loading" class="pending-loading mt-6" role="status" aria-live="polite">Memuat ringkasan klaim…</div>
    <div id="pending-content" hidden>
        <div class="pending-section-heading"><h2>Ringkasan klaim</h2><span>Sesuai filter terpilih</span></div>
        <section id="pending-kpis" class="pe-kpis" aria-label="Ringkasan klaim"></section>
        <div id="pending-value-note" class="pending-value-note" role="note"></div>
        <section class="prem-card pe-panel pending-progress" aria-labelledby="pending-progress-title"><div class="pe-panel-heading"><div><h2 id="pending-progress-title">Apa yang berubah pada bulan terakhir?</h2><p>Membandingkan bulan pulang terakhir yang memiliki data dengan bulan sebelumnya.</p></div><span class="pe-panel-label" id="pending-progress-period"></span></div><div id="pending-progress" class="pending-insights"></div><p class="pending-method-inline">Perubahan jumlah bukan jumlah klaim yang berhasil diselesaikan. Kelengkapan impor dan perbedaan volume pelayanan dapat memengaruhi hasil.</p></section>
        <div class="pending-grid">
            <section class="prem-card pe-panel"><div class="pe-panel-heading"><div><h2>Tren jumlah klaim bulanan</h2><p>Pending dan tidak layak menurut bulan pulang, bukan pasien unik.</p></div></div><div id="pending-count-chart" class="pending-chart" role="img" aria-label="Grafik garis jumlah klaim per bulan"></div></section>
            <section class="prem-card pe-panel"><div class="pe-panel-heading"><div><h2>Tren nilai pengajuan bulanan</h2><p>Hanya menjumlahkan nilai yang terisi, bukan nilai pembayaran.</p></div></div><div id="pending-value-chart" class="pending-chart" role="img" aria-label="Grafik garis nilai pengajuan per bulan"></div></section>
        </div>
        <section class="prem-card pe-panel pending-monthly-panel"><div class="pe-panel-heading"><div><h2>Perbandingan tiap bulan</h2><p>Bandingkan jumlah klaim dan nilai pengajuan setiap bulan. Perubahan jumlah dihitung terhadap bulan sebelumnya jika datanya tersedia.</p></div></div><div class="table-responsive" tabindex="0" role="region" aria-label="Ringkasan bulanan klaim, dapat digulir"><table class="table table-row-dashed pe-table align-middle pending-monthly" id="pending-monthly-table"><thead><tr><th scope="col">Bulan pulang</th><th scope="col">Klaim pending</th><th scope="col">Klaim tidak layak</th><th scope="col">Total klaim</th><th scope="col">Perubahan dari bulan lalu</th><th scope="col">Total nilai pengajuan</th><th scope="col">Klaim dengan nilai pengajuan</th></tr></thead><tbody id="pending-monthly"></tbody></table></div></section>
        <div class="row g-6 mb-6 pending-analysis-row">
            <div class="col-12 col-xl-4"><section class="prem-card pe-panel h-100"><div class="pe-panel-heading"><div><h2>Komposisi klaim tercatat</h2><p>Proporsi rawat inap dan rawat jalan pada data yang dipilih.</p></div></div><div id="pending-service-chart" class="pending-chart" role="img" aria-label="Donut chart komposisi klaim menurut pelayanan"></div><div id="pending-service-legend" class="pending-service-legend"></div></section></div>
            <div class="col-12 col-xl-4"><section class="prem-card pe-panel h-100"><div class="pe-panel-heading"><div><h2>Tipe algoritma terbanyak</h2><p>Satu tipe tercatat per klaim; label mengikuti sumber BAHV.</p></div></div><div id="pending-type-chart" class="pending-contributors" role="list" aria-label="Distribusi klaim menurut tipe algoritma"></div></section></div>
            <div class="col-12 col-xl-4"><section class="prem-card pe-panel pending-reasons h-100"><div class="pe-panel-heading"><div><h2>Algoritma verifikasi yang sering muncul</h2><p>Delapan kemunculan tertinggi. Satu klaim dapat memuat lebih dari satu algoritma.</p></div></div><div id="pending-algorithm-chart" class="pending-contributors" role="list" aria-label="Distribusi kemunculan algoritma verifikasi"></div></section></div>
        </div>
    </div>
    <div class="hr-users-page mt-6">
        <section class="card hu-card pending-details app-table-card" aria-labelledby="pending-detail-title">
            <div class="card-header app-table-heading border-0 pt-6">
                <div class="hu-table-title"><h3 id="pending-detail-title" class="app-table-title">Rincian klaim</h3><p class="app-table-description">Telusuri klaim sesuai filter berdasarkan SEP, pasien, kode INA-CBG, atau catatan verifikasi.</p></div>
                <div class="app-table-search">
                    <label for="searchtable" class="visually-hidden">Cari rincian klaim</label>
                    <input id="searchtable" type="search" maxlength="200" aria-label="Cari rincian klaim" data-kt-customer-table-filter="search" class="form-control form-control-solid" placeholder="Cari rincian klaim…">
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="pending-detail-message" class="alert alert-warning d-none" role="status"></div>
                <div class="hu-table-scroll pending-detail-frame" role="region" aria-label="Rincian klaim sesuai filter">
                    <table class="table align-middle table-row-dashed gy-2" id="dataraweklaim_table">
                        <thead><tr class="fw-bolder text-muted bg-light"><th class="text-start ps-4 rounded-start">No.</th><th>SEP / pasien</th><th>Pelayanan</th><th>Tanggal pelayanan</th><th>Verifikasi</th><th>INA-CBG / severity</th><th>Status</th><th class="text-end">Pengajuan</th><th>Tipe algoritma</th><th class="pe-4 rounded-end">Algoritma &amp; catatan</th></tr></thead>
                        <tbody class="fw-bold text-gray-600" id="resultdataraweklaim"></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
    <section class="prem-card pe-panel pending-guide mt-6"><div class="pe-panel-heading"><div><h2><i class="bi bi-info-circle me-2" aria-hidden="true"></i>Dasar pembacaan</h2></div></div><div class="pending-guide-grid"><div><h3>Periode dan status</h3><p>Tahun/bulan memakai tanggal pulang. Status mengikuti data BAHV tersimpan. Status selain Pending dan Tidak Layak tetap dipertahankan, tidak digabungkan otomatis.</p></div><div><h3>Nilai yang belum terisi</h3><p>Nilai kosong bukan nol. Total rupiah hanya mencakup pengajuan yang terisi; bandingkan kelengkapannya sebelum menafsirkan perubahan nilai.</p></div><div><h3>Perubahan, bukan penyelesaian</h3><p>Tidak tersedia riwayat perubahan status atau pembayaran. Turunnya jumlah antarbulan belum membuktikan perbaikan penyelesaian klaim.</p></div><div><h3>Cakupan impor</h3><p>Angka hanya mewakili klaim yang tercatat pada kode PPK organisasi. Pada grafik, bulan tanpa data digambar pada titik nol agar garis tersambung. Tabel bulanan menunjukkan ketersediaan data. Persentase terhadap seluruh klaim rumah sakit tidak dihitung karena denominator belum tersedia.</p></div></div></section>
</main>
