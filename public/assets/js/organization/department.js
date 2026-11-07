datamasterdepartment();

function getdata(btn){
    $("#modal_edit_masterdepartment_departmentid").val(btn.data("departmentid"));
    $("#modal_edit_masterdepartment_department").val(btn.data("department"));
    $("#modal_edit_masterdepartment_headdepartment").val(btn.data("headdepartment")).trigger("change");
}

function updateActivation (el) {
    const id        = el.data("departmentid");
    const active    = el.data("active");
    const newActive = active == 1 ? 0 : 1;
    const isActive  = active == 1;

    Swal.fire({
        title             : isActive ? "Deactivate Department?" : "Activate Department?",
        html              : isActive ? `Department ini akan dinonaktifkan dan tidak dapat digunakan.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>` : `Department ini akan diaktifkan kembali dan dapat digunakan.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`,
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
                url      : url + "organization/department/updatedepartment",
                type     : "POST",
                data     : {departmentid : id,active: newActive},
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
                                datamasterdepartment();
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

function datamasterdepartment() {
    $.ajax({
        url       : url+"organization/department/datamasterdepartment",
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

            $("#resultdatamasterdepartment").empty();
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
                const avatar           = `${url}assets/media/avatars/${result[i].last_update_by}.jpg`;
                const avatarDefault    = `${url}assets/media/avatars/blank.png`;
                const avatarpic        = `${url}assets/media/avatars/${result[i].user_id}.jpg`;
                const avatarDefaultpic = `${url}assets/media/avatars/blank.png`;

                var getvariabel = "";
                    getvariabel += " data-departmentid='" + result[i].department_id + "'";
                    getvariabel += " data-department='" + result[i].department + "'";
                    getvariabel += " data-headdepartment='" + result[i].user_id + "'";

                let btnaction = "";

                btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm "+(result[i].active === '1' ? 'text-danger' : 'text-success')+"' "+getvariabel+" data-active='"+result[i].active+"' onclick='updateActivation($(this));'><i class='bi "+(result[i].active === '1' ? 'bi-x-circle text-danger' : 'bi-check-circle text-success')+" me-4'></i>"+(result[i].active === '1' ? 'Deactivate' : 'Activate')+"</a>";
                btnaction += "<a class='dropdown-item btn btn-sm text-primary' data-bs-toggle='modal' data-bs-target='#modal_edit_masterdepartment' "+getvariabel+" onclick='getdata($(this));'><i class='bi bi-pencil text-primary me-4'></i>Edit</a>";

                tableResult += "<tr>";
                    tableResult += "<td class='text-start ps-4'>"+(parseInt(i) + 1)+"</td>";
                    tableResult += "<td>"+(result[i].department || "-")+"</td>";

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

                    tableResult += "<td>" + renderteam(result[i].memberdepartment, 3) + "</td>";

                    tableResult += "<td>"+(result[i].active == 1 ? '<span class=\"badge badge-light-success\">Active</span>' : '<span class=\"badge badge-light-danger\">Deactive</span>')+"</td>";
                    
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

            $("#resultdatamasterdepartment").html(tableResult);
            initDataTable("#datamasterdepartment_table","#searchtable",10);
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

$(document).on("submit", "#formeditmasterdepartment", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'organization/department/editmasterdepartment',
        data       : data,
        method     : "POST",
        dataType   : "JSON",
        cache      : false,
        processData: false,
        contentType: false,
        beforeSend : function () {
            Swal.fire({
                title: 'Processing',
                html : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });
        },
		success: function (response) {
            if (response.responseCode !== "00") {
                Swal.fire({
                    title            : "<h1 class='font-weight-bold'>For Your Information</h1>",
                    html             : "<b>"+response.responseDesc+"</b>",
                    icon             : data.responseHead,
                    confirmButtonText: 'Please Try Again',
                    customClass      : {confirmButton: 'btn btn-danger'},
                    timerProgressBar : true,
                    timer            : 5000,
                    showClass        : {popup: "animate__animated animate__fadeInUp animate__faster"},
                    hideClass        : {popup: "animate__animated animate__fadeOutDown animate__faster"}
                });
                return;
            }

            $('#modal_edit_masterdepartment').modal('hide');
            Swal.close();
			 datamasterdepartment();
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
    return false;
});

$(document).on("submit", "#formaddmasterdepartment", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'organization/department/addmasterdepartment',
        data       : data,
        method     : "POST",
        dataType   : "JSON",
        cache      : false,
        processData: false,
        contentType: false,
        beforeSend : function () {
            Swal.fire({
                title: 'Processing',
                html : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });
        },
		success: function (response) {
            if (response.responseCode !== "00") {
                Swal.fire({
                    title            : "<h1 class='font-weight-bold'>For Your Information</h1>",
                    html             : "<b>"+response.responseDesc+"</b>",
                    icon             : response.responseHead,
                    confirmButtonText: 'Please Try Again',
                    customClass      : {confirmButton: 'btn btn-danger'},
                    timerProgressBar : true,
                    timer            : 5000,
                    showClass        : {popup: "animate__animated animate__fadeInUp animate__faster"},
                    hideClass        : {popup: "animate__animated animate__fadeOutDown animate__faster"}
                });
                return;
            }

            $('#modal_add_department').modal('hide');
            Swal.close();
			 datamasterdepartment();
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
