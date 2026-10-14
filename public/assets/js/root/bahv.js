$("#modal_upload_txt_bahv").on("show.bs.modal", function() {
    resetPreviewBahv();
});

$("#btnImportTxtBahv").on("click", function() {
    const data = Array.isArray(window.dataTxtBahv) ? window.dataTxtBahv : [];

    if (data.length === 0) {
        Swal.fire({
            icon             : "warning",
            title            : "No Data Available",
            text             : "Please select and preview a TXT BAHV file before proceeding.",
            confirmButtonText: "OK",
            customClass      : {confirmButton: "btn btn-primary"},
            buttonsStyling   : false
        });
        return;
    }

    Swal.fire({
        title            : "Import BAHV Data",
        html             : "Are you sure you want to import <strong>"+data.length.toLocaleString("id-ID")+"</strong> records?",
        icon             : "question",
        showCancelButton : true,
        confirmButtonText: "Yes, Import",
        cancelButtonText : "Cancel",
        customClass      : {confirmButton: "btn btn-primary",cancelButton: "btn btn-secondary"},
        buttonsStyling   : false
    }).then(function(result) {
        if (result.isConfirmed) {
            prosesImportTxtBahv(data);
        }
    });
});
function resetPreviewBahv(){
    $("#file_txt_bahv").val("");
    $("#jml_data_bahv").text("0");
    $("#jml_sep_bahv").text("0");
    $("#total_pengajuan_bahv").text("Rp 0");
    $("#jml_pending_bahv").text("0");
    $("#headerPreviewbahv").empty();
    $("#resultpreviewtxtbahv").empty();

    window.dataTxtBahv   = [];
    window.headerTxtBahv = [];
}

$("#file_txt_bahv").on("change", function() {
    const input = this;
    const file  = input.files[0];

    resetPreviewBahv();

    if (!file) {
        Swal.close();
        return;
    }

    const extension = file.name.split(".").pop().toLowerCase();

    if (!["txt", "xlsx", "xls"].includes(extension)) {
        Swal.fire({
            icon             : "warning",
            title            : "Invalid File Format",
            html             : 'Please select a supported file format: <strong>TXT</strong>, <strong>Excel Workbook (.xlsx)</strong>, or <strong>Excel 97–2003 Workbook (.xls)</strong>.',
            confirmButtonText: "OK",
            customClass      : {confirmButton: "btn btn-primary"},
            buttonsStyling   : false
        });

        $(input).val("");
        return;
    }

    Swal.fire({
        title            : "Reading File",
        text             : "Please wait while the BAHV data is being processed...",
        allowOutsideClick: false,
        allowEscapeKey   : false,
        showConfirmButton: false,
        didOpen          : function() {
            Swal.showLoading();
        }
    });

    if (extension === "txt") {
        bacaFileTxtBahv(file);
    } else {
        bacaFileExcelBahv(file);
    }

});

function bacaFileTxtBahv(file) {
    const reader = new FileReader();

    reader.onload = function(event) {
        try {
            let content = event.target.result || "";
                content = content.replace(/^\uFEFF/, "").replace(/\r\n/g, "\n").replace(/\r/g, "\n");

            const lines = content.split("\n");

            while (lines.length > 0 &&lines[lines.length - 1].trim() === ""){
                lines.pop();
            }

            if (lines.length < 2) {
                Swal.fire({
                    icon             : "warning",
                    title            : "No Data Found",
                    text             : "The TXT file contains no BAHV data.",
                    confirmButtonText: "OK",
                    customClass      : {confirmButton: "btn btn-primary"},
                    buttonsStyling   : false
                });
                return;
            }

            const headers = lines[0].split("\t").map(function(header) {
                return header.trim();
            });

            if (headers.length === 0 || headers.every(function(header) {return header === "";})){
                Swal.fire({
                    icon             : "error",
                    title            : "Invalid Header",
                    text             : "The TXT file does not contain a valid header row.",
                    confirmButtonText: "OK",
                    customClass      : {confirmButton: "btn btn-primary"},
                    buttonsStyling   : false
                });
                return;
            }

            const rows        = [];
            const invalidRows = [];

            lines.slice(1).forEach(function(line, index) {
                const lineNumber = index + 2;

                if (line.trim() === "") {
                    return;
                }

                const row = line.split("\t");

                if (row.length !== headers.length) {
                    invalidRows.push({
                        line  : lineNumber,
                        reason: "Column count does not match the header"
                    });
                    return;
                }

                rows.push({
                    values: row,
                    line  : lineNumber
                });
            });

            prosesFileBahv(headers, rows, invalidRows);

        } catch (error) {
            resetPreviewBahv();

            Swal.fire({
                icon             : "error",
                title            : "File Read Error",
                text             : "An error occurred while reading the BAHV TXT file.",
                confirmButtonText: "OK",
                customClass      : {confirmButton: "btn btn-primary"},
                buttonsStyling   : false
            });
        }
    };

    reader.onerror = function() {
        resetPreviewBahv();

        Swal.fire({
            icon             : "error",
            title            : "File Read Error",
            text             : "The BAHV TXT file could not be read.",
            confirmButtonText: "OK",
            customClass      : {confirmButton: "btn btn-primary"},
            buttonsStyling   : false
        });
    };

    reader.readAsText(file);
}

function bacaFileExcelBahv(file) {
    const reader = new FileReader();

    reader.onload = function(event) {
        try {
            if (typeof XLSX === "undefined") {
                Swal.fire({
                    icon             : "error",
                    title            : "Excel Library Not Found",
                    text             : "The SheetJS XLSX library has not been loaded on this page.",
                    confirmButtonText: "OK",
                    customClass      : {confirmButton: "btn btn-primary"},
                    buttonsStyling   : false
                });
                return;
            }

            const workbook = XLSX.read(event.target.result, {
                type     : "array",
                cellDates: false,
                cellNF   : false,
                cellText : true
            });

            if (!workbook.SheetNames || workbook.SheetNames.length === 0) {
                Swal.fire({
                    icon             : "warning",
                    title            : "No Worksheet Found",
                    text             : "The Excel file does not contain any worksheets.",
                    confirmButtonText: "OK",
                    customClass      : {confirmButton: "btn btn-primary"},
                    buttonsStyling   : false
                });
                return;
            }

            const worksheet = workbook.Sheets[workbook.SheetNames[0]];

            const rows = XLSX.utils.sheet_to_json(worksheet, {
                header: 1,
                defval: "",
                raw   : false
            });

            if (!rows || rows.length < 2) {
                Swal.fire({
                    icon             : "warning",
                    title            : "No Data Found",
                    text             : "The Excel file contains no BAHV data.",
                    confirmButtonText: "OK",
                    customClass      : {confirmButton: "btn btn-primary"},
                    buttonsStyling   : false
                });
                return;
            }

            const headers = rows[0].map(function(header) {
                return String(header ?? "").trim();
            });

            if (headers.length === 0 || headers.every(function(header) {return header === "";})) {
                Swal.fire({
                    icon             : "error",
                    title            : "Invalid Header",
                    text             : "The Excel file does not contain a valid header row.",
                    confirmButtonText: "OK",
                    customClass      : {confirmButton: "btn btn-primary"},
                    buttonsStyling   : false
                });
                return;
            }

            const validRows   = [];
            const invalidRows = [];

            rows.slice(1).forEach(function(row, index) {
                const lineNumber = index + 2;

                const isEmpty = row.every(function(value) {
                    return String(value ?? "").trim() === "";
                });

                if (isEmpty) {
                    return;
                }

                if (row.length !== headers.length) {
                    invalidRows.push({
                        line  : lineNumber,
                        reason: "Column count does not match the header"
                    });
                    return;
                }

                validRows.push({
                    values: row,
                    line  : lineNumber
                });
            });

            prosesFileBahv(headers, validRows, invalidRows);

        } catch (error) {
            resetPreviewBahv();

            Swal.fire({
                icon             : "error",
                title            : "File Read Error",
                text             : "An error occurred while reading the BAHV Excel file.",
                confirmButtonText: "OK",
                customClass      : {confirmButton: "btn btn-primary"},
                buttonsStyling   : false
            });
        }
    };

    reader.onerror = function() {
        resetPreviewBahv();

        Swal.fire({
            icon             : "error",
            title            : "File Read Error",
            text             : "The BAHV Excel file could not be read.",
            confirmButtonText: "OK",
            customClass      : {confirmButton: "btn btn-primary"},
            buttonsStyling   : false
        });
    };

    reader.readAsArrayBuffer(file);
}

function prosesFileBahv(headers, rows, invalidRows = []) {
    const normalize = function(value) {
        return String(value ?? "").trim().toUpperCase().replace(/[\s_-]+/g, "");
    };

    const index = {};

    headers.forEach(function(header, i) {
        index[normalize(header)] = i;
    });

    const indexNoSEP = index["NOSEP"] !== undefined ? index["NOSEP"] : index["SEP"];

    if (indexNoSEP === undefined) {
        resetPreviewBahv();

        Swal.fire({
            icon             : "error",
            title            : "Column Not Found",
            text             : "The NOSEP/SEP column is required.",
            confirmButtonText: "OK",
            customClass      : {confirmButton: "btn btn-primary"},
            buttonsStyling   : false
        });

        return;
    }

    const data = [];

    rows.forEach(function(item, indexRow) {
        const row        = Array.isArray(item) ? item : item.values;
        const lineNumber = Array.isArray(item) ? indexRow + 2 : item.line;

        if (!Array.isArray(row)) {
            invalidRows.push({
                line  : lineNumber,
                reason: "Invalid row format"
            });
            return;
        }

        const isEmpty = row.every(function(value) {
            return String(value ?? "").trim() === "";
        });

        if (isEmpty) {
            return;
        }

        if (row.length !== headers.length) {
            invalidRows.push({
                line  : lineNumber,
                reason: "Column count does not match the header"
            });
            return;
        }

        const sep = String(row[indexNoSEP] ?? "").trim();

        if (!sep) {
            invalidRows.push({
                line  : lineNumber,
                reason: "NOSEP/SEP is empty"
            });
            return;
        }

        data.push(row);
    });

    prosesDataBahv(headers, data, invalidRows);
}

function prosesDataBahv(headers, data, invalidRows = []) {
    window.headerTxtBahv = headers;
    window.dataTxtBahv   = data;

    const normalize = function(value) {
        return String(value ?? "").trim().toUpperCase().replace(/[\s_-]+/g, "");
    };

    const index = {};

    headers.forEach(function(header, i) {
        index[normalize(header)] = i;
    });

    const indexNoSEP  = index["NOSEP"] !== undefined ? index["NOSEP"] : index["SEP"];
    const indexBiaya  = index["BYPENGAJUAN"];
    const indexStatus = index["STATUS"];

    let totalPengajuan = 0;
    let jumlahPending  = 0;

    const sepSet = new Set();

    data.forEach(function(row) {
        const sep = String(row[indexNoSEP] ?? "").trim();

        if (sep) {
            sepSet.add(sep);
        }

        if (indexBiaya !== undefined) {
            totalPengajuan += parseNominalEklaim(row[indexBiaya]);
        }

        if (indexStatus !== undefined) {
            const status = String(row[indexStatus] ?? "").trim().toUpperCase();

            if (status.includes("PENDING")) {
                jumlahPending++;
            }
        }
    });

    // Ringkasan data
    $("#jml_data_bahv").text(
        data.length.toLocaleString("id-ID")
    );

    $("#jml_sep_bahv").text(
        sepSet.size.toLocaleString("id-ID")
    );

    $("#total_pengajuan_bahv").text(
        todesimal(totalPengajuan)
    );

    $("#jml_pending_bahv").text(
        jumlahPending.toLocaleString("id-ID")
    );

    // Header preview
    let htmlHeader = "<tr>";

    headers.forEach(function(header) {
        htmlHeader +='<th class="text-nowrap">'+escapeHtml(header)+"</th>";
    });

    htmlHeader += "</tr>";

    $("#headerPreviewbahv").html(htmlHeader);

    let htmlBody = "";
    data.forEach(function(row) {
        htmlBody += "<tr>";

        headers.forEach(function(_, columnIndex) {
            const value = String(row[columnIndex] ?? "").toUpperCase();

            if (columnIndex === indexStatus) {
                const status = value.trim().toUpperCase();

                if (status.includes("PENDING")) {
                    htmlBody +='<td><span class="badge badge-warning text-dark">'+escapeHtml(value)+"</span></td>";
                } else if (status.includes("TIDAK LAYAK")) {
                    htmlBody +='<td><span class="badge badge-danger">'+escapeHtml(value)+"</span></td>";
                } else {
                    htmlBody +="<td>"+escapeHtml(value)+"</td>";
                }
            } else {
                htmlBody +='<td class="text-nowrap">'+escapeHtml(value)+"</td>";
            }
        });

        htmlBody += "</tr>";
    });

    $("#resultpreviewtxtbahv").html(htmlBody);

    Swal.close();

    if (invalidRows.length > 0) {
        Swal.fire({
            icon             : "warning",
            title            : "Data Read Successfully",
            html             : "Valid rows: <strong>"+data.length.toLocaleString("en-US")+"</strong><br>Skipped data rows: <strong>"+invalidRows.length.toLocaleString("en-US")+"</strong>",
            confirmButtonText: "OK",
            customClass      : {confirmButton: "btn btn-primary"},
            buttonsStyling   : false
        });

        console.table(invalidRows);
    } else {
        Swal.fire({
            icon             : "success",
            title            : "File Read Successfully",
            text             : data.length.toLocaleString("en-US")+" BAHV data rows have been processed successfully.",
            timer            : 1800,
            showConfirmButton: false
        });
    }
}

function prosesImportTxtBahv(data) {
    data = Array.isArray(data) ? data : [];

    const headers   = Array.isArray(window.headerTxtBahv) ? window.headerTxtBahv : [];
    const total     = data.length;
    const batchSize = 500;
    const startTime = Date.now();

    let index        = 0;
    let totalSuccess = 0;
    let totalFailed  = 0;

    if (total === 0) {
        Swal.fire({
            icon             : "warning",
            title            : "No Data Available",
            text             : "There is no BAHV data available to import.",
            confirmButtonText: "OK",
            customClass      : { confirmButton: "btn btn-primary" },
            buttonsStyling   : false
        });
        return;
    }

    if (headers.length === 0) {
        Swal.fire({
            icon             : "error",
            title            : "Headers Not Found",
            text             : "The BAHV file does not contain any headers.",
            confirmButtonText: "OK",
            customClass      : { confirmButton: "btn btn-primary" },
            buttonsStyling   : false
        });
        return;
    }

    Swal.fire({
        title: "Importing BAHV Data",
        html: `
            <div class="text-center">
                <div class="fs-5 mb-3">
                    Please wait while the BAHV data is being imported...
                </div>

                <div class="fs-3 fw-bold text-primary">
                    <span id="importProgressTxtBahv">0</span>
                    / ${total.toLocaleString("id-ID")}
                </div>

                <div class="progress mt-3" style="height:10px;">
                    <div
                        id="importProgressBarTxtBahv"
                        class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                        role="progressbar"
                        style="width:0%"
                        aria-valuenow="0"
                        aria-valuemin="0"
                        aria-valuemax="100">
                    </div>
                </div>

                <div class="mt-3 small text-muted">
                    <div>
                        Processing Speed:
                        <span id="importSpeedTxtBahv">0</span> records/sec
                    </div>
                    <div>
                        Estimated Time Remaining:
                        <span id="importEtaTxtBahv">Calculating...</span>
                    </div>
                </div>
            </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey   : false,
        showConfirmButton: false,
        didOpen: function() {
            Swal.showLoading();
            kirimBatchTxtBahv();
        }
    });

    function kirimBatchTxtBahv() {
        const batch = data.slice(index, index + batchSize);

        if (batch.length === 0) {
            return;
        }

        $.ajax({
            url     : url + "bahv/importtxtbahv",
            type    : "POST",
            dataType: "JSON",
            data    : {
                headers: JSON.stringify(headers),
                data   : JSON.stringify(batch)
            },

            success: function(res) {
                if (!res || res.responCode !== "00") {
                    Swal.fire({
                        icon             : "error",
                        title            : "Import Failed",
                        text             : res?.responMsg || "Failed to import BAHV data.",
                        confirmButtonText: "OK",
                        customClass      : { confirmButton: "btn btn-primary" },
                        buttonsStyling   : false
                    });
                    return;
                }

                const batchResult = res.responResult || {};

                totalSuccess += Number(batchResult.success || 0);
                totalFailed  += Number(batchResult.failed || 0);

                index += batch.length;

                $("#importProgressTxtBahv").text(
                    index.toLocaleString("id-ID")
                );

                const percent = (index / total) * 100;

                $("#importProgressBarTxtBahv").css("width", percent + "%").attr("aria-valuenow", percent);

                const elapsed = (Date.now() - startTime) / 1000;
                const speed   = elapsed > 0 ? index / elapsed : 0;
                const remain  = total - index;
                const eta     = speed > 0 ? remain / speed : 0;

                $("#importSpeedTxtBahv").text(speed.toFixed(2));
                $("#importEtaTxtBahv").text(remain > 0 ? formatDuration(eta) : "Completed");

                if (index < total) {
                    kirimBatchTxtBahv();
                    return;
                }

                $("#importProgressBarTxtBahv").css("width", "100%").attr("aria-valuenow", 100);
                $("#importProgressTxtBahv").text(total.toLocaleString("id-ID"));
                $("#importEtaTxtBahv").text("Completed");

                setTimeout(function() {
                    Swal.fire({
                        icon: totalFailed > 0 ? "warning" : "success",
                        title: totalFailed > 0 ? "Import Completed with Warnings" : "Import Successfully Completed",
                        html: `
                            <div class="text-center">
                                <div class="text-muted mb-3">
                                    The BAHV data import process has been completed.
                                </div>

                                <div class="border rounded p-3 text-start">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total Records</span>
                                        <strong>${total.toLocaleString("id-ID")}</strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Successfully Processed</span>
                                        <strong class="text-success">${totalSuccess.toLocaleString("id-ID")}</strong>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Failed to Process</span>
                                        <strong class="text-danger">${totalFailed.toLocaleString("id-ID")}</strong>
                                    </div>
                                </div>

                                <div class="alert ${totalFailed > 0 ? "alert-warning" : "alert-success"} py-2 mt-3 mb-0">
                                    ${totalFailed > 0 ? `<strong>${totalFailed.toLocaleString("id-ID")}</strong> record(s) could not be processed. Please review the affected data.` : "All BAHV records have been successfully processed."}
                                </div>
                            </div>
                        `,

                        confirmButtonText: "Done",
                        customClass: {confirmButton: "btn btn-primary"},
                        buttonsStyling   : false,
                        allowOutsideClick: false,
                        allowEscapeKey   : false,
                        width            : 450,
                        timer            : 3000,
                        timerProgressBar : true,
                        willClose: function() {
                            resetPreviewBahv();
                            $("#modal_upload_txt_bahv").modal("hide");

                            load();
                        }
                    });
                }, 200);
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon             : "error",
                    title            : "Request Failed",
                    text             : "An error occurred while importing BAHV data.",
                    confirmButtonText: "OK",
                    customClass      : { confirmButton: "btn btn-primary" },
                    buttonsStyling   : false
                });
            }
        });
    }
}
