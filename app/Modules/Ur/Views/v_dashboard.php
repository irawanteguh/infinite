<div id="ur-executive" class="prem-page pe-dashboard ur-dashboard">
    <section class="pe-page-heading ur-hero" aria-labelledby="ur-title">
        <div class="ur-heading"><div><div class="prem-eyebrow ur-eyebrow">UTILIZATION REVIEW</div><h1 id="ur-title">Dashboard kinerja pelayanan</h1><p>Pantau episode BPJS, perbandingan tarif, dan pola pelayanan rumah sakit.</p></div></div>
        <div class="pe-heading-actions ur-hero-actions"><button type="button" class="btn btn-light ur-button" id="ur-refresh"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Perbarui</button><button type="button" class="btn btn-light ur-button" id="ur-print"><i class="bi bi-printer" aria-hidden="true"></i> Cetak ringkasan</button></div>
    </section>
    <section class="prem-card ur-filterbar" aria-labelledby="ur-filter-title">
        <div class="ur-filter-heading"><span class="prem-eyebrow">CAKUPAN DATA</span><h2 id="ur-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter dashboard</h2><p>Pilih bulan pulang dan jenis layanan untuk menyesuaikan ringkasan.</p></div>
        <div class="ur-filters">
            <div class="ur-filter"><label for="ur-month">Bulan pulang</label><select id="ur-month" class="form-select form-select-sm form-select-solid"><option value="">Semua bulan</option><?php foreach (['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $month): ?><option value="<?= $i + 1 ?>"><?= $month ?></option><?php endforeach; ?></select></div>
            <div class="ur-filter"><label for="ur-service">Jenis layanan</label><select id="ur-service" class="form-select form-select-sm form-select-solid"><option value="">Semua layanan</option><option value="1">Rawat inap</option><option value="2">Rawat jalan</option></select></div>
            <span class="ur-filter-note"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>Tahun mengikuti filter periode di atas.<br>Data berdasarkan tanggal pulang.</span>
        </div>
        <div class="ur-scope"><i class="bi bi-calendar-range" aria-hidden="true"></i><span id="ur-coverage">Memuat cakupan data…</span></div>
    </section>
    <div id="ur-status" class="ur-status" role="status" aria-live="polite" hidden>Memuat ringkasan…</div>
    <div id="ur-content" hidden>
        <div class="ur-section-heading"><h2>Ringkasan utama</h2><span>Sesuai cakupan dan filter terpilih</span></div>
        <section class="pe-kpis ur-kpis" id="ur-kpis" aria-label="Indikator utama"></section>
        <div class="ur-grid-main ur-grid-full">
            <section class="prem-card pe-panel ur-panel"><div class="pe-panel-heading ur-panel-head"><div><div class="prem-eyebrow ur-eyebrow"><i class="bi bi-graph-up-arrow me-2"></i>PERBANDINGAN TARIF</div><h2>Tren tarif bulanan</h2><p>Tarif RS, total INA-CBG, dan IDRG pada cakupan terpilih.</p></div><span class="pe-panel-label ur-tag">Rupiah</span></div><div id="ur-finance-chart" class="ur-chart" role="img" aria-label="Tren tarif RS, INA-CBG, dan IDRG per bulan"></div></section>
        </div>
        <div class="ur-grid-equal">
            <section class="prem-card pe-panel ur-panel"><div class="pe-panel-heading ur-panel-head"><div><div class="prem-eyebrow ur-eyebrow"><i class="bi bi-activity me-2"></i>AKTIVITAS LAYANAN</div><h2>Tren episode pelayanan</h2><p>Episode klaim, bukan jumlah pasien unik.</p></div></div><div id="ur-visit-chart" class="ur-chart" role="img" aria-label="Tren episode rawat inap dan rawat jalan per bulan"></div></section>
            <section class="prem-card pe-panel ur-panel"><div class="pe-panel-heading ur-panel-head"><div><div class="prem-eyebrow ur-eyebrow"><i class="bi bi-pie-chart-fill me-2"></i>KOMPLEKSITAS RAWAT INAP</div><h2>Komposisi severity</h2><p>Severity yang tercatat pada hasil grouping INA-CBG.</p></div></div><div id="ur-severity"></div><div class="ur-mini-grid" id="ur-clinical"></div></section>
        </div>
        <div class="ur-grid-main">
            <section class="prem-card pe-panel ur-panel"><div class="pe-panel-heading ur-panel-head"><div><div class="prem-eyebrow ur-eyebrow">KINERJA PER BULAN</div><h2>Ringkasan kinerja pelayanan bulanan</h2></div></div><div class="ur-table-wrap"><table class="table pe-table align-middle ur-table"><thead><tr><th>Bulan</th><th>Episode</th><th>Tarif RS</th><th>Total INA-CBG</th><th>Selisih tarif</th><th>Rasio cakupan</th></tr></thead><tbody id="ur-monthly"></tbody></table></div></section>
            <section class="prem-card pe-panel ur-panel"><div class="pe-panel-heading ur-panel-head"><div><div class="prem-eyebrow ur-eyebrow">KOMPONEN TARIF RS</div><h2>Kontributor terbesar</h2><p>Enam komponen terbesar dalam data impor.</p></div></div><div id="ur-components"></div></section>
        </div>
        <section class="prem-card pe-panel ur-panel ur-method" aria-labelledby="ur-method-title">
            <div class="pe-panel-heading ur-panel-head"><div><span class="prem-eyebrow">PANDUAN MEMBACA DATA</span><h2 id="ur-method-title"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>Dasar pembacaan</h2><p>Pahami sumber nilai dan rumus sebelum menafsirkan hasil dashboard.</p></div></div>
            <div class="ur-method-grid">
                <div><h3>Nilai INA-CBG</h3><p>Memakai TOTAL_TARIF, termasuk komponen tambahan yang tercatat pada data E-Klaim.</p></div>
                <div><h3>Selisih dan rasio cakupan</h3><p><strong>Selisih = total INA-CBG − tarif RS</strong><br>Rasio cakupan = total INA-CBG ÷ tarif RS × 100%.</p></div>
                <div><h3>Tarif bukan biaya aktual</h3><p>Tarif RS bukan biaya aktual. Selisih tarif tidak menunjukkan laba atau rugi akuntansi.</p></div>
                <div><h3>Nilai IDRG</h3><p>Nilai pembanding pada data impor, bukan konfirmasi pembayaran yang diterima rumah sakit.</p></div>
            </div>
            <div class="ur-method-period"><i class="bi bi-calendar3 me-2" aria-hidden="true"></i>Bulan terakhir dapat belum lengkap. Bulan tanpa data tidak ditampilkan sebagai nol.</div>
        </section>
    </div>
</div>