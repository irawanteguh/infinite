
$("#modal_upload_txt_eklaim").on("show.bs.modal", function() {

    $("#filetxteklaim").val("");
    $("#jmlDataEklaim").text("0");
    $("#totalTarifRSEklaim").text("Rp 0");
    $("#totalNilaiEklaim").text("Rp 0");
    $("#selisihTarifEklaim").text("Rp 0");
    $("#totalTarifIDRGEklaim").text("Rp 0");
    $("#selisihTarifIDRGEklaim").text("Rp 0");
    $("#headerPreviewtxtEklaim").empty();
    $("#resultpreviewtxteklaim").empty();

    window.dataTxtEklaim = [];
    window.headerTxtEklaim = [];
});

$("#filetxteklaim").on("change", function() {
    const input = this;
    const file  = input.files[0];

    $("#jmlDataEklaim").text("0");
    $("#totalNilaiEklaim").text("Rp 0");
    $("#totalTarifRSEklaim").text("Rp 0");
    $("#selisihTarifEklaim").text("Rp 0");
    $("#totalTarifIDRGEklaim").text("Rp 0");
    $("#selisihTarifIDRGEklaim").text("Rp 0");
    $("#headerPreviewtxtEklaim").empty();
    $("#resultpreviewtxteklaim").empty();

    window.dataTxtEklaim   = [];
    window.headerTxtEklaim = [];

    const extension = file.name.split(".").pop().toLowerCase();

    if (!["txt", "xlsx", "xls"].includes(extension)) {
        Swal.fire({
            icon             : "warning",
            title            : "Invalid File Format",
            html             : `Please select a supported file format: <strong>TXT</strong>, <strong>Excel Workbook (.xlsx)</strong>, or <strong>Excel 97–2003 Workbook (.xls)</strong>.`,
            confirmButtonText: "OK",
            customClass      : { confirmButton: "btn btn-primary" },
            buttonsStyling   : false
        });
        $(input).val("");
        return;
    }

    Swal.fire({
        title            : "Reading File",
        text             : "Please wait while the E-Klaim data is being processed...",
        allowOutsideClick: false,
        allowEscapeKey   : false,
        showConfirmButton: false,
        didOpen          : function() {Swal.showLoading();}
    });


    if (extension === "txt") {
        const reader = new FileReader();

        reader.onload = function(e) {
            try {
                let text = e.target.result || "";
                text = text.replace(/^\uFEFF/, "").replace(/\r\n/g, "\n").replace(/\r/g, "\n");

                const lines = text.split("\n").filter(function(line) {
                    return line.trim() !== "";
                });

                if (lines.length < 2) {
                    Swal.fire({
                        icon             : "warning",
                        title            : "No Data Found",
                        text             : "The TXT file does not contain any E-Klaim data.",
                        confirmButtonText: "OK",
                        customClass      : { confirmButton: "btn btn-primary" },
                        buttonsStyling   : false
                    });
                    return;
                }

                const headers = lines[0].replace(/^\uFEFF/, "").split("\t").map(function(header) {
                    return header.trim();
                });

                const indexSEP = headers.indexOf("SEP");
                const data = [];
                const invalidRows = [];

                if (indexSEP === -1) {
                    Swal.fire({
                        icon             : "error",
                        title            : "Required Column Not Found",
                        text             : "The SEP column was not found in the E-Klaim TXT file.",
                        confirmButtonText: "OK",
                        customClass      : { confirmButton: "btn btn-primary" },
                        buttonsStyling   : false
                    });
                    return;
                }

                for (let i = 1; i < lines.length; i++) {
                    const row = lines[i].split("\t");

                    if (row.length !== headers.length) {
                        invalidRows.push({
                            line  : i + 1,
                            column: row.length,
                            reason: "Column count does not match the header"
                        });
                        continue;
                    }

                    const sep = String(row[indexSEP] || "").trim();

                    if (sep === "") {
                        invalidRows.push({
                            line  : i + 1,
                            column: row.length,
                            reason: "SEP is empty"
                        });
                        continue;
                    }

                    data.push(row);
                }

                prosesDataEklaim(headers, data, invalidRows);

            } catch (error) {
                console.error("ERROR READING E-KLAIM TXT:", error);

                Swal.fire({
                    icon             : "error",
                    title            : "File Read Error",
                    text             : "An error occurred while reading the E-Klaim TXT file.",
                    confirmButtonText: "OK",
                    customClass      : { confirmButton: "btn btn-primary" },
                    buttonsStyling   : false
                });

                window.dataTxtEklaim = [];
                window.headerTxtEklaim = [];
            }
        };

        reader.onerror = function() {
            Swal.fire({
                icon             : "error",
                title            : "File Read Error",
                text             : "The TXT file could not be read.",
                confirmButtonText: "OK",
                customClass      : { confirmButton: "btn btn-primary" },
                buttonsStyling   : false
            });

            window.dataTxtEklaim = [];
            window.headerTxtEklaim = [];
        };

        reader.readAsText(file);
        return;
    }

    const readerExcel = new FileReader();

    readerExcel.onload = function(e) {
        try {
            if (typeof XLSX === "undefined") {
                Swal.fire({
                    icon             : "error",
                    title            : "Excel Library Not Found",
                    text             : "The SheetJS XLSX library has not been loaded on this page.",
                    confirmButtonText: "OK",
                    customClass      : { confirmButton: "btn btn-primary" },
                    buttonsStyling   : false
                });
                return;
            }

            const workbook = XLSX.read(e.target.result, {
                type     : "array",
                cellDates: false,
                cellNF   : false,
                cellText : true
            });

            if (!workbook.SheetNames || workbook.SheetNames.length === 0) {
                Swal.fire({
                    icon             : "warning",
                    title            : "No Worksheet Found",
                    text             : "The Excel file does not contain any worksheet.",
                    confirmButtonText: "OK",
                    customClass      : { confirmButton: "btn btn-primary" },
                    buttonsStyling   : false
                });
                return;
            }

            const worksheet = workbook.Sheets[workbook.SheetNames[0]];
            const rows = XLSX.utils.sheet_to_json(worksheet, {
                header: 1,
                defval: "",
                raw   : true
            });

            if (!rows || rows.length < 2) {
                Swal.fire({
                    icon             : "warning",
                    title            : "No Data Found",
                    text             : "The Excel file does not contain any E-Klaim data.",
                    confirmButtonText: "OK",
                    customClass      : { confirmButton: "btn btn-primary" },
                    buttonsStyling   : false
                });
                return;
            }

            const headers = rows[0].map(function(header) {
                return String(header ?? "").trim();
            });

            const indexSEP = headers.indexOf("SEP");
            const data = [];
            const invalidRows = [];

            if (indexSEP === -1) {
                Swal.fire({
                    icon             : "error",
                    title            : "Required Column Not Found",
                    text             : "The SEP column was not found in the E-Klaim Excel file.",
                    confirmButtonText: "OK",
                    customClass      : { confirmButton: "btn btn-primary" },
                    buttonsStyling   : false
                });
                return;
            }

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];

                if (!row || row.every(function(value) {
                    return String(value ?? "").trim() === "";
                })) {
                    continue;
                }

                if (row.length > headers.length) {
                    invalidRows.push({
                        line  : i + 1,
                        column: row.length,
                        reason: "Column count does not match the header"
                    });
                    continue;
                }

                const normalizedRow = [];

                for (let j = 0; j < headers.length; j++) {
                    normalizedRow.push(row[j] ?? "");
                }

                const sep = String(normalizedRow[indexSEP] ?? "").trim();

                if (sep === "") {
                    invalidRows.push({
                        line  : i + 1,
                        column: normalizedRow.length,
                        reason: "SEP is empty"
                    });
                    continue;
                }

                data.push(normalizedRow);
            }

            prosesDataEklaim(headers, data, invalidRows);

        } catch (error) {
            console.error("ERROR READING E-KLAIM EXCEL:", error);

            Swal.fire({
                icon             : "error",
                title            : "File Read Error",
                text             : "The Excel file could not be read.",
                confirmButtonText: "OK",
                customClass      : { confirmButton: "btn btn-primary" },
                buttonsStyling   : false
            });

            window.dataTxtEklaim = [];
            window.headerTxtEklaim = [];
        }
    };


    readerExcel.onerror = function() {
        Swal.fire({
            icon             : "error",
            title            : "File Read Error",
            text             : "The Excel file could not be read.",
            confirmButtonText: "OK",
            customClass      : { confirmButton: "btn btn-primary" },
            buttonsStyling   : false
        });

        window.dataTxtEklaim = [];
        window.headerTxtEklaim = [];
    };

    readerExcel.readAsArrayBuffer(file);
});

$("#btnImportTxtEklaim").on("click", function() {
    const data = Array.isArray(window.dataTxtEklaim) ? window.dataTxtEklaim : [];

    if (data.length === 0) {
        Swal.fire({
            icon : "warning",
            title: "No Data Available",
            text : "Please select and preview a TXT E-Klaim file before proceeding."
        });
        return;
    }

    Swal.fire({
        title             : "Import Data E-Klaim",
        html              : "Are you sure you want to import <strong>" + data.length.toLocaleString("id-ID") + "</strong> records?",
        icon              : "question",
        showCancelButton  : true,
        confirmButtonText : "Yes, Import",
        cancelButtonText  : "Cancel",
        confirmButtonColor: "#0d6efd",
        cancelButtonColor : "#6c757d"
    }).then(function(result) {
        if (result.isConfirmed) prosesImportTxtEklaim(data);
    });
});

function prosesDataEklaim(headers, data, invalidRows) {

    window.headerTxtEklaim = headers;

    const indexSEP       = headers.indexOf("SEP");
    const indexTarif     = headers.indexOf("TARIF_INACBG");
    const indexTarifRS   = headers.indexOf("TARIF_RS");
    const indexTarifIDRG = headers.indexOf("IDRG_TOTAL_TARIF");

    const requiredHeaders = [
        {
            name: "SEP",
            index: indexSEP
        },
        {
            name: "TARIF_INACBG",
            index: indexTarif
        },
        {
            name: "TARIF_RS",
            index: indexTarifRS
        },
        {
            name: "IDRG_TOTAL_TARIF",
            index: indexTarifIDRG
        }
    ];

    const missingHeaders = requiredHeaders
        .filter(function(item) {
            return item.index === -1;
        })
        .map(function(item) {
            return item.name;
        });

    if (missingHeaders.length > 0) {
        Swal.fire({
            icon             : "error",
            title            : "Missing Required Columns",
            html             : `The following required columns were not found in the uploaded file:<br><br><strong>${missingHeaders.join(", ")}</strong>`,
            confirmButtonText: "OK",
            customClass      : { confirmButton: "btn btn-primary" },
            buttonsStyling   : false
        });

        window.headerTxtEklaim = [];
        window.dataTxtEklaim   = [];

        return;
    }

    const validData    = [];
    const filteredRows = [];

    data.forEach(function(row, index) {
        const sep = String(row[indexSEP] || "").trim();

        if (sep === "") {
            filteredRows.push({
                line  : index + 2,
                reason: "SEP is empty"
            });
            return;
        }

        validData.push(row);
    });

    data        = validData;
    invalidRows = invalidRows.concat(filteredRows);

    if (data.length === 0) {
        Swal.fire({
            icon             : "warning",
            title            : "No Valid Data",
            text             : "No valid E-Klaim data was found to process.",
            confirmButtonText: "OK",
            customClass      : { confirmButton: "btn btn-primary" },
            buttonsStyling   : false
        });

        window.headerTxtEklaim = [];
        window.dataTxtEklaim   = [];

        return;
    }

    let headerHtml = "<tr class='fw-bolder'>";

    headers.forEach(function(header, index) {
        let className = "bg-dark text-white text-nowrap";
        if (index === 0) className += " ps-4";
        if (index === headers.length - 1) className += " pe-4";
        headerHtml += `<th class="${className}">${escapeHtml(header)}</th>`;
    });


    headerHtml += "</tr>";

    $("#headerPreviewtxtEklaim").html(headerHtml);

    window.dataTxtEklaim = data;

    $("#jmlDataEklaim").text(data.length.toLocaleString("id-ID"));

    let totalTarifINACBG = 0;
    let totalTarifRS     = 0;
    let totalTarifIDRG   = 0;

    data.forEach(function(row) {

        totalTarifINACBG += parseNominalEklaim(
            row[indexTarif]
        );

        totalTarifRS += parseNominalEklaim(
            row[indexTarifRS]
        );

        totalTarifIDRG += parseNominalEklaim(
            row[indexTarifIDRG]
        );

    });

    const selisihTarifINACBG = totalTarifINACBG - totalTarifRS;
    const selisihTarifIDRG   = totalTarifIDRG - totalTarifRS;

    $("#totalNilaiEklaim").text(`Rp ${totalTarifINACBG.toLocaleString("id-ID")}`);
    $("#totalTarifRSEklaim").text(`Rp ${totalTarifRS.toLocaleString("id-ID")}`);
    $("#totalTarifIDRGEklaim").text(`Rp ${totalTarifIDRG.toLocaleString("id-ID")}`);

    const warnaSelisihINACBG = selisihTarifINACBG >= 0 ? "text-success" : "text-danger";
    $("#selisihTarifEklaim").removeClass("text-success text-danger").addClass(warnaSelisihINACBG).text(`Rp ${selisihTarifINACBG.toLocaleString("id-ID")}`);

    const warnaSelisihIDRG = selisihTarifIDRG >= 0 ? "text-success" : "text-danger";
    $("#selisihTarifIDRGEklaim").removeClass("text-success text-danger").addClass(warnaSelisihIDRG).text(`Rp ${selisihTarifIDRG.toLocaleString("id-ID")}`);

    let bodyHtml = "";

    data.slice(0, 100).forEach(function(row) {
        bodyHtml += "<tr>";
        headers.forEach(function(header, columnIndex) {
            const value = row[columnIndex] ?? "";
            bodyHtml += `<td class="text-nowrap">${escapeHtml(value)}</td>`;
        });
        bodyHtml += "</tr>";
    });

    $("#resultpreviewtxteklaim").html(bodyHtml);

    Swal.close();

    if (invalidRows.length > 0) {
        setTimeout(function() {
            Swal.fire({
                icon             : "warning",
                title            : "Data Imported with Warnings",
                html             : `Valid records: <strong>${data.length.toLocaleString("id-ID")}</strong><br>Skipped records: <strong>${invalidRows.length.toLocaleString("id-ID")}</strong>`,
                confirmButtonText: "OK",
                customClass      : { confirmButton: "btn btn-primary" },
                buttonsStyling   : false
            });
        }, 300);
    }

};

function parseNominalEklaim(value) {
    if (value === null || value === undefined || value === "") {
        return 0;
    }

    if (typeof value === "number") {
        return isFinite(value) ? value : 0;
    }

    value = String(value).trim();

    if (value === "") {
        return 0;
    }

    value = value.replace(/[^\d,.-]/g, "");

    if (value.includes(",") && value.includes(".")) {
        const lastComma = value.lastIndexOf(",");
        const lastDot = value.lastIndexOf(".");

        if (lastComma > lastDot) {
            value = value.replace(/\./g, "").replace(",", ".");
        } else {
            value = value.replace(/,/g, "");
        }
    } else if (value.includes(",")) {
        const parts = value.split(",");

        if (parts.length === 2 && parts[1].length <= 2) {
            value = value.replace(",", ".");
        } else {
            value = value.replace(/,/g, "");
        }
    } else if (value.includes(".")) {
        const parts = value.split(".");

        if (!(parts.length === 2 && parts[1].length <= 2)) {
            value = value.replace(/\./g, "");
        }
    }

    const result = parseFloat(value);

    return isNaN(result) ? 0 : result;
};

function escapeHtml(text) {
    return String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
};

function formatDuration(seconds) {
    seconds = Math.max(0, Math.round(seconds));

    let h = Math.floor(seconds / 3600);
    let m = Math.floor((seconds % 3600) / 60);
    let s = seconds % 60;

    if (h > 0) {return h + " hour " + m + " minute " + s + " second";}
    if (m > 0) {return m + " minute " + s + " second";}
    return s + " second";
};

function prosesImportTxtEklaim(data) {
    data = Array.isArray(data) ? data : [];

    const headers = Array.isArray(window.headerTxtEklaim) ? window.headerTxtEklaim : [];
    const total   = data.length;

    let   index     = 0;
    const batchSize = 500;
    const startTime = Date.now();


    let totalSuccess = 0;
    let totalFailed  = 0;

    if (total === 0) {
        Swal.fire({
            icon             : "warning",
            title            : "No Data Available",
            text             : "There is no E-Klaim data available to import.",
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
            text             : "The E-Klaim file does not contain any headers.",
            confirmButtonText: "OK",
            customClass      : { confirmButton: "btn btn-primary" },
            buttonsStyling   : false
        });
        return;
    }

    $("#importProgressTxtEklaim").text("0");
    $("#importSpeedTxtEklaim").text("0");
    $("#importEtaTxtEklaim").text("Calculating...");

    Swal.fire({
        title: "Importing E-Klaim Data",
        html: `
            <div class="text-center">
                <div class="fs-5 mb-3">Please wait while the data is being imported...</div>
                <div class="fs-3 fw-bold text-primary">
                    <span id="importProgressTxtEklaim">0</span> / ${total.toLocaleString("id-ID")}
                </div>
                <div class="progress mt-3" style="height:10px;">
                    <div
                        id="importProgressBarTxtEklaim"
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
                        <span id="importSpeedTxtEklaim">0</span> records/sec
                    </div>
                    <div>
                        Estimated Time Remaining:
                        <span id="importEtaTxtEklaim">Calculating...</span>
                    </div>
                </div>
            </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: function() {
            Swal.showLoading();
            kirimBatchTxtEklaim();
        }
    });


    function kirimBatchTxtEklaim() {
        const batch = data.slice(index, index + batchSize);

        if (batch.length === 0) {
            return;
        }

        $.ajax({
            url: url + "ur/importtxteklaim",
            type    : "POST",
            dataType: "JSON",
            data    : {headers: JSON.stringify(headers),data: JSON.stringify(batch)},
            success : function(res) {
                if (!res || res.responCode !== "00") {
                    Swal.fire({
                        icon: "error",
                        title: "Import Failed",
                        text: res?.responMsg || "Failed to import E-Klaim data.",
                        confirmButtonText: "OK",
                        customClass: { confirmButton: "btn btn-primary" },
                        buttonsStyling: false
                    });
                    return;
                }

                const batchResult  = res.responResult || {};
                const batchSuccess = Number(batchResult.success || 0);
                const batchFailed  = Number(batchResult.failed || 0);

                totalSuccess += batchSuccess;
                totalFailed += batchFailed;

                // console.log("BATCH:", `${index} - ${index + batch.length}`, "SUCCESS:", batchSuccess, "FAILED:", batchFailed, "TOTAL SUCCESS:", totalSuccess, "TOTAL FAILED:", totalFailed);

                index += batch.length;

                $("#importProgressTxtEklaim").text(index.toLocaleString("id-ID"));

                const percent = total > 0 ? (index / total) * 100 : 0;
                $("#importProgressBarTxtEklaim").css("width", percent + "%").attr("aria-valuenow", percent);

                const elapsed = (Date.now() - startTime) / 1000;
                const speed = elapsed > 0 ? index / elapsed : 0;
                $("#importSpeedTxtEklaim").text(speed.toFixed(2));

                const remaining = total - index;
                const eta = speed > 0 ? remaining / speed : 0;
                $("#importEtaTxtEklaim").text(remaining > 0 ? formatDuration(eta) : "Completed");

                if (index < total) {
                    kirimBatchTxtEklaim();
                    return;
                }

                $("#importProgressBarTxtEklaim").css("width", "100%").attr("aria-valuenow", 100);
                $("#importProgressTxtEklaim").text(total.toLocaleString("id-ID"));
                $("#importEtaTxtEklaim").text("Completed");


                // FINAL RESULT
                setTimeout(function() {
                    const totalResult   = total;
                    const successResult = totalSuccess;
                    const failedResult  = totalFailed;

                    Swal.fire({
                        icon: failedResult > 0 ? "warning" : "success",
                        title: failedResult > 0 ? "Import Completed with Warnings" : "Import Successfully Completed",
                        html: `
                            <div class="text-center">
                                <div class="text-muted mb-3">The E-Klaim data import process has been completed.</div>
                                <div class="border rounded p-3 text-start">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total Records</span>
                                        <strong>${totalResult.toLocaleString("en-US")}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Successfully Processed</span>
                                        <strong class="text-success">${successResult.toLocaleString("en-US")}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Failed to Process</span>
                                        <strong class="text-danger">${failedResult.toLocaleString("en-US")}</strong>
                                    </div>
                                </div>
                                <div class="alert ${failedResult > 0 ? "alert-warning" : "alert-success"} py-2 mt-3 mb-0">
                                    ${failedResult > 0
                                        ? `<strong>${failedResult.toLocaleString("en-US")}</strong> record(s) could not be processed. Please review the affected data.`
                                        : "All records have been successfully processed and saved to the system."
                                    }
                                </div>
                            </div>
                        `,
                        confirmButtonText : "Done",
                        confirmButtonColor: "#009EF7",
                        allowOutsideClick : false,
                        allowEscapeKey    : false,
                        width             : 450,
                        timer             : 3000,
                        timerProgressBar  : true,
                        willClose         : function() {
                            window.dataTxtEklaim = [];
                            window.headerTxtEklaim = [];
                            $("#filetxteklaim").val("");
                            $("#jmlDataEklaim").text("0");
                            $("#totalTarifRSEklaim").text("Rp 0");
                            $("#totalNilaiEklaim").text("Rp 0");
                            $("#selisihTarifEklaim").text("Rp 0");
                            $("#totalTarifIDRGEklaim").text("Rp 0");
                            $("#selisihTarifIDRGEklaim").text("Rp 0");
                            $("#headerPreviewtxtEklaim").empty();
                            $("#resultpreviewtxteklaim").empty();
                            $("#modal_upload_txt_eklaim").modal("hide");
                            
                            const pathSegments  = window.location.pathname.split("/").filter(Boolean);
                            const secondSegment = pathSegments[2];

                            if (secondSegment === "dashboard") {
                                dataraweklaim();
                            } else if (secondSegment === "dataeklaim") {
                                dataraweklaim();
                            } else if (secondSegment === "casemixindex") {
                                load();
                            }
                        }
                    });
                }, 200);
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon             : "error",
                    title            : "Request Failed",
                    text             : "An error occurred while processing your request.",
                    confirmButtonText: "OK"
                });
            }
        });
    }
}