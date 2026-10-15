
<div class="col-xl-12 mb-5">
    <div class="card card-flush">
        <div class="card-header pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Slit Lamp Procedure Analysis</span>
                <span class="text-muted mt-1 fw-bold fs-7">Analisis jumlah kasus, tren pelayanan, dan distribusi prosedur Slit Lamp berdasarkan periode pelayanan rumah sakit.</span>
            </h3>
        </div>
        <div class="card-body py-3">
            <div class="card-rounded-bottom" id="trencase"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
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
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed gy-2" id="dataraweklaim_table">
                <thead>
                    <tr class="fw-bolder text-muted bg-light">
                        <th class="text-start rounded-start ps-4">#</th>
                        <th>SEP Number</th>
                        <th>Patient Name</th>
                        <th>Admission Date</th>
                        <th>Discharge Date</th>
                        <th>Verification Date</th>
                        <th>Inpatient / Outpatient</th>
                        <th>INA-CBG Code</th>
                        <th>Severity Level</th>
                        <th>Algorithm Type</th>
                        <th>Verification Algorithm</th>
                        <th>Verification Notes</th>
                        <th class="text-end">Claim Amount</th>
                        <th>Claim Status</th>
                        <th class="text-end rounded-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="fw-bold text-gray-600" id="resultdataraweklaim"></tbody>
            </table>
        </div>
    </div>
</div>