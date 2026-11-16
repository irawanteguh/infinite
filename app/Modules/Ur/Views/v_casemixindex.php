<div id="ur-executive" class="prem-page pe-dashboard ur-dashboard cm-page">
    <?php include APPPATH . 'Modules/Ur/Pageheader/casemixindex.php'; ?>
    <section class="prem-card ur-filterbar cm-filterbar" aria-labelledby="cm-filter-title">
        <div class="ur-filter-heading"><h2 id="cm-filter-title">Filter casemix</h2><p>Sesuaikan bulan pulang dan jenis layanan pada tahun yang dipilih di toolbar.</p></div>
        <div class="ur-filters">
            <div class="ur-filter"><label for="cm-month">Bulan pulang</label><select id="cm-month" data-control="select2" data-minimum-results-for-search="0" data-placeholder="Pilih bulan" class="form-select form-select-solid"><option value="all" selected>Pilih semua</option><?php foreach (['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $month): ?><option value="<?= $i + 1 ?>"><?= $month ?></option><?php endforeach; ?></select></div>
            <div class="ur-filter"><label for="cm-service">Jenis layanan</label><select id="cm-service" data-control="select2" data-minimum-results-for-search="0" data-placeholder="Pilih jenis layanan" class="form-select form-select-solid"><option value="all" selected>Pilih semua</option><option value="1">Rawat inap</option><option value="2">Rawat jalan</option></select></div>
            <span class="ur-filter-note"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>Data mengikuti tanggal pulang.<br>Tahun mengikuti periode pada toolbar.</span>
        </div>
        <div class="ur-scope"><i class="bi bi-calendar-range" aria-hidden="true"></i><span id="cm-coverage">Pilih cakupan untuk meninjau data.</span></div>
    </section>
    <div id="cm-status" class="ur-status" role="status" aria-live="polite" hidden></div>
    <div id="cm-content" hidden>
        <section id="cm-kpis" class="ur-kpis" aria-label="Ringkasan casemix"></section>
        <div id="cm-quality" class="alert alert-warning d-flex align-items-center gap-3" hidden><i class="bi bi-info-circle fs-3"></i><span id="cm-quality-text"></span></div>
        <div class="ur-grid-equal">
            <section class="card card-flush ur-panel"><div class="ur-panel-head"><div><h2>Perubahan komposisi bulanan</h2><p>Jumlah episode SL I, II, III, serta severity lain yang tercatat.</p></div></div><div id="cm-severity-chart" class="ur-chart"></div><div id="cm-severity-mix" class="cm-mix"></div></section>
            <section class="card card-flush ur-panel"><div class="ur-panel-head"><div><h2>Tujuh CMG dengan episode terbanyak</h2><p>Peringkat mengikuti filter layanan dan bulan yang dipilih.</p></div></div><div id="cm-cmg-chart" class="ur-chart"></div></section>
        </div>
        <section class="card card-flush ur-panel mb-5"><div class="ur-panel-head"><div><h2>Profil kelompok kasus</h2><p>Proporsi episode dan total INA-CBG per kelompok kasus.</p></div></div><div id="cm-casegroups" class="cm-casegroups"></div></section>
        <section class="card card-flush ur-panel">
            <div class="ur-panel-head cm-detail-head"><div><h2>Rekap bulanan casemix</h2><p>Telusuri episode dan nilai INA-CBG menurut kelompok, severity, atau CMG.</p></div><div class="cm-search"><label for="cm-search" class="visually-hidden">Cari kode atau nama kelompok</label><svg class="cm-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="m16 16 4.5 4.5"></path></svg><input id="cm-search" type="search" class="form-control form-control-solid form-control-sm" placeholder="Cari kode atau nama kelompok"></div></div>
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" id="cm-tab-cg" data-bs-toggle="tab" data-bs-target="#cm-detail-cg" type="button" role="tab" aria-controls="cm-detail-cg" aria-selected="true">Kelompok kasus</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="cm-tab-sl" data-bs-toggle="tab" data-bs-target="#cm-detail-sl" type="button" role="tab" aria-controls="cm-detail-sl" aria-selected="false">Severity</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="cm-tab-cmg" data-bs-toggle="tab" data-bs-target="#cm-detail-cmg" type="button" role="tab" aria-controls="cm-detail-cmg" aria-selected="false">CMG</button></li>
            </ul>
            <div class="tab-content">
                <?php foreach (['cg', 'sl', 'cmg'] as $index => $group): ?>
                <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="cm-detail-<?= $group ?>" role="tabpanel" aria-labelledby="cm-tab-<?= $group ?>"><div class="ur-table-wrap" tabindex="0" role="region" aria-label="Tabel rekap casemix, dapat digulir"><table class="table table-row-dashed align-middle ur-table cm-table"><thead id="cm-head-<?= $group ?>"></thead><tbody id="cm-body-<?= $group ?>"></tbody><tfoot id="cm-foot-<?= $group ?>"></tfoot></table></div></div>
                <?php endforeach; ?>
            </div>
        </section>
        <div class="ur-method mt-5"><strong>Dasar pembacaan.</strong> Episode bukan pasien unik. Distribusi severity dan ALOS hanya menggunakan rawat inap. Total INA-CBG memakai TOTAL_TARIF. Rata-rata bobot IDRG = jumlah IDRG_COST_WEIGHT positif pada rawat inap dibagi episode dengan bobot positif; ini ringkasan bobot impor, bukan pengganti indeks CMI resmi. Kode yang belum ada di master tetap dihitung. Tanda — berarti belum ada data pada bulan tersebut; kelengkapan bulan terakhir perlu dikonfirmasi.</div>
    </div>
</div>
