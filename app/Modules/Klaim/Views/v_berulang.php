<div class="alert alert-dismissible bg-light-info border border-info border-3 border-dashed d-flex flex-column flex-sm-row w-100 p-5 mb-10">
    <span class="svg-icon svg-icon-2hx svg-icon-info me-4 mb-5 mb-sm-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path opacity="0.3" d="M2 4V16C2 16.6 2.4 17 3 17H13L16.6 20.6C17.1 21.1 18 20.8 18 20V17H21C21.6 17 22 16.6 22 16V4C22 3.4 21.6 3 21 3H3C2.4 3 2 3.4 2 4Z" fill="black"></path>
            <path d="M18 9H6C5.4 9 5 8.6 5 8C5 7.4 5.4 7 6 7H18C18.6 7 19 7.4 19 8C19 8.6 18.6 9 18 9ZM16 12C16 11.4 15.6 11 15 11H6C5.4 11 5 11.4 5 12C5 12.6 5.4 13 6 13H15C15.6 13 16 12.6 16 12Z" fill="black"></path>
        </svg>
    </span>

    <div class="d-flex flex-column pe-0 pe-sm-10">
        <h5 class="mb-2">Rekomendasi Sistem Deteksi Prosedur Medis Berulang</h5>
        <p class="mb-3">
            Pemantauan prosedur medis berulang untuk mengidentifikasi pola pelayanan, mengevaluasi kebutuhan klinis, dan mendukung efisiensi pelayanan rumah sakit.
        </p>

        <span class="fw-bold text-gray-800 mb-2">Fitur Utama:</span>

        <ul class="mb-0 ps-5">
            <li class="mb-2">
                <strong>Deteksi Pengulangan:</strong>
                mengidentifikasi prosedur yang sama pada pasien yang sama.
            </li>
            <li class="mb-2">
                <strong>Pemeriksaan Interval:</strong>
                menganalisis jarak waktu antarprosedur, misalnya dalam 1, 7,
                atau 30 hari.
            </li>
            <li class="mb-2">
                <strong>Identifikasi Pola:</strong>
                membedakan pengulangan dalam episode rawat yang sama dan
                kunjungan yang berbeda.
            </li>
        </ul>
    </div>
</div>

<div class="col-xl-12 mb-5">
    <div class="card card-flush">
        <div class="card-header pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Repeated Procedure Analysis</span>
                <span class="text-muted mt-1 fw-bold fs-7"> Analisis frekuensi prosedur berulang pada pasien berdasarkan periode pelayanan rumah sakit.</span>
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