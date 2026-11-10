<div id="ur-executive" class="ur-dashboard">
    <section class="ur-hero">
        <div><div class="ur-eyebrow">UTILIZATION REVIEW / EXECUTIVE OVERVIEW</div><h1>Ringkasan kinerja pelayanan rumah sakit</h1><p>Ringkasan episode BPJS, perbandingan tarif, dan prioritas evaluasi pelayanan rumah sakit.</p>
        <div class="ur-coverage" id="ur-coverage">Memuat cakupan dataâ€¦</div></div>
        <div class="ur-hero-actions"><button type="button" class="btn btn-sm ur-button" id="ur-refresh"><i class="bi bi-arrow-clockwise"></i> Perbarui</button><button type="button" class="btn btn-sm ur-button" id="ur-print"><i class="bi bi-printer"></i> Cetak ringkasan</button></div>
    </section>
    <div class="ur-filterbar"><div class="ur-filter"><label for="ur-month">Bulan pulang</label><select id="ur-month" class="form-select form-select-sm form-select-solid"><option value="">Semua bulan</option><?php foreach (['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $month): ?><option value="<?= $i + 1 ?>"><?= $month ?></option><?php endforeach; ?></select></div>
    <div class="ur-filter"><label for="ur-service">Jenis layanan</label><select id="ur-service" class="form-select form-select-sm form-select-solid"><option value="">Semua layanan</option><option value="1">Rawat inap</option><option value="2">Rawat jalan</option></select></div><span class="ur-filter-note">Tahun mengikuti filter periode di atas Â· Acuan: tanggal pulang</span></div>
    <div id="ur-status" class="ur-status" role="status" aria-live="polite" hidden>Memuat ringkasanâ€¦</div>
    <div id="ur-content" hidden>
        <section class="ur-kpis" id="ur-kpis" aria-label="Indikator utama"></section>
        <div class="ur-grid-main ur-grid-full">
            <section class="card card-flush ur-panel"><div class="ur-panel-head"><div><div class="ur-eyebrow"><i class="bi bi-graph-up-arrow me-2"></i>PERBANDINGAN TARIF</div><h2>Tren tarif bulanan</h2><p>Tarif RS, total INA-CBG, dan IDRG pada cakupan terpilih.</p></div><span class="badge badge-light-primary ur-tag">Rupiah</span></div><div id="ur-finance-chart" class="ur-chart"></div></section>
        </div>
        <div class="ur-grid-equal">
            <section class="card card-flush ur-panel"><div class="ur-panel-head"><div><div class="ur-eyebrow"><i class="bi bi-activity me-2"></i>AKTIVITAS LAYANAN</div><h2>Tren episode pelayanan</h2><p>Episode klaim, bukan jumlah pasien unik.</p></div></div><div id="ur-visit-chart" class="ur-chart"></div></section>
            <section class="card card-flush ur-panel"><div class="ur-panel-head"><div><div class="ur-eyebrow"><i class="bi bi-pie-chart-fill me-2"></i>KOMPLEKSITAS RAWAT INAP</div><h2>Komposisi severity</h2><p>Severity yang tercatat pada hasil grouping INA-CBG.</p></div></div><div id="ur-severity"></div><div class="ur-mini-grid" id="ur-clinical"></div></section>
        </div>
        <div class="ur-grid-main">
            <section class="card card-flush ur-panel"><div class="ur-panel-head"><div><div class="ur-eyebrow">KINERJA PER BULAN</div><h2>Ringkasan kinerja pelayanan bulanan</h2></div></div><div class="ur-table-wrap"><table class="table table-row-dashed align-middle ur-table"><thead><tr><th>Bulan</th><th>Episode</th><th>Tarif RS</th><th>Total INA-CBG</th><th>Selisih tarif</th><th>Rasio cakupan</th></tr></thead><tbody id="ur-monthly"></tbody></table></div></section>
            <section class="card card-flush ur-panel"><div class="ur-panel-head"><div><div class="ur-eyebrow">KOMPONEN TARIF RS</div><h2>Kontributor terbesar</h2><p>Enam komponen terbesar dalam data impor.</p></div></div><div id="ur-components"></div></section>
        </div>
        <div class="ur-method"><strong>Dasar pembacaan.</strong> INA-CBG memakai TOTAL_TARIF, termasuk komponen tambahan yang tercatat. Selisih = tarif klaim âˆ’ tarif RS; rasio cakupan = total INA-CBG Ã· tarif RS. Tarif RS bukan biaya aktual, sehingga selisih ini bukan laba/rugi akuntansi. IDRG adalah nilai pembanding pada data impor, bukan konfirmasi pembayaran. Bulan terakhir dapat belum lengkap; bulan tanpa data tidak ditampilkan sebagai nol.</div>
    </div>
</div>