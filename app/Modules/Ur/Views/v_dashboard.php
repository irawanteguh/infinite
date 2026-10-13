<div class="col-xl-12">
    <div class="row">

        <!-- Total Kunjungan -->
        <div class="col-xl-2 col-lg-4 col-md-6 mb-5">
            <div class="card border border-primary bg-light-primary shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-hospital-user fs-3 text-primary me-2"></i>
                        <div>
                            <div class="fw-bold fs-6 text-primary">
                                Total Kunjungan
                            </div>
                            <div class="text-muted" style="font-size:11px">
                                Total Episode BPJS
                            </div>
                        </div>
                    </div>

                    <div class="fw-bolder fs-4 text-primary mb-3">
                        <span id="jmlkunjungan">0</span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-procedures me-1"></i>
                            InPatient:
                            <span id="jmlkunjungan_inpatient">0</span>
                        </div>
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-user-md me-1"></i>
                            OutPatient:
                            <span id="jmlkunjungan_outpatient">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Tarif RS -->
        <div class="col-xl-2 col-lg-4 col-md-6 mb-5">
            <div class="card border border-info bg-light-info shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-hospital fs-3 text-info me-2"></i>
                        <div>
                            <div class="fw-bold fs-6 text-info">
                                Total Tarif RS
                            </div>
                            <div class="text-muted" style="font-size:11px">
                                Total Tarif Rumah Sakit
                            </div>
                        </div>
                    </div>

                    <div class="fw-bolder fs-5 text-info mb-3">
                        Rp <span id="jmltarifrs">0</span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-procedures me-1"></i>
                            InPatient: Rp <span id="jmltarifrs_inpatient">0</span>
                        </div>
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-user-md me-1"></i>
                            OutPatient: Rp <span id="jmltarifrs_outpatient">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total INA-CBG -->
        <div class="col-xl-2 col-lg-4 col-md-6 mb-5">
            <div class="card border border-success bg-light-success shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-file-invoice-dollar fs-3 text-success me-2"></i>
                        <div>
                            <div class="fw-bold fs-6 text-success">
                                Total INA-CBG
                            </div>
                            <div class="text-muted" style="font-size:11px">
                                Total Tarif INA-CBG
                            </div>
                        </div>
                    </div>

                    <div class="fw-bolder fs-5 text-success mb-3">
                        Rp <span id="jmlinacbg">0</span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-procedures me-1"></i>
                            InPatient: Rp <span id="jmlinacbg_inpatient">0</span>
                        </div>
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-user-md me-1"></i>
                            OutPatient: Rp <span id="jmlinacbg_outpatient">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total iDRG -->
        <div class="col-xl-2 col-lg-4 col-md-6 mb-5">
            <div class="card border border-success bg-light-success shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-project-diagram fs-3 text-success me-2"></i>
                        <div>
                            <div class="fw-bold fs-6 text-success">
                                Total iDRG
                            </div>
                            <div class="text-muted" style="font-size:11px">
                                Total Tarif iDRG
                            </div>
                        </div>
                    </div>

                    <div class="fw-bolder fs-5 text-success mb-3">
                        Rp <span id="jmlidrg">0</span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-procedures me-1"></i>
                            InPatient: Rp <span id="jmlidrg_inpatient">0</span>
                        </div>
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-user-md me-1"></i>
                            OutPatient: Rp <span id="jmlidrg_outpatient">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selisih RS vs INA-CBG -->
        <div class="col-xl-2 col-lg-4 col-md-6 mb-5">
            <div class="card border border-dark bg-light-dark shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-balance-scale fs-3 text-dark me-2"></i>
                        <div>
                            <div class="fw-bold fs-6 text-dark">
                                Selisih RS vs INA-CBG
                            </div>
                            <div class="text-muted" style="font-size:11px">
                                INA-CBG − Tarif RS
                            </div>
                        </div>
                    </div>

                    <div class="fw-bolder fs-5 text-dark mb-3">
                        Rp <span id="selisihinacbg">0</span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-procedures me-1"></i>
                            InPatient: Rp <span id="selisihinacbg_inpatient">0</span>
                        </div>
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-user-md me-1"></i>
                            OutPatient: Rp <span id="selisihinacbg_outpatient">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selisih RS vs iDRG -->
        <div class="col-xl-2 col-lg-4 col-md-6 mb-5">
            <div class="card border border-dark bg-light-dark shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-balance-scale fs-3 text-dark me-2"></i>
                        <div>
                            <div class="fw-bold fs-6 text-dark">
                                Selisih RS vs iDRG
                            </div>
                            <div class="text-muted" style="font-size:11px">
                                iDRG − Tarif RS
                            </div>
                        </div>
                    </div>

                    <div class="fw-bolder fs-5 text-dark mb-3">
                        Rp <span id="selisihidrg">0</span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-procedures me-1"></i>
                            InPatient: Rp <span id="selisihidrg_inpatient">0</span>
                        </div>
                        <div class="text-muted fw-semibold" style="font-size:11px">
                            <i class="fas fa-user-md me-1"></i>
                            OutPatient: Rp <span id="selisihidrg_outpatient">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="col-xl-12 mb-5">
    <div class="card card-flush">
        <div class="card-header pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Hospital Service Analytics</span>
                <span class="text-muted mt-1 fw-bold fs-7">Analisis tren kunjungan dan pendapatan pelayanan rumah sakit</span>
            </h3>
            <div class="card-toolbar m-0">
                <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0 fw-bolder" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link justify-content-center text-active-gray-800 active" data-bs-toggle="tab" role="tab" href="#visit">Visit Trends</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link justify-content-center text-active-gray-800" data-bs-toggle="tab" role="tab" href="#revenue">Revenue</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link justify-content-center text-active-gray-800" data-bs-toggle="tab" role="tab" href="#revenuecompare">Revenue Comparison</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-body py-3">
            <div class="tab-content">
                <div id="visit" class="card-body p-0 tab-pane fade show active" role="tabpanel">
                    <div class="card-rounded-bottom" id="trenvisit"></div>
                </div>
                <div id="revenue" class="card-body p-0 tab-pane fade " role="tabpanel">
                    <div class="card-rounded-bottom" id="trenrevenue"></div>
                </div>
                <div id="revenuecompare" class="card-body p-0 tab-pane fade" role="tabpanel">
                    <div class="card-rounded-bottom" id="trenrevenuecompare"></div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<div class="col-xl-12 mb-5">
    <div class="row">
        <div class="col-xl-9">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Monthly Hospital Service Cost Distribution InPatient</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Monthly breakdown of hospital service costs by tariff component</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="card-rounded-bottom" id="costdistribution"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Cost Component Distribution</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Distribusi biaya per komponen</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="card-rounded-bottom" id="costcomponentyear"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-12 mb-5">
    <div class="row">
        <div class="col-xl-9">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Severity Level Inpatient</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Distribusi tingkat keparahan kasus</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="card-rounded-bottom" id="severitylevelmountly"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Severity Level</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Distribusi tingkat keparahan kasus</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="card-rounded-bottom" id="severitylevelyear"></div>
                </div>
            </div>
        </div>
    </div>
</div>
