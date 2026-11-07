datamasterindikator();

function getdata(btn){

    $("#modal_edit_masterindikator_indikatorid").val(btn.data("indikatorid"));
    $("#modal_edit_masterindikator_indikator").val(btn.data("indikator"));
    $("#modal_edit_masterindikator_dasarpemikiran").val(btn.data("dasar_pemikiran"));
    $("#modal_edit_masterindikator_tujuan").val(btn.data("tujuan"));
    $("#modal_edit_masterindikator_definisi").val(btn.data("definisi"));
    $("#modal_edit_masterindikator_numerator").val(btn.data("numerator"));
    $("#modal_edit_masterindikator_denominator").val(btn.data("denominator"));
    $("#modal_edit_masterindikator_formula").val(btn.data("formula"));
    $("#modal_edit_masterindikator_populasi").val(btn.data("populasi"));
    $("#modal_edit_masterindikator_kriteriainklusi").val(btn.data("inklusi"));
    $("#modal_edit_masterindikator_kriteriaeksklusi").val(btn.data("eksklusi"));
    $("#modal_edit_masterindikator_instrumen").val(btn.data("instrument"));

    $("#modal_edit_masterindikator_satuanid").val(btn.data("satuan_id")).trigger("change");
    $("#modal_edit_masterindikator_frekuensiid").val(btn.data("frekuensi_id")).trigger("change");
    $("#modal_edit_masterindikator_sumberid").val(btn.data("sumber_id")).trigger("change");
    $("#modal_edit_masterindikator_donabedianid").val(btn.data("donabedian_id")).trigger("change");
    $("#modal_edit_masterindikator_targetcapaian").val(btn.data("target_capaian")).trigger("change");
    $("#modal_edit_masterindikator_benchmarkid").val(btn.data("benchmark_id")).trigger("change");
    $("#modal_edit_masterindikator_jenis").val(btn.data("jenis_indikatorid")).trigger("change");

    $("#modal_edit_masterindikator_dimensikeselamatan").prop("checked", btn.data("dimensi_keselamatan") === "Y");
    $("#modal_edit_masterindikator_dimensiwaktu").prop("checked", btn.data("dimensi_waktu") === "Y");
    $("#modal_edit_masterindikator_dimensiefektif").prop("checked", btn.data("dimensi_efektif") === "Y");
    $("#modal_edit_masterindikator_dimensiefisien").prop("checked", btn.data("dimensi_efisien") === "Y");
    $("#modal_edit_masterindikator_dimensipasien").prop("checked", btn.data("dimensi_pasien") === "Y");
    $("#modal_edit_masterindikator_dimensiintegrasi").prop("checked", btn.data("dimensi_integrasi") === "Y");

}

function updateActivation (el) {
    const id        = el.data("indikatorid");
    const active    = el.data("active");
    const newActive = active == 1 ? 0 : 1;
    const isActive  = active == 1;

    Swal.fire({
        ttitle             : isActive ? "Deactivate Indicator?" : "Activate Indicator?",
        html              : isActive ? `Indicator ini akan dinonaktifkan dan tidak dapat digunakan.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>` : `Indicator ini akan diaktifkan kembali dan dapat digunakan.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`,
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
                url      : url + "qi/masterindikator/updateindikator",
                type     : "POST",
                data     : {indikatorid : id,active: newActive},
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
                                datamasterindikator();
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

function datamasterindikator() {
    $.ajax({
        url       : url+"qi/masterindikator/datamasterindikator",
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

            $("#resultdatamasterindikator").empty();
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
                const avatar        = `${url}assets/media/avatars/${result[i].last_update_by}.jpg`;
                const avatarDefault = `${url}assets/media/avatars/blank.png`;

                var getvariabel = "";
                    getvariabel += " data-indikatorid='" + result[i].indikator_id + "'";
                    getvariabel += " data-indikator=\"" + result[i].indikator + "\"";
                    getvariabel += " data-dasar_pemikiran=\"" + result[i].dasar_pemikiran + "\"";
                    getvariabel += " data-jenis_indikatorid=\"" + result[i].jenis_indikator_id + "\"";
                    getvariabel += " data-tujuan=\"" + result[i].tujuan + "\"";
                    getvariabel += " data-definisi=\"" + result[i].definisi + "\"";
                    getvariabel += " data-numerator=\"" + result[i].numerator + "\"";
                    getvariabel += " data-denumerator=\"" + result[i].denumerator + "\"";
                    getvariabel += " data-formula=\"" + result[i].formula + "\"";
                    getvariabel += " data-populasi=\"" + result[i].populasi + "\"";
                    getvariabel += " data-inklusi=\"" + result[i].inklusi + "\"";
                    getvariabel += " data-eksklusi=\"" + result[i].eksklusi + "\"";
                    getvariabel += " data-instrument=\"" + result[i].instrument + "\"";
                    getvariabel += " data-satuan_id=\"" + result[i].satuan_id + "\"";
                    getvariabel += " data-frekuensi_id=\"" + result[i].frekuensi_id + "\"";
                    getvariabel += " data-sumber_id=\"" + result[i].sumber_id + "\"";
                    getvariabel += " data-donabedian_id=\"" + result[i].donabedian_id + "\"";
                    getvariabel += " data-target_capaian=\"" + result[i].target_capaian + "\"";
                    getvariabel += " data-benchmark_id=\"" + result[i].benchmark_id + "\"";
                    getvariabel += " data-active=\"" + result[i].active + "\"";
                    getvariabel += " data-dimensi_keselamatan=\"" + result[i].dimensi_mutu_keselamatan + "\"";
                    getvariabel += " data-dimensi_waktu=\"" + result[i].dimensi_mutu_waktu + "\"";
                    getvariabel += " data-dimensi_efektif=\"" + result[i].dimensi_mutu_efektif + "\"";
                    getvariabel += " data-dimensi_efesien=\"" + result[i].dimensi_mutu_efesien + "\"";
                    getvariabel += " data-dimensi_pasien=\"" + result[i].dimensi_mutu_pasien + "\"";
                    getvariabel += " data-dimensi_integrasi=\"" + result[i].dimensi_mutu_integrasi + "\"";
                    


                let btnaction = "";

                btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm "+(result[i].active === '1' ? 'text-danger' : 'text-success')+"' "+getvariabel+" data-active='"+result[i].active+"' onclick='updateActivation($(this));'><i class='bi "+(result[i].active === '1' ? 'bi-x-circle text-danger' : 'bi-check-circle text-success')+" me-4'></i>"+(result[i].active === '1' ? 'Deactivate' : 'Activate')+"</a>";
                btnaction += "<a class='dropdown-item btn btn-sm text-primary' data-bs-toggle='modal' data-bs-target='#modal_edit_masterindikator' "+getvariabel+" onclick='getdata($(this));'><i class='bi bi-pencil text-primary me-4'></i>Edit</a>";

                tableResult += "<tr>";

                    tableResult += "<td class='ps-4'>"+(parseInt(i) + 1)+"</td>";
                    tableResult += "<td>"+(result[i].indikator_code || "-")+"</td>";

                    tableResult += "<td>";
                        tableResult += "<div>"+(result[i].indikator || "-")+"</div>";
                        // tableResult += "<div class='fst-italic'>"+(result[i].definisi || "-")+"</div>";
                        tableResult += "<div>";
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_keselamatan, "Keselamatan Pasien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_waktu, "Tepat Waktu", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_efektif, "Efektif", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_efesien, "Efisien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_pasien, "Berorientasi Pada Pasien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_integrasi, "Integrasi", "info");
                        tableResult += "</div>";
                        tableResult += result[i].jenis ? "<div><span class='badge badge-light-primary mt-4'>" + result[i].jenis + "</span></div>" : "";
                    tableResult += "</td>";

                    // tableResult += "<td>";
                    // tableResult += "<div><span class='fw-bold text-primary'>Dasar Pemikiran</span><br>" + (result[i].dasar_pemikiran || "-") + "</div>";
                    // tableResult += "<hr class='my-2'>";
                    // tableResult += "<div><span class='fw-bold text-success'>Tujuan</span><br>" + (result[i].tujuan || "-") + "</div>";
                    // tableResult += "</td>";

                    tableResult += "<td>";
                    tableResult += "<div><span class='fw-bold text-primary'>Numerator</span><br>" + (result[i].numerator || "-") + "</div>";
                    tableResult += "<hr class='my-2'>";
                    tableResult += "<div><span class='fw-bold text-success'>Denominator</span><br>" + (result[i].denumerator || "-") + "</div>";
                    tableResult += "</td>";

                    // tableResult += "<td>";
                    // tableResult += "<div><span class='fw-bold text-primary'>Inklusi</span><br>" + (result[i].inklusi || "-") + "</div>";
                    // tableResult += "<hr class='my-2'>";
                    // tableResult += "<div><span class='fw-bold text-success'>Eksklusi</span><br>" + (result[i].eksklusi || "-") + "</div>";
                    // tableResult += "</td>";

                    tableResult += "<td>"+(result[i].satuan || "")+"</td>";

                    // Status
                    tableResult += "<td>" + (result[i].active == "1" ? "<span class='badge badge-light-success'>Active</span>" : "<span class='badge badge-light-danger'>Inactive</span>") + "</td>";

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

            $("#resultdatamasterindikator").html(tableResult);

            initDataTable("#datamasterindikator_table","#searchtable",10);
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

$(document).on("submit", "#formeditmasterindikator", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'qi/masterindikator/editmasterindikator',
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

            $('#modal_edit_masterindikator').modal('hide');
            Swal.close();
			datamasterindikator();
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

$(document).on("submit", "#formaddmasterindikator", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'qi/masterindikator/addmasterindikator',
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

            $('#modal_add_masterindikator').modal('hide');
            Swal.close();
			datamasterindikator();
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
