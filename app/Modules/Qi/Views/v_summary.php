<div class="quality-summary">
    <?php include APPPATH . 'Modules/Qi/Pageheader/summary.php'; ?>

    <div class="summary-overview mb-6">
        <div class="summary-primary">
            <span class="summary-eyebrow">KETERCAPAIAN TARGET</span>
            <div id="summary-average" class="summary-primary-value">—</div>
            <div class="summary-progress"><div id="summary-target-progress" style="width:0%"></div></div>
            <p class="mb-0 mt-4">Persentase penilaian bulanan yang memenuhi target.</p>
            <a href="#summary-analysis" class="summary-primary-link mt-5">Lihat analisis <i class="bi bi-arrow-down-right ms-2"></i></a>
        </div>
        <div class="summary-metrics">
            <div class="summary-metric"><span class="summary-metric-icon bg-light-primary text-primary"><i class="bi bi-clipboard2-pulse"></i></span><div><div class="text-muted fs-7">Total indikator</div><div class="summary-metric-value" id="summary-total-indicators">0</div><span class="text-muted fs-8">Penugasan pada periode ini</span></div></div>
            <div class="summary-metric"><span class="summary-metric-icon"><i class="bi bi-check2-square"></i></span><div><div class="text-muted fs-7">Sudah dinilai</div><div class="summary-metric-value" id="summary-assessed-indicators">0</div><span class="text-muted fs-8">Memiliki data capaian</span></div></div>
            <div class="summary-metric"><span class="summary-metric-icon bg-light-info text-info"><i class="bi bi-globe2"></i></span><div><div class="text-muted fs-7">Indikator nasional</div><div class="summary-metric-value" id="summary-national-indicators">0</div><span class="text-muted fs-8">Penugasan indikator nasional</span></div></div>
            <div class="summary-metric"><span class="summary-metric-icon"><i class="bi bi-building"></i></span><div><div class="text-muted fs-7">Departemen tercakup</div><div class="summary-metric-value" id="summary-departments">0</div><span class="text-muted fs-8">Unit dengan indikator</span></div></div>
        </div>
    </div>

    <a href="#summary-overdue-section" class="summary-attention mb-8">
        <span class="summary-attention-icon"><i class="bi bi-clock-history"></i></span>
        <span class="flex-grow-1"><strong class="d-block">Pengisian yang perlu ditindaklanjuti</strong><span class="summary-attention-caption" id="summary-overdue-caption">Memuat status pengisian periode terpilih—</span></span>
        <span class="summary-attention-count" id="summary-overdue-total">—</span><i class="bi bi-arrow-down"></i>
    </a>
    <nav class="app-filter-card mb-8" aria-labelledby="summary-navigation-title">
        <div class="app-filter-heading">
            <h2 id="summary-navigation-title"><i class="bi bi-compass me-2" aria-hidden="true"></i>Navigasi ringkasan</h2>
            <p>Pilih bagian untuk meninjau jenis indikator, kinerja, keterlambatan, atau rincian departemen.</p>
        </div>
        <div class="app-filter-fields">
            <div class="app-filter-options" aria-label="Bagian ringkasan indikator">
                <a class="btn btn-sm btn-light-primary" href="#summary-types"><i class="bi bi-grid me-2" aria-hidden="true"></i>Jenis indikator</a>
                <a class="btn btn-sm btn-light-primary" href="#summary-analysis"><i class="bi bi-graph-up me-2" aria-hidden="true"></i>Analisis kinerja</a>
                <a class="btn btn-sm btn-light-primary" href="#summary-overdue-section"><i class="bi bi-clock-history me-2" aria-hidden="true"></i>Keterlambatan</a>
                <a class="btn btn-sm btn-light-primary" href="#summary-aggregates"><i class="bi bi-intersect me-2" aria-hidden="true"></i>Agregat indikator</a>
                <a class="btn btn-sm btn-light-primary" href="#summary-detail"><i class="bi bi-table me-2" aria-hidden="true"></i>Detail departemen</a>
            </div>
        </div>
    </nav>

<div class="mb-4" id="summary-types">
    <h3 class="app-table-title">Ringkasan berdasarkan jenis indikator</h3>
    <span class="text-muted fs-7">Ketercapaian target dari penilaian yang sudah diisi, dikelompokkan menurut jenis indikator</span>
</div>
<div class="row row-cols-1 row-cols-md-2 row-cols-xxl-5 g-5 mb-6" id="summaryIndicatorTypes">
    <div class="col" data-indicator-type="1f513f16-d546-11f1-91d6-202b2018cf5a"><div class="card card-flush h-100 summary-type-card"><div class="card-body py-5"><div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted fw-bold fs-7">Indikator Global</span><i class="bi bi-globe2 fs-2 text-primary"></i></div><div class="text-gray-900 fw-semibold fs-2x" data-type-count>0</div><div class="text-muted fs-8">indikator · <span data-type-assessed>0</span> dinilai</div><div class="separator my-3"></div><div class="text-primary fw-bold">Ketercapaian target <span data-type-average>—</span></div></div></div></div>
    <div class="col" data-indicator-type="25ae8766-7384-11f1-a5e6-e0be037258a4"><div class="card card-flush h-100 summary-type-card"><div class="card-body py-5"><div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted fw-bold fs-7">Indikator Mutu Nasional</span><i class="bi bi-award fs-2 text-primary"></i></div><div class="text-gray-900 fw-semibold fs-2x" data-type-count>0</div><div class="text-muted fs-8">indikator · <span data-type-assessed>0</span> dinilai</div><div class="separator my-3"></div><div class="text-primary fw-bold">Ketercapaian target <span data-type-average>—</span></div></div></div></div>
    <div class="col" data-indicator-type="25ae8926-7384-11f1-a5e6-e0be037258a4"><div class="card card-flush h-100 summary-type-card"><div class="card-body py-5"><div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted fw-bold fs-7">Indikator Prioritas Rumah Sakit</span><i class="bi bi-hospital fs-2 text-primary"></i></div><div class="text-gray-900 fw-semibold fs-2x" data-type-count>0</div><div class="text-muted fs-8">indikator · <span data-type-assessed>0</span> dinilai</div><div class="separator my-3"></div><div class="text-primary fw-bold">Ketercapaian target <span data-type-average>—</span></div></div></div></div>
    <div class="col" data-indicator-type="25ae899b-7384-11f1-a5e6-e0be037258a4"><div class="card card-flush h-100 summary-type-card"><div class="card-body py-5"><div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted fw-bold fs-7">Indikator Mutu Departemen</span><i class="bi bi-diagram-3 fs-2 text-primary"></i></div><div class="text-gray-900 fw-semibold fs-2x" data-type-count>0</div><div class="text-muted fs-8">indikator · <span data-type-assessed>0</span> dinilai</div><div class="separator my-3"></div><div class="text-primary fw-bold">Ketercapaian target <span data-type-average>—</span></div></div></div></div>
    <div class="col" data-indicator-type="38505839-d546-11f1-91d6-202b2018cf5a"><div class="card card-flush h-100 summary-type-card"><div class="card-body py-5"><div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted fw-bold fs-7">Indikator Keselamatan Pasien</span><i class="bi bi-shield-check fs-2 text-primary"></i></div><div class="text-gray-900 fw-semibold fs-2x" data-type-count>0</div><div class="text-muted fs-8">indikator · <span data-type-assessed>0</span> dinilai</div><div class="separator my-3"></div><div class="text-primary fw-bold">Ketercapaian target <span data-type-average>—</span></div></div></div></div>
</div>

<div class="summary-section-title" id="summary-analysis"><div><span class="summary-eyebrow">ANALISIS KINERJA</span><h2>Tren dan sebaran ketercapaian</h2></div><span class="text-muted fs-7">Hanya penilaian yang sudah terisi</span></div>
<div class="row g-5 mb-6">
    <div class="col-12">
        <div class="card h-100">
            <div class="card-header app-table-heading">
                <div class="card-title flex-column">
                    <h3 class="app-table-title">Tren ketercapaian target bulanan</h3>
                    <p class="app-table-description">Persentase penilaian yang memenuhi target, per bulan untuk indikator departemen dan nasional.</p>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="summaryTrendChart"></div>
            </div>
        </div>
    </div>
</div>
<div class="row g-5 mb-6">
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header app-table-heading">
                <div class="card-title flex-column">
                    <h3 class="app-table-title">Ketercapaian target per dimensi mutu</h3>
                    <p class="app-table-description">Ketercapaian target pada setiap dimensi dari penilaian yang sudah terisi.</p>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="summaryDimensionRadar"></div>
                <div id="summaryDimensionRadarEmpty" class="text-center text-muted py-8 d-none">Belum ada capaian terukur untuk dimensi mutu pada periode ini.</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header app-table-heading">
                <div class="card-title flex-column">
                    <h3 class="app-table-title">Peringkat ketercapaian target departemen</h3>
                    <p class="app-table-description" id="summaryDepartmentCaption">Persentase penilaian bulanan yang memenuhi target</p>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="summaryDepartmentChart"></div>
                <div id="summaryDepartmentEmpty" class="text-center text-muted py-8 d-none"><i class="bi bi-bar-chart-line fs-2x d-block mb-2"></i>Belum ada data capaian departemen pada periode ini.</div>
            </div>
        </div>
    </div>
</div>

<div class="card app-table-card mb-8 summary-overdue-card" id="summary-overdue-section">
    <div class="card-header app-table-heading">
        <div>
            <h3 class="app-table-title">Indikator melewati batas waktu</h3>
            <p class="app-table-description">Periode terpilih · <span id="summary-overdue-count">0</span> indikator/departemen. Izin khusus yang masih berlaku tidak termasuk.</p>
        </div>
        <div class="app-table-search"><input type="search" class="form-control form-control-solid w-250px" id="summary-overdue-search" placeholder="Cari indikator atau departemen" aria-label="Cari indikator terlambat"></div>
    </div>
    <div class="card-body pt-0"><div class="table-responsive">
        <table class="table align-middle table-row-dashed fs-8 gy-2" id="summary-overdue-table" data-datatable="manual">
            <thead><tr class="fw-semibold text-muted bg-light">
                <th class="ps-4 rounded-start">#</th><th>Departemen</th><th>Indikator</th><th>Jenis</th>
                <th>Bulan belum diisi</th><th class="text-center">Jumlah bulan</th><th class="text-end pe-4 rounded-end">Status</th>
            </tr></thead>
            <tbody class="fw-semibold text-gray-700" id="summary-overdue-rows"></tbody>
        </table>
    </div></div>
</div>

<section id="summary-aggregates" class="alert alert-info d-flex align-items-start gap-3 mb-5" aria-labelledby="summary-aggregate-title">
    <i class="bi bi-info-circle fs-2" aria-hidden="true"></i>
    <div>
        <h2 id="summary-aggregate-title" class="mb-1">Agregat indikator</h2>
        <p class="mb-0">Gabungan seluruh unit pada periode terpilih. Pilih jenis indikator, lalu buka rincian untuk melihat kontribusi setiap departemen.</p>
    </div>
</section>
<section class="app-filter-card mb-5" aria-labelledby="summary-aggregate-filter-title">
    <div class="app-filter-heading">
        <h2 id="summary-aggregate-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter jenis indikator</h2>
        <p>Pilih jenis indikator untuk melihat agregat seluruh departemen.</p>
    </div>
    <div class="app-filter-fields">
        <div class="app-filter-options summary-aggregate-tabs" role="group" aria-label="Jenis agregat indikator">
            <button class="btn btn-sm btn-light-primary" aria-pressed="true" id="aggregate-global-tab" data-aggregate-target="#aggregate-global-panel" type="button" aria-controls="aggregate-global-panel">Global</button>
            <button class="btn btn-sm btn-light-primary" aria-pressed="false" id="aggregate-national-tab" data-aggregate-target="#aggregate-national-panel" type="button" aria-controls="aggregate-national-panel">Mutu Nasional</button>
            <button class="btn btn-sm btn-light-primary" aria-pressed="false" id="aggregate-priority-tab" data-aggregate-target="#aggregate-priority-panel" type="button" aria-controls="aggregate-priority-panel">Prioritas Rumah Sakit</button>
        </div>
    </div>
    <div id="summary-aggregate-filter-status" class="app-filter-scope" role="status">Menampilkan agregat Indikator Global.</div>
</section>
<div class="tab-content mb-8">
<?php foreach ([
    'global' => 'Indikator Global',
    'national' => 'Indikator Mutu Nasional',
    'priority' => 'Indikator Prioritas Rumah Sakit',
] as $aggregateKey => $aggregateTitle): ?>
<div class="tab-pane <?= $aggregateKey === 'global' ? 'show active' : '' ?>" id="aggregate-<?= esc($aggregateKey, 'attr') ?>-panel" role="tabpanel" aria-labelledby="aggregate-<?= esc($aggregateKey, 'attr') ?>-tab" tabindex="0">
<div class="card app-table-card mb-6">
    <div class="card-header app-table-heading">
        <div>
            <h3 class="app-table-title"><?= esc($aggregateTitle) ?> — Agregat</h3>
            <p class="app-table-description">Gabungan seluruh departemen pada periode terpilih.</p>
        </div>
        <div class="app-table-search"><input type="search" class="form-control form-control-solid w-250px" id="aggregate-<?= esc($aggregateKey, 'attr') ?>-search" placeholder="Cari indikator" aria-label="Cari <?= esc($aggregateTitle, 'attr') ?>"></div>
    </div>
    <div class="card-body pt-0"><div class="table-responsive">
        <table class="table align-middle table-row-dashed fs-8 gy-2" id="aggregate-<?= esc($aggregateKey, 'attr') ?>-table" data-datatable="manual">
            <thead><tr class="fw-semibold text-muted bg-light">
                <th class="ps-4 rounded-start">#</th><th>Indikator</th><th class="text-center">Departemen</th>
                <th class="text-center">Target</th><th class="text-center">Penilaian</th>
                <th class="text-center">Total numerator</th><th class="text-center">Total denominator</th>
                <th class="text-center">Capaian gabungan</th><th class="text-end">Ketercapaian target</th>
                <th class="text-end pe-4 rounded-end">Actions</th>
            </tr></thead>
            <tbody class="fw-semibold text-gray-700" id="aggregate-<?= esc($aggregateKey, 'attr') ?>-rows"></tbody>
        </table>
    </div></div>
</div>
</div>
<?php endforeach; ?>
</div>

<div class="card app-table-card" id="summary-detail">
    <div class="card-header app-table-heading">
        <div><h3 class="app-table-title">Detail indikator per departemen</h3><p class="app-table-description">Capaian pengukuran dan kelengkapan pengisian setiap unit.</p></div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="bi bi-search position-absolute ms-5 text-muted"></i>
                <input type="text" class="form-control form-control-solid w-250px ps-12" id="qualitySummarySearch" placeholder="Cari indikator atau departemen">
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-8 gy-2" id="qualitySummaryTable" data-datatable="manual">
                <thead>
                    <tr class="fw-semibold text-muted bg-light">
                        <th class="ps-4">#</th>
                        <th>Departemen</th>
                        <th>Indikator</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Bulan terisi</th>
                        <th class="text-center">Penilaian</th>
                        <th class="text-end pe-4">Rata-rata capaian</th>
                        <th class="text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody id="qualitySummaryRows" class="fw-semibold text-gray-700"></tbody>
            </table>
        </div>
    </div>
</div>

</div>
