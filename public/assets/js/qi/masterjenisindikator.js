datamasterjenisindikator();

function updateActivation (el) {
    const id     = el.data("id");
    const active    = el.data("active");
    const newActive = active == 1 ? 0 : 1;
    const isActive  = active == 1;

    Swal.fire({
        title            : isActive ? "Deactivate Indicator Category?" : "Activate Indicator Category?",
        text             : isActive ? "This Indicator Category will be deactivated." : "This Indicator Category will be activated.",
        icon             : "warning",
        showCancelButton : true,
        confirmButtonText: isActive ? "Yes, Deactivate" : "Yes, Activate",
        cancelButtonText : "Cancel",
        buttonsStyling   : false,
        customClass      : {confirmButton: isActive ? "btn btn-danger" : "btn btn-success", cancelButton: "btn btn-light"}
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url      : url + "qi/masterjenisindikator/updatejenisindikator",
                type     : "POST",
                data     : {jenisindikatorid : id,active: newActive},
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

function datamasterjenisindikator() {
    $.ajax({
        url       : url+"qi/masterjenisindikator/datamasterjenisindikator",
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

            $("#resultdatamasterjenisindikator").empty();
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
                const avatar        = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefault = `${url}assets/media/avatars/blank.png`;

                getvariabel =   "data-id='"+result[i].jenis_indikator_id+"' ";

                let btnaction = "";

                btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm "+(result[i].active === '1' ? 'text-danger' : 'text-success')+"' "+getvariabel+" data-active='"+result[i].active+"' onclick='updateActivation($(this));'><i class='bi "+(result[i].active === '1' ? 'bi-x-circle text-danger' : 'bi-check-circle text-success')+" me-4'></i>"+(result[i].active === '1' ? 'Non Active' : 'Active')+"</a>";

                tableResult += "<tr>";

                    tableResult += "<td class='ps-4'>"+(parseInt(i) + 1)+"</td>";
                    tableResult += "<td>"+result[i].jenis+"</td>";

                    // Status
                    tableResult += "<td>";
                    if (result[i].active == "1") {
                        tableResult += "<span class='badge badge-light-success'>";
                        tableResult += "Active";
                        tableResult += "</span>";
                    } else {
                        tableResult += "<span class='badge badge-light-danger'>";
                        tableResult += "Inactive";
                        tableResult += "</span>";
                    }
                    tableResult += "</td>";

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

                    // Action
                    tableResult += "<td class='text-end'>";
                        tableResult += "<div class='btn-group'>";
                            tableResult += "<button type='button' class='btn btn-light-primary btn-sm dropdown-toggle' data-bs-toggle='dropdown'> Actions</button>";
                            tableResult += "<div class='dropdown-menu dropdown-menu-end'>";
                                tableResult += btnaction;
                            tableResult += "</div>";
                        tableResult += "</div>";
                    tableResult += "</td>";
                
                tableResult += "</tr>";
            }

            $("#resultdatamasterjenisindikator").html(tableResult);

            initDataTable("#datamasterjenisindikator_table","#searchtable",10);
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