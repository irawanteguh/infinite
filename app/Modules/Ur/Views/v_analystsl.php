
<div class="col-xl-12">
    <div class="row">

        <!-- AKTUAL SL I -->
        <div class="col-xl-4 col-lg-4 col-md-6 mb-5">
            <div class="card border border-primary bg-light-primary shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-chart-bar fs-2 text-primary me-3"></i>
                        <div>
                            <div class="fw-bold fs-5 text-primary">Severity Level I</div>
                            <div class="text-muted fs-7">Data Aktual INA-CBG</div>
                        </div>
                    </div>

                    <div class="text-muted fs-7 mb-1">Jumlah Kasus</div>
                    <div class="fw-bolder fs-2 text-primary mb-4">
                        <span id="jmlkasussl1">0</span>
                    </div>

                    <div class="separator separator-dashed mb-3"></div>

                    <div class="text-muted fs-7 mb-1">Pendapatan INA-CBG</div>
                    <div class="fw-bolder fs-4 text-primary">
                        Rp <span id="pendapatansl1">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AKTUAL SL II -->
        <div class="col-xl-4 col-lg-4 col-md-6 mb-5">
            <div class="card border border-info bg-light-info shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-chart-bar fs-2 text-info me-3"></i>
                        <div>
                            <div class="fw-bold fs-5 text-info">Severity Level II</div>
                            <div class="text-muted fs-7">Data Aktual INA-CBG</div>
                        </div>
                    </div>

                    <div class="text-muted fs-7 mb-1">Jumlah Kasus</div>
                    <div class="fw-bolder fs-2 text-info mb-4">
                        <span id="jmlkasussl2">0</span>
                    </div>

                    <div class="separator separator-dashed mb-3"></div>

                    <div class="text-muted fs-7 mb-1">Pendapatan INA-CBG</div>
                    <div class="fw-bolder fs-4 text-info">
                        Rp <span id="pendapatansl2">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AKTUAL SL III -->
        <div class="col-xl-4 col-lg-4 col-md-6 mb-5">
            <div class="card border border-success bg-light-success shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-chart-bar fs-2 text-success me-3"></i>
                        <div>
                            <div class="fw-bold fs-5 text-success">Severity Level III</div>
                            <div class="text-muted fs-7">Data Aktual INA-CBG</div>
                        </div>
                    </div>

                    <div class="text-muted fs-7 mb-1">Jumlah Kasus</div>
                    <div class="fw-bolder fs-2 text-success mb-4">
                        <span id="jmlkasussl3">0</span>
                    </div>

                    <div class="separator separator-dashed mb-3"></div>

                    <div class="text-muted fs-7 mb-1">Pendapatan INA-CBG</div>
                    <div class="fw-bolder fs-4 text-success">
                        Rp <span id="pendapatansl3">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SIMULASI SL I -->
        <div class="col-xl-4 col-lg-4 col-md-6 mb-5">
            <div class="card border border-primary border-dashed shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-exchange-alt fs-2 text-primary me-3"></i>
                        <div>
                            <div class="fw-bold fs-5 text-primary">Simulasi SL I</div>
                            <div class="text-muted fs-7">Perpindahan ke SL II</div>
                        </div>
                    </div>

                    <div class="text-muted fs-7 mb-1">Kasus Aktual</div>
                    <div class="fw-bold fs-6 text-gray-700">
                        <span id="simawaljmlkasussl1">0</span>
                    </div>

                    <div class="text-muted fs-7 mt-3 mb-1">Kasus Berpindah (10%)</div>
                    <div class="fw-bolder fs-5 text-danger">
                        -<span id="simpindahkasussl1">0</span> kasus
                    </div>

                    <div class="separator separator-dashed my-3"></div>

                    <div class="text-muted fs-7 mb-1">Kasus Setelah Simulasi</div>
                    <div class="fw-bolder fs-2 text-primary">
                        <span id="simjmlkasussl1">0</span>
                    </div>

                    <div class="text-muted fs-7 mt-3 mb-1">Pendapatan Simulasi</div>
                    <div class="fw-bolder fs-4 text-primary">
                        Rp <span id="simpendapatansl1">0</span>
                    </div>

                    <div class="separator separator-dashed my-3"></div>

                    <div class="rounded bg-light-primary p-3">
                        <div class="fw-bold text-primary fs-7 mb-1">
                            <i class="fas fa-info-circle me-1"></i> Asumsi Simulasi
                        </div>
                        <div class="text-gray-700 fs-7">
                            Sebanyak 10% kasus aktual SL I berpindah ke SL II.
                            Pendapatan dihitung dari kasus tersisa dikalikan
                            rata-rata tarif INA-CBG aktual SL I.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SIMULASI SL II -->
        <div class="col-xl-4 col-lg-4 col-md-6 mb-5">
            <div class="card border border-info border-dashed shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-exchange-alt fs-2 text-info me-3"></i>
                        <div>
                            <div class="fw-bold fs-5 text-info">Simulasi SL II</div>
                            <div class="text-muted fs-7">Menerima dari SL I · Ke SL III</div>
                        </div>
                    </div>

                    <div class="text-muted fs-7 mb-1">Kasus Aktual</div>
                    <div class="fw-bold fs-6 text-gray-700">
                        <span id="simawaljmlkasussl2">0</span>
                    </div>

                    <div class="text-muted fs-7 mt-3 mb-1">Masuk dari SL I</div>
                    <div class="fw-bolder fs-5 text-success">
                        +<span id="simmasukkasussl2">0</span> kasus
                    </div>

                    <div class="text-muted fs-7 mt-3 mb-1">Pindah ke SL III (10% aktual SL II)</div>
                    <div class="fw-bolder fs-5 text-danger">
                        -<span id="simpindahkasussl2">0</span> kasus
                    </div>

                    <div class="separator separator-dashed my-3"></div>

                    <div class="text-muted fs-7 mb-1">Kasus Setelah Simulasi</div>
                    <div class="fw-bolder fs-2 text-info">
                        <span id="simjmlkasussl2">0</span>
                    </div>

                    <div class="text-muted fs-7 mt-3 mb-1">Pendapatan Simulasi</div>
                    <div class="fw-bolder fs-4 text-info">
                        Rp <span id="simpendapatansl2">0</span>
                    </div>

                    <div class="separator separator-dashed my-3"></div>

                    <div class="rounded bg-light-info p-3">
                        <div class="fw-bold text-info fs-7 mb-1">
                            <i class="fas fa-info-circle me-1"></i> Asumsi Simulasi
                        </div>
                        <div class="text-gray-700 fs-7">
                            SL II menerima 10% kasus aktual SL I. Selanjutnya,
                            10% kasus aktual SL II sebelum menerima perpindahan
                            dialihkan ke SL III. Pendapatan menggunakan rata-rata
                            tarif INA-CBG aktual SL II.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SIMULASI SL III -->
        <div class="col-xl-4 col-lg-4 col-md-6 mb-5">
            <div class="card border border-success border-dashed shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-exchange-alt fs-2 text-success me-3"></i>
                        <div>
                            <div class="fw-bold fs-5 text-success">Simulasi SL III</div>
                            <div class="text-muted fs-7">Menerima dari SL II</div>
                        </div>
                    </div>

                    <div class="text-muted fs-7 mb-1">Kasus Aktual</div>
                    <div class="fw-bold fs-6 text-gray-700">
                        <span id="simawaljmlkasussl3">0</span>
                    </div>

                    <div class="text-muted fs-7 mt-3 mb-1">Masuk dari SL II</div>
                    <div class="fw-bolder fs-5 text-success">
                        +<span id="simmasukkasussl3">0</span> kasus
                    </div>

                    <div class="separator separator-dashed my-3"></div>

                    <div class="text-muted fs-7 mb-1">Kasus Setelah Simulasi</div>
                    <div class="fw-bolder fs-2 text-success">
                        <span id="simjmlkasussl3">0</span>
                    </div>

                    <div class="text-muted fs-7 mt-3 mb-1">Pendapatan Simulasi</div>
                    <div class="fw-bolder fs-4 text-success">
                        Rp <span id="simpendapatansl3">0</span>
                    </div>

                    <div class="separator separator-dashed my-3"></div>

                    <div class="rounded bg-light-success p-3">
                        <div class="fw-bold text-success fs-7 mb-1">
                            <i class="fas fa-info-circle me-1"></i> Asumsi Simulasi
                        </div>
                        <div class="text-gray-700 fs-7">
                            SL III menerima 10% dari kasus aktual SL II.
                            Pendapatan dihitung dari total kasus setelah simulasi
                            dikalikan rata-rata tarif INA-CBG aktual SL III.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<!-- SUMMARY PERUBAHAN AKTUAL VS SIMULASI -->
<div class="row g-5 g-xl-8 mb-5">

    <!-- PERUBAHAN JUMLAH KASUS -->
    <div class="col-xl-4 col-md-6">
        <div class="card card-flush h-100 border border-primary">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-4 fw-bold text-primary">
                        Perubahan Jumlah Kasus
                    </span>
                    <span class="text-gray-500 fw-semibold fs-7">
                        Simulasi dibanding aktual
                    </span>
                </div>
                <div class="card-toolbar">
                    <span class="symbol symbol-45px">
                        <span class="symbol-label bg-light-primary">
                            <i class="bi bi-arrow-left-right fs-2 text-primary"></i>
                        </span>
                    </span>
                </div>
            </div>

            <div class="card-body pt-2">
                <div class="d-flex flex-column mb-5">
                    <span class="text-gray-600 fw-semibold fs-7 mb-1">
                        Total Kasus Aktual
                    </span>
                    <span id="summarykasusaktual"
                          class="fs-3 fw-bold text-gray-900">0</span>
                </div>

                <div class="d-flex flex-column mb-5">
                    <span class="text-gray-600 fw-semibold fs-7 mb-1">
                        Total Kasus Simulasi
                    </span>
                    <span id="summarykasussimulasi"
                          class="fs-3 fw-bold text-primary">0</span>
                </div>

                <div class="separator separator-dashed mb-4"></div>

                <div class="d-flex flex-column">
                    <span class="text-gray-600 fw-semibold fs-7 mb-1">
                        Perubahan Jumlah Kasus
                    </span>
                    <span id="summaryperubahankasus"
                          class="fs-3 fw-bold text-success">0 (0%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- PERUBAHAN PENDAPATAN -->
    <div class="col-xl-4 col-md-6">
        <div class="card card-flush h-100 border border-success">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-4 fw-bold text-success">
                        Perubahan Pendapatan
                    </span>
                    <span class="text-gray-500 fw-semibold fs-7">
                        Pendapatan INA-CBG simulasi vs aktual
                    </span>
                </div>
                <div class="card-toolbar">
                    <span class="symbol symbol-45px">
                        <span class="symbol-label bg-light-success">
                            <i class="bi bi-graph-up-arrow fs-2 text-success"></i>
                        </span>
                    </span>
                </div>
            </div>

            <div class="card-body pt-2">
                <div class="d-flex flex-column mb-5">
                    <span class="text-gray-600 fw-semibold fs-7 mb-1">
                        Pendapatan Aktual
                    </span>
                    <span class="fs-3 fw-bold text-gray-900">
                        Rp <span id="summarypendapatanaktual">0</span>
                    </span>
                </div>

                <div class="d-flex flex-column mb-5">
                    <span class="text-gray-600 fw-semibold fs-7 mb-1">
                        Pendapatan Simulasi
                    </span>
                    <span class="fs-3 fw-bold text-success">
                        Rp <span id="summarypendapatansimulasi">0</span>
                    </span>
                </div>

                <div class="separator separator-dashed mb-4"></div>

                <div class="d-flex flex-column">
                    <span class="text-gray-600 fw-semibold fs-7 mb-1">
                        Selisih Pendapatan
                    </span>
                    <span id="summaryselisihpendapatan" class="fs-3 fw-bold text-success">Rp 0 (0%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- RATA-RATA PENDAPATAN -->
    <div class="col-xl-4 col-md-6">
        <div class="card card-flush h-100 border border-info">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-4 fw-bold text-info">
                        Rata-rata Pendapatan per Kasus
                    </span>
                    <span class="text-gray-500 fw-semibold fs-7">
                        INA-CBG per kasus
                    </span>
                </div>
                <div class="card-toolbar">
                    <span class="symbol symbol-45px">
                        <span class="symbol-label bg-light-info">
                            <i class="bi bi-calculator fs-2 text-info"></i>
                        </span>
                    </span>
                </div>
            </div>

            <div class="card-body pt-2">
                <div class="d-flex flex-column mb-5">
                    <span class="text-gray-600 fw-semibold fs-7 mb-1">
                        Aktual per Kasus
                    </span>
                    <span class="fs-3 fw-bold text-gray-900">
                        Rp <span id="summaryrataaktual">0</span>
                    </span>
                </div>

                <div class="d-flex flex-column mb-5">
                    <span class="text-gray-600 fw-semibold fs-7 mb-1">
                        Simulasi per Kasus
                    </span>
                    <span class="fs-3 fw-bold text-info">
                        Rp <span id="summaryratasimulasi">0</span>
                    </span>
                </div>

                <div class="separator separator-dashed mb-4"></div>

                <div class="d-flex flex-column">
                    <span class="text-gray-600 fw-semibold fs-7 mb-1">
                        Perubahan Rata-rata
                    </span>
                    <span id="summarypersenrata"
                          class="fs-3 fw-bold text-info">0%</span>
                </div>
            </div>
        </div>
    </div>

</div>


<div class="col-xl-12 mb-5">
    <div class="row">
        <div class="col-xl-12">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Severity Level Inpatient</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Jumlah Kasus Berdasarkan Severity Level</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="card-rounded-bottom" id="severitylevelmountly"></div>
                </div>
            </div>
        </div>
        <!-- <div class="col-xl-3">
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
        </div> -->
    </div>
</div>

<div class="col-xl-12 mb-5">
    <div class="row">
        <div class="col-xl-12">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Severity Level Inpatient</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Jumlah Pendapatan Berdasarkan Severity Level</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="card-rounded-bottom" id="severitylevelmountlypendapatan"></div>
                </div>
            </div>
        </div>
        <!-- <div class="col-xl-3">
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
        </div> -->
    </div>
</div>