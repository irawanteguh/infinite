<div class="card rounded-3 border border-gray-200">
    <div class="card-header border-0 pt-6 pb-5 gap-4">
        <div class="card-title flex-column align-items-start">
            <span class="text-muted fs-8 fw-bolder text-uppercase mb-2">Pengelolaan indikator mutu</span>
            <h2 class="fw-bolder text-gray-900 fs-3 mb-1">Indikator per departemen</h2>
            <span class="text-muted fs-7">Pantau target, capaian pengukuran, dan penanggung jawab indikator.</span>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center position-relative my-1">
                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="black"></rect>
                        <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="black"></path>
                    </svg>
                </span>
                <input type="search" data-kt-customer-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Cari indikator atau departemen" aria-label="Cari indikator atau departemen" id="searchtable">
            </div>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed gy-2" id="dataindikatorunit_table">
                <thead>
                    <tr class="fw-bolder text-muted bg-light">
                        <th class="text-start ps-4 rounded-start">#</th>
                        <th>Indikator</th>
                        <th>Periode</th>
                        <th>Departemen</th>
                        <th>Target</th>
                        <th>Capaian pengukuran</th>
                        <th>Status persetujuan</th>
                        <th>PIC indikator</th>
                        <th class="text-end pe-4 rounded-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="fw-bold text-gray-600" id="resultdataindikatorunit"></tbody>
            </table>
        </div>
    </div>
</div>
