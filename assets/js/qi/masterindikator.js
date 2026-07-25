let table = null;

dataindikator();

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
    $("#modal_edit_masterindikator_metodepengumpulan").val(btn.data("metode"));
    $("#modal_edit_masterindikator_kriteriainklusi").val(btn.data("inklusi"));
    $("#modal_edit_masterindikator_kriteriaeksklusi").val(btn.data("eksklusi"));
    $("#modal_edit_masterindikator_instrumen").val(btn.data("instrument"));

    $("#modal_edit_masterindikator_satuanid").val(btn.data("satuan_id")).trigger("change");
    $("#modal_edit_masterindikator_frekuensiid").val(btn.data("frekuensi_id")).trigger("change");
    $("#modal_edit_masterindikator_sumberid").val(btn.data("sumber_id")).trigger("change");
    $("#modal_edit_masterindikator_donabedianid").val(btn.data("donabedian_id")).trigger("change");
    $("#modal_edit_masterindikator_targetcapaian").val(btn.data("target_capaian")).trigger("change");
    $("#modal_edit_masterindikator_benchmarkid").val(btn.data("benchmark_id")).trigger("change");
    $("#modal_edit_masterindikator_active").val(btn.data("active")).trigger("change");

    $("#modal_edit_masterindikator_dimensikeselamatan").prop("checked", btn.data("dimensi_keselamatan") === "Y");
    $("#modal_edit_masterindikator_dimensiwaktu").prop("checked", btn.data("dimensi_waktu") === "Y");
    $("#modal_edit_masterindikator_dimensiefektif").prop("checked", btn.data("dimensi_efektif") === "Y");
    $("#modal_edit_masterindikator_dimensiefisien").prop("checked", btn.data("dimensi_efisien") === "Y");
    $("#modal_edit_masterindikator_dimensipasien").prop("checked", btn.data("dimensi_pasien") === "Y");
    $("#modal_edit_masterindikator_dimensiintegrasi").prop("checked", btn.data("dimensi_integrasi") === "Y");

}

function dataindikator() {
    $.ajax({
        url       : url + "index.php/qi/masterindikator/dataindikator",
        type      : "POST",
        dataType  : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : "Processing",
                html             : "Loading data, please wait...",
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });

            if ($.fn.DataTable.isDataTable("#dataindikator_table")) {
                $("#dataindikator_table").DataTable().clear().destroy();
            }

            $("#resultdataindikator").empty();

        },

        success: function (response) {
            const result = Array.isArray(response.responResult) ? response.responResult : [];

            if (response.responCode !== "00") {
                Swal.fire({
                    icon: "info",
                    title: "Information",
                    text: response.responDesc || "No indicator data found."
                });

                return;
            }

            let tableresult = "";

            for (var i in result) {

                const avatar        = `${url}assets/media/avatars/${result[i].last_update_by}.jpg`;
                const avatarDefault = `${url}assets/media/avatars/blank.png`;

                var getvariabel = "";
                    getvariabel += " data-indikatorid='" + result[i].indikator_id + "'";
                    getvariabel += " data-indikator=\"" + result[i].indikator + "\"";
                    getvariabel += " data-dasar_pemikiran=\"" + result[i].dasar_pemikiran + "\"";
                    getvariabel += " data-tujuan=\"" + result[i].tujuan + "\"";
                    getvariabel += " data-definisi=\"" + result[i].definisi + "\"";
                    getvariabel += " data-numerator=\"" + result[i].numerator + "\"";
                    getvariabel += " data-denumerator=\"" + result[i].denumerator + "\"";
                    getvariabel += " data-formula=\"" + result[i].formula + "\"";
                    getvariabel += " data-populasi=\"" + result[i].populasi + "\"";
                    getvariabel += " data-metode=\"" + result[i].metode + "\"";
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

                btnaction += "<a class='dropdown-item btn btn-sm text-primary' data-bs-toggle='modal' data-bs-target='#modal_edit_masterindikator' "+getvariabel+" onclick='getdata($(this));'><i class='bi bi-pencil text-primary me-4'></i>Edit</a>";

                tableresult += "<tr>";

                tableresult += "<td class='ps-4'>"+(parseInt(i) + 1)+"</td>";

                
                tableresult += "<td>";
                    tableresult += "<div>"+(result[i].indikator || "-")+"</div>";
                    tableresult += "<div class='fst-italic'>"+(result[i].definisi || "-")+"</div>";
                    tableresult += "<div>";
                    tableresult += badgeMutu(result[i].dimensi_mutu_keselamatan, "Keselamatan Pasien", "success");
                    tableresult += badgeMutu(result[i].dimensi_mutu_waktu, "Tepat Waktu", "success");
                    tableresult += badgeMutu(result[i].dimensi_mutu_efektif, "Efektif", "success");
                    tableresult += badgeMutu(result[i].dimensi_mutu_efesien, "Efisien", "success");
                    tableresult += badgeMutu(result[i].dimensi_mutu_pasien, "Berorientasi Pada Pasien", "success");
                    tableresult += badgeMutu(result[i].dimensi_mutu_integrasi, "Integrasi", "success");
                    tableresult += "</div>";
                tableresult += "</td>";

                tableresult += "<td>";

                    tableresult += "<div>";
                        tableresult += "<span class='fw-bold text-primary'>Dasar Pemikiran</span><br>";
                        tableresult += (result[i].dasar_pemikiran || "-");
                    tableresult += "</div>";

                    tableresult += "<hr class='my-2'>";

                    tableresult += "<div>";
                        tableresult += "<span class='fw-bold text-success'>Tujuan</span><br>";
                        tableresult += (result[i].tujuan || "-");
                    tableresult += "</div>";

                tableresult += "</td>";

                // Numerator Denominator
                tableresult += "<td>";

                    tableresult += "<div>";
                        tableresult += "<span class='fw-bold text-primary'>Numerator</span><br>";
                        tableresult += (result[i].numerator || "-");
                    tableresult += "</div>";

                    tableresult += "<hr class='my-2'>";

                    tableresult += "<div>";
                        tableresult += "<span class='fw-bold text-success'>Denominator</span><br>";
                        tableresult += (result[i].denumerator || "-");
                    tableresult += "</div>";

                tableresult += "</td>";

                tableresult += "<td>";

                    tableresult += "<div>";
                        tableresult += "<span class='fw-bold text-primary'>Inklusi</span><br>";
                        tableresult += (result[i].inklusi || "-");
                    tableresult += "</div>";

                    tableresult += "<hr class='my-2'>";

                    tableresult += "<div>";
                        tableresult += "<span class='fw-bold text-success'>Eksklusi</span><br>";
                        tableresult += (result[i].eksklusi || "-");
                    tableresult += "</div>";

                tableresult += "</td>";

                // Status
                tableresult += "<td>";
                if (result[i].active == "1") {
                    tableresult += "<span class='badge badge-light-success'>";
                    tableresult += "Active";
                    tableresult += "</span>";
                } else {
                    tableresult += "<span class='badge badge-light-danger'>";
                    tableresult += "Inactive";
                    tableresult += "</span>";
                }
                tableresult += "</td>";

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

                // Action
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

            $("#resultdataindikator").html(tableresult);

            const table = $("#dataindikator_table").DataTable({
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

            // Search
            initTableSearch("#dataindikator_table", "#searchtable");
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

    function badgeMutu(status, text, color) {
        if (status !== "Y") return "";
        return "<span class='badge badge-light-" + color + " me-1 mb-1'>" + text + "</span>";
    }

}

$(document).on("submit", "#formeditmasterindikator", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'index.php/qi/masterindikator/editmasterindikator',
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

            $('#modal_edit_masterindikator').modal('hide');
            Swal.close();
		},
        complete: function () {
            Swal.close();
            dataindikator();
		},
        error: function(xhr, status, error) {
            Swal.fire({
                icon : 'error',
                title: 'System Error',
                text : 'Failed to retrieve emergency visit data.'
            });
		}
	});
    return false;
});

$(document).on("submit", "#formaddmasterindikator", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'index.php/qi/masterindikator/addmasterindikator',
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

            $('#modal_add_masterindikator').modal('hide');
            Swal.close();
		},
        complete: function () {
            Swal.close();
            dataindikator();
		},
        error: function(xhr, status, error) {
            Swal.fire({
                icon : 'error',
                title: 'System Error',
                text : 'Failed to retrieve emergency visit data.'
            });
		}
	});
    return false;
});