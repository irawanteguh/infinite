$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | Initial Load
    |--------------------------------------------------------------------------
    */
    loadBackupList();


    /*
    |--------------------------------------------------------------------------
    | Backup Database
    |--------------------------------------------------------------------------
    */
    $("#btnBackupDatabase").on("click", function (e) {

        e.preventDefault();

        Swal.fire({

            title: "Backup Database",

            text: "Create a new database backup?",

            icon: "warning",

            showCancelButton: true,

            confirmButtonText: "Backup",

            cancelButtonText: "Cancel"

        }).then(function (result) {

            if (!result.isConfirmed) {
                return;
            }


            $.ajax({

                url: url + "developer/backupdb/backup",

                type: "GET",

                dataType: "JSON",


                beforeSend: function () {

                    Swal.fire({

                        title: "Creating Backup",

                        html: "Please wait while the database backup is being created.",

                        allowOutsideClick: false,

                        allowEscapeKey: false,

                        showConfirmButton: false,

                        didOpen: function () {

                            Swal.showLoading();

                        }

                    });

                },


                success: function (response) {

                    if (response.responseCode !== "00") {

                        Swal.fire({

                            icon: "error",

                            title: "Backup Failed",

                            text: response.responseDesc ||
                                "Failed to create database backup.",

                            confirmButtonText: "OK"

                        });

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Update Backup List
                    |--------------------------------------------------------------------------
                    */
                    renderBackupList(
                        response.responseResult
                    );


                    Swal.fire({

                        icon: "success",

                        title: "Backup Successful",

                        text: response.responseDesc ||
                            "Database backup successfully created.",

                        showConfirmButton: false,

                        timer: 2000

                    });

                },


                error: function () {

                    Swal.fire({

                        icon: "error",

                        title: "Request Failed",

                        text: "An error occurred while creating the database backup.",

                        confirmButtonText: "OK"

                    });

                },


                complete: function () {

                    Swal.close();

                }

            });

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Load Backup List
    |--------------------------------------------------------------------------
    */
    function loadBackupList() {

        $.ajax({

            url: url + "developer/backupdb/listbackup",

            type: "GET",

            dataType: "JSON",


            success: function (response) {

                if (response.responseCode !== "00") {

                    renderBackupList([]);

                    return;
                }


                renderBackupList(
                    response.responseResult
                );

            },


            error: function () {

                renderBackupList([]);

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Render Backup List
    |--------------------------------------------------------------------------
    */
    function renderBackupList(result) {

        let tableResult = "";


        /*
        |--------------------------------------------------------------------------
        | No Data
        |--------------------------------------------------------------------------
        */
        if (!Array.isArray(result) || result.length === 0) {

            tableResult = `

                <tr>

                    <td
                        colspan="5"
                        class="text-center text-muted py-10"
                    >

                        <i class="bi bi-database-x fs-2x d-block mb-3"></i>

                        No backup files found.

                    </td>

                </tr>

            `;

            $("#resultbackup").html(tableResult);

            initBackupDataTable();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Generate Rows
        |--------------------------------------------------------------------------
        */
        result.forEach(function (item, index) {

            const filename = item.filename || "";

            const downloadUrl =
                url +
                "developer/backupdb/download/" +
                encodeURIComponent(filename);


            tableResult += `

                <tr>

                    <!-- Number -->
                    <td class="ps-4">

                        ${index + 1}

                    </td>


                    <!-- Backup File -->
                    <td>

                        <div class="d-flex align-items-center">

                            <i class="bi bi-filetype-sql fs-2x text-danger me-3"></i>

                            <div>

                                <div class="fw-bold text-gray-800">

                                    ${filename}

                                </div>

                                <div class="text-muted fs-7">

                                    Database Backup

                                </div>

                            </div>

                        </div>

                    </td>


                    <!-- Size -->
                    <td>

                        <span class="fw-semibold">

                            ${item.sizeFormatted || "0 Bytes"}

                        </span>

                    </td>


                    <!-- Created -->
                    <td>

                        <span class="text-gray-700">

                            ${item.created || ""}

                        </span>

                    </td>


                    <!-- Action -->
                    <td class="text-center">

                        <a
                            href="${downloadUrl}"
                            class="btn btn-sm btn-light-primary"
                            title="Download Backup"
                        >

                            <i class="bi bi-download me-1"></i>

                            Download

                        </a>

                    </td>

                </tr>

            `;

        });


        /*
        |--------------------------------------------------------------------------
        | Insert Data
        |--------------------------------------------------------------------------
        */
        $("#resultbackup").html(tableResult);


        /*
        |--------------------------------------------------------------------------
        | Initialize DataTable
        |--------------------------------------------------------------------------
        */
        initBackupDataTable();

    }


    /*
    |--------------------------------------------------------------------------
    | Initialize DataTable
    |--------------------------------------------------------------------------
    */
    function initBackupDataTable() {

        if ($.fn.DataTable.isDataTable("#backupdatabase_table")) {

            $("#backupdatabase_table")
                .DataTable()
                .destroy();

        }


        $("#backupdatabase_table").DataTable({

            responsive: false,

            pageLength: 10,

            autoWidth: false,

            destroy: true,

            ordering: false,

            searching: true,

            info: true,

            language: {

                emptyTable: "No backup files available",

                search: "Search:",

                lengthMenu: "_MENU_",

                info: "Showing _START_ to _END_ of _TOTAL_ backups"

            }

        });

    }

});