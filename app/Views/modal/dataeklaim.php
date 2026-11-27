<style>
#modal_upload_txt_eklaim { color: #42636d; }
#modal_upload_txt_eklaim .modal-header { padding: 22px 28px; background: #f6faf9; border-bottom: 1px solid #dceae5; }
#modal_upload_txt_eklaim .modal-title { color: #15373e; font-size: 20px; font-weight: 600 !important; }
#modal_upload_txt_eklaim .modal-header p { color: #81969b !important; font-size: 12px !important; line-height: 1.7; }
#modal_upload_txt_eklaim .modal-body { background: #f6faf9 !important; }
#modal_upload_txt_eklaim .card { background: #fff; border: 1px solid #e1ebed !important; border-radius: 16px; box-shadow: 0 8px 28px #1c454b04; }
#modal_upload_txt_eklaim h3 { color: #234a54; font-size: 16px !important; font-weight: 600 !important; }
#modal_upload_txt_eklaim .card p, #modal_upload_txt_eklaim .form-text { color: #81969b !important; font-size: 12px !important; line-height: 1.7; }
#modal_upload_txt_eklaim .form-label { color: #648088; font-size: 12px; }
#modal_upload_txt_eklaim .form-control { font-size: 12px; border: 1px solid #dce9e5; border-radius: 8px; background: #f5f8fa; color: #42636d; }
#modal_upload_txt_eklaim .btn-primary { background: #087e82 !important; border-color: #087e82 !important; font-size: 12px; }
#modal_upload_txt_eklaim .btn-primary:hover { background: #066b6e !important; border-color: #066b6e !important; }
#modal_upload_txt_eklaim .row > div:first-child .card { background: #f0f8f5; }
#modal_upload_txt_eklaim .symbol-label { background: #eff7f5 !important; }
#modal_upload_txt_eklaim .symbol-label i { color: #087e82 !important; font-size: 18px !important; }
#modal_upload_txt_eklaim .row .text-gray-600 { color: #6e8b90 !important; font-size: 12px !important; font-weight: 400 !important; }
#modal_upload_txt_eklaim .row [id] { color: #15373e !important; font-size: 23px !important; font-weight: 600 !important; letter-spacing: -.4px; font-variant-numeric: tabular-nums; }
#modal_upload_txt_eklaim .table-responsive { max-height: 55vh; }
#modal_upload_txt_eklaim table { width: 100%; font-size: 12px !important; font-variant-numeric: tabular-nums; }
#modal_upload_txt_eklaim table thead { position: sticky; top: 0; z-index: 1; }
#modal_upload_txt_eklaim table thead th { background: #eff7f5 !important; color: #62858b !important; font-size: 12px; font-weight: 600 !important; padding: 12px; white-space: nowrap; border-bottom: 1px solid #dceae5; }
#modal_upload_txt_eklaim table tbody td { padding: 12px; color: #42636d; font-weight: 400; border-color: #edf2f3; }
#modal_upload_txt_eklaim table tbody tr:hover { background: #f6faf9; }
#modal_upload_txt_eklaim .ek-upload-filter { background:#f6faf9; border-color:#cddfdb!important; border-top:3px solid #087e82!important; overflow:hidden; }
#modal_upload_txt_eklaim .ek-upload-filter > .card-body { padding:24px!important; }
#modal_upload_txt_eklaim .ek-upload-filter h3 { font-size:18px!important; color:#15373e; }
#modal_upload_txt_eklaim .ek-upload-filter .form-control { background:#fff; min-height:43px; }
#modal_upload_txt_eklaim .ek-upload-filter .form-text { margin:22px -24px -24px; padding:11px 24px; border-top:1px solid #dce9e5; background:#edf6f3; }
@media(max-width:575px){#modal_upload_txt_eklaim .ek-upload-filter > .card-body{padding:20px 18px!important}#modal_upload_txt_eklaim .ek-upload-filter .form-text{margin:18px -18px -20px;padding:10px 18px}}body .swal2-popup.eklaim-alert { padding:28px; border:1px solid #e1ebed; border-radius:16px; background:#fff; box-shadow:0 24px 70px #15373e26; color:#42636d; }
body .eklaim-alert .swal2-title { padding:0; margin:12px 0 0; color:#15373e; font-size:20px; font-weight:600; }
body .eklaim-alert .swal2-html-container { margin:16px 0 0; color:#81969b; font-size:13px; line-height:1.7; }
body .eklaim-alert .swal2-actions { gap:10px; margin:24px 0 0; }
body .eklaim-alert .btn { margin:0; padding:11px 20px; border-radius:8px; font-size:12px; font-weight:600; }
body .eklaim-alert .btn-primary { background:#087e82!important; border:1px solid #087e82!important; color:#fff!important; }
body .eklaim-alert .btn-light { background:#f3f7f8!important; border:1px solid #e2eaec!important; color:#638087!important; }
body .eklaim-alert .swal2-loader { border-color:#087e82 transparent #087e82 transparent; }
body .eklaim-alert .text-primary { color:#087e82!important; }
body .eklaim-alert .border.rounded { background:#f6faf9; border:1px solid #dceae5!important; border-radius:12px!important; padding:18px!important; }
body .eklaim-alert .text-muted { color:#81969b!important; }
body .eklaim-alert .swal2-timer-progress-bar { background:#75bdb5; }#modal_upload_txt_eklaim .row #selisihTarifEklaim, #modal_upload_txt_eklaim .row #selisihTarifIDRGEklaim { color: #bf6973 !important; }
</style>
<div class="modal fade" id="modal_upload_txt_eklaim" tabindex="-1" aria-labelledby="uploadTxtEklaimTitle" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <div class="me-4">
                    <h2 class="modal-title fw-bold mb-1" id="uploadTxtEklaimTitle">Upload Data Txt E-Klaim</h2>
                    <p class="text-muted fs-7 mb-0">Unggah file TXT untuk meninjau data dan ringkasan tarif E-Klaim sebelum impor.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body bg-light p-5 p-lg-8">
                <div class="card card-flush border mb-6 ek-upload-filter">
                    <div class="card-body p-5">
                        <h3 class="fs-5 fw-bold mb-2">Unggah File E-Klaim</h3>
                        <p class="text-muted fs-7 mb-5">Pilih file hasil ekspor E-Klaim. Periksa ringkasan dan pratinjau data, lalu klik Import untuk menyimpan.</p>
                        <label class="form-label fw-semibold" for="filetxteklaim">File E-Klaim</label>
                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <input type="file" class="form-control form-control-solid" id="filetxteklaim" accept=".txt" aria-describedby="fileTxtEklaimFormat">
                            <button type="button" class="btn btn-primary flex-shrink-0" id="btnImportTxtEklaim">
                                <i class="bi bi-upload me-1" aria-hidden="true"></i>Import
                            </button>
                        </div>
                        <div class="form-text" id="fileTxtEklaimFormat">Format file yang didukung adalah TXT.</div>
                    </div>
                </div>
                <div class="row g-4 mb-6">

                    <!-- Jumlah Data -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                        <div class="card card-flush border h-100">
                            <div class="card-body p-5">
                                <div class="d-flex flex-column align-items-start gap-4">
                                    <div class="symbol symbol-40px flex-shrink-0">
                                        <div class="symbol-label bg-light-info">
                                            <i class="bi bi-files fs-2 text-info"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Jumlah Data</div>
                                        <div class="fw-bold fs-4 text-break lh-base text-info" id="jmlDataEklaim">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tarif RS -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                        <div class="card card-flush border h-100">
                            <div class="card-body p-5">
                                <div class="d-flex flex-column align-items-start gap-4">
                                    <div class="symbol symbol-40px flex-shrink-0">
                                        <div class="symbol-label bg-light-primary">
                                            <i class="bi bi-hospital fs-2 text-primary"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Total Tarif RS</div>
                                        <div class="fw-bold fs-4 text-break lh-base text-primary" id="totalTarifRSEklaim">Rp 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total INA-CBG -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                        <div class="card card-flush border h-100">
                            <div class="card-body p-5">
                                <div class="d-flex flex-column align-items-start gap-4">
                                    <div class="symbol symbol-40px flex-shrink-0">
                                        <div class="symbol-label bg-light-success">
                                            <i class="bi bi-cash-stack fs-2 text-success"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Total Nilai INA-CBG</div>
                                        <div class="fw-bold fs-4 text-break lh-base text-success" id="totalNilaiEklaim">Rp 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selisih RS - INA-CBG -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                        <div class="card card-flush border h-100">
                            <div class="card-body p-5">
                                <div class="d-flex flex-column align-items-start gap-4">
                                    <div class="symbol symbol-40px flex-shrink-0">
                                        <div class="symbol-label bg-light-danger">
                                            <i class="bi bi-graph-down-arrow fs-2 text-danger"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Selisih RS − INA-CBG</div>
                                        <div class="fw-bold fs-4 text-break lh-base text-danger" id="selisihTarifEklaim">Rp 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total IDRG -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                        <div class="card card-flush border h-100">
                            <div class="card-body p-5">
                                <div class="d-flex flex-column align-items-start gap-4">
                                    <div class="symbol symbol-40px flex-shrink-0">
                                        <div class="symbol-label bg-light-success">
                                            <i class="bi bi-calculator fs-2 text-success"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Total Tarif IDRG</div>
                                        <div class="fw-bold fs-4 text-break lh-base text-success" id="totalTarifIDRGEklaim">Rp 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selisih RS - IDRG -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                        <div class="card card-flush border h-100">
                            <div class="card-body p-5">
                                <div class="d-flex flex-column align-items-start gap-4">
                                    <div class="symbol symbol-40px flex-shrink-0">
                                        <div class="symbol-label bg-light-danger">
                                            <i class="bi bi-arrow-left-right fs-2 text-danger"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Selisih RS − IDRG</div>
                                        <div class="fw-bold fs-4 text-break lh-base text-danger" id="selisihTarifIDRGEklaim">Rp 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <section class="card app-table-card" aria-labelledby="previewTxtEklaimTitle">
                    <div class="card-header app-table-heading">
                        <div>
                            <h3 class="app-table-title" id="previewTxtEklaimTitle">Pratinjau data</h3>
                            <p class="app-table-description">Periksa data E-Klaim sebelum diunggah.</p>
                        </div>
                    </div>
                    <div class="card-body">
                    <div class="table-responsive" tabindex="0" role="region" aria-labelledby="previewTxtEklaimTitle">
                        <table class="table align-middle table-row-dashed gy-3 fs-7 mb-0 text-nowrap fs-8 gy-2" id="tablePreviewEklaim">
                            <thead class="align-middle" id="headerPreviewtxtEklaim"></thead>
                            <tbody class="fw-bold text-gray-600" id="resultpreviewtxteklaim"></tbody>
                        </table>
                    </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
