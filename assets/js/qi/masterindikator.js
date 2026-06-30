let table = null;

dataindikator();

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

                const avatar = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefault = `${url}assets/media/avatars/blank.png`;

                let btnaction = "";

                btnaction += "<a href='#' class='dropdown-item'><i class='ki-outline ki-eye fs-6 me-2'></i>Detail</a>";
                btnaction += "<a href='#' class='dropdown-item'><i class='ki-outline ki-pencil fs-6 me-2'></i>Edit</a>";
                btnaction += "<a href='#' class='dropdown-item text-danger'><i class='ki-outline ki-trash fs-6 me-2'></i>Delete</a>";

                tableresult += "<tr>";

                // No
                tableresult += "<td class='text-start ps-4'>";
                tableresult += (i + 1);
                tableresult += "</td>";

                // Nama Indikator
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

                tableresult += "<br><div'><span class='badge badge-light-info'>"+(result[i].jenisindikator || "-")+"</span></div>";

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
                        tableresult += "<div class='symbol symbol-circle symbol-50px overflow-hidden me-3'>";
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
                tableresult += "<td class='text-end pe-4'>";
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

            table = $("#dataindikator_table").DataTable({
                responsive: true,
                pageLength: 10,
                destroy: true,
                autoWidth: false,
                order: [],
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