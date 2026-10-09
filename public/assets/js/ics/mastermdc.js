var mdcRows = {};   // cache data MDC per ID (dipakai modal edit)

load();

function load(){
    datamastermdc();
    datamastersection();
};

$(document).on("click", ".btn-edit-mdc", function (e) {
    e.preventDefault();
    const id = $(this).data("id");
    const row = mdcRows[id];

        if (!row) {
            Swal.fire({
                icon: "warning",
                title: "Data Not Found",
                text: "The selected MDC data could not be found. Please refresh the page and try again.",
                timer: 2500,
                showConfirmButton: false
            });
            return;
        }

    // console.log("EDIT MDC:", row);

    $("#id_mdc").val(row.ID || "");
    $("#edit_document_id").val(row.DOCUMENT_ID || "").trigger("change");
    $("#edit_mdc_code").val(row.MDC_CODE || "");
    $("#edit_name_id").val(row.NAME_ID || "");
    $("#edit_name_en").val(row.NAME_EN || "");
    $("#edit_chapter_no").val(row.CHAPTER_NO || "");
    $("#edit_page_start").val(row.PAGE_START || "");
    $("#edit_page_end").val(row.PAGE_END || "");
    $("#edit_perhatian").val(row.EXPLANATION || "");
    $("#edit_contoh").val(row.EXAMPLE || "");
    $("#title_modal_edit_master_mdc").text("Edit Master MDC " + (row.MDC_CODE || ""));
});

function datamastermdc(){
    $.ajax({
        url      : url + "ics/mastermdc/datamastermdc",
        type     : "POST",
        dataType : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : 'Processing',
                html             : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });

            $("#resultdatamastermdc").empty();
        },
        success: function (response) {

            if (!response || response.responseCode !== "00") {
                Swal.fire({
                    icon             : 'warning',
                    title            : 'No Records Found',
                    text             : 'No records are available for the selected period.',
                    showConfirmButton: false,
                    timer            : 2000
                });
                return;
            }

            const result = Array.isArray(response.responseResult) ? response.responseResult : [];

            // cache row per ID, dipakai saat modal edit dibuka
            mdcRows = {};
            for (var k in result) { mdcRows[result[k].ID] = result[k]; }

            let tableResult = "";

            for (var i in result) {
                var collapseId = 'kt_mdc_row_' + result[i].ID;
                var isFirst    = (parseInt(i) === 0);   // i dari for...in bertipe STRING ("0")
                var page       = result[i].PAGE_END && result[i].PAGE_END !== result[i].PAGE_START ? result[i].PAGE_START + ' - ' + result[i].PAGE_END : (result[i].PAGE_START || '-');
            
                if (!isFirst) {
                    tableResult += "<div class='separator separator-dashed'></div>";
                }
            
                tableResult += "<div class='py-0' data-kt-mdc-row='" + result[i].ID + "'>";
            
                    // ================= HEADER =================
                    tableResult += "<div class='py-3 d-flex flex-stack flex-wrap'>";
            
                        // ---- Toggle ----
                        tableResult += "<div class='d-flex align-items-center collapsible rotate" + (isFirst ? " collapsed" : " collapsed") + "' data-bs-toggle='collapse' href='#" + collapseId + "' role='button' aria-expanded='" + (isFirst ? "true" : "false") + "' aria-controls='" + collapseId + "'>";
                            tableResult += "<div class='me-3 rotate-90'><i class='bi bi-chevron-right text-gray-700'></i></div>";
                            tableResult += "<div class='symbol symbol-40px me-3'><span class='symbol-label bg-light-primary text-primary fw-bolder'>" + esc(result[i].MDC_CODE) + "</span></div>";
                            tableResult += "<div class='me-3'>";
                                tableResult += "<div class='d-flex align-items-center'><h6 class='text-gray-800 fw-bolder mb-0'>" + esc(result[i].NAME_ID || result[i].NAME_EN) + "</h6><div class='badge badge-light-primary ms-5'>Bab " + esc(result[i].CHAPTER_NO || '-') + "</div></div>";
                                tableResult += "<div class='text-muted'>MDC " + esc(result[i].MDC_CODE) + " &bull; Hal. " + esc(page) + "</div>";
                            tableResult += "</div>";
                        tableResult += "</div>";
            
                        // ---- Toolbar ----
                        tableResult += "<div class='d-flex my-3 ms-9'>";
                            tableResult += "<a href='#' class='btn btn-icon btn-active-light-primary w-30px h-30px me-3 btn-edit-mdc' data-id='" + result[i].ID + "' data-bs-toggle='modal' data-bs-target='#modal_edit_master_mdc' title='Edit'><i class='bi bi-pencil-square fs-5'></i></a>";
                            tableResult += "<a href='#' class='btn btn-icon btn-active-light-info w-30px h-30px me-3 btn-view-document' data-filename='"+result[i].DOCUMENT_ID+"' data-page='"+result[i].PAGE_START+"' data-highlight='"+result[i].NAME_ID+"' data-id='" + result[i].ID + "' title='View Document' data-bs-toggle='modal' data-bs-target='#modal_view_document'><i class='bi bi-file-earmark-text fs-5'></i></a>";
                            tableResult += "<a href='#' class='btn btn-icon btn-active-light-danger w-30px h-30px btn-delete-mdc' data-id='" + result[i].ID + "' data-bs-toggle='tooltip' title='Hapus'><i class='bi bi-trash fs-5'></i></a>";
                        tableResult += "</div>";
            
                    tableResult += "</div>";
            
                    // ================= BODY (sejajar dengan header, BUKAN di dalamnya) =================
                    var perhatian = result[i].EXPLANATION ? esc(result[i].EXPLANATION) : "Belum ada perhatian khusus untuk MDC ini.";
                    var example = result[i].EXAMPLE ? esc(result[i].EXAMPLE) : "Belum ada contoh kasus untuk MDC ini.";
            
                    tableResult += "<div id='" + collapseId + "' class='collapse" + (isFirst ? "" : "") + " fs-6 ps-10' data-bs-parent='#kt_mdc_list'>";
                        tableResult += "<div class='py-5 pe-3'>";
            
                            // ---- Ringkasan (kotak statistik) ----
                            tableResult += "<div class='d-flex flex-wrap mb-3'>";
                                tableResult += "<div class='border border-gray-300 border-dashed rounded min-w-100px py-3 px-4 me-4 mb-3'><div class='fs-7 text-muted fw-bold'>Kode MDC</div><div class='fs-3 fw-bolder text-gray-800'>" + esc(result[i].MDC_CODE) + "</div></div>";
                                tableResult += "<div class='border border-gray-300 border-dashed rounded min-w-100px py-3 px-4 me-4 mb-3'><div class='fs-7 text-muted fw-bold'>Bab</div><div class='fs-3 fw-bolder text-gray-800'>" + esc(result[i].CHAPTER_NO || '-') + "</div></div>";
                                tableResult += "<div class='border border-gray-300 border-dashed rounded min-w-100px py-3 px-4 me-4 mb-3'><div class='fs-7 text-muted fw-bold'>Halaman</div><div class='fs-3 fw-bolder text-gray-800'>" + esc(page) + "</div></div>";
                                tableResult += "<div class='border border-gray-300 border-dashed rounded py-3 px-4 mb-3 flex-grow-1'><div class='fs-7 text-muted fw-bold'>Nama MDC</div><div class='d-flex flex-wrap align-items-baseline'><span class='fs-6 fw-bolder text-gray-800 me-2'>" + esc(result[i].NAME_ID) + "</span><span class='fs-7 fst-italic text-gray-600'>" + esc(result[i].NAME_EN) + "</span></div></div>";
                            tableResult += "</div>";
            
                            // ---- Card: Perhatian Khusus ----
                            tableResult += "<div class='bg-light-warning border-start border-4 border-warning rounded p-5 mb-5'>";
                                tableResult += "<div class='d-flex align-items-center mb-3'><i class='bi bi-exclamation-triangle-fill text-warning fs-3 me-3'></i><span class='fw-bolder text-gray-800 fs-6'>Perhatian Khusus Penginputan iDRG</span><span class='badge badge-light-warning ms-3'>MDC " + esc(result[i].MDC_CODE) + "</span></div>";
                                tableResult += "<div class='text-gray-700 lh-lg' style='white-space: pre-line;'>" + italicizeForeignText(perhatian) + "</div>";
                            tableResult += "</div>";
            
                            // ---- Card: Contoh Khusus ----
                            tableResult += "<div class='bg-light-info border-start border-4 border-info rounded p-5'>";
                                tableResult += "<div class='d-flex align-items-center mb-3'><i class='bi bi-info-circle-fill text-info fs-3 me-3'></i><span class='fw-bolder text-gray-800 fs-6'>Contoh Kasus</span><span class='badge badge-light-info ms-3'>MDC " + esc(result[i].MDC_CODE) + "</span></div>";
                                tableResult += "<div class='text-gray-700 lh-lg' style='white-space: pre-line;'>" + italicizeForeignText(example) + "</div>";
                            tableResult += "</div>";
            
                        tableResult += "</div>";
                    tableResult += "</div>";
            
                tableResult += "</div>";
            }

            $("#resultdatamastermdc").html(tableResult);

        },
        complete: function () {
            Swal.close();
        },
        error: function () {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "An error occurred while processing your request.",
                confirmButtonText: "OK"
            });
        }
    });
};

function datamastersection(){
    $.ajax({
        url      : url + "ics/mastermdc/datamastersection",
        type     : "POST",
        dataType : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : 'Processing',
                html             : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });

            $("#resultdatasection").empty();
        },
        success: function (response) {

            if (!response || response.responseCode !== "00") {
                Swal.fire({
                    icon             : 'warning',
                    title            : 'No Records Found',
                    text             : 'No records are available for the selected period.',
                    showConfirmButton: false,
                    timer            : 2000
                });
                return;
            }

            const result = Array.isArray(response.responseResult) ? response.responseResult : [];

            let tableResult = "";
            for (var i in result) {
                tableResult += "<div class='bg-white rounded p-3 mb-2 shadow-sm'>";
                tableResult += "<div class='d-flex align-items-center'>";
                tableResult += "<div class='me-3'>";
                tableResult += "<span class='badge badge-light-primary fw-bold px-3 py-2'>";
                tableResult += esc(result[i].SECTION_CODE);
                tableResult += "</span>";
                tableResult += "</div>";
                tableResult += "<div class='flex-grow-1'>";
                tableResult += "<div class='fw-bolder text-gray-900 fs-6 mb-1'>";
                tableResult += esc(result[i].TITLE_IND);
                tableResult += "</div>";
                tableResult += "<div class='fst-italic text-gray-600 fs-7 mb-2'>";
                tableResult += esc(result[i].TITLE_ENG);
                tableResult += "</div>";
                tableResult += "<div class='d-flex align-items-center'>";
                tableResult += "<i class='bi bi-code-square text-primary me-2'></i>";
                tableResult += "<span class='text-muted fs-8 me-2'>ICD Range</span>";
                tableResult += "<span class='badge badge-light-info fw-semibold'>";
                tableResult += esc(result[i].ICD_RANGE_TEXT);
                tableResult += "</span>";
                tableResult += "</div>";

                if (result[i].CODING_RULES) {
                    tableResult += "<div class='separator separator-dashed my-2'></div>";
                    tableResult += "<div class='d-flex align-items-center mb-2'>";
                    tableResult += "<i class='bi bi-journal-text text-primary me-2'></i>";
                    tableResult += "<span class='text-muted fs-8 me-2'>Coding Rules</span>";
                    tableResult += "</div>";

                    var rules = result[i].CODING_RULES.split(";");

                    for (var r = 0; r < rules.length; r++) {
                        if (!rules[r].trim()) {
                            continue;
                        }

                        var ruleData = {};
                        var ruleParts = rules[r].split("|");

                        for (var p = 0; p < ruleParts.length; p++) {
                            var separatorIndex = ruleParts[p].indexOf("=");

                            if (separatorIndex > -1) {
                                var key = ruleParts[p].substring(0, separatorIndex);
                                var value = ruleParts[p].substring(separatorIndex + 1);

                                ruleData[key] = value;
                            }
                        }

                        tableResult += "<div class='bg-light-primary rounded p-2 mb-2'>";
                        tableResult += "<div class='d-flex align-items-start'>";

                        if (ruleData.RULE_TYPE === "SELECT_BY_CONDITION") {

                            tableResult += "<div class='rounded px-3 py-2 me-2 flex-shrink-0'>";
                            tableResult += "<span class='text-primary fw-bold fs-8'>";
                            tableResult += "CONDITION";
                            tableResult += "</span>";
                            tableResult += "</div>";

                            tableResult += "<div class='flex-grow-1'>";

                            tableResult += "<div class='text-gray-900 fw-semibold fs-8 mb-1'>";
                            tableResult += esc(ruleData.CONDITION || "");
                            tableResult += "</div>";

                            if (ruleData.INSTRUCTION) {
                                tableResult += "<div class='text-gray-700 fs-8'>";
                                tableResult += esc(ruleData.INSTRUCTION);
                                tableResult += "</div>";
                            }

                            tableResult += "</div>";

                        } else if (ruleData.RULE_TYPE) {

                            tableResult += "<div class='rounded px-3 py-2 me-2 flex-shrink-0'>";
                            tableResult += "<span class='text-primary fw-bold fs-8'>";
                            tableResult += esc(ruleData.RULE_TYPE.replace(/_/g, " "));
                            tableResult += "</span>";
                            tableResult += "</div>";

                            tableResult += "<div class='flex-grow-1'>";

                            tableResult += "<div class='text-gray-900 fw-semibold fs-8 mb-1'>";
                            tableResult += esc(ruleData.CONDITION || "");
                            tableResult += "</div>";

                            if (ruleData.INSTRUCTION) {
                                tableResult += "<div class='text-gray-700 fs-8'>";
                                tableResult += esc(ruleData.INSTRUCTION);
                                tableResult += "</div>";
                            }

                            tableResult += "</div>";
                        }

                        tableResult += "</div>";

                        if (
                            ruleData.MANDATORY === "1" ||
                            ruleData.AGE ||
                            ruleData.SEX
                        ) {

                            tableResult += "<div class='d-flex align-items-center flex-wrap gap-2 mt-2'>";

                            if (ruleData.MANDATORY === "1") {
                                tableResult += "<span class='badge badge-light-danger fw-semibold'>";
                                tableResult += "Mandatory";
                                tableResult += "</span>";
                            }

                            if (ruleData.SEX === "L") {
                                tableResult += "<span class='badge badge-light-primary fw-semibold'>";
                                tableResult += "Male";
                                tableResult += "</span>";
                            }

                            if (ruleData.SEX === "P") {
                                tableResult += "<span class='badge badge-light-info fw-semibold'>";
                                tableResult += "Female";
                                tableResult += "</span>";
                            }

                            if (ruleData.AGE) {
                                tableResult += "<span class='badge badge-light-warning fw-semibold'>";
                                tableResult += "Age: " + esc(ruleData.AGE);
                                tableResult += "</span>";
                            }

                            tableResult += "</div>";
                        }

                        tableResult += "</div>";
                    }
                }

                tableResult += "</div>";
                tableResult += "</div>";
                tableResult += "</div>";
            }

            $("#resultdatasection").html(tableResult);

        },
        complete: function () {
            Swal.close();
        },
        error: function () {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "An error occurred while processing your request.",
                confirmButtonText: "OK"
            });
        }
    });
};

$(document).on("submit", "#formaddmastermdc", function (e) {
	e.preventDefault();
    e.stopPropagation();
	var form = $(this);
    var url  = $(this).attr("action");

	$.ajax({
        url       : url,
        data      : form.serialize(),
        method    : "POST",
        dataType  : "JSON",
        cache     : false,
        beforeSend: function () {
            toastr.clear();
            toastr["info"]("Sending request...", "Please wait");
			$("#modal_add_master_mdc_btn").addClass("disabled");
        },
		success: function (data) {

            if(data.responCode == "00"){
                load();
                $("#modal_add_master_mdc").modal("hide");
			}

            toastr.clear();
            toastr[data.responHead](data.responDesc, "INFORMATION");
		},
        complete: function(){
            toastr.clear();
            $("#modal_add_master_mdc_btn").removeClass("disabled");
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
    return false;
});

$(document).on("submit", "#formeditmastermdc", function (e) {
    e.preventDefault();
    e.stopPropagation();
    var form = $(this);
    var url  = form.attr("action");

    $.ajax({
        url       : url,
        data      : form.serialize(),
        method    : "POST",
        dataType  : "JSON",
        cache     : false,
        beforeSend: function () {
            toastr.clear();
            toastr["info"]("Sending request...", "Please wait");
            $("#modal_edit_master_mdc_btn").addClass("disabled");
        },
        success: function (data) {
            if (data.responCode == "00") {
                load();
                $("#modal_edit_master_mdc").modal("hide");
            }
            toastr.clear();
            toastr[data.responHead](data.responDesc, "INFORMATION");
        },
        complete: function () {
            toastr.clear();
            $("#modal_edit_master_mdc_btn").removeClass("disabled");
        },
        error: function () {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "An error occurred while processing your request.",
                confirmButtonText: "OK"
            });
        }
    });
    return false;
});
