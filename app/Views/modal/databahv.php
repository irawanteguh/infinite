<div class="modal fade" id="modal_upload_txt_bahv" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Upload Data BAHV</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <div class="col-md-12">
                    <label class="form-label fw-bold">File BAHV</label>
                    <div class="input-group">
                        <input type="file" class="form-control" id="file_txt_bahv" accept=".txt,.xls,.xlsx,.pdf">
                        <button type="button" class="btn btn-primary" id="btnImportTxtBahv"><i class="bi bi-upload"></i>Import</button>
                    </div>
                    <div class="form-text mt-2">
                        Format file yang didukung:
                        <span class="badge badge-light-primary">TXT</span>
                        <span class="badge badge-light-success">XLS</span>
                        <span class="badge badge-light-success">XLSX</span>
                        <span class="badge badge-light-danger">PDF</span>
                    </div>
                </div>
                <br>
                <div class="row mt-4 mb-3">

                    <!-- Jumlah Data -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-3">
                                        <div class="symbol-label bg-light-primary">
                                            <i class="bi bi-files fs-2 text-primary"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-8">Jumlah Data</div>
                                        <div class="fw-bold fs-2 text-primary" id="jml_data_bahv">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jumlah SEP -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-3">
                                        <div class="symbol-label bg-light-info">
                                            <i class="bi bi-card-list fs-2 text-info"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-8">Jumlah SEP</div>
                                        <div class="fw-bold fs-2 text-info" id="jml_sep_bahv">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Pengajuan -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-3">
                                        <div class="symbol-label bg-light-success">
                                            <i class="bi bi-cash-stack fs-2 text-success"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-8">Total Biaya Pengajuan</div>
                                        <div class="fw-bold fs-2 text-success" id="total_pengajuan_bahv">Rp 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Pending -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-3">
                                        <div class="symbol-label bg-light-danger">
                                            <i class="bi bi-exclamation-triangle fs-2 text-danger"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-8">Status Pending</div>
                                        <div class="fw-bold fs-2 text-danger" id="jml_pending_bahv">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Preview Table -->
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed gy-2 fs-8" id="tablePreviewbahv">
                        <thead class="align-middle" id="headerPreviewbahv"></thead>
                        <tbody class="fw-bold text-gray-600" id="resultpreviewtxtbahv"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>