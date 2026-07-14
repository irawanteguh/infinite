let table = null;

dataindikator();

function getdata(btn){

    $("#modal_indikator_id").val(btn.data("indikatorid"));
    $("#modal_indikator").val(btn.data("indikator"));
    $("#modal_dasar_pemikiran").val(btn.data("dasar_pemikiran"));
    $("#modal_tujuan").val(btn.data("tujuan"));
    $("#modal_definisi").val(btn.data("definisi"));
    $("#modal_numerator").val(btn.data("numerator"));
    $("#modal_denumerator").val(btn.data("denumerator"));
    $("#modal_formula").val(btn.data("formula"));
    $("#modal_populasi").val(btn.data("populasi"));
    $("#modal_metode").val(btn.data("metode"));
    $("#modal_inklusi").val(btn.data("inklusi"));
    $("#modal_eksklusi").val(btn.data("eksklusi"));
    $("#modal_instrument").val(btn.data("instrument"));

    $("#modal_satuan_id").val(btn.data("satuan_id")).trigger("change");
    $("#modal_frekuensi_id").val(btn.data("frekuensi_id")).trigger("change");
    $("#modal_sumber_id").val(btn.data("sumber_id")).trigger("change");
    $("#modal_donabedian_id").val(btn.data("donabedian_id")).trigger("change");
    $("#modal_target_capaian").val(btn.data("target_capaian")).trigger("change");
    $("#modal_benchmark_id").val(btn.data("benchmark_id")).trigger("change");
    $("#modal_active").val(btn.data("active")).trigger("change");

    $("input[name='dimensi_keselamatan']").prop("checked", btn.data("dimensi_keselamatan") == "Y");
    $("input[name='dimensi_waktu']").prop("checked", btn.data("dimensi_waktu") == "Y");
    $("input[name='dimensi_efektif']").prop("checked", btn.data("dimensi_efektif") == "Y");
    $("input[name='dimensi_efesien']").prop("checked", btn.data("dimensi_efesien") == "Y");
    $("input[name='dimensi_pasien']").prop("checked", btn.data("dimensi_pasien") == "Y");
    $("input[name='dimensi_integrasi']").prop("checked", btn.data("dimensi_integrasi") == "Y");

}

function dataindikator() {
    $.ajax({
        url: url + "index.php/qi/masterindikator/dataindikator",
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

            if ($.fn.DataTable.isDataTable("#dataindikator_table")) {
                $("#dataindikator_table").DataTable().clear().destroy();
            }

            $("#resultdataindikator").empty();

        },

        success: function (response) {

            Swal.close();

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

            for (let i = 0; i < result.length; i++) {

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

            if ($.fn.DataTable.isDataTable("#dataindikator_table")) {
                $("#dataindikator_table").DataTable().destroy();
            }

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
                icon: "error",
                title: "System Error",
                text: "Unable to retrieve indicator data."
            });
        }

    });

    function badgeMutu(status, text, color) {
        if (status !== "Y") return "";
        return "<span class='badge badge-light-" + color + " me-1 mb-1'>" + text + "</span>";
    }

}