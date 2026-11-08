let parentOrganizationOptions = [];
const organizationParentTypes = {
    private_hospital: { holding: 'H', label: 'PT / Badan Penyelenggara' },
    subdistrict_health_department: { holding: 'A', label: 'Dinas Kesehatan' },
    government_hospital: { holding: 'B', label: 'Suku Dinas Kesehatan' }
};
const organizationHierarchyDescriptions = {
    private_company: 'Hirarki swasta: PT / Badan Penyelenggara.',
    private_hospital: 'Hirarki swasta: PT / Badan Penyelenggara → Rumah Sakit Swasta.',
    health_department: 'Hirarki pemerintah daerah: Dinas Kesehatan → Suku Dinas Kesehatan → Rumah Sakit.',
    subdistrict_health_department: 'Hirarki pemerintah daerah: Dinas Kesehatan → Suku Dinas Kesehatan → Rumah Sakit.',
    government_hospital: 'Hirarki pemerintah daerah: Dinas Kesehatan → Suku Dinas Kesehatan → Rumah Sakit Pemerintah Daerah.'
};
const escapeOrganizationText = value => $('<div>').text(value == null ? '' : String(value)).html();

masterorganization();
loadParentOrganizations();
toggleParentOrganizationField();
$(document).on("change", "#modal_add_organization_type", toggleParentOrganizationField);

function toggleParentOrganizationField() {
    const type = $("#modal_add_organization_type").val();
    const parentConfig = organizationParentTypes[type];
    const isHospital = type === 'private_hospital' || type === 'government_hospital';
    $("#parentOrganizationContainer").toggle(Boolean(parentConfig));
    $("#modal_add_organization_header").prop('required', Boolean(parentConfig));
    $("#parentOrganizationLabel").text(parentConfig ? `Pilih ${parentConfig.label}` : 'Organisasi Induk');
    $("#organizationHospitalCodeContainer, #organizationPpkContainer").toggle(isHospital);
    $("#organizationHierarchyHelp").text(organizationHierarchyDescriptions[type] || 'Pilih jenis organisasi untuk melihat jalur hirarkinya.');
    if (!isHospital) $("#modal_add_organization_koders, #modal_add_organization_kodeppk").val('');
    if (!parentConfig) $("#modal_add_organization_header").val("").trigger("change");
    renderParentOrganizationOptions(parentConfig ? parentConfig.holding : '');
}

function loadParentOrganizations() {
    $.ajax({
        url     : url + "developer/organization/headerorganizations",
        type    : "POST",
        dataType: "JSON",
        success : function (response) {
            if (response.responseCode !== "00" || !Array.isArray(response.responseResult)) {
                return;
            }
            parentOrganizationOptions = response.responseResult;
            toggleParentOrganizationField();
        }
    });
}

function renderParentOrganizationOptions(requiredHolding) {
    const $select = $("#modal_add_organization_header");
    const previousValue = $select.val();
    $select.find("option:not(:first)").remove();
    parentOrganizationOptions.filter(row => !requiredHolding || row.holding === requiredHolding).forEach(row => {
        const typeLabel = row.holding === 'H' ? 'PT / Badan Penyelenggara' : row.holding === 'A' ? 'Dinas Kesehatan' : 'Suku Dinas Kesehatan';
        $select.append($("<option>", { value: row.org_id, text: `${row.org_name || '-'} — ${typeLabel}` }));
    });
    const previousOptionExists = previousValue && $select.find('option').toArray().some(option => option.value === previousValue);
    if (previousOptionExists) {
        $select.val(previousValue);
    } else {
        $select.val('');
    }
    $select.trigger('change');
}

function organizationTypeLabel(row) {
    if (row.holding === 'H') return 'PT / Badan Penyelenggara';
    if (row.holding === 'A') return 'Dinas Kesehatan';
    if (row.holding === 'B') return 'Suku Dinas Kesehatan';
    if (row.holding === 'C' && row.parent_holding === 'H') return 'Rumah Sakit Swasta';
    if (row.holding === 'C') return 'Rumah Sakit Pemerintah Daerah';
    return 'Organisasi';
}

function organizationHierarchyCell(row) {
    const level = Math.max(0, Math.min(2, Number(row.hierarchy_level) || 0));
    const parentLine = row.hierarchy_parent_name
        ? `<div class="text-muted fs-8 mt-1"><i class="bi bi-diagram-3 me-1"></i>${escapeOrganizationText(row.hierarchy_parent_name)}</div>`
        : '';
    const connector = level > 0 ? `<span class="text-gray-400 me-2"><i class="bi bi-arrow-return-right"></i></span>` : '';
    return `<div class="d-flex align-items-start" style="padding-left:${level * 28}px">${connector}<div class="d-flex flex-column"><span class="text-gray-800 fw-bold">${escapeOrganizationText(row.org_name || '-')}</span><span class="badge badge-light-${row.holding === 'A' ? 'primary' : row.holding === 'B' ? 'warning' : row.holding === 'C' ? (row.parent_holding === 'H' ? 'success' : 'danger') : 'info'} mt-1 align-self-start">${organizationTypeLabel(row)}</span>${parentLine}</div></div>`;
}

function addAdministratorUser(el) {
    const orgid   = el.data("orgid");
    const koders  = el.data("koders");
    const username = "admin_" + koders;

    Swal.fire({
        title             : "Add User Administrator?",
        html              : "Akan dibuat user <b>" + username + "</b> untuk organization ini.<br><small class=\"text-muted\">Password default: 12345678</small>",
        icon              : "question",
        showCancelButton  : true,
        confirmButtonColor: "#50CD89",
        cancelButtonColor : "#6c757d",
        confirmButtonText : '<i class="bi bi-person-add text-white"></i> Ya, Buat',
        cancelButtonText  : "Cancel",
        reverseButtons    : true
    }).then(function (result) {
        if (!result.isConfirmed) {
            return;
        }
        $.ajax({
            url     : url + "developer/organization/addadministratoruser",
            type    : "POST",
            data    : { orgid: orgid },
            dataType: "JSON",
            success : function (response) {
                if (response.responseCode === "00") {
                    Swal.fire({
                        title            : "Success",
                        text             : response.responseDesc,
                        icon             : "success",
                        timer            : 2500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire("Info", response.responseDesc, response.responseHead === "error" ? "error" : "info");
                }
            }
        });
    });
}

function updateActivation (el) {
    const orgid     = el.data("orgid");
    const active    = el.data("active");
    const newActive = active == 1 ? 0 : 1;
    const isActive  = active == 1;

    Swal.fire({
        title             : isActive ? "Deactivate Organization?" : "Activate Organization?",
        html              : isActive ? `Organization ini akan dinonaktifkan dan tidak dapat digunakan.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>` : `Organization ini akan diaktifkan kembali dan dapat digunakan.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`,
        icon              : "warning",
        showCancelButton  : true,
        confirmButtonColor: isActive ? "#d33" : "#50CD89",
        cancelButtonColor : "#6c757d",
        confirmButtonText : isActive ? '<i class="bi bi-trash3 text-white"></i> Yes, Deactivate' : '<i class="bi bi-check-circle text-white"></i> Yes, Activate',
        cancelButtonText  : "Cancel",
        reverseButtons    : true,
        timer             : 10000,
        timerProgressBar  : true
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url      : url + "developer/organization/updateActivation",
                type     : "POST",
                data     : {orgid : orgid,active: newActive},
                dataType : "JSON",
                success  : function(response) {
                    if (response.responseCode === "00") {
                        Swal.fire({
                            title             : "Success",
                            text              : response.responseDesc,
                            icon              : "success",
                            timer             : 1500,
                            showConfirmButton : false,
                            didClose          : function() {
                                masterorganization();
                            }
                        });
                    } else {
                        Swal.fire("Error", response.responseDesc, "error");
                    }
                }
            });
        }
    });
}

function masterorganization() {
    $.ajax({
        url       : url+"developer/organization/masterorganization",
        type      : "POST",
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

            $("#resultmasterorganization").empty();
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

            for (var i in result) {
                const iconrs           = `${url}assets/media/logos/${result[i].org_id}.png`;
                const iconrsDefault    = `${url}assets/media/noimages.png`;
                const avatar           = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefault    = `${url}assets/media/avatars/blank.png`;
                const avatarpic        = `${url}assets/media/avatars/${result[i].user_id}.jpg`;
                const avatarDefaultpic = `${url}assets/media/avatars/blank.png`;

                    const getvariabel =   "data-orgid='"+result[i].org_id+"'"+
                                "data-active='"+result[i].active+"'";

                let btnaction = "";

                if (result[i].kode_rs) {
                    btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm' data-orgid='"+result[i].org_id+"' data-koders='"+result[i].kode_rs+"' onclick='addAdministratorUser($(this));'><i class='bi bi-person-add me-4'></i>Add User Administrator</a>";
                }

                btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm "+(result[i].active === '1' ? 'text-danger' : 'text-success')+"' "+getvariabel+" data-active='"+result[i].active+"' onclick='updateActivation($(this));'><i class='bi "+(result[i].active === '1' ? 'bi-x-circle text-danger' : 'bi-check-circle text-success')+" me-4'></i>"+(result[i].active === '1' ? 'Non Active' : 'Active')+"</a>";

                tableResult += "<tr>";
                    tableResult += "<td class='text-start ps-4'>"+(parseInt(i) + 1)+"</td>";

                    tableResult += "<td>";
                        tableResult += "<div class='d-flex align-items-center'>";
                            tableResult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                                tableResult += "<div class='symbol-label'>";
                                    tableResult += "<img src='" + iconrs + "' class='w-100' alt='" + (result[i].org_name || "") + "' onerror=\"this.onerror=null;this.src='" + iconrsDefault + "';\">";
                                tableResult += "</div>";
                            tableResult += "</div>";
                            tableResult += organizationHierarchyCell(result[i]);
                        tableResult += "</div>";
                    tableResult += "</td>";

                    tableResult += "<td><div><a href='"+(result[i].website || "-")+"'target='_blank'>"+(result[i].website || "-")+"</a></div><div>"+(result[i].email || "-")+"</div></td>";
                    tableResult += "<td>"+(result[i].address || "-")+"</td>";

                    tableResult += "<td>";
                        tableResult += "<div class='d-flex align-items-center'>";
                            tableResult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                                tableResult += "<div class='symbol-label'>";
                                    tableResult += "<img ";
                                    tableResult += "src='" + avatarpic + "' ";
                                    tableResult += "class='w-100' ";
                                    tableResult += "alt='" + (result[i].pic || "") + "' ";
                                    tableResult += "onerror=\"this.onerror=null;this.src='" + avatarDefaultpic + "';\">";
                                tableResult += "</div>";
                            tableResult += "</div>";
                            tableResult += "<div class='d-flex flex-column'>";
                                tableResult += "<span class='text-gray-800 fw-bold'>";
                                tableResult += (result[i].pic || "-");
                                tableResult += "</span>";
                                tableResult += "<span class='text-muted'>";
                                tableResult += (result[i].emailpic || "-");
                                tableResult += "</span>";
                            tableResult += "</div>";
                        tableResult += "</div>";
                    tableResult += "</td>";

                    tableResult += "<td><span class='badge badge-light-"+(result[i].active == '1' ? "success" : "danger")+"'>"+(result[i].active == '1' ? "Active" : "Non Active")+"</span></td>";

                    tableResult += "<td>";
                        tableResult += "<div class='d-flex align-items-center'>";
                            tableResult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                                tableResult += "<div class='symbol-label'>";
                                    tableResult += "<img ";
                                    tableResult += "src='" + avatar + "' ";
                                    tableResult += "class='w-100' ";
                                    tableResult += "alt='" + (result[i].dibuatoleh || "") + "' ";
                                    tableResult += "onerror=\"this.onerror=null;this.src='" + avatarDefault + "';\">";
                                tableResult += "</div>";
                            tableResult += "</div>";
                            tableResult += "<div class='d-flex flex-column'>";
                                tableResult += "<span class='text-gray-800 fw-bold'>";
                                tableResult += (result[i].dibuatoleh || "-");
                                tableResult += "</span>";
                                tableResult += "<span class='text-muted'>";
                                tableResult += (result[i].dibuattgl || "-");
                                tableResult += "</span>";
                            tableResult += "</div>";
                        tableResult += "</div>";
                    tableResult += "</td>";

                    tableResult += "<td class='text-end'>";
                        tableResult += "<div class='btn-group'>";
                            tableResult += "<button type='button' class='btn btn-light-primary dropdown-toggle btn-sm' data-bs-toggle='dropdown'>Actions</button>";
                            tableResult += "<div class='dropdown-menu'>";
                                tableResult += btnaction;
                            tableResult += "</div>";
                        tableResult += "</div>";
                    tableResult += "</td>";
                    
                tableResult += "</tr>";
            }

            $("#resultmasterorganization").html(tableResult);

            initDataTable("#masterorganization_table","#searchtable",10);
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

$(document).on("submit", "#formaddorganization", function (e) {
    e.preventDefault();
    const data = new FormData(this);
    $.ajax({
        url        : url + "developer/organization/addorganization",
        data       : data,
        method     : "POST",
        dataType   : "JSON",
        cache      : false,
        processData: false,
        contentType: false,
        beforeSend : function () {
            Swal.fire({
                title            : "Processing",
                html             : "Please wait while the system saves organization data.",
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });
        },
        success: function (response) {
            if (response.responseCode !== "00") {
                Swal.fire("Info", response.responseDesc, response.responseHead === "error" ? "error" : "info");
                return;
            }
            $("#modal_add_organization")[0].reset();
            $("#modal_add_organization").modal("hide");
            $("#modal_add_organization_type, #modal_add_organization_header").val("").trigger("change");
            toggleParentOrganizationField();
            Swal.fire({
                title            : "Success",
                text             : response.responseDesc,
                icon             : "success",
                timer            : 2000,
                showConfirmButton: false,
                didClose         : function () {
                    masterorganization();
                }
            });
        },
        error: function () {
            Swal.fire("Error", "An error occurred while processing your request.", "error");
        },
        complete: function () {
            Swal.close();
        }
    });
    return false;
});
