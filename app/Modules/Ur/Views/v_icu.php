<link rel="stylesheet" href="<?= base_url('assets/css/ur/dashboard.css?v=' . filemtime(FCPATH . 'assets/css/ur/dashboard.css')) ?>">
<div id="ur-executive" class="prem-page pe-dashboard ur-dashboard icu-page">
    <section class="ur-hero app-page-heading"><div><h1>Utilisasi ICU</h1><p>Episode dengan ICU_INDIKATOR berbeda dari 0. Hari ICU dan jam ventilator mengikuti data impor.</p></div></section>
    <section class="ur-filterbar app-filter-card" aria-label="Filter data"><div class="app-filter-heading"><h2><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter icu</h2><p>Pilih bulan pulang untuk menyesuaikan ringkasan dan daftar kasus.</p></div><div class="app-filter-fields"><div class="ur-filter"><label for="icu-month">Bulan pulang</label><select id="icu-month" data-control="select2" data-hide-search="true" class="form-select form-select-sm form-select-solid"><option value="">Semua bulan</option><?php foreach (['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $month): ?><option value="<?= $i + 1 ?>"<?= $selectedMonth === $i + 1 ? ' selected' : '' ?>><?= $month ?></option><?php endforeach; ?></select></div><span class="badge badge-light-primary">Cakupan · Utilisasi ICU</span><span class="ur-filter-note">Berdasarkan tanggal pulang</span></div><div class="app-filter-scope"><i class="bi bi-calendar-range" aria-hidden="true"></i><span id="icu-coverage">Memuat cakupan data…</span></div></section>
    <div id="icu-status" class="ur-status" role="status" aria-live="polite" hidden></div>
    <div id="icu-content" hidden>
        <section id="icu-kpis" class="ur-kpis" aria-label="Ringkasan icu"></section>
        <div class="ur-grid-equal"><section class="card card-flush ur-panel"><div class="ur-panel-head"><div><h2>Episode icu per bulan</h2></div></div><div id="icu-trend" class="ur-chart"></div></section><section class="card card-flush ur-panel"><div class="ur-panel-head"><div><h2>Komposisi severity</h2></div></div><div id="icu-severity" class="ur-chart"></div></section></div>
<section class="ur-filterbar app-filter-card" aria-labelledby="icu-doctor-filter-title">
    <div class="app-filter-heading"><h2 id="icu-doctor-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter icu per dokter</h2></div>
    <div class="app-filter-fields">
        <div class="icu-gap-options" role="group" aria-label="Selisih tarif">
            <button type="button" class="btn icu-gap-option" data-gap="" aria-pressed="true">Semua</button>
            <button type="button" class="btn icu-gap-option" data-gap="positive" aria-pressed="false">Selisih positif</button>
            <button type="button" class="btn icu-gap-option" data-gap="negative" aria-pressed="false">Selisih negatif</button>
        </div>
    </div>
    <div class="app-filter-scope"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Berdasarkan total INA-CBG dikurangi total tarif RS per dokter pada bulan terpilih.</span></div>
</section>
<section class="card card-flush ur-panel app-table-card"><div class="ur-panel-head app-table-heading"><div><h2 class="app-table-title">Utilisasi ICU per dokter</h2><p class="app-table-description">Episode, lama rawat dan tarif berdasarkan DPJP.</p></div><div class="app-table-search"><input id="icu-search" type="search" class="form-control form-control-solid" placeholder="Cari DPJP…" aria-label="Cari DPJP"></div></div><div class="ur-table-wrap"><table id="icu-table" data-datatable="manual" class="table table-row-dashed ur-table"><thead><tr><th>DPJP</th><th>Episode</th><th>Rata-rata LOS</th><th>Tarif RS</th><th>INA-CBG</th><th>Selisih</th></tr></thead><tbody id="icu-doctors"></tbody></table></div></section>
<div class="icu-method" role="note"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Dasar pembacaan.</strong> Episode dengan ICU_INDIKATOR berbeda dari 0. Hari ICU dan jam ventilator mengikuti data impor.</div></div></div></div>