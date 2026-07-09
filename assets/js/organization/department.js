let table = null;

datadepartment();

function activation(el) {

    let departmentid = el.data('departmentid');
    let active = String(el.data('active'));

    let isDeactive = active === "0";

    Swal.fire({
        title: isDeactive ? "Deactivate Department?" : "Reactivate Department?",
        html: isDeactive
            ? `
                Department ini akan dinonaktifkan.<br>
                <small class="text-muted">
                    Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.
                </small>
              `
            : `
                Department ini akan diaktifkan kembali.<br>
                <small class="text-muted">
                    Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.
                </small>
              `,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: isDeactive ? "#d33" : "#50CD89",
        cancelButtonColor: "#6c757d",
        confirmButtonText: isDeactive
            ? '<i class="bi bi-trash3 text-white"></i> Ya, Deactivate'
            : '<i class="bi bi-check-circle text-white"></i> Ya, Reactivate',
        cancelButtonText: "Batal",
        reverseButtons: true,
        timer: 10000,
        timerProgressBar: true
    }).then((result) => {

        if (!result.isConfirmed) return;

        $.ajax({
            url: url + "index.php/organization/department/activation",
            type: "POST",
            dataType: "json",
            data: {
                departmentid: departmentid,
                active: active
            },
            success: function (response) {

                Swal.fire({
                    icon: response.responHead,
                    title: response.responDesc,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });

                if (response.responCode === "00") {
                    datadepartment();
                }

            },
            error: function () {
                Swal.fire({
                    icon: "error",
                    title: "Request Failed",
                    text: "We were unable to process your request due to a server error. Please try again later. If the problem persists, contact your system administrator.",
                    confirmButtonText: "OK"
                });
            }
        });

    });

}

function datadepartment() {
    $.ajax({
        url: url + "index.php/organization/department/datadepartment",
        type: "POST",
        dataType: "json",

        beforeSend: function () {

            Swal.fire({
                title: "Processing",
                html: "Loading data, please wait...",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            if ($.fn.DataTable.isDataTable("#datadepartment_table")) {
                $("#datadepartment_table").DataTable().clear().destroy();
            }

            $("#resultdatadepartment").empty();
        },

        success: function (response) {
            Swal.close();
            const result = Array.isArray(response.responResult) ? response.responResult : [];

            if (response.responCode !== "00") {
                $("#resultdatadepartment").html("");
                Swal.fire({
                    icon: "info",
                    title: "Information",
                    text: response.responDesc || "No user data found."
                });

                return;
            }

            let tableresult = "";

            for (var i in result) {

                const avatar           = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefault    = `${url}assets/media/avatars/blank.png`;
                const avatarpic        = `${url}assets/media/avatars/${result[i].user_id}.jpg`;
                const avatarDefaultpic = `${url}assets/media/avatars/blank.png`;

                getvariabel =   "data-departmentid='"+result[i].department_id+"'";

                let btnaction = "";

                if(result[i].active==="1"){
                    btnaction += "<a class='dropdown-item btn btn-sm text-danger' "+getvariabel+" data-active='0' onclick='activation($(this));'><i class='bi bi-trash3 text-danger me-4'></i>Deactive</a>";
                }else{
                    btnaction += "<a class='dropdown-item btn btn-sm text-success' "+getvariabel+" data-active='1' onclick='activation($(this));'><i class='bi bi-bookmark-check text-success me-4'></i>Reactive</a>";
                }

                tableresult += "<tr>";
                    tableresult += "<td class='text-start ps-4'>"+(parseInt(i) + 1)+"</td>";
                    tableresult += "<td>"+(result[i].department || "-")+"</td>";

                    tableresult += "<td>";
                        tableresult += "<div class='d-flex align-items-center'>";
                            tableresult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                                tableresult += "<div class='symbol-label'>";
                                    tableresult += "<img ";
                                    tableresult += "src='" + avatarpic + "' ";
                                    tableresult += "class='w-100' ";
                                    tableresult += "alt='" + (result[i].pic || "") + "' ";
                                    tableresult += "onerror=\"this.onerror=null;this.src='" + avatarDefaultpic + "';\">";
                                tableresult += "</div>";
                            tableresult += "</div>";
                            tableresult += "<div class='d-flex flex-column'>";
                                tableresult += "<span class='text-gray-800 fw-bold'>";
                                tableresult += (result[i].pic || "-");
                                tableresult += "</span>";
                                tableresult += "<span class='text-muted'>";
                                tableresult += (result[i].emailpic || "-");
                                tableresult += "</span>";
                            tableresult += "</div>";
                        tableresult += "</div>";
                    tableresult += "</td>";

                    tableresult += "<td>"+(result[i].active == 1 ? '<span class=\"badge badge-light-success\">Active</span>' : '<span class=\"badge badge-light-danger\">Deactive</span>')+"</td>";
                    tableresult += "<td>";
                        tableresult += "<div class='d-flex align-items-center'>";
                            tableresult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                                tableresult += "<div class='symbol-label'>";
                                    tableresult += "<img ";
                                    tableresult += "src='" + avatar + "' ";
                                    tableresult += "class='w-100' ";
                                    tableresult += "alt='" + (result[i].dibuatoleh || "") + "' ";
                                    tableresult += "onerror=\"this.onerror=null;this.src='" + avatarDefault + "';\">";
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
                            tableresult += "<button type='button' class='btn btn-light-primary dropdown-toggle btn-sm' data-bs-toggle='dropdown'>Actions</button>";
                            tableresult += "<div class='dropdown-menu'>";
                                tableresult += btnaction;
                            tableresult += "</div>";
                        tableresult += "</div>";
                    tableresult += "</td>";
                    
                tableresult += "</tr>";
            }

            $("#resultdatadepartment").html(tableresult);

            if ($.fn.DataTable.isDataTable("#datadepartment_table")) {
                $("#datadepartment_table").DataTable().destroy();
            }

            const table = $("#datadepartment_table").DataTable({
                responsive: false,
                pageLength: 10,
                autoWidth : false,
                destroy   : true,
                ordering  : false,
                searching : true,
                info      : true,
                language: {
                    emptyTable: "No data available"
                }
            });

            initTableSearch("#datadepartment_table", "#searchtable");

        },

        complete: function () {
            Swal.close();
        },

        error: function () {
            Swal.fire({
                icon: "error",
                title: "Request Failed",
                text: "We were unable to process your request due to a server error. Please try again later. If the problem persists, contact your system administrator.",
                confirmButtonText: "OK"
            });
        }
    });

}

$(document).on("submit", "#formadddepartment", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'index.php/organization/department/adddepartment',
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
            if (response.responCode !== "00") {
                Swal.fire({
                    title            : "<h1 class='font-weight-bold'>For Your Information</h1>",
                    html             : "<b>"+data.responDesc+"</b>",
                    icon             : data.responHead,
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
		},
        complete: function () {
            Swal.close();
            datadepartment();
		},
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Request Failed",
                text: "We were unable to process your request due to a server error. Please try again later. If the problem persists, contact your system administrator.",
                confirmButtonText: "OK"
            });
        }
	});
    return false;
});