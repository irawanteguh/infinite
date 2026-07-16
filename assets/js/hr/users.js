let table = null;

datausers();

$('#modal_add_user').on('hidden.bs.modal', function () {
    datausers();
});

$('#modal_edit_user').on('hidden.bs.modal', function () {
    datausers();
});

function activation(el) {

    let userid = el.data('userid');
    let active = String(el.data('active'));

    let isDeactive = active === "0";

    Swal.fire({
        title: isDeactive ? "Deactivate Account?" : "Reactivate Account?",
        html: isDeactive
            ? `
                User ini akan dinonaktifkan dan tidak dapat login ke sistem.<br>
                <small class="text-muted">
                    Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.
                </small>
              `
            : `
                User ini akan diaktifkan kembali dan dapat login ke sistem.<br>
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
            url: url + "index.php/hr/users/activation",
            type: "POST",
            dataType: "json",
            data: {
                userid: userid,
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
                    datausers();
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

function getdata(btn){
    var userid        = btn.attr("data-userid");
    var nikrs         = btn.attr("data-nikrs");
    var username      = btn.attr("data-username");
    var name          = btn.attr("data-name");
    var email         = btn.attr("data-email");
    var avatar        = url+"assets/media/avatars/"+userid+".jpg";
    var avatarDefault = url+"assets/media/avatars/blank.png";

	$(":hidden[name='modal_edit_user_userid']").val(userid);
    $("#modal_edit_user_username").val(username);
    $("#modal_edit_user_nikrs").val(nikrs === "null" || nikrs === null ? "" : nikrs);
    $("#modal_edit_user_name").val(name);
    $("#modal_edit_user_email").val(email);
    $("<img>").attr("src",avatar).on("load",function(){$("#avatar-preview-edit").css("background-image","url('"+avatar+"')");}).on("error",function(){$("#avatar-preview-edit").css("background-image","url('"+avatarDefault+"')");});
};

function datausers() {
    $.ajax({
        url     : url + "index.php/hr/users/datausers",
        type    : "POST",
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

            if ($.fn.DataTable.isDataTable('#datausers_table')) {
                $('#datausers_table').DataTable().destroy();
            }

            $("#resultdatausers").empty();
        },
        success: function (response) {

            Swal.close();

            const result = Array.isArray(response.responResult) ? response.responResult : [];

            if (response.responCode !== "00") {
                $("#resultdatausers").html("");
                Swal.fire({
                    icon: "info",
                    title: "Information",
                    text: response.responDesc || "No user data found."
                });
                return;
            }

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

                btnaction += "<a class='dropdown-item btn btn-sm text-primary' data-bs-toggle='modal' data-bs-target='#modal_edit_user' "+getvariabel+" onclick='getdata($(this));'><i class='bi bi-pencil text-primary me-4'></i>Edit</a>";
                
                if(result[i].active==="1"){
                    btnaction += "<a class='dropdown-item btn btn-sm text-danger' "+getvariabel+" data-active='0' onclick='activation($(this));'><i class='bi bi-trash3 text-danger me-4'></i>Deactive</a>";
                }else{
                    btnaction += "<a class='dropdown-item btn btn-sm text-success' "+getvariabel+" data-active='1' onclick='activation($(this));'><i class='bi bi-bookmark-check text-success me-4'></i>Reactive</a>";
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

                tableresult += "<td>";
                if (result[i].active == "1") {
                    tableresult += "<span class='badge badge-light-success'>";
                    tableresult += "Active";
                    tableresult += "</span>";
                } else {
                    tableresult += "<span class='badge badge-light-danger'>";
                    tableresult += "Deactive";
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

                tableresult += "</tr>";
            }

            $("#resultdatausers").html(tableresult);

            const table = $("#datausers_table").DataTable({
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

            initTableSearch('#datausers_table', '#searchtable');
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