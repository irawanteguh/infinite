<link rel="stylesheet" href="<?= base_url('assets/css/ur/dashboard.css?v=' . filemtime(FCPATH . 'assets/css/ur/dashboard.css')) ?>">
<div id="ur-executive" class="prem-page pe-dashboard ur-dashboard biaya-page">
    <section class="ur-hero app-page-heading"><div><h1>Analisis tarif dan biaya</h1><p>Seluruh episode rawat inap dan rawat jalan. Selisih menggunakan TOTAL_TARIF dikurangi TARIF_RS. Periode mengikuti tanggal pulang.</p></div></section>
    <section class="ur-filterbar app-filter-card" aria-label="Filter data"><div class="app-filter-heading"><h2><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter biaya</h2><p>Pilih bulan pulang untuk menyesuaikan ringkasan dan daftar kasus.</p></div><div class="app-filter-fields"><div class="ur-filter"><label for="biaya-month">Bulan pulang</label><select id="biaya-month" data-control="select2" data-hide-search="true" class="form-select form-select-sm form-select-solid"><option value="">Semua bulan</option><?php foreach (['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $month): ?><option value="<?= $i + 1 ?>"<?= $selectedMonth === $i + 1 ? ' selected' : '' ?>><?= $month ?></option><?php endforeach; ?></select></div><span class="badge badge-light-primary">Cakupan · Analisis tarif dan biaya</span><span class="ur-filter-note">Berdasarkan tanggal pulang</span></div><div class="app-filter-scope"><i class="bi bi-calendar-range" aria-hidden="true"></i><span id="biaya-coverage">Memuat cakupan data…</span></div></section>
    <div id="biaya-status" class="ur-status" role="status" aria-live="polite" hidden></div>
    <div id="biaya-content" hidden>
        <section id="biaya-kpis" class="ur-kpis" aria-label="Ringkasan biaya"></section>
        <div class="ur-grid-equal"><section class="card card-flush ur-panel"><div class="ur-panel-head"><div><h2>Episode biaya per bulan</h2></div></div><div id="biaya-trend" class="ur-chart"></div></section><section class="card card-flush ur-panel"><div class="ur-panel-head"><div><h2>Komposisi severity</h2></div></div><div id="biaya-severity" class="ur-chart"></div></section></div>

<section class="card card-flush ur-panel app-table-card">
<div class="ur-panel-head app-table-heading"><div><h2 class="app-table-title">Variasi komponen tarif</h2><p class="app-table-description">Minimum, maksimum, rata-rata, simpangan baku populasi, dan koefisien variasi. Nilai nol termasuk perhitungan.</p></div></div>
<div class="ur-filterbar app-filter-card mb-5" aria-label="Filter variasi komponen tarif"><div class="app-filter-fields">
<div class="ur-filter"><label for="cost-group">Kelompok analisis</label><select id="cost-group" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true"><option value="inacbg">INA-CBG</option><option value="diagnosis">Diagnosis utama</option><option value="procedure_code">Prosedur pertama</option></select></div>
<div class="ur-filter"><label for="cost-field">Komponen tarif</label><select id="cost-field" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="Pilih komponen tarif">
<?php foreach (['tarif_rs','prosedur_non_bedah','prosedur_bedah','konsultasi','tenaga_ahli','keperawatan','penunjang','radiologi','laboratorium','pelayanan_darah','rehabilitasi','kamar_akomodasi','rawat_intensif','obat','alkes','bmhp','sewa_alat','obat_kronis','obat_kemo'] as $field): ?>
<option value="<?= $field ?>"><?= ucwords(str_replace('_', ' ', $field)) ?></option>
<?php endforeach; ?></select></div></div></div>
<div id="cost-status" role="status"></div><div class="ur-table-wrap"><table id="cost-table" data-datatable="manual" class="table table-row-dashed ur-table"><thead><tr><th>Kelompok</th><th>Episode</th><th>ALOS</th><th>Minimum</th><th>Maksimum</th><th>Rata-rata</th><th>Simpangan baku</th><th>CV</th></tr></thead><tbody id="cost-body"></tbody></table></div></section>
<section class="ur-filterbar app-filter-card" aria-labelledby="biaya-doctor-filter-title">
    <div class="app-filter-heading"><h2 id="biaya-doctor-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter biaya per dokter</h2></div>
    <div class="app-filter-fields">
        <div class="biaya-gap-options" role="group" aria-label="Selisih tarif">
            <button type="button" class="btn biaya-gap-option" data-gap="" aria-pressed="true">Semua</button>
            <button type="button" class="btn biaya-gap-option" data-gap="positive" aria-pressed="false">Selisih positif</button>
            <button type="button" class="btn biaya-gap-option" data-gap="negative" aria-pressed="false">Selisih negatif</button>
        </div>
    </div>
    <div class="app-filter-scope"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Berdasarkan total INA-CBG dikurangi total tarif RS per dokter pada bulan terpilih.</span></div>
</section>
<section class="card card-flush ur-panel app-table-card"><div class="ur-panel-head app-table-heading"><div><h2 class="app-table-title">Analisis tarif dan biaya per dokter</h2><p class="app-table-description">Episode, lama rawat dan tarif berdasarkan DPJP.</p></div><div class="app-table-search"><input id="biaya-search" type="search" class="form-control form-control-solid" placeholder="Cari DPJP…" aria-label="Cari DPJP"></div></div><div class="ur-table-wrap"><table id="biaya-table" data-datatable="manual" class="table table-row-dashed ur-table"><thead><tr><th>DPJP</th><th>Episode</th><th>Rata-rata LOS</th><th>Tarif RS</th><th>INA-CBG</th><th>Selisih</th></tr></thead><tbody id="biaya-doctors"></tbody></table></div></section>
<div class="biaya-method" role="note"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Dasar pembacaan.</strong> Seluruh episode rawat inap dan rawat jalan. Selisih menggunakan TOTAL_TARIF dikurangi TARIF_RS. Periode mengikuti tanggal pulang.</div></div></div></div>