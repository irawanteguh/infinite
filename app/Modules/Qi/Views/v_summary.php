<div class="d-flex flex-wrap flex-stack mb-6">
    <div>
        <h2 class="text-gray-900 fw-bolder mb-2">Ringkasan Indikator Mutu</h2>
        <p class="text-muted mb-0">Pantau capaian, kelengkapan penilaian, dan performa unit dalam satu periode.</p>
    </div>
    <span class="badge badge-light-primary fs-6 mt-3 mt-md-0"><i class="bi bi-calendar3 me-2"></i>Periode <span id="summary-period-label"><?= esc($selectedPeriod ?: '-') ?></span></span>
</div>

<div class="row row-cols-2 row-cols-md-3 row-cols-xxl-5 g-5 mb-6">
    <div class="col">
        <div class="card card-flush h-100 border-start border-4 border-primary">
            <div class="card-body d-flex align-items-center justify-content-between py-5">
                <div><span class="text-muted fw-bold fs-7">Total indikator</span><div class="text-gray-900 fw-bolder fs-2x mt-1" id="summary-total-indicators">0</div><span class="text-muted fs-8">Terdaftar pada periode</span></div>
                <span class="symbol symbol-50px"><span class="symbol-label bg-light-primary"><i class="bi bi-clipboard2-pulse fs-2x text-primary"></i></span></span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card card-flush h-100 border-start border-4 border-info">
            <div class="card-body d-flex align-items-center justify-content-between py-5">
                <div><span class="text-muted fw-bold fs-7">Indikator nasional</span><div class="text-info fw-bolder fs-2x mt-1" id="summary-national-indicators">0</div><span class="text-muted fs-8">Indikator Nasional Mutu</span></div>
                <span class="symbol symbol-50px"><span class="symbol-label bg-light-info"><i class="bi bi-globe2 fs-2x text-info"></i></span></span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card card-flush h-100 border-start border-4 border-success">
            <div class="card-body d-flex align-items-center justify-content-between py-5">
                <div><span class="text-muted fw-bold fs-7">Sudah dinilai</span><div class="text-success fw-bolder fs-2x mt-1" id="summary-assessed-indicators">0</div><span class="text-muted fs-8">Indikator punya data capaian</span></div>
                <span class="symbol symbol-50px"><span class="symbol-label bg-light-success"><i class="bi bi-check2-square fs-2x text-success"></i></span></span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card card-flush h-100 border-start border-4 border-warning">
            <div class="card-body d-flex align-items-center justify-content-between py-5">
                <div><span class="text-muted fw-bold fs-7">Rata-rata capaian</span><div class="text-warning fw-bolder fs-2x mt-1" id="summary-average">0%</div><span class="text-muted fs-8">Dari indikator yang dinilai</span></div>
                <span class="symbol symbol-50px"><span class="symbol-label bg-light-warning"><i class="bi bi-speedometer2 fs-2x text-warning"></i></span></span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card card-flush h-100 border-start border-4 border-danger">
            <div class="card-body d-flex align-items-center justify-content-between py-5">
                <div><span class="text-muted fw-bold fs-7">Departemen tercakup</span><div class="text-danger fw-bolder fs-2x mt-1" id="summary-departments">0</div><span class="text-muted fs-8">Unit dengan indikator</span></div>
                <span class="symbol symbol-50px"><span class="symbol-label bg-light-danger"><i class="bi bi-building fs-2x text-danger"></i></span></span>
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
                    <h3 class="fw-bolder text-gray-900 mb-1">Peringkat capaian departemen</h3>
                    <span class="text-muted fs-7" id="summaryDepartmentCaption">Rata-rata dari indikator yang sudah dinilai</span>
                </div>
                <div class="card-toolbar">
                    <select class="form-select form-select-sm form-select-solid w-150px" id="summaryDepartmentView" aria-label="Pilih kelompok departemen">
                        <option value="top">10 capaian tertinggi</option>
                        <option value="attention">Perlu perhatian</option>
                    </select>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="summaryDepartmentChart"></div>
                <div id="summaryDepartmentEmpty" class="text-center text-muted py-8 d-none"><i class="bi bi-bar-chart-line fs-2x d-block mb-2"></i>Belum ada data capaian departemen pada periode ini.</div>
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
                        <th class="text-center pe-4">Rata-rata capaian</th>
                    </tr>
                </thead>
                <tbody id="qualitySummaryRows" class="fw-semibold text-gray-700"></tbody>
            </table>
        </div>
    </div>
</div>
