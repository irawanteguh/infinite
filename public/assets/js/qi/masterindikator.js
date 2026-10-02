datamasterindikator();

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

                getvariabel =   "data-id='"+result[i].indikator_id+"' "+
                                "data-indikator='"+result[i].indikator+"' "+ 
                                "data-definisi='"+result[i].definisi+"' "+
                                "data-dasar_pemikiran='"+result[i].dasar_pemikiran+"' "+
                                "data-tujuan='"+result[i].tujuan+"' ";


                let btnaction = "";

                btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm "+(result[i].active === '1' ? 'text-danger' : 'text-success')+"' "+getvariabel+" data-active='"+result[i].active+"' onclick='updateActivation($(this));'><i class='bi "+(result[i].active === '1' ? 'bi-x-circle text-danger' : 'bi-check-circle text-success')+" me-4'></i>"+(result[i].active === '1' ? 'Non Active' : 'Active')+"</a>";

                tableResult += "<tr>";

                    tableResult += "<td class='ps-4'>"+(parseInt(i) + 1)+"</td>";
                    tableResult += "<td>"+(result[i].indikator_code || "-")+"</td>";

                    tableResult += "<td>";
                        tableResult += "<div>"+(result[i].indikator || "-")+"</div>";
                        tableResult += "<div class='fst-italic'>"+(result[i].definisi || "-")+"</div>";
                        tableResult += "<div>";
                        tableResult += badgeMutu(result[i].dimensi_mutu_keselamatan, "Keselamatan Pasien", "success");
                        tableResult += badgeMutu(result[i].dimensi_mutu_waktu, "Tepat Waktu", "success");
                        tableResult += badgeMutu(result[i].dimensi_mutu_efektif, "Efektif", "success");
                        tableResult += badgeMutu(result[i].dimensi_mutu_efesien, "Efisien", "success");
                        tableResult += badgeMutu(result[i].dimensi_mutu_pasien, "Berorientasi Pada Pasien", "success");
                        tableResult += badgeMutu(result[i].dimensi_mutu_integrasi, "Integrasi", "success");
                        tableResult += "</div>";
                    tableResult += "</td>";

                    tableResult += "<td>";

                        tableResult += "<div>";
                            tableResult += "<span class='fw-bold text-primary'>Dasar Pemikiran</span><br>";
                            tableResult += (result[i].dasar_pemikiran || "-");
                        tableResult += "</div>";

                        tableResult += "<hr class='my-2'>";

                        tableResult += "<div>";
                            tableResult += "<span class='fw-bold text-success'>Tujuan</span><br>";
                            tableResult += (result[i].tujuan || "-");
                        tableResult += "</div>";

                    tableResult += "</td>";

                    // Numerator Denominator
                    tableResult += "<td>";

                        tableResult += "<div>";
                            tableResult += "<span class='fw-bold text-primary'>Numerator</span><br>";
                            tableResult += (result[i].numerator || "-");
                        tableResult += "</div>";

                        tableResult += "<hr class='my-2'>";

                        tableResult += "<div>";
                            tableResult += "<span class='fw-bold text-success'>Denominator</span><br>";
                            tableResult += (result[i].denumerator || "-");
                        tableResult += "</div>";

                    tableResult += "</td>";

                    tableResult += "<td>";

                        tableResult += "<div>";
                            tableResult += "<span class='fw-bold text-primary'>Inklusi</span><br>";
                            tableResult += (result[i].inklusi || "-");
                        tableResult += "</div>";

                        tableResult += "<hr class='my-2'>";

                        tableResult += "<div>";
                            tableResult += "<span class='fw-bold text-success'>Eksklusi</span><br>";
                            tableResult += (result[i].eksklusi || "-");
                        tableResult += "</div>";

                    tableResult += "</td>";

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

function badgeMutu(status, text, color) {
    if (status !== "Y") return "";
    return "<span class='badge badge-light-" + color + " me-1 mb-1'>" + text + "</span>";
}