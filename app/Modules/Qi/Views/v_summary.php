<div class="d-flex flex-wrap flex-stack mb-6">
    <div>
        <h2 class="text-gray-900 fw-bolder mb-2">Summary Penilaian Indikator Mutu</h2>
        <p class="text-muted mb-0">Ringkasan capaian indikator departemen dan Indikator Nasional Mutu.</p>
    </div>
    <span class="badge badge-light-primary fs-6 mt-3 mt-md-0">Periode <span id="summary-period-label"><?= esc($selectedPeriod ?: '-') ?></span></span>
</div>

<div class="row g-5 mb-6">
    <div class="col-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <span class="text-muted fw-bold fs-7">Total indikator</span>
                <span class="text-gray-900 fw-bolder fs-2x" id="summary-total-indicators">0</span>
                <span class="text-muted fs-8">Indikator aktif pada periode</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <span class="text-muted fw-bold fs-7">Indikator Nasional Mutu</span>
                <span class="text-primary fw-bolder fs-2x" id="summary-national-indicators">0</span>
                <span class="text-muted fs-8">Penetapan bertipe nasional</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <span class="text-muted fw-bold fs-7">Penilaian tercatat</span>
                <span class="text-success fw-bolder fs-2x" id="summary-total-reports">0</span>
                <span class="text-muted fs-8">Jumlah indikator-bulan yang dinilai</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <span class="text-muted fw-bold fs-7">Rata-rata capaian</span>
                <span class="text-warning fw-bolder fs-2x" id="summary-average">0%</span>
                <span class="text-muted fs-8">Rata-rata seluruh penilaian</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-5 mb-6">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header border-0 pt-6">
                <div class="card-title flex-column">
                    <h3 class="fw-bolder text-gray-900 mb-1">Tren capaian bulanan</h3>
                    <span class="text-muted fs-7">Perbandingan indikator departemen dan nasional</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="summaryTrendChart"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header border-0 pt-6">
                <div class="card-title flex-column">
                    <h3 class="fw-bolder text-gray-900 mb-1">Capaian per departemen</h3>
                    <span class="text-muted fs-7">Rata-rata penilaian pada periode terpilih</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="summaryDepartmentChart"></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="bi bi-search position-absolute ms-5 text-muted"></i>
                <input type="text" class="form-control form-control-solid w-250px ps-12" id="qualitySummarySearch" placeholder="Cari indikator atau departemen">
            </div>
        </div>
        <div class="card-toolbar">
            <span class="text-muted fs-7">Seluruh indikator pada periode terpilih</span>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed gy-3" id="qualitySummaryTable">
                <thead>
                    <tr class="fw-bolder text-muted bg-light">
                        <th class="ps-4">#</th>
                        <th>Kategori</th>
                        <th>Departemen</th>
                        <th>Kode Indikator</th>
                        <th>Indikator</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Bulan Terlapor</th>
                        <th class="text-center">Penilaian</th>
                        <th class="text-center pe-4">Rata-rata Capaian</th>
                    </tr>
                </thead>
                <tbody id="qualitySummaryRows" class="fw-semibold text-gray-700"></tbody>
            </table>
        </div>
    </div>
</div>
