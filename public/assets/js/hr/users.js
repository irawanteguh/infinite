datamasterusers();

function datamasterusers() {
    $.ajax({
        url       : url+"hr/users/datamasterusers",
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

            $("#resultmasterusers").empty();
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
                const avatar                 = `${url}assets/media/avatars/${result[i].user_id}.jpg`;
                const avatarDefault          = `${url}assets/media/avatars/blank.png`;
                const avatarcreatedby        = `${url}assets/media/avatars/${result[i].last_update_by}.jpg`;
                const avatarDefaultcreatedby = `${url}assets/media/avatars/blank.png`;

                var getvariabel = "";
                    getvariabel += " data-userid='" + result[i].user_id + "'";
                    getvariabel += " data-nikrs='" + result[i].nik + "'";
                    getvariabel += " data-username='" + result[i].username + "'";
                    getvariabel += " data-name='" + result[i].name + "'";
                    getvariabel += " data-email='" + result[i].email + "'";
                    getvariabel += " data-department='" + result[i].department_id + "'";
                    

                let btnaction = "";

                btnaction += "<a class='dropdown-item btn btn-sm text-primary' data-bs-toggle='modal' data-bs-target='#modal_edit_user' "+getvariabel+" onclick='getdatausers($(this));'><i class='bi bi-pencil text-primary me-4'></i>Edit</a>";
                
                if(result[i].suspend==="N"){btnaction += "<a class='dropdown-item btn btn-sm text-danger' "+getvariabel+" data-active='Y' onclick='activationuser($(this));'><i class='bi bi-person-slash text-danger me-4'></i>Suspend</a>";}else{btnaction += "<a class='dropdown-item btn btn-sm text-success' "+getvariabel+" data-active='N' onclick='activationuser($(this));'><i class='bi bi-bookmark-check text-success me-4'></i>Reactive</a>";}

                if(result[i].active==="1"){btnaction += "<a class='dropdown-item btn btn-sm text-danger' "+getvariabel+" data-active='0' onclick='deleteuser($(this));'><i class='bi bi-trash-fill text-danger me-4'></i>Delete</a>";}

                tableResult += "<tr>";
                tableResult += "<td class='ps-4'>" + (parseInt(i) + 1) + "</td>";
                tableResult += "<td>" + (result[i].username || "-") + "</td>";

                tableResult += "<td>";
                    tableResult += "<div class='d-flex align-items-center'>";
                        tableResult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                            tableResult += "<div class='symbol-label'>";
                                tableResult += "<img ";
                                tableResult += "src='" + avatar + "' ";
                                tableResult += "class='w-100' ";
                                tableResult += "alt='" + (result[i].name || "") + "' ";
                                tableResult += "onerror=\"this.onerror=null;this.src='" + avatarDefault + "';\">";
                            tableResult += "</div>";
                        tableResult += "</div>";
                        tableResult += "<div class='d-flex flex-column'>";
                            tableResult += "<span class='text-gray-800 fw-bold'>";
                            tableResult += (result[i].name || "-");
                            tableResult += "</span>";
                            tableResult += "<span class='text-muted'>";
                            tableResult += (result[i].email || "-");
                            tableResult += "</span>";
                        tableResult += "</div>";
                    tableResult += "</div>";
                tableResult += "</td>";

                tableResult += "<td><div>" + (result[i].orgname || "-") + "</div><div>" + (result[i].department || "-") + "</div></td>";

                tableResult += "<td>";
                if (result[i].suspend == "N") {
                    tableResult += "<span class='badge badge-light-success'>";
                    tableResult += "Active";
                    tableResult += "</span>";
                } else {
                    tableResult += "<span class='badge badge-light-danger'>";
                    tableResult += "Suspend";
                    tableResult += "</span>";
                }
                tableResult += "</td>";

                tableResult += "<td>";
                    tableResult += "<div class='d-flex align-items-center'>";
                        tableResult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                            tableResult += "<div class='symbol-label'>";
                                tableResult += "<img ";
                                tableResult += "src='" + avatarcreatedby + "' ";
                                tableResult += "class='w-100' ";
                                tableResult += "alt='" + (result[i].dibuatoleh || "") + "' ";
                                tableResult += "onerror=\"this.onerror=null;this.src='" + avatarDefaultcreatedby + "';\">";
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
                        tableResult += "<button ";
                        tableResult += "type='button' ";
                        tableResult += "class='btn btn-light-primary btn-sm dropdown-toggle' ";
                        tableResult += "data-bs-toggle='dropdown'>";
                        tableResult += "Actions";
                        tableResult += "</button>";
                        tableResult += "<div class='dropdown-menu dropdown-menu-end'>";
                        tableResult += btnaction;
                        tableResult += "</div>";
                    tableResult += "</div>";
                tableResult += "</td>";
                tableResult += "</tr>";
            }

            $("#resultdatamasterusers").html(tableResult);

            initDataTable("#datamasterusers_table","#searchtable",10);
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