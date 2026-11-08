<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title d-flex flex-column align-items-start gap-3">
            <div>
                <h2 class="fs-3 fw-bolder text-gray-900 mb-1">Master Organisasi Rumah Sakit</h2>
                <div class="text-gray-600 fs-7">Kelola rumah sakit dan organisasi induknya dalam satu hirarki.</div>
            </div>
            <div class="d-flex align-items-center position-relative my-1">
                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="black"></rect>
                        <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="black"></path>
                    </svg>
                </span>
                <input type="text" data-kt-customer-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search Data" id="searchtable">
            </div>
        </div>
    </div>

    <div class="card-body pt-0 pb-5 border-bottom">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="border border-primary border-dashed rounded p-4 h-100 bg-light-primary">
                    <div class="text-primary fw-bolder fs-7 text-uppercase mb-3"><i class="bi bi-buildings me-2"></i>Hirarki Rumah Sakit Swasta</div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge badge-light-primary py-3 px-4">PT / Badan Penyelenggara</span>
                        <i class="bi bi-arrow-right text-primary"></i>
                        <span class="badge badge-light-success py-3 px-4">Rumah Sakit Swasta</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="border border-success border-dashed rounded p-4 h-100 bg-light-success">
                    <div class="text-success fw-bolder fs-7 text-uppercase mb-3"><i class="bi bi-diagram-3 me-2"></i>Hirarki Rumah Sakit Pemerintah Daerah</div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge badge-light-primary py-3 px-4">Dinas Kesehatan</span>
                        <i class="bi bi-arrow-right text-success"></i>
                        <span class="badge badge-light-warning py-3 px-4">Suku Dinas Kesehatan</span>
                        <i class="bi bi-arrow-right text-success"></i>
                        <span class="badge badge-light-danger py-3 px-4">Rumah Sakit</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body pt-5">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed gy-2" id="masterorganization_table">
                <thead>
                    <tr class="fw-bolder text-muted bg-light">
                        <th class="text-start rounded-start ps-4">#</th>
                        <th>Organization / Hierarchy</th>
                        <th>Website / Email</th>
                        <th>Address</th>
                        <th>Executive Director</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th class="text-end rounded-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="fw-bold text-gray-600" id="resultmasterorganization"></tbody>
            </table>
        </div>
    </div>
</div>
