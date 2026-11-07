datamasterperiodepelaporan();

function getdata(btn){
    $("#modal_edit_periodepelaporan_id").val(btn.data("periodeid"));
    $("#modal_edit_periodepelaporan_periode").val(btn.data("periode"));
    $("#modal_edit_periodepelaporan_user").val(btn.data("userid")).trigger("change");
    $("#modal_edit_periodepelaporan_status").val(btn.data("status")).trigger("change");
}

function datamasterperiodepelaporan(onComplete = null) {
    let refreshSucceeded = false;
    $.ajax({
        url       : url+"qi/periodepelaporan/datamasterperiodepelaporan",
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

            $("#resultdatamasterperiodepelaporan").empty();
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
                $("#resultdatamasterperiodepelaporan").empty();
                initDataTable("#datamasterperiodepelaporan_table", "#searchtable", 10);
                return;
            }

            refreshSucceeded = true;

            const result = Array.isArray(response.responseResult) ? response.responseResult : [];

            let tableResult = "";

            for (var i in result) {
                const avatar           = `${url}assets/media/avatars/${result[i].last_update_by}.jpg`;
                const avatarDefault    = `${url}assets/media/avatars/blank.png`;
                const avatarpic        = `${url}assets/media/avatars/${result[i].user_id}.jpg`;
                const avatarDefaultpic = `${url}assets/media/avatars/blank.png`;

                var getvariabel = "";
                    getvariabel += " data-periodeid='" + result[i].periode_id + "'";
                    getvariabel += " data-periode='" + result[i].tahun + "'";
                    getvariabel += " data-userid='" + result[i].user_id + "'";
                    getvariabel += " data-status='" + result[i].status_id + "'";

                let btnaction = "";

                btnaction += "<a class='dropdown-item btn btn-sm text-primary' data-bs-toggle='modal' data-bs-target='#modal_edit_periodepelaporan' "+getvariabel+" onclick='getdata($(this));'><i class='bi bi-pencil text-primary me-4'></i>Edit</a>";
                btnaction += "<a class='dropdown-item btn btn-sm text-primary' href='#' onclick=\"manageTeam('"+result[i].periode_id+"','"+result[i].tahun+"'); return false;\"><i class='bi bi-people text-primary me-4'></i>Manage Quality Team</a>";

                tableResult += "<tr>";
                tableResult += "<td class='text-start ps-4'>"+(parseInt(i) + 1)+"</td>";
                tableResult += "<td>"+(result[i].tahun || "-")+"</td>";

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

                tableResult += "<td>" + renderteam(result[i].komite, 3) + "</td>";
                tableResult += "<td>" + renderteam(result[i].picindikator, 3) + "</td>";

                tableResult += "<td>";
                tableResult += `
                    <span class="badge badge-light-${result[i].statuscolor}">
                        <i class="${result[i].statusicon} text-${result[i].statuscolor} me-1"></i>
                        ${result[i].status}
                    </span>
                    <div class="text-muted fs-8 mt-1">
                        ${result[i].statusdescription || ""}
                    </div>
                `;
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

            $("#resultdatamasterperiodepelaporan").html(tableResult);
            initDataTable("#datamasterperiodepelaporan_table","#searchtable",10);
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
            if (typeof onComplete === "function") {
                onComplete(refreshSucceeded);
            }
        }
    });
}

function manageTeam(periodeId, periode) {
    $("#quality_team_periode_id").val(periodeId);
    $("#quality_team_period").text("— " + periode);
    $("#quality_team_members").val(null).trigger("change");

    $.ajax({
        url: url + "qi/periodepelaporan/datateam",
        type: "POST",
        dataType: "JSON",
        data: { periode_id: periodeId },
        success: function (response) {
            if (response.responseCode !== "00") {
                Swal.fire({ icon: "error", title: "Gagal memuat anggota", text: response.responseDesc || "Silakan coba kembali." });
                return;
            }

            const selected = (response.responseResult || []).map(function (member) { return String(member.user_id); });
            $("#quality_team_members").val(selected).trigger("change");
            $("#modal_quality_team").modal("show");
        },
        error: function () {
            Swal.fire({ icon: "error", title: "Request Failed", text: "Anggota tim tidak dapat dimuat." });
        }
    });
}

$(document).on("submit", "#form_quality_team", function (e) {
    e.preventDefault();
    $.ajax({
        url: url + "qi/periodepelaporan/updateteam",
        type: "POST",
        data: $(this).serialize(),
        dataType: "JSON",
        success: function (response) {
            if (response.responseCode !== "00") {
                Swal.fire({ icon: "error", title: "Gagal menyimpan", text: response.responseDesc || "Silakan coba kembali." });
                return;
            }
            $("#modal_quality_team").one("hidden.bs.modal", function () {
                datamasterperiodepelaporan(function (refreshSucceeded) {
                    if (refreshSucceeded) {
                        Swal.fire({ icon: "success", title: "Berhasil", text: response.responseDesc, timer: 1600, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: "warning", title: "Tersimpan", text: "Data tersimpan, tetapi tabel gagal dimuat ulang." });
                    }
                });
            }).modal("hide");
        },
        error: function () {
            Swal.fire({ icon: "error", title: "Request Failed", text: "Anggota tim tidak dapat disimpan." });
        }
    });
    return false;
});

$(document).on("submit", "#formaddperiode", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'qi/periodepelaporan/addperiode',
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

            $('#modal_add_periodepelaporan').modal('hide');
            Swal.close();
			datamasterperiodepelaporan();
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

$(document).on("submit", "#formeditperiode", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'qi/periodepelaporan/editperiode',
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

            $('#modal_edit_periodepelaporan').modal('hide');
            Swal.close();
			datamasterperiodepelaporan();
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
