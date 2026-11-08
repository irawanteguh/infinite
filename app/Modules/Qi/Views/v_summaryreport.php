<div class="d-flex flex-wrap flex-stack mb-6">
    <div>
        <h2 class="text-gray-900 fw-bolder mb-2">Laporan Tahunan PMKP</h2>
        <p class="text-muted mb-0">Pilih periode untuk menyusun laporan pimpinan yang merangkum capaian indikator, analisis akar masalah fishbone, dan tindak lanjut PDSA.</p>
    </div>
</div>

<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="bi bi-search fs-3 position-absolute ms-5 text-gray-500"></i>
                <input type="text" class="form-control form-control-solid w-250px ps-13" id="summaryReportSearch" placeholder="Cari periode / status">
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed gy-3" id="summaryReportTable">
                <thead>
                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase bg-light">
                        <th class="ps-4">#</th>
                        <th>Periode pelaporan</th>
                        <th>Penanggung jawab</th>
                        <th>Tim mutu</th>
                        <th>Status</th>
                        <th>Terakhir diperbarui</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody id="summaryReportRows" class="text-gray-700 fw-semibold"></tbody>
            </table>
        </div>
        <div id="summaryReportEmpty" class="text-center text-muted py-10 d-none">
            <i class="bi bi-file-earmark-bar-graph fs-3x d-block mb-3"></i>
            Belum ada periode pelaporan yang tersedia.
        </div>
    </div>
</div>
