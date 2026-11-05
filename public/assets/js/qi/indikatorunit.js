dataindikatorunit();

function dataindikatorunit() {
    $.ajax({
        url       : url+"qi/indikatorunit/dataindikatorunit",
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

            $("#resultdataindikatorunit").empty();
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
                const avatar        = `${url}assets/media/avatars/${result[i].pic}.jpg`;
                const avatarDefault = `${url}assets/media/avatars/blank.png`;

                const nilai = [
                    result[i].nilai01, result[i].nilai02, result[i].nilai03, result[i].nilai04,
                    result[i].nilai05, result[i].nilai06, result[i].nilai07, result[i].nilai08,
                    result[i].nilai09, result[i].nilai10, result[i].nilai11, result[i].nilai12
                ];

                const nilaiValid = nilai.filter(v => v != null && v !== "" && !isNaN(v)).map(Number);
                const avg = Number((nilaiValid.reduce((a, b) => a + b, 0) / (nilaiValid.length || 1)).toFixed(2));
                const badgeAvg = avg >= Number(result[i].target) ? "badge-light-success" : "badge-light-danger";

                let btnaction      = "";

                tableResult += "<tr>";
                tableResult += "<td class='ps-4'>"+(parseInt(i) + 1)+"</td>";

                tableResult += "<td>";
                    tableResult += "<div class='fw-bold'>" + result[i].indikator + "</div><div class='text-muted fst-italic'>" + result[i].definisi + "</div>";
                    tableResult += "<div>";
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_keselamatan, "Keselamatan Pasien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_waktu, "Tepat Waktu", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_efektif, "Efektif", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_efesien, "Efisien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_pasien, "Berorientasi Pada Pasien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_integrasi, "Integrasi", "info");
                    tableResult += "</div>";
                    tableResult += "<div><span class='badge badge-light-info mt-4'>"+result[i].jenis+"</span></div>";
                tableResult += "</td>";

                tableResult += "</td>";
                tableResult += "<td>"+(result[i].tahun || "")+"</td>";
                tableResult += "<td>"+result[i].department+"</td>";
                tableResult += "<td><span class='badge badge-light-info'>"+result[i].target+(result[i].kode ||"")+"</span></td>";     
                tableResult += "<td><span class='badge "+badgeAvg+"'>"+avg.toFixed(2)+"%</span></td>";

                tableResult += "<td>" + (result[i].statusidindikatorunit == "0" ? "<span class='badge badge-light-info'>Submitted</span>" : result[i].statusidindikatorunit == "1" ? "<span class='badge badge-light-success'>Approved</span>" : result[i].statusidindikatorunit == "9" ? "<span class='badge badge-light-danger'>Declined</span>" : "<span class='badge badge-light-secondary'>Unknown</span>") + "</td>";

                tableResult += "<td>";
                    tableResult += "<div class='d-flex align-items-center'>";
                        tableResult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                            tableResult += "<div class='symbol-label'>";
                                tableResult += "<img ";
                                tableResult += "src='" + avatar + "' ";
                                tableResult += "class='w-100' ";
                                tableResult += "alt='" + (result[i].picname || "") + "' ";
                                tableResult += "onerror=\"this.onerror=null;this.src='" + avatarDefault + "';\">";
                            tableResult += "</div>";
                        tableResult += "</div>";
                        tableResult += "<div class='d-flex flex-column'>";
                            tableResult += "<span class='text-gray-800 fw-bold'>";
                            tableResult += (result[i].picname || "-");
                            tableResult += "</span>";
                            tableResult += "<span class='text-muted'>";
                            tableResult += (result[i].dibuattgl || "-");
                            tableResult += "</span>";
                        tableResult += "</div>";
                    tableResult += "</div>";
                tableResult += "</td>";

                tableResult += "<td class='text-end'>";
                    tableResult += "<div class='btn-group'>";
                        tableResult += "<button ";
                        tableResult += "type='button' ";
                        tableResult += "class='btn btn-light-primary btn-sm dropdown-toggle' ";
                        tableResult += "data-bs-toggle='dropdown'>";
                        tableResult += "Actions";
                        tableResult += "</button>";
                        tableResult += "<div class='dropdown-menu dropdown-menu-end'>";
                        tableResult += btnaction;
                        tableResult += "</div>";
                    tableResult += "</div>";
                tableResult += "</td>";

                tableResult += "</tr>";
            }

            $("#resultdataindikatorunit").html(tableResult);
            // initDataTable("#dataindikatorunit_table","#searchtable",10);
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