loadbackuplist();

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
                        text: response.responseDesc || "Failed to create database backup.",
                        confirmButtonText: "OK"
                    });
                    return;
                }
                loadbackuplist();
                Swal.fire({
                    icon: "success",
                    title: "Backup Successful",
                    text: response.responseDesc || "Database backup successfully created.",
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

function loadbackuplist() {
    $.ajax({
        url       : url+"developer/backupdb/listbackup",
        type      : "GET",
        dataType  : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : "Processing",
                html             : "Please wait while the system displays the requested data.",
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });

            $("#resultbackup").empty();
        },
        success: function (response) {
            if (response.responseCode !== "00") {
                Swal.fire({
                    icon             : "info",
                    title            : "No Records Found",
                    text             : "No records are available for the selected period.",
                    showConfirmButton: false,
                    timer            : 2000
                });
                return;
            }

            const result = Array.isArray(response.responseResult) ? response.responseResult : [];

            let tableResult = "";

            if (result.length === 0) {
                tableResult += "<tr>";
                tableResult += "<td colspan='4' class='text-center'><i class='bi bi-database-x fs-2x d-block mb-3'></i> No backup files found.</td>";
                tableResult += "</tr>";
            }else{
                for (var i in result) {
                    tableResult += "<tr>";
                    tableResult += "<td class='ps-4'>" + (parseInt(i)+1) + "</td>";
                    tableResult += "<td><i class='bi bi-filetype-sql fs-2x text-danger me-3'></i>"+(result[i].filename||"")+"</td>";
                    tableResult += "<td>"+(result[i].sizeFormatted||"")+"</td>";
                    tableResult += "<td>"+(result[i].created||"")+"</td>";
                    tableResult += "<td class='text-end'>";
                    tableResult += "<a href='"+url+"developer/backupdb/download/"+encodeURIComponent(result[i].filename || "")+"' target='_blank' class='btn btn-sm btn-light-primary me-2'><i class='bi bi-download me-1'></i> Download"+"</a>";
                    tableResult += "<button type='button' class='btn btn-sm btn-light-danger' onclick='deleteBackup(\""+(result[i].filename||"")+"\")'><i class='bi bi-trash me-1'></i> Delete</button>";
                    tableResult += "</td>";
                    tableResult += "</tr>";
                }
            }

            $("#resultbackup").html(tableResult);
            initDataTable("#backup_table","#searchtable");
        },
        error: function () {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "An error occurred while processing your request.",
                confirmButtonText: "OK"
            });
        },
        complete: function () {
            Swal.close();
        }
    });
}

function deleteBackup(filename) {

    if (!filename) {
        return;
    }

    Swal.fire({
        icon: "warning",
        title: "Delete Backup?",
        html: "Backup file <strong>" + filename + "</strong> will be permanently deleted.",
        showCancelButton: true,
        confirmButtonText: "Yes, Delete",
        cancelButtonText: "Cancel",
        reverseButtons: true
    }).then(function (result) {

        if (!result.isConfirmed) {
            return;
        }

        Swal.fire({
            title: "Deleting",
            html: "Please wait while the backup file is being deleted.",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: url + "developer/backupdb/delete/" + encodeURIComponent(filename),
            type: "DELETE",
            dataType: "JSON",
            success: function (response) {

                if (response.responseCode === "00") {

                    Swal.fire({
                        icon: "success",
                        title: "Deleted",
                        text: response.responseDesc || "Backup file has been deleted successfully.",
                        showConfirmButton: false,
                        timer: 1500
                    });

                    loadbackuplist();

                } else {

                    Swal.fire({
                        icon: "error",
                        title: "Delete Failed",
                        text: response.responseDesc || "Backup file could not be deleted.",
                        confirmButtonText: "OK"
                    });
                }
            },
            error: function (xhr) {

                let message = "An error occurred while deleting the backup file.";

                if (xhr.responseJSON && xhr.responseJSON.responseDesc) {
                    message = xhr.responseJSON.responseDesc;
                }

                Swal.fire({
                    icon: "error",
                    title: "Request Failed",
                    text: message,
                    confirmButtonText: "OK"
                });
            },
            complete: function () {
                Swal.close();
            }
        });
    });
}