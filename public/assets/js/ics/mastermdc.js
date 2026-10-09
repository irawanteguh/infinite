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

$(document).on("click", ".btn-edit-coding-rule", function () {
    const index = Number($(this).attr("data-rule-index"));
    const rule  = window.codingRulesData?.[index];

    if (!rule) {
        Swal.fire("Error", "Coding rule data not found.", "error");
        return;
    }

    const ruleType = (rule.RULE_TYPE ?? "").trim().toUpperCase().replace(/\s+/g, "_");

    $("#coding_rule_id").val(rule.ID ?? rule.RULE_ID ?? "");
    $("#coding_rule_section_id").val(rule.SECTION_ID ?? "");
    $("#coding_rule_type").val(ruleType);
    $("#coding_rule_mandatory").val(rule.IS_MANDATORY ?? rule.MANDATORY ?? "");
    $("#coding_rule_sex").val(rule.SEX_CONDITION ?? rule.SEX ?? "");
    $("#coding_rule_age").val(rule.AGE_CONDITION ?? rule.AGE ?? "");
    $("#coding_rule_condition").val(rule.CONDITION_TEXT ?? rule.CONDITION ?? "");
    $("#coding_rule_instruction").val(rule.INSTRUCTION_TEXT ?? rule.INSTRUCTION ?? "");
    $("#coding_rule_source").val(rule.SOURCE_TEXT ?? rule.SOURCE ?? "");
    $("#coding_rule_page_printed").val(rule.PAGE_PRINTED ?? "");
    $("#coding_rule_page_pdf").val(rule.PAGE_PDF ?? "");

    $("#title_modal_edit_master_rules").text("Edit Coding Rules");

    const modalElement = document.getElementById("modal_edit_master_rules");
    bootstrap.Modal.getOrCreateInstance(modalElement).show();
});

$(document).on("click", ".btn-add-coding-rule", function (e) {
    e.preventDefault();

    const sectionId = $(this).attr("data-section-id");

    // Reset form Add Coding Rules
    const form = $("#formaddrules")[0];

    if (!form) {
        Swal.fire("Error", "Add Coding Rules form not found.", "error");
        return;
    }

    form.reset();

    // Set Section ID
    $("#add_coding_rule_id").val("");
    $("#add_coding_rule_section_id").val(sectionId);

    // Set title dan tombol
    $("#title_modal_add_master_rules").text("Add Coding Rules");

    $("#modal_add_master_rules_btn").html(
        "<i class='bi bi-plus-lg me-1'></i> ADD RULES"
    );

    // Tampilkan modal
    const modalElement = document.getElementById("modal_add_master_rules");

    bootstrap.Modal.getOrCreateInstance(modalElement).show();
});

// Open modal Add Notes
$(document).on("click", ".btn_add_note_item", function (e) {
    e.preventDefault();

    const index = Number($(this).attr("data-rule-index"));
    const rule = window.codingRulesData?.[index];

    if (!rule) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Coding Rule data not found."
        });
        return;
    }

    // Set Rule ID and Section ID
    $("#notes_rule_id").val(rule.RULE_ID ?? rule.ID ?? "");
    $("#notes_section_id").val(rule.SECTION_ID ?? "");

    // Display selected Coding Rule
    const condition =
        rule.CONDITION ||
        rule.CONDITION_TEXT ||
        rule.INSTRUCTION ||
        rule.INSTRUCTION_TEXT ||
        "Coding Rule";

    $("#notes_rule_condition").text(condition);

    // Reset notes to one empty item
    $("#rule_notes_container").html(`
        <div class="rule-note-item mb-3">
            <div class="d-flex align-items-start gap-3">
                <span class="badge badge-light-primary mt-3">1</span>

                <div class="flex-grow-1">
                    <textarea
                        class="form-control form-control-solid rule-note-text"
                        name="NOTES[]"
                        rows="2"
                        placeholder="Enter note details..."
                        required></textarea>
                </div>

                <button type="button"
                    class="btn btn-sm btn-icon btn-light-danger btn-remove-note mt-1"
                    title="Remove note">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `);

    // Show modal
    bootstrap.Modal
        .getOrCreateInstance(document.getElementById("modal_add_rule_notes"))
        .show();
});

// Add note item
$(document).on("click", "#btn_add_note_item", function () {
    const item = `
        <div class="rule-note-item mb-3">
            <div class="d-flex align-items-start gap-3">
                <span class="badge badge-light-primary mt-3"></span>

                <div class="flex-grow-1">
                    <textarea
                        class="form-control form-control-solid rule-note-text"
                        name="NOTES[]"
                        rows="2"
                        placeholder="Enter note details..."
                        required></textarea>
                </div>

                <button type="button"
                    class="btn btn-sm btn-icon btn-light-danger btn-remove-note mt-1"
                    title="Remove note">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>`;

    $("#rule_notes_container").append(item);
    updateNoteNumbers();
});

// Remove note item
$(document).on("click", ".btn-remove-note", function () {
    const items = $("#rule_notes_container .rule-note-item");

    if (items.length <= 1) {
        $(this).closest(".rule-note-item").find("textarea").val("");
        return;
    }

    $(this).closest(".rule-note-item").remove();
    updateNoteNumbers();
});

// Update numbering
function updateNoteNumbers() {
    $("#rule_notes_container .rule-note-item").each(function (index) {
        $(this).find(".badge").first().text(index + 1);
    });
}

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
                    icon: "warning",
                    title: "No Records Found",
                    text: "No records are available for the selected period.",
                    showConfirmButton: false,
                    timer: 2000
                });
                $("#resultdatasection").empty();
                return;
            }

            const result = Array.isArray(response.responseResult) ? response.responseResult : [];

            const codingRulesData = [];
            const escHtml = typeof esc === "function"
                ? esc
                : function (value) {
                    return String(value ?? "").replace(/[&<>"']/g, function (char) {
                        return {
                            "&": "&amp;",
                            "<": "&lt;",
                            ">": "&gt;",
                            '"': "&quot;",
                            "'": "&#39;"
                        }[char];
                    });
                };

            // PARSE CODING RULE
            function parseRule(raw, sectionId) {
                const rule = {
                    SECTION_ID: sectionId
                };

                raw.split("|").forEach(function (part) {
                    const pos = part.indexOf("=");

                    if (pos < 0) return;

                    const key = part.substring(0, pos).trim();
                    const value = part.substring(pos + 1).trim();

                    rule[key] = value;
                });

                return rule;
            }

            // PARSE HIERARCHICAL NOTES
            function parseNotes(raw) {
                const detailMap = {};
                const roots = [];

                if (!raw) return roots;

                raw.split("~").forEach(function (entry) {
                    if (!entry.trim()) return;

                    const item = {
                        id: "",
                        parentId: "0",
                        seq: 0,
                        text: "",
                        children: []
                    };

                    entry.split("^").forEach(function (field) {
                        const pos = field.indexOf(":");

                        if (pos < 0) return;

                        const key = field.substring(0, pos).trim();
                        const value = field.substring(pos + 1).trim();

                        if (key === "ID") item.id           = value;
                        if (key === "PARENT") item.parentId = value;
                        if (key === "SEQ") item.seq         = parseInt(value, 10) || 0;
                        if (key === "TEXT") item.text       = value.replace(/^\*\*/, "");
                    });

                    if (item.id && item.text) {
                        detailMap[item.id] = item;
                    }
                });

                Object.keys(detailMap).forEach(function (id) {
                    const item = detailMap[id];

                    if (item.parentId !== "0" && detailMap[item.parentId]) {
                        detailMap[item.parentId].children.push(item);
                    } else {
                        roots.push(item);
                    }
                });

                function sortTree(items) {
                    items.sort(function (a, b) {
                        return a.seq - b.seq;
                    });

                    items.forEach(function (item) {
                        sortTree(item.children);
                    });
                }

                sortTree(roots);
                return roots;
            }

            // RENDER NOTES
            function renderNotes(items) {
                let html = "";

                items.forEach(function (item) {
                    html += "<li class='mb-2'>";
                    html += escHtml(item.text);

                    if (item.children.length) {
                        html += "<ul class='text-gray-600 fs-8 ps-5 mt-2 mb-0'>";
                        html += renderNotes(item.children);
                        html += "</ul>";
                    }

                    html += "</li>";
                });

                return html;
            }

            // RENDER RULE CARD
            function renderRule(rule, index) {
                const type        = rule.RULE_TYPE || "";
                const condition   = rule.CONDITION || rule.CONDITION_TEXT || "";
                const instruction = rule.INSTRUCTION || rule.INSTRUCTION_TEXT || "";
                const mandatory   = rule.MANDATORY ?? rule.IS_MANDATORY ?? "";
                const sex         = rule.SEX ?? rule.SEX_CONDITION ?? "";
                const age         = rule.AGE || rule.AGE_CONDITION || "";
                const rawDetails  = (rule.SUBNOTE || "").trim();
                const rawNote     = (rule.NOTE || "").trim();
                const details     = parseNotes(rawDetails);

                let html = "";

                html += "<div class='bg-light-primary rounded p-3 mb-2'>";
                html += "<div class='row align-items-start g-3'>";

                // RULE TYPE
                html += "<div class='col-md-1 col-12'>";
                if (type) {
                    html += "<div class='rounded px-3 py-2'>";
                    html += "<span class='text-primary fw-bold fs-8'>";
                    html += escHtml(
                        type === "SELECT_BY_CONDITION"
                            ? "CONDITION"
                            : type.replace(/_/g, " ")
                    );
                    html += "</span>";
                    html += "</div>";
                }
                html += "</div>"; // RULE TYPE

                // RULE CONTENT
                html += "<div class='col-md-10 col-12'>";

                // CONDITION
                if (condition) {
                    html += "<div class='text-gray-900 fw-bold fs-8 mb-2' style='white-space: pre-line;'>";
                    html += escHtml(condition);
                    html += "</div>";
                }

                // INSTRUCTION
                if (instruction) {
                    html += "<div class='text-gray-700 fs-8 mb-2' style='white-space: pre-line;'>";
                    html += escHtml(instruction);
                    html += "</div>";
                }

                // MANDATORY, SEX, AGE
                if (String(mandatory) === "1" || age || sex) {
                    html += "<div class='d-flex align-items-center flex-wrap gap-2 mt-2'>";

                    if (String(mandatory) === "1") {
                        html += "<span class='badge badge-light-danger fw-semibold'>Mandatory</span>";
                    }

                    if (sex === "L") {
                        html += "<span class='badge badge-light-primary fw-semibold'>Male</span>";
                    }

                    if (sex === "P") {
                        html += "<span class='badge badge-light-info fw-semibold'>Female</span>";
                    }

                    if (age) {
                        html += "<span class='badge badge-light-warning fw-semibold'>";
                        html += "Age: " + escHtml(age);
                        html += "</span>";
                    }

                    html += "</div>";
                }

                // NOTES DETAILS
                if (details.length) {
                    html += "<div class='mt-3'>";
                    html += "<div class='text-gray-700 fw-bold fs-8 mb-2'>";
                    html += "<i class='ki-duotone ki-notepad fs-5 me-1'>";
                    html += "<span class='path1'></span><span class='path2'></span>";
                    html += "</i>Notes Details</div>";
                    html += "<ul class='text-gray-700 fs-8 ps-5 mb-0'>";
                    html += renderNotes(details);
                    html += "</ul></div>";

                } else if (rawNote) {
                    const notes = rawNote.split("~").filter(function (note) {
                        note = note.trim();
                        return note && !(note.startsWith("ID:") && note.includes("^PARENT:"));
                    });

                    if (notes.length) {
                        html += "<div class='mt-3'>";
                        html += "<div class='text-gray-700 fw-bold fs-8 mb-2'>";
                        html += "<i class='ki-duotone ki-notepad fs-5 me-1'>";
                        html += "<span class='path1'></span><span class='path2'></span>";
                        html += "</i>Notes Details</div>";
                        html += "<ul class='text-gray-700 fs-8 ps-5 mb-0'>";

                        notes.forEach(function (note) {
                            html += "<li class='mb-1'>" + escHtml(note.trim()) + "</li>";
                        });

                        html += "</ul></div>";
                    }
                }

                html += "</div>"; // RULE CONTENT

                // ACTION DROPDOWN
                html += "<div class='col-md-1 col-12 text-md-end'>";

                

                html += "<div class='dropdown'>";
                html += "<button type='button' class='btn btn-sm btn-light-primary dropdown-toggle' ";
                html += "data-bs-toggle='dropdown' data-bs-boundary='viewport' aria-expanded='false'>";
                html += "<i class='ki-duotone ki-setting-2 fs-5 me-1'>";
                html += "<span class='path1'></span><span class='path2'></span>";
                html += "</i>Action</button>";

                html += "<div class='dropdown-menu dropdown-menu-end'>";

                html += "<a href='javascript:void(0)' class='dropdown-item btn_add_note_item' ";
                html += "data-rule-index='" + index + "'>";
                html += "<i class='bi bi-list-ul me-2'></i>";
                html += "Add Notes</a>";

                // EDIT
                html += "<a href='javascript:void(0)' class='dropdown-item btn-edit-coding-rule' ";
                html += "data-rule-index='" + index + "'>";
                html += "<i class='bi bi-pencil-square me-2'>";
                html += "<span class='path1'></span><span class='path2'></span>";
                html += "</i>Edit Coding Rules</a>";

                html += "<div class='separator my-2'></div>";

                // DELETE
                html += "<a href='javascript:void(0)' class='dropdown-item text-danger' ";
                html += "data-action='delete-rule' data-rule-index='" + index + "'>";
                html += "<i class='ki-duotone ki-trash fs-5 me-2'>";
                html += "<span class='path1'></span><span class='path2'></span>";
                html += "</i>Delete Rule</a>";

                html += "</div>"; // Dropdown menu
                html += "</div>"; // Dropdown
                html += "</div>"; // ACTION

                html += "</div>"; // ROW
                html += "</div>"; // Rule card

                return html;
            }

            // RENDER SECTION
            let tableResult = "";

            result.forEach(function (section) {
                tableResult += "<div class='bg-white rounded p-3 mb-5 shadow-sm'>";

                // SECTION HEADER
                tableResult += "<div class='d-flex align-items-start justify-content-between'>";

                // Section information
                tableResult += "<div class='d-flex align-items-start flex-grow-1'>";

                // Section code
                tableResult += "<div class='me-3'>";
                tableResult += "<span class='badge badge-light-primary fw-bold px-3 py-2'>";
                tableResult += escHtml(section.SECTION_CODE);
                tableResult += "</span>";
                tableResult += "</div>";

                // Section titles and ICD range
                tableResult += "<div class='flex-grow-1'>";

                tableResult += "<div class='fw-bolder text-gray-900 fs-6 mb-1'>";
                tableResult += escHtml(section.TITLE_IND);
                tableResult += "<span class='badge badge-light-info fw-semibold ms-5'>";
                tableResult += escHtml(section.ICD_RANGE_TEXT);
                tableResult += "</span>";
                tableResult += "</div>";

                tableResult += "<div class='fst-italic text-gray-600 fs-7 mb-2'>";
                tableResult += escHtml(section.TITLE_ENG);
                tableResult += "</div>";

                tableResult += "</div>"; // Section titles
                tableResult += "</div>"; // Section information

                // Add Rules button
                tableResult += "<div class='ms-3 flex-shrink-0'>";
                tableResult += "<a href='javascript:void(0);' ";
                tableResult += "class='btn btn-sm btn-light-info btn-add-coding-rule' ";
                tableResult += "data-section-id='" + section.SECTION_ID + "'>";
                tableResult += "<i class='bi bi-plus-lg fs-7 me-1'></i>Add Rules";
                tableResult += "</a>";
                tableResult += "</div>"; // Add Rules button

                tableResult += "</div>"; // Section header

                // CODING RULES
                if (section.CODING_RULES) {
                    tableResult += "<div class='separator separator-dashed my-3'></div>";

                    tableResult += "<div class='d-flex align-items-center mb-2'>";
                    tableResult += "<i class='bi bi-journal-text text-primary me-2'></i>";
                    tableResult += "<span class='text-muted fs-8'>Coding Rules</span>";
                    tableResult += "</div>";

                    section.CODING_RULES.split(";").forEach(function (raw) {
                        if (!raw.trim()) {
                            return;
                        }

                        const rule = parseRule(raw, section.SECTION_ID);
                        const index = codingRulesData.length;

                        // Save object for Edit and Delete
                        codingRulesData.push(rule);

                        tableResult += renderRule(rule, index);
                    });
                }

                tableResult += "</div>"; // Section card
            });

            // SAVE DATA OUTSIDE THE AJAX CALLBACK SCOPE
            window.codingRulesData = codingRulesData;

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

$(document).on("submit", "#formeditreules", function (e) {
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
            $("#modal_edit_master_rules_btn").addClass("disabled");
        },
        success: function (data) {
            if (data.responCode == "00") {
                load();
                $("#modal_edit_master_rules").modal("hide");
            }
            toastr.clear();
            toastr[data.responHead](data.responDesc, "INFORMATION");
        },
        complete: function () {
            toastr.clear();
            $("#modal_edit_master_rules_btn").removeClass("disabled");
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

$(document).on("submit", "#formaddrules", function (e) {
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
            $("#modal_add_master_rules_btn").addClass("disabled");
        },
        success: function (data) {
            if (data.responCode == "00") {
                load();
                $("#modal_add_master_rules").modal("hide");
            }
            toastr.clear();
            toastr[data.responHead](data.responDesc, "INFORMATION");
        },
        complete: function () {
            toastr.clear();
            $("#modal_add_master_rules_btn").removeClass("disabled");
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

$(document).on("submit", "#formaddrulenotes", function (e) {
    e.preventDefault();

    const button = $("#modal_add_rule_notes_btn");

    button.prop("disabled", true);

    $.ajax({
        url: url + "Ics/mastermdc/savemasternotes",
        type: "POST",
        data: $(this).serialize(),
        dataType: "JSON",

        success: function (response) {
            if (response.responCode === "00") {
                $("#modal_add_rule_notes").modal("hide");

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: response.responDesc
                });
            } else {
                Swal.fire({
                    icon: "info",
                    title: "Information",
                    text: response.responDesc
                });
            }
        },

        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Failed to save notes."
            });
        },

        complete: function () {
            button.prop("disabled", false);
        }
    });
});