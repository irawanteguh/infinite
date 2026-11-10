<div class="summary-report">
    <div class="report-hero mb-6">
        <div class="report-hero-content">
            <span class="report-eyebrow">LAPORAN MUTU &amp; KESELAMATAN PASIEN</span>
            <h1>Laporan Tahunan PMKP</h1>
            <p>Ringkasan kinerja untuk mendukung evaluasi dan pengambilan keputusan pimpinan.</p>
            <div class="report-hero-tags"><span><i class="bi bi-bar-chart-line"></i> Capaian indikator</span><span><i class="bi bi-diagram-3"></i> Analisis fishbone</span><span><i class="bi bi-arrow-repeat"></i> Tindak lanjut PDSA</span></div>
        </div>
        <div class="report-hero-symbol" aria-hidden="true"><i class="bi bi-file-earmark-bar-graph"></i></div>
    </div>

    <div class="report-stats mb-7">
        <div class="report-stat"><span class="report-stat-icon bg-light-primary text-primary"><i class="bi bi-calendar3"></i></span><div><span class="text-muted fs-7">Periode tersedia</span><strong id="report-period-count">—</strong><small>Periode pelaporan aktif</small></div></div>
        <div class="report-stat"><span class="report-stat-icon bg-light-success text-success"><i class="bi bi-person-check"></i></span><div><span class="text-muted fs-7">Dengan penanggung jawab</span><strong id="report-pic-count">—</strong><small>Periode yang memiliki PIC</small></div></div>
        <div class="report-stat"><span class="report-stat-icon bg-light-info text-info"><i class="bi bi-clock-history"></i></span><div><span class="text-muted fs-7">Periode terbaru</span><strong id="report-latest-period">—</strong><small>Tahun pelaporan terakhir</small></div></div>
    </div>

    <div class="card report-period-card">
        <div class="card-header border-0 pt-6 pb-5 gap-4">
            <div class="card-title flex-column align-items-start">
                <span class="report-eyebrow mb-2">ARSIP PELAPORAN</span>
                <h2 class="fw-bolder text-gray-900 fs-3 mb-1">Pilih periode laporan</h2>
                <span class="text-muted fs-7">Buka laporan PDF untuk melihat capaian, analisis, dan rencana perbaikan.</span>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative">
                    <i class="bi bi-search fs-4 position-absolute ms-5 text-gray-500"></i>
                    <input type="search" class="form-control form-control-solid w-250px ps-13" id="summaryReportSearch" placeholder="Cari periode, PIC, atau status" aria-label="Cari periode, PIC, atau status">
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div id="summaryReportLoading" class="text-center text-muted py-10" role="status"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memuat periode pelaporan…</div>
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-3" id="summaryReportTable">
                    <thead><tr class="text-muted fw-bolder bg-light">
                        <th class="ps-4">#</th><th>Periode pelaporan</th><th>Penanggung jawab</th><th>Tim mutu</th><th>Status</th><th>Terakhir diperbarui</th><th class="text-end pe-4">Aksi</th>
                    </tr></thead>
                    <tbody id="summaryReportRows" class="text-gray-700 fw-semibold"></tbody>
                </table>
            </div>
            <div id="summaryReportEmpty" class="report-empty text-center py-10 d-none" role="status">
                <span class="report-empty-icon"><i class="bi bi-file-earmark-bar-graph"></i></span>
                <h3 class="fs-5 fw-bold mt-5 mb-2">Belum ada periode pelaporan</h3>
                <p class="text-muted mb-0">Laporan akan tersedia setelah periode pelaporan ditambahkan.</p>
            </div>
            <div id="summaryReportError" class="alert alert-danger mt-5 d-none" role="alert">Daftar periode gagal dimuat. <button type="button" class="btn btn-sm btn-light-danger ms-2" id="summaryReportRetry">Coba lagi</button></div>
            <div class="report-footer"><i class="bi bi-file-earmark-pdf me-2"></i>Laporan PDF dibuka pada tab baru sesuai periode yang dipilih.</div>
        </div>
    </div>
</div>
