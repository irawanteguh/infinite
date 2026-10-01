<div class="card">

    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">

        <div class="card-title">

            <div class="d-flex align-items-center position-relative my-1">

                <i class="bi bi-database fs-2 me-3"></i>

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


        <div class="card-toolbar">

            <button
                type="button"
                id="btnBackupDatabase"
                class="btn btn-sm btn-danger"
            >

                <i class="bi bi-database-down me-1"></i>

                Backup Database

            </button>

        </div>

    </div>
    <!--end::Card header-->


    <!--begin::Card body-->
    <div class="card-body py-4">

        <div class="table-responsive">

            <table
                id="backupdatabase_table"
                class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4"
            >

                <thead>

                    <tr class="fw-bolder text-muted bg-light">

                        <th class="ps-4 rounded-start">
                            #
                        </th>

                        <th>
                            BACKUP FILE
                        </th>

                        <th>
                            SIZE
                        </th>

                        <th>
                            CREATED
                        </th>

                        <th class="text-center rounded-end">
                            ACTION
                        </th>

                    </tr>

                </thead>


                <tbody id="resultbackup">

                </tbody>

            </table>

        </div>

    </div>
    <!--end::Card body-->

</div>