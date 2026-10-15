<div class="col-xl-12">
    <div class="row">

        <!-- Jumlah Pending -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-5">
            <div class="card border border-warning bg-light-warning shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-hourglass-half fs-2x text-warning me-3"></i>
                        <div>
                            <div class="fw-bold fs-5 text-warning">
                                Jumlah Pending
                            </div>
                            <div class="text-muted fs-7">
                                Total kasus pending klaim
                            </div>
                        </div>
                    </div>
                    <div class="fw-bolder fs-2 text-warning">
                        <span id="jmlpending">0</span>
                        <span class="fs-7 text-muted">kasus</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jumlah Tidak Layak -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-5">
            <div class="card border border-danger bg-light-danger shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-ban fs-2x text-danger me-3"></i>
                        <div>
                            <div class="fw-bold fs-5 text-danger">
                                Jumlah Tidak Layak
                            </div>
                            <div class="text-muted fs-7">
                                Total kasus tidak layak klaim
                            </div>
                        </div>
                    </div>
                    <div class="fw-bolder fs-2 text-danger">
                        <span id="jmltidaklayak">0</span>
                        <span class="fs-7 text-muted">kasus</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nilai Uang Pending -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-5">
            <div class="card border border-warning bg-light-warning shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-file-invoice-dollar fs-2x text-warning me-3"></i>
                        <div>
                            <div class="fw-bold fs-5 text-warning">
                                Nilai Uang Pending
                            </div>
                            <div class="text-muted fs-7">
                                Total pengajuan pending klaim
                            </div>
                        </div>
                    </div>
                    <div class="fw-bolder fs-2 text-warning">
                        Rp <span id="nilaipending">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nilai Uang Tidak Layak -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-5">
            <div class="card border border-danger bg-light-danger shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-money-bill-wave fs-2x text-danger me-3"></i>
                        <div>
                            <div class="fw-bold fs-5 text-danger">
                                Nilai Uang Tidak Layak
                            </div>
                            <div class="text-muted fs-7">
                                Total pengajuan tidak layak klaim
                            </div>
                        </div>
                    </div>
                    <div class="fw-bolder fs-2 text-danger">
                        Rp <span id="nilaitidaklayak">0</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="col-xl-12 mb-5">
    <div class="row">
        <div class="col-xl-6 mb-5">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">BPJS Claim Case Analytics</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Analisis tren pending klaim dan klaim tidak layak rumah sakit</span>
                    </h3>
                    <div class="card-toolbar m-0">
                        <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0 fw-bolder" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link justify-content-center text-active-gray-800 active" data-bs-toggle="tab" role="tab" href="#pending">Pending</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link justify-content-center text-active-gray-800" data-bs-toggle="tab" role="tab" href="#tidaklayak">Tidak Layak</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body py-3">
                    <div class="tab-content">
                        <div id="pending" class="card-body p-0 tab-pane fade show active" role="tabpanel">
                            <div class="card-rounded-bottom" id="trenpending"></div>
                        </div>
                        <div id="tidaklayak" class="card-body p-0 tab-pane fade " role="tabpanel">
                            <div class="card-rounded-bottom" id="trentidaklayak"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-5">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">BPJS Claim Value Analytics</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Analisis tren nilai rupiah pending klaim dan klaim tidak layak rumah sakit</span>
                    </h3>
                    <div class="card-toolbar m-0">
                        <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0 fw-bolder" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link justify-content-center text-active-gray-800 active" data-bs-toggle="tab" role="tab" href="#pendingnilai">Pending</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link justify-content-center text-active-gray-800" data-bs-toggle="tab" role="tab" href="#tidaklayaknilai">Tidak Layak</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body py-3">
                    <div class="tab-content">
                        <div id="pendingnilai" class="card-body p-0 tab-pane fade show active" role="tabpanel">
                            <div class="card-rounded-bottom" id="trenpendingnilai"></div>
                        </div>
                        <div id="tidaklayaknilai" class="card-body p-0 tab-pane fade " role="tabpanel">
                            <div class="card-rounded-bottom" id="trentidaklayaknilai"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-12 mb-5">
    <div class="row">
        <div class="col-xl-6 mb-5">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Algorithm Analysis</span>
                        <span class="text-muted mt-1 fw-bold fs-7">VAnalisis frekuensi algoritma verifikasi berdasarkan jumlah temuan pending klaim pada periode terpilih.</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div id="trenalgoritma" style="width: 100%; height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-5">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Algorithm Breakdown Analysis</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Visualisasi frekuensi kemunculan algoritma verifikasi berdasarkan jumlah temuan pending klaim pada periode terpilih.</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div id="trenalgoritmabreakdown" style="width: 100%; height: 350px;"></div>
                </div>
            </div>
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