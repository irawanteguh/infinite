masterorganization();

function updateActivation (el) {
    const orgid     = el.data("orgid");
    const active    = el.data("active");
    const newActive = active == 1 ? 0 : 1;
    const isActive  = active == 1;

    Swal.fire({
        title            : isActive ? "Deactivate Organization?" : "Activate Organization?",
        text             : isActive ? "This organization will be deactivated." : "This organization will be activated.",
        icon             : "warning",
        showCancelButton : true,
        confirmButtonText: isActive ? "Yes, Deactivate" : "Yes, Activate",
        cancelButtonText : "Cancel",
        buttonsStyling   : false,
        customClass      : {confirmButton: isActive ? "btn btn-danger" : "btn btn-success", cancelButton: "btn btn-light"}
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
                                location.reload();
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
                const iconrsDefault    = `${url}assets/media/logos/infinite.png`;
                const avatar           = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefault    = `${url}assets/media/avatars/blank.png`;
                const avatarpic        = `${url}assets/media/avatars/${result[i].user_id}.jpg`;
                const avatarDefaultpic = `${url}assets/media/avatars/blank.png`;

                getvariabel =   "data-orgid='"+result[i].org_id+"'"+
                                "data-active='"+result[i].active+"'";

                let btnaction = "";

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
                            tableResult += "<div class='d-flex flex-column align-items-start'>";
                                tableResult += "<span class='text-gray-800 fw-bold'>" + (result[i].org_name || "-") + "</span>";
                                tableResult += (result[i].holding === "Y" ? '<span class="badge badge-light-primary mt-1">Headquarters</span>' : '<span class="badge badge-light-info mt-1">Branch</span>');
                            tableResult += "</div>";
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