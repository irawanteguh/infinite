function eklaimAlert(options) {
    return Swal.fire({
        ...options,
        buttonsStyling: false,
        customClass: {
            ...options.customClass,
            popup: ["eklaim-alert", options.customClass?.popup].filter(Boolean).join(" "),
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-light"
        }
    });
}

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
        eklaimAlert({
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

    eklaimAlert({
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
                    eklaimAlert({
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
                    eklaimAlert({
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

                eklaimAlert({
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
            eklaimAlert({
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
                eklaimAlert({
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
                eklaimAlert({
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
                eklaimAlert({
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
                eklaimAlert({
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

            eklaimAlert({
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
        eklaimAlert({
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
        eklaimAlert({
            icon : "warning",
            title: "No Data Available",
            text : "Please select and preview a TXT E-Klaim file before proceeding."
        });
        return;
    }

    eklaimAlert({
        title             : "Import Data E-Klaim",
        html              : "Are you sure you want to import <strong>" + data.length.toLocaleString("id-ID") + "</strong> records?",
        icon              : "question",
        showCancelButton  : true,
        confirmButtonText : "Yes, Import",
        cancelButtonText  : "Cancel",
        confirmButtonColor: "#087e82",
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
        eklaimAlert({
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
        eklaimAlert({
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
            eklaimAlert({
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
        eklaimAlert({
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
        eklaimAlert({
            icon             : "error",
            title            : "Headers Not Found",
            text             : "The E-Klaim file does not contain any headers.",
            confirmButtonText: "OK",
            customClass      : { confirmButton: "btn btn-primary" },
            buttonsStyling   : false
        });
        return;
    }

    const stages = [
        "Periksa berkas dan kolom wajib",
        "Kirim dan proses data per batch",
        "Siapkan ringkasan hasil impor"
    ];
    let progressPopup;

    function setImportStage(currentStage) {
        progressPopup.querySelectorAll(".report-progress-step").forEach((step, stepIndex) => {
            const complete = stepIndex < currentStage;
            const current = stepIndex === currentStage;
            step.classList.toggle("is-complete", complete);
            step.classList.toggle("is-current", current);
            if (current) step.setAttribute("aria-current", "step");
            else step.removeAttribute("aria-current");
            step.querySelector(".report-progress-icon").innerHTML = complete
                ? '<i class="bi bi-check-lg"></i>'
                : current ? '<span class="spinner-border spinner-border-sm"></span>' : '<span class="report-progress-dot"></span>';
        });
        progressPopup.querySelector("[data-eklaim-stage]").textContent = stages[currentStage] || "Proses impor selesai";
    }

    eklaimAlert({
        title: "Mengimpor data E-Klaim",
        width: "min(560px, calc(100vw - 32px))",
        customClass: { popup: "report-progress-popup" },
        html: `
            <div class="report-progress">${stages.map(label => `
                <div class="report-progress-step">
                    <span class="report-progress-icon" aria-hidden="true"></span><span>${label}</span>
                </div>`).join("")}
            </div>
            <div class="text-center mt-3">
                <div class="visually-hidden" data-eklaim-stage role="status"></div>
                <div class="fs-3 fw-bold text-primary">
                    <span id="importProgressTxtEklaim">0</span> / ${total.toLocaleString("id-ID")}
                </div>
                <div class="progress report-progress-meter mt-3">
                    <div
                        id="importProgressBarTxtEklaim"
                        class="progress-bar progress-bar-striped progress-bar-animated"
                        aria-label="Progres impor E-Klaim"
                        role="progressbar"
                        style="width:0%"
                        aria-valuenow="0"
                        aria-valuemin="0"
                        aria-valuemax="100">
                    </div>
                </div>
                <div class="mt-3 small text-muted">
                    <div>
                        Kecepatan:
                        <span id="importSpeedTxtEklaim">0</span> baris/detik
                    </div>
                    <div>
                        Estimasi waktu tersisa:
                        <span id="importEtaTxtEklaim">Menghitung…</span>
                    </div>
                </div>
            </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: function(popup) {
            progressPopup = popup;
            setImportStage(1);
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
                if (!res || res.responseCode !== "00") {
                    eklaimAlert({
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
                $("#importEtaTxtEklaim").text(remaining > 0 ? formatDuration(eta) : "Selesai");

                if (index < total) {
                    kirimBatchTxtEklaim();
                    return;
                }

                $("#importProgressBarTxtEklaim").css("width", "100%").attr("aria-valuenow", 100);
                $("#importProgressTxtEklaim").text(total.toLocaleString("id-ID"));
                $("#importEtaTxtEklaim").text("Selesai");
                setImportStage(2);

                setTimeout(function() {
                    setImportStage(stages.length);
                    const totalResult   = total;
                    const successResult = totalSuccess;
                    const failedResult  = totalFailed;

                    eklaimAlert({
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
                        confirmButtonColor: "#087e82",
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
                            } else if (secondSegment === "biaya") {
                                load();
                            }
                        }
                    });
                }, 200);
            },
            error: function(xhr, status, error) {
                eklaimAlert({
                    icon             : "error",
                    title            : "Request Failed",
                    text             : "An error occurred while processing your request.",
                    confirmButtonText: "OK"
                });
            }
        });
    }
}

function formatDiaglistFKTP(diaglist) {
    var icd10FKTP = /^(R56\.0|A35|B24|G44\.2|G43\.9|G51\.0|R42|F45\.9|G47\.0|T15\.1|H10\.9|H11\.3|H04\.1|H01\.0|H00\.0|H02\.0|H15\.1|H52\.0|H52\.1|H52\.2|H52\.4|H53\.6|H60\.9|H66\.0|H61\.2|T75\.3|J34\.0|J00|J30\.0|J30\.4|T17\.1|R04\.0|J11\.1|A37\.9|J02\.9|J03\.9|J04\.0|J45\.9|J20\.9|J18(\.[0-9]+)?|A16\.2|I10|B37\.0|K12\.0|B00\.1|K11\.2|P38|K29\.7|A09|K21\.9|A01\.0|K90\.4|T78\.1|A05\.9|B76\.9|B78\.9|B77\.9|B65\.9|B68\.9|B15\.9|A03\.9|A06\.0|K64\.0|K64\.1|N39\.0|A54\.9|N10|N47|N34\.1|A54\.0|N76\.2|N76\.0|N70\.9|Z34\.9|O03\.9|O99\.0|D50\.9|O70\.0|O70\.1|L02\.9|N61|O92\.1|N64\.5|E10\.9|E11\.9|E16\.2|E46|E56\.9|E61\.9|E78\.5|E79\.0|E66\.9|I88\.9|A90|A91|A27\.9|T78\.2|L97|D17\.9|B07\.9|B08\.1|B02\.9|B05\.9|B01\.9|L01\.0|L08\.0|L73\.9|L08\.1|A46|A18\.4|A30\.9|A51\.0|A51\.3|B35\.0|B35\.4|B35\.2|B35\.1|B35\.6|B35\.3|B36\.0|B37\.2|B74\.9|B85\.0|B85\.3|B86|T63\.4|L24\.9|L20\.9|L30\.0|L22|L21\.9|L42|L70\.0|L73\.2|L71\.0|L74\.3|L50\.9|L27\.0|L27\.1)$/i;

    return (diaglist || "").split(";").map(function (diag) {
        var kode = diag.trim();

        if (!kode) {
            return "";
        }

        if (icd10FKTP.test(kode)) {
            return "<span class='badge badge-light-info'>" + escapeHtml(kode) + "</span>";
        }

        return escapeHtml(kode);
    }).filter(Boolean).join("<br>");
}