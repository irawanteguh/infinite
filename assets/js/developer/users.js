datausers();

function datausers() {
    $.ajax({
        url     : url + "index.php/developer/users/datausers",
        type    : "POST",
        dataType: "json",
        beforeSend: function () {
            Swal.fire({
                title            : "Processing",
                html             : "Loading data, please wait...",
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });

            $("#resultdatausers").empty();
        },
        success: function (response) {
            if (response.responCode !== "00") {
                Swal.fire({
                    icon             : 'warning',
                    title            : 'No Records Found',
                    text             : 'No records are available for the selected period.',
                    showConfirmButton: false,
                    timer            : 2000
                });
                return;
            }

            const result = Array.isArray(response.responResult) ? response.responResult : [];
            let tableresult = "";

            for (var i in result) {

                const avatar                 = `${url}assets/media/avatars/${result[i].user_id}.jpg`;
                const avatarDefault          = `${url}assets/media/avatars/blank.png`;
                const avatarcreatedby        = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefaultcreatedby = `${url}assets/media/avatars/blank.png`;

                getvariabel =   "data-userid='"+result[i].user_id+"'"+
                                "data-nikrs='"+result[i].nik+"'"+
                                "data-username='"+result[i].username+"'"+
                                "data-name='"+result[i].name+"'"+
                                "data-email='"+result[i].email+"'";

                let btnaction = "";

                btnaction += "<a class='dropdown-item btn btn-sm text-primary' data-bs-toggle='modal' data-bs-target='#modal_edit_user_root' "+getvariabel+" onclick='getdata($(this));'><i class='bi bi-pencil text-primary me-4'></i>Edit</a>";
                
                if(result[i].suspend==="N"){
                    btnaction += "<a class='dropdown-item btn btn-sm text-danger' "+getvariabel+" data-active='Y' onclick='activation($(this));'><i class='bi bi-person-slash text-danger me-4'></i>Suspend</a>";
                }else{
                    btnaction += "<a class='dropdown-item btn btn-sm text-success' "+getvariabel+" data-active='N' onclick='activation($(this));'><i class='bi bi-bookmark-check text-success me-4'></i>Reactive</a>";
                }

                if(result[i].active==="1"){
                    btnaction += "<a class='dropdown-item btn btn-sm text-danger' "+getvariabel+" data-active='0' onclick='deleteuser($(this));'><i class='bi bi-trash-fill text-danger me-4'></i>Delete</a>";
                }

                tableresult += "<tr>";
                tableresult += "<td class='ps-4'>" + (parseInt(i) + 1) + "</td>";
                tableresult += "<td>" + (result[i].username || "-") + "</td>";

                tableresult += "<td>";
                    tableresult += "<div class='d-flex align-items-center'>";
                        tableresult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                            tableresult += "<div class='symbol-label'>";
                                tableresult += "<img ";
                                tableresult += "src='" + avatar + "' ";
                                tableresult += "class='w-100' ";
                                tableresult += "alt='" + (result[i].name || "") + "' ";
                                tableresult += "onerror=\"this.onerror=null;this.src='" + avatarDefault + "';\">";
                            tableresult += "</div>";
                        tableresult += "</div>";
                        tableresult += "<div class='d-flex flex-column'>";
                            tableresult += "<span class='text-gray-800 fw-bold'>";
                            tableresult += (result[i].name || "-");
                            tableresult += "</span>";
                            tableresult += "<span class='text-muted'>";
                            tableresult += (result[i].email || "-");
                            tableresult += "</span>";
                        tableresult += "</div>";
                    tableresult += "</div>";
                tableresult += "</td>";

                tableresult += "<td>" + (result[i].orgname || "-") + "</td>";

                tableresult += "<td>";
                if (result[i].suspend == "N") {
                    tableresult += "<span class='badge badge-light-success'>";
                    tableresult += "Active";
                    tableresult += "</span>";
                } else {
                    tableresult += "<span class='badge badge-light-danger'>";
                    tableresult += "Suspend";
                    tableresult += "</span>";
                }
                tableresult += "</td>";

                tableresult += "<td>";
                    tableresult += "<div class='d-flex align-items-center'>";
                        tableresult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                            tableresult += "<div class='symbol-label'>";
                                tableresult += "<img ";
                                tableresult += "src='" + avatarcreatedby + "' ";
                                tableresult += "class='w-100' ";
                                tableresult += "alt='" + (result[i].dibuatoleh || "") + "' ";
                                tableresult += "onerror=\"this.onerror=null;this.src='" + avatarDefaultcreatedby + "';\">";
                            tableresult += "</div>";
                        tableresult += "</div>";
                        tableresult += "<div class='d-flex flex-column'>";
                            tableresult += "<span class='text-gray-800 fw-bold'>";
                            tableresult += (result[i].dibuatoleh || "-");
                            tableresult += "</span>";
                            tableresult += "<span class='text-muted'>";
                            tableresult += (result[i].dibuattgl || "-");
                            tableresult += "</span>";
                        tableresult += "</div>";
                    tableresult += "</div>";
                tableresult += "</td>";

                tableresult += "<td class='text-end'>";
                    tableresult += "<div class='btn-group'>";
                        tableresult += "<button ";
                        tableresult += "type='button' ";
                        tableresult += "class='btn btn-light-primary btn-sm dropdown-toggle' ";
                        tableresult += "data-bs-toggle='dropdown'>";
                        tableresult += "Actions";
                        tableresult += "</button>";
                        tableresult += "<div class='dropdown-menu dropdown-menu-end'>";
                        tableresult += btnaction;
                        tableresult += "</div>";
                    tableresult += "</div>";
                tableresult += "</td>";
                tableresult += "</tr>";
            }

            $("#resultdatausers").html(tableresult);

            initDataTable("#datausers_table", "#searchtable");
        },
        complete: function () {
            Swal.close();
        },
        error: function () {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "We were unable to process your request due to a server error. Please try again later. If the problem persists, contact your system administrator.",
                confirmButtonText: "OK"
            });
        }
    });

}