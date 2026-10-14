<div class="modal fade" id="modal_upload_txt_eklaim" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Upload Data Txt E-Klaim</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <label class="form-label fw-bold">File E-Klaim</label>
                    <div class="input-group">
                        <input type="file" class="form-control" id="filetxteklaim" accept=".txt">
                        <button type="button" class="btn btn-primary" id="btnImportTxtEklaim"><i class="bi bi-upload"></i>Import</button>
                    </div>
                    <div class="form-text mt-2">
                        Format file yang didukung:
                        <span class="badge badge-light-primary">TXT</span>
                    </div>
                </div>
                <br>
                <div class="row mt-4 mb-3">

                    <!-- Jumlah Data -->
                    <div class="col-xl-2 col-md-6">
                        <div class="card border h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-3">
                                        <div class="symbol-label bg-light-info">
                                            <i class="bi bi-files fs-2 text-info"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-8">Jumlah Data</div>
                                        <div class="fw-bold fs-2 text-info" id="jmlDataEklaim">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tarif RS -->
                    <div class="col-xl-2 col-md-6">
                        <div class="card border h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-3">
                                        <div class="symbol-label bg-light-primary">
                                            <i class="bi bi-hospital fs-2 text-primary"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-8">Total Tarif RS</div>
                                        <div class="fw-bold fs-2 text-primary" id="totalTarifRSEklaim">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total INA-CBG -->
                    <div class="col-xl-2 col-md-6">
                        <div class="card border h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-3">
                                        <div class="symbol-label bg-light-success">
                                            <i class="bi bi-cash-stack fs-2 text-success"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-8">Total Nilai INA-CBG</div>
                                        <div class="fw-bold fs-2 text-success" id="totalNilaiEklaim">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selisih RS - INA-CBG -->
                    <div class="col-xl-2 col-md-6">
                        <div class="card border h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-3">
                                        <div class="symbol-label bg-light-danger">
                                            <i class="bi bi-graph-down-arrow fs-2 text-danger"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-8">Selisih RS − INA-CBG</div>
                                        <div class="fw-bold fs-2 text-danger" id="selisihTarifEklaim">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total IDRG -->
                    <div class="col-xl-2 col-md-6">
                        <div class="card border h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-3">
                                        <div class="symbol-label bg-light-success">
                                            <i class="bi bi-calculator fs-2 text-success"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-8">Total Tarif IDRG</div>
                                        <div class="fw-bold fs-2 text-success" id="totalTarifIDRGEklaim">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selisih RS - IDRG -->
                    <div class="col-xl-2 col-md-6">
                        <div class="card border h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-3">
                                        <div class="symbol-label bg-light-danger">
                                            <i class="bi bi-arrow-left-right fs-2 text-danger"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-8">Selisih RS − IDRG</div>
                                        <div class="fw-bold fs-2 text-danger" id="selisihTarifIDRGEklaim">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <br>
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed gy-2 fs-8" id="tablePreviewEklaim">
                        <thead class="align-middle" id="headerPreviewtxtEklaim"></thead>
                        <tbody class="fw-bold text-gray-600" id="resultpreviewtxteklaim"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>