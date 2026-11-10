<div class="quality-summary">
    <div class="summary-heading mb-6">
        <div>
            <div class="summary-eyebrow">DASHBOARD MUTU</div>
            <h1 class="fw-bolder text-gray-900 mt-2 mb-2">Ringkasan indikator</h1>
            <p class="text-muted mb-0">Pantau target, temukan keterlambatan, dan telusuri kinerja setiap departemen.</p>
        </div>
        <span class="summary-period"><i class="bi bi-calendar3 me-2"></i>Periode <strong data-summary-period><?= esc($selectedPeriod ?: '�') ?></strong></span>
    </div>

    <div class="summary-overview mb-6">
        <div class="summary-primary">
            <span class="summary-eyebrow">KETERCAPAIAN TARGET</span>
            <div id="summary-average" class="summary-primary-value">�</div>
            <div class="summary-progress"><div id="summary-target-progress" style="width:0%"></div></div>
            <p class="mb-0 mt-4">Persentase penilaian bulanan yang memenuhi target.</p>
            <a href="#summary-analysis" class="summary-primary-link mt-5">Lihat analisis <i class="bi bi-arrow-down-right ms-2"></i></a>
        </div>
        <div class="summary-metrics">
            <div class="summary-metric"><span class="summary-metric-icon bg-light-primary text-primary"><i class="bi bi-clipboard2-pulse"></i></span><div><div class="text-muted fs-7">Total indikator</div><div class="summary-metric-value" id="summary-total-indicators">0</div><span class="text-muted fs-8">Penugasan pada periode ini</span></div></div>
            <div class="summary-metric"><span class="summary-metric-icon bg-light-success text-success"><i class="bi bi-check2-square"></i></span><div><div class="text-muted fs-7">Sudah dinilai</div><div class="summary-metric-value" id="summary-assessed-indicators">0</div><span class="text-muted fs-8">Memiliki data capaian</span></div></div>
            <div class="summary-metric"><span class="summary-metric-icon bg-light-info text-info"><i class="bi bi-globe2"></i></span><div><div class="text-muted fs-7">Indikator nasional</div><div class="summary-metric-value" id="summary-national-indicators">0</div><span class="text-muted fs-8">Penugasan indikator nasional</span></div></div>
            <div class="summary-metric"><span class="summary-metric-icon bg-light-warning text-warning"><i class="bi bi-building"></i></span><div><div class="text-muted fs-7">Departemen tercakup</div><div class="summary-metric-value" id="summary-departments">0</div><span class="text-muted fs-8">Unit dengan indikator</span></div></div>
        </div>
    </div>

    <a href="#summary-overdue-section" class="summary-attention mb-8">
        <span class="summary-attention-icon"><i class="bi bi-clock-history"></i></span>
        <span class="flex-grow-1"><strong class="d-block">Pengisian yang perlu ditindaklanjuti</strong><span class="summary-attention-caption" id="summary-overdue-caption">Memuat status pengisian periode terpilih�</span></span>
        <span class="summary-attention-count" id="summary-overdue-total">�</span><i class="bi bi-arrow-down"></i>
    </a>
    <nav class="summary-section-nav mb-8" aria-label="Navigasi ringkasan">
        <a href="#summary-types">Jenis indikator</a><a href="#summary-analysis">Analisis kinerja</a><a href="#summary-overdue-section">Keterlambatan</a><a href="#summary-aggregates">Agregat indikator</a><a href="#summary-detail">Detail departemen</a>
    </nav>

<div class="mb-4" id="summary-types">
    <h3 class="fw-bolder text-gray-900 mb-1">Ringkasan berdasarkan jenis indikator</h3>
    <span class="text-muted fs-7">Ketercapaian target dari penilaian yang sudah diisi, dikelompokkan menurut jenis indikator</span>
</div>
<div class="row row-cols-1 row-cols-md-2 row-cols-xxl-5 g-5 mb-6" id="summaryIndicatorTypes">
    <div class="col" data-indicator-type="1f513f16-d546-11f1-91d6-202b2018cf5a"><div class="card card-flush h-100 summary-type-card border-top border-3 border-primary"><div class="card-body py-5"><div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted fw-bold fs-7">Indikator Global</span><i class="bi bi-globe2 fs-2 text-primary"></i></div><div class="text-gray-900 fw-bolder fs-2x" data-type-count>0</div><div class="text-muted fs-8">indikator · <span data-type-assessed>0</span> dinilai</div><div class="separator my-3"></div><div class="text-primary fw-bold">Ketercapaian target <span data-type-average>—</span></div></div></div></div>
    <div class="col" data-indicator-type="25ae8766-7384-11f1-a5e6-e0be037258a4"><div class="card card-flush h-100 summary-type-card border-top border-3 border-info"><div class="card-body py-5"><div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted fw-bold fs-7">Indikator Mutu Nasional</span><i class="bi bi-award fs-2 text-info"></i></div><div class="text-gray-900 fw-bolder fs-2x" data-type-count>0</div><div class="text-muted fs-8">indikator · <span data-type-assessed>0</span> dinilai</div><div class="separator my-3"></div><div class="text-info fw-bold">Ketercapaian target <span data-type-average>—</span></div></div></div></div>
    <div class="col" data-indicator-type="25ae8926-7384-11f1-a5e6-e0be037258a4"><div class="card card-flush h-100 summary-type-card border-top border-3 border-warning"><div class="card-body py-5"><div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted fw-bold fs-7">Indikator Prioritas Rumah Sakit</span><i class="bi bi-hospital fs-2 text-warning"></i></div><div class="text-gray-900 fw-bolder fs-2x" data-type-count>0</div><div class="text-muted fs-8">indikator · <span data-type-assessed>0</span> dinilai</div><div class="separator my-3"></div><div class="text-warning fw-bold">Ketercapaian target <span data-type-average>—</span></div></div></div></div>
    <div class="col" data-indicator-type="25ae899b-7384-11f1-a5e6-e0be037258a4"><div class="card card-flush h-100 summary-type-card border-top border-3 border-success"><div class="card-body py-5"><div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted fw-bold fs-7">Indikator Mutu Department</span><i class="bi bi-diagram-3 fs-2 text-success"></i></div><div class="text-gray-900 fw-bolder fs-2x" data-type-count>0</div><div class="text-muted fs-8">indikator · <span data-type-assessed>0</span> dinilai</div><div class="separator my-3"></div><div class="text-success fw-bold">Ketercapaian target <span data-type-average>—</span></div></div></div></div>
    <div class="col" data-indicator-type="38505839-d546-11f1-91d6-202b2018cf5a"><div class="card card-flush h-100 summary-type-card border-top border-3 border-danger"><div class="card-body py-5"><div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted fw-bold fs-7">Indikator Keselamatan Pasien</span><i class="bi bi-shield-check fs-2 text-danger"></i></div><div class="text-gray-900 fw-bolder fs-2x" data-type-count>0</div><div class="text-muted fs-8">indikator · <span data-type-assessed>0</span> dinilai</div><div class="separator my-3"></div><div class="text-danger fw-bold">Ketercapaian target <span data-type-average>—</span></div></div></div></div>
</div>

<div class="summary-section-title" id="summary-analysis"><div><span class="summary-eyebrow">ANALISIS KINERJA</span><h2>Tren dan sebaran ketercapaian</h2></div><span class="text-muted fs-7">Hanya penilaian yang sudah terisi</span></div>
<div class="row g-5 mb-6">
    <div class="col-xl-8 col-12">
        <div class="card h-100">
            <div class="card-header border-0 pt-6">
                <div class="card-title flex-column">
                    <h3 class="fw-bolder text-gray-900 mb-1">Tren ketercapaian target bulanan</h3>
                    <span class="text-muted fs-7">Persentase penilaian bulanan yang memenuhi target: departemen dan nasional</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="summaryTrendChart"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-12">
        <div class="card h-100">
            <div class="card-header border-0 pt-6">
                <div class="card-title flex-column">
                    <h3 class="fw-bolder text-gray-900 mb-1">Ketercapaian target per dimensi mutu</h3>
                    <span class="text-muted fs-7">Persentase penilaian bulanan yang memenuhi target</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="summaryDimensionRadar"></div>
                <div id="summaryDimensionRadarEmpty" class="text-center text-muted py-8 d-none">Belum ada capaian terukur untuk dimensi mutu pada periode ini.</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-5 mb-6">
    <div class="col-12">
        <div class="card h-100">
            <div class="card-header border-0 pt-6">
                <div class="card-title flex-column">
                    <h3 class="fw-bolder text-gray-900 mb-1">Peringkat ketercapaian target departemen</h3>
                    <span class="text-muted fs-7" id="summaryDepartmentCaption">Persentase penilaian bulanan yang memenuhi target</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="summaryDepartmentChart"></div>
                <div id="summaryDepartmentEmpty" class="text-center text-muted py-8 d-none"><i class="bi bi-bar-chart-line fs-2x d-block mb-2"></i>Belum ada data capaian departemen pada periode ini.</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-8 summary-overdue-card" id="summary-overdue-section">
    <div class="card-header border-0 pt-6">
        <div class="card-title flex-column align-items-start">
            <span class="summary-eyebrow text-danger mb-2">TINDAK LANJUT PENGISIAN</span>
            <h3 class="fw-bolder text-gray-900 mb-1">Indikator melewati batas waktu</h3>
            <span class="text-muted fs-7">Periode terpilih · <span id="summary-overdue-count">0</span> indikator/departemen · Izin khusus yang masih berlaku tidak termasuk</span>
        </div>
        <div class="card-toolbar"><input type="search" class="form-control form-control-solid w-250px" id="summary-overdue-search" placeholder="Cari indikator atau departemen" aria-label="Cari indikator terlambat"></div>
    </div>
    <div class="card-body pt-0"><div class="table-responsive">
        <table class="table align-middle table-row-dashed gy-3" id="summary-overdue-table">
            <thead><tr class="fw-bolder text-muted bg-light">
                <th class="ps-4 rounded-start">#</th><th>Departemen</th><th>Indikator</th><th>Jenis</th>
                <th>Bulan belum diisi</th><th class="text-center">Jumlah bulan</th><th class="text-end pe-4 rounded-end">Status</th>
            </tr></thead>
            <tbody class="fw-semibold text-gray-700" id="summary-overdue-rows"></tbody>
        </table>
    </div></div>
</div>

<div id="summary-aggregates" class="summary-section-title"><div><span class="summary-eyebrow">GABUNGAN SELURUH UNIT</span><h2>Agregat indikator</h2></div><span class="text-muted fs-7">Buka rincian untuk melihat kontribusi departemen</span></div>
<ul class="nav summary-aggregate-tabs mb-5" role="tablist" aria-label="Jenis agregat indikator">
    <li class="nav-item" role="presentation"><button class="nav-link active" id="aggregate-global-tab" data-bs-toggle="tab" data-bs-target="#aggregate-global-panel" type="button" role="tab" aria-controls="aggregate-global-panel" aria-selected="true">Global</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" id="aggregate-national-tab" data-bs-toggle="tab" data-bs-target="#aggregate-national-panel" type="button" role="tab" aria-controls="aggregate-national-panel" aria-selected="false">Mutu Nasional</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" id="aggregate-priority-tab" data-bs-toggle="tab" data-bs-target="#aggregate-priority-panel" type="button" role="tab" aria-controls="aggregate-priority-panel" aria-selected="false">Prioritas Rumah Sakit</button></li>
</ul>
<div class="tab-content mb-8">
<?php foreach ([
    'global' => 'Indikator Global',
    'national' => 'Indikator Mutu Nasional',
    'priority' => 'Indikator Prioritas Rumah Sakit',
] as $aggregateKey => $aggregateTitle): ?>
<div class="tab-pane <?= $aggregateKey === 'global' ? 'show active' : '' ?>" id="aggregate-<?= esc($aggregateKey, 'attr') ?>-panel" role="tabpanel" aria-labelledby="aggregate-<?= esc($aggregateKey, 'attr') ?>-tab" tabindex="0">
<div class="card mb-6">
    <div class="card-header border-0 pt-6">
        <div class="card-title flex-column align-items-start">
            <h3 class="fw-bolder text-gray-900 mb-1"><?= esc($aggregateTitle) ?> — Agregat</h3>
            <span class="text-muted fs-7">Gabungan seluruh departemen pada periode terpilih</span>
        </div>
        <div class="card-toolbar"><input type="search" class="form-control form-control-solid w-250px" id="aggregate-<?= esc($aggregateKey, 'attr') ?>-search" placeholder="Cari indikator" aria-label="Cari <?= esc($aggregateTitle, 'attr') ?>"></div>
    </div>
    <div class="card-body pt-0"><div class="table-responsive">
        <table class="table align-middle table-row-dashed gy-3" id="aggregate-<?= esc($aggregateKey, 'attr') ?>-table">
            <thead><tr class="fw-bolder text-muted bg-light">
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

<div class="card" id="summary-detail">
    <div class="card-header border-0 pt-6">
        <div class="card-title flex-column align-items-start"><span class="summary-eyebrow mb-2">DATA LENGKAP</span><h3 class="fw-bolder mb-1">Detail indikator per departemen</h3><span class="text-muted fs-7">Capaian pengukuran dan kelengkapan pengisian setiap unit</span></div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="bi bi-search position-absolute ms-5 text-muted"></i>
                <input type="text" class="form-control form-control-solid w-250px ps-12" id="qualitySummarySearch" placeholder="Cari indikator atau departemen">
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed gy-3" id="qualitySummaryTable">
                <thead>
                    <tr class="fw-bolder text-muted bg-light">
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
