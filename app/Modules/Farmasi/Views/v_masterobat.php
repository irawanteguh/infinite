<div class="col-xl-12 mb-5">
    <div class="card card-flush app-table-card">
        <div class="card-header app-table-heading">
            <div>
                <h2 class="app-table-title">Master Obat</h2>
                <p class="app-table-description">Daftar obat aktif, harga, dan total pemakaian tahun berjalan.</p>
            </div>
            <div class="app-table-search">
                <input id="searchtable" type="search" class="form-control" aria-label="Cari obat" placeholder="Cari obat">
            </div>
            <div class="card-toolbar m-0">
                <button type="button" class="btn btn-clean btn-sm btn-icon btn-icon-primary btn-active-light-primary me-n3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <span class="svg-icon svg-icon-3 svg-icon-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="5" y="5" width="5" height="5" rx="1" fill="#000000" />
                                <rect x="14" y="5" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                                <rect x="5" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                                <rect x="14" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                            </g>
                        </svg>
                    </span>
                </button>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-bold w-200px py-3" data-kt-menu="true">
                    <div class="menu-item px-3">
                        <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">More Actions</div>
                    </div>
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" id="btnDownloadExcelKunjungan">Download Excel</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-2 fs-8" id="masterobat_table" data-datatable="manual">
                    <thead class="align-middle">
                        <tr class="fw-bolder text-muted bg-light">
                            <th class="ps-4 rounded-start">#</th>
                            <th>Drug Name</th>
                            <th>Category</th>
                            <th>Principal</th>
                            <th>Distributor Price</th>
                            <th>Discount</th>
                            <th>VAT</th>
                            <th>Total Cost</th>
                            <th>Insurance Price</th>
                            <th>General Price</th>
                            <th>Total Usage</th>
                            <th class="rounded-end">Created By</th>
                        </tr>
                    </thead>
                    <tbody class="fw-bold text-gray-600" id="resultmasterobat"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>