<div class="col-xl-12 mb-5">
    <div class="card card-flush">
        <div class="card-header pt-5">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <div>
                        <div class="fs-3 fw-bold text-gray-800">
                            Database Backup
                        </div>
                        <div class="text-muted fs-7">
                            Manage database backup files
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body py-3">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-2 fs-8" id="backup_table">
                    <thead class="align-middle">
                        <tr class="fw-bolder text-muted bg-light">
                            <th class="ps-4 rounded-start">#</th>
                            <th>Back Up File</th>
                            <th>Size</th>
                            <th>Created At</th>
                            <th class="text-end rounded-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="fw-bold text-gray-600" id="resultbackup"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>