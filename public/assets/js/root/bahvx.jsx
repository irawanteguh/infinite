
/**
 * BAHV - Reset modal saat dibuka
 */
$("#modal_upload_txt_bahv").on("show.bs.modal", function() {
    $("#file_txt_bahv").val("");

    resetPreviewBahv();
});


/**
 * BAHV - Reset data dan tampilan
 */
function resetPreviewBahv() {
    $("#jml_data_bahv").text("0");
    $("#jml_sep_bahv").text("0");
    $("#total_pengajuan_bahv").text("Rp 0");
    $("#jml_pending_bahv").text("0");

    $("#headerPreviewbahv").empty();
    $("#resultpreviewtxtbahv").empty();

    window.dataTxtBahv   = [];
    window.headerTxtBahv = [];
}


/**
 * BAHV - Format angka Indonesia
 */
function parseNumber(value) {
    if (typeof value === "number") {
        return Number.isFinite(value) ? value : 0;
    }

    let text = String(value ?? "").trim();

    if (!text) {
        return 0;
    }

    // Hapus simbol mata uang, spasi, dan karakter nonangka
    text = text.replace(/[^\d,.-]/g, "");

    // Format Indonesia: 1.234.567,89
    if (text.includes(",")) {
        text = text.replace(/\./g, "").replace(",", ".");
    } else if (/^-?\d{1,3}(\.\d{3})+$/.test(text)) {
        // Format ribuan: 1.234.567
        text = text.replace(/\./g, "");
    }

    const number = Number(text);

    return Number.isFinite(number) ? number : 0;
}


/**
 * BAHV - Format Rupiah
 */
function formatRupiah(value) {
    return new Intl.NumberFormat("id-ID", {
        style                : "currency",
        currency             : "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}


/**
 * BAHV - Escape HTML untuk preview
 */
function escapeHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, function(character) {
        const entities = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;"
        };

        return entities[character];
    });
}


/**
 * BAHV - Pesan SweetAlert
 */
function alertBahv(options) {
    Swal.close();

    Swal.fire(Object.assign({
        confirmButtonText: "OK",
        customClass      : {
            confirmButton: "btn btn-primary"
        },
        buttonsStyling: false
    }, options));
}


/**
 * BAHV - Event pemilihan file
 */
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
        alertBahv({
            icon : "warning",
            title: "Invalid File Format",
            html : 'Please select a supported file format: <strong>TXT</strong>, <strong>Excel Workbook (.xlsx)</strong>, or <strong>Excel 97–2003 Workbook (.xls)</strong>.'
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


/**
 * BAHV - Baca file TXT
 */
function bacaFileTxtBahv(file) {
    const reader = new FileReader();

    reader.onload = function(event) {
        try {
            let content = event.target.result || "";

            content = content
                .replace(/^\uFEFF/, "")
                .replace(/\r\n/g, "\n")
                .replace(/\r/g, "\n");

            const lines = content.split("\n");

            // Hapus baris kosong di akhir file
            while (
                lines.length > 0 &&
                lines[lines.length - 1].trim() === ""
            ) {
                lines.pop();
            }

            if (lines.length < 2) {
                alertBahv({
                    icon : "warning",
                    title: "No Data Found",
                    text : "The TXT file contains no BAHV data."
                });
                return;
            }

            const headers = lines[0].split("\t").map(function(header) {
                return header.trim();
            });

            if (headers.length === 0 || headers.every(function(header) {
                return header === "";
            })) {
                alertBahv({
                    icon : "error",
                    title: "Invalid Header",
                    text : "The TXT file does not contain a valid header row."
                });
                return;
            }

            const rows        = [];
            const invalidRows = [];

            lines.slice(1).forEach(function(line, index) {
                const lineNumber = index + 2;

                // Abaikan baris kosong, tetapi pertahankan nomor baris asli
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
            console.error("BAHV TXT Read Error:", error);

            resetPreviewBahv();

            alertBahv({
                icon : "error",
                title: "File Read Error",
                text : "An error occurred while reading the BAHV TXT file."
            });
        }
    };

    reader.onerror = function() {
        resetPreviewBahv();

        alertBahv({
            icon : "error",
            title: "File Read Error",
            text : "The BAHV TXT file could not be read."
        });
    };

    reader.readAsText(file);
}


/**
 * BAHV - Baca file Excel XLS/XLSX
 */
function bacaFileExcelBahv(file) {
    const reader = new FileReader();

    reader.onload = function(event) {
        try {
            if (typeof XLSX === "undefined") {
                alertBahv({
                    icon : "error",
                    title: "Excel Library Not Found",
                    text : "The SheetJS XLSX library has not been loaded on this page."
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
                alertBahv({
                    icon : "warning",
                    title: "No Worksheet Found",
                    text : "The Excel file does not contain any worksheets."
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
                alertBahv({
                    icon : "warning",
                    title: "No Data Found",
                    text : "The Excel file contains no BAHV data."
                });
                return;
            }

            const headers = rows[0].map(function(header) {
                return String(header ?? "").trim();
            });

            if (headers.length === 0 || headers.every(function(header) {
                return header === "";
            })) {
                alertBahv({
                    icon : "error",
                    title: "Invalid Header",
                    text : "The Excel file does not contain a valid header row."
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
            console.error("BAHV Excel Read Error:", error);

            resetPreviewBahv();

            alertBahv({
                icon : "error",
                title: "File Read Error",
                text : "An error occurred while reading the BAHV Excel file."
            });
        }
    };

    reader.onerror = function() {
        resetPreviewBahv();

        alertBahv({
            icon : "error",
            title: "File Read Error",
            text : "The BAHV Excel file could not be read."
        });
    };

    reader.readAsArrayBuffer(file);
}


/**
 * BAHV - Validasi header dan data
 */
function prosesFileBahv(headers, rows, invalidRows = []) {
    const normalize = function(value) {
        return String(value ?? "")
            .trim()
            .toUpperCase()
            .replace(/[\s_-]+/g, "");
    };

    const index = {};

    headers.forEach(function(header, i) {
        index[normalize(header)] = i;
    });

    const indexNoSEP = index["NOSEP"] !== undefined
        ? index["NOSEP"]
        : index["SEP"];

    if (indexNoSEP === undefined) {
        resetPreviewBahv();

        alertBahv({
            icon : "error",
            title: "Column Not Found",
            text : "The NOSEP/SEP column is required."
        });
        return;
    }

    const data = [];

    rows.forEach(function(item, indexRow) {
        const row = Array.isArray(item)
            ? item
            : item.values;

        const lineNumber = Array.isArray(item)
            ? indexRow + 2
            : item.line;

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

        // SEP duplikat tetap dipertahankan
        data.push(row);
    });

    prosesDataBahv(headers, data, invalidRows);
}


/**
 * BAHV - Ringkasan dan preview
 */

function prosesDataBahv(headers, data, invalidRows = []) {
    window.headerTxtBahv = headers;
    window.dataTxtBahv   = data;

    const normalize = function(value) {
        return String(value ?? "")
            .trim()
            .toUpperCase()
            .replace(/[\s_-]+/g, "");
    };

    const index = {};

    headers.forEach(function(header, i) {
        index[normalize(header)] = i;
    });

    const indexNoSEP = index["NOSEP"] !== undefined
        ? index["NOSEP"]
        : index["SEP"];

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
            totalPengajuan += parseNumber(row[indexBiaya]);
        }

        if (indexStatus !== undefined) {
            const status = String(row[indexStatus] ?? "")
                .trim()
                .toUpperCase();

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
        formatRupiah(totalPengajuan)
    );

    $("#jml_pending_bahv").text(
        jumlahPending.toLocaleString("id-ID")
    );

    // Header preview
    let htmlHeader = "<tr>";

    headers.forEach(function(header) {
        htmlHeader +=
            '<th class="text-nowrap">' +
            escapeHtml(header) +
            "</th>";
    });

    htmlHeader += "</tr>";

    $("#headerPreviewbahv").html(htmlHeader);

    // Body preview
    let htmlBody = "";

    data.forEach(function(row) {
        htmlBody += "<tr>";

        headers.forEach(function(_, columnIndex) {
            const value = String(row[columnIndex] ?? "").toUpperCase();

            if (columnIndex === indexStatus) {
                const status = value.trim().toUpperCase();

                if (status.includes("PENDING")) {
                    htmlBody +=
                        '<td><span class="badge badge-warning text-dark">' +
                        escapeHtml(value) +
                        "</span></td>";
                } else if (status.includes("TIDAK LAYAK")) {
                    htmlBody +=
                        '<td><span class="badge badge-danger">' +
                        escapeHtml(value) +
                        "</span></td>";
                } else {
                    htmlBody +=
                        "<td>" + escapeHtml(value) + "</td>";
                }
            } else {
                htmlBody +=
                    '<td class="text-nowrap">' +
                    escapeHtml(value) +
                    "</td>";
            }
        });

        htmlBody += "</tr>";
    });

    $("#resultpreviewtxtbahv").html(htmlBody);

    // Tutup loading
    Swal.close();

    // Notifikasi hasil
    if (invalidRows.length > 0) {
        Swal.fire({
            icon : "warning",
            title: "Data Read Successfully",
            html:
                "Valid rows: <strong>" +
                data.length.toLocaleString("en-US") +
                "</strong><br>Skipped data rows: <strong>" +
                invalidRows.length.toLocaleString("en-US") +
                "</strong>",
            confirmButtonText: "OK",
            customClass: {
                confirmButton: "btn btn-primary"
            },
            buttonsStyling: false
        });

        console.table(invalidRows);
    } else {
        Swal.fire({
            icon             : "success",
            title            : "File Read Successfully",
            text             : data.length.toLocaleString("en-US") +
                               " BAHV data rows have been processed successfully.",
            timer            : 1800,
            showConfirmButton: false
        });
    }
}
