dataindikatorunit();

function dataindikatorunit() {
    $.ajax({
        url       : url + "index.php/qi/indikatorunit/dataindikatorunit",
        type      : "POST",
        dataType  : "json",
        beforeSend: function () {
            Swal.fire({
                title            : "Processing",
                html             : "Loading data, please wait...",
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });

            if ($.fn.DataTable.isDataTable("#dataindikatorunit_table")) {
                $("#dataindikatorunit_table").DataTable().clear().destroy();
            }

            $("#resultdataindikatorunit").empty();
        },
        success: function (response) {
            Swal.close();
            const result = Array.isArray(response.responResult) ? response.responResult : [];

            if (response.responCode !== "00") {
                Swal.fire({
                    icon : "info",
                    title: "Information",
                    text : response.responDesc
                });
                return;
            }

            let tableresult = "";
            let btnaction   = "";

            for (var i in result) {
                const avatar        = url+"assets/media/avatars/"+result[i].pic+".jpg";
                const avatarDefault = url+"assets/media/avatars/blank.png";

                const nilai = [
                    result[i].nilai01, result[i].nilai02, result[i].nilai03, result[i].nilai04,
                    result[i].nilai05, result[i].nilai06, result[i].nilai07, result[i].nilai08,
                    result[i].nilai09, result[i].nilai10, result[i].nilai11, result[i].nilai12
                ];

                const nilaiValid = nilai.filter(v => v != null && v !== "" && !isNaN(v)).map(Number);
                const avg = Number((nilaiValid.reduce((a, b) => a + b, 0) / (nilaiValid.length || 1)).toFixed(2));
                const badgeAvg = avg >= Number(result[i].target) ? "badge-light-success" : "badge-light-danger";

                tableresult += "<tr>";
                tableresult += "<td class='ps-4'>" + (parseInt(i) + 1) + "</td>";

                tableresult += "<td>";
                    tableresult += "<div class='fw-bold'>" + result[i].indikator + "</div><div class='text-muted fst-italic'>" + result[i].definisi + "</div>";
                    tableresult += "<div>";
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_keselamatan, "Keselamatan Pasien", "success");
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_waktu, "Tepat Waktu", "success");
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_efektif, "Efektif", "success");
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_efesien, "Efisien", "success");
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_pasien, "Berorientasi Pada Pasien", "success");
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_integrasi, "Integrasi", "success");
                    tableresult += "</div>";
                tableresult += "</td>";
                    
                tableresult += "</td>";
                tableresult += "<td><span class='badge badge-light-info'>"+result[i].jenis+"</span></td>";
                tableresult += "<td><div>"+result[i].tahun+"<span class='badge badge-light-"+result[i].statuscolor+" ms-2'><i class='"+result[i].statusicon+" text-"+result[i].statuscolor+" me-1'></i>"+result[i].status+"</span></div><div class='text-muted fs-8 mt-1'>"+result[i].statusdescription+"</div></td>";
                tableresult += "<td>"+result[i].department+"</td>";
                tableresult += "<td><span class='badge badge-light-info'>"+result[i].target+"%</span></td>";    
                tableresult += "<td><span class='badge "+badgeAvg+"'>"+avg.toFixed(2)+"%</span></td>";

                tableresult += "<td>";
                    tableresult += "<div class='d-flex align-items-center'>";
                        tableresult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                            tableresult += "<div class='symbol-label'>";
                                tableresult += "<img ";
                                tableresult += "src='" + avatar + "' ";
                                tableresult += "class='w-100' ";
                                tableresult += "alt='" + (result[i].picname || "") + "' ";
                                tableresult += "onerror=\"this.onerror=null;this.src='" + avatarDefault + "';\">";
                            tableresult += "</div>";
                        tableresult += "</div>";
                        tableresult += "<div class='d-flex flex-column'>";
                            tableresult += "<span class='text-gray-800 fw-bold'>";
                            tableresult += (result[i].picname || "-");
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
            }

            $("#resultdataindikatorunit").html(tableresult);

            const table = $("#dataindikatorunit_table").DataTable({
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

            initTableSearch('#dataindikatorunit_table', '#searchtable');
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

function badgeDimensiMutu(status, text, color) {
    if (status !== "Y") return "";
    return "<span class='badge badge-light-" + color + " me-1 mb-1'>" + text + "</span>";
}

function dataindikatorunitx() {
    $.ajax({
        url: url + "index.php/qi/indikatorunit/dataindikatorunit",
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

            if ($.fn.DataTable.isDataTable("#dataindikatorunit_table")) {
                $("#dataindikatorunit_table").DataTable().clear().destroy();
            }

            $("#resultdataindikatorunit").empty();

        },

        success: function (response) {

            Swal.close();

            const result = Array.isArray(response.responResult) ? response.responResult : [];

            if (response.responCode !== "00") {
                Swal.fire({
                    icon : "info",
                    title: "Information",
                    text : response.responDesc
                });
                return;
            }

            let totalAvg = 0;
            let totalIndikator = 0;

            result.forEach(item => {

                const nilai = [
                    item.nilai01,item.nilai02,item.nilai03,item.nilai04,
                    item.nilai05,item.nilai06,item.nilai07,item.nilai08,
                    item.nilai09,item.nilai10,item.nilai11,item.nilai12
                ];

                const nilaiValid = nilai
                    .filter(v => v !== null && v !== "" && v !== undefined)
                    .map(Number);

                if (nilaiValid.length > 0) {
                    totalAvg += nilaiValid.reduce((a,b) => a+b,0) / nilaiValid.length;
                    totalIndikator++;
                }
            });

            const overallAvg = totalIndikator ? (totalAvg / totalIndikator) : 0;

            const progressColor = overallAvg >= 80 ? "success" : overallAvg >= 60 ? "warning" : "danger";

            $("#overallpencapaian").html(`
                <span class="fs-7 text-gray-700 fw-bolder pe-4 ps-1">
                    Pencapaian:
                </span>

                <div class="progress w-200px h-25px bg-light-${progressColor}">
                    <div class="progress-bar bg-${progressColor} fw-bold fs-7"
                        role="progressbar"
                        style="width:${overallAvg.toFixed(2)}%"
                        aria-valuenow="${overallAvg.toFixed(2)}"
                        aria-valuemin="0"
                        aria-valuemax="100">
                        ${overallAvg.toFixed(2)}%
                    </div>
                </div>

                <span class="badge badge-light-${progressColor} ms-3">
                    ${totalIndikator} Indikator
                </span>
            `);

            let tableresult = "";

            for (let i = 0; i < result.length; i++) {

                const avatar = `${url}assets/media/avatars/${result[i].pic}.jpg`;
                const avatarDefault = `${url}assets/media/avatars/blank.png`;

                const nilai = [
                    result[i].nilai01, result[i].nilai02, result[i].nilai03, result[i].nilai04,
                    result[i].nilai05, result[i].nilai06, result[i].nilai07, result[i].nilai08,
                    result[i].nilai09, result[i].nilai10, result[i].nilai11, result[i].nilai12
                ];

                const nilaiValid = nilai.filter(v => v !== null && v !== "" && v !== undefined).map(Number);
                const avg        = nilaiValid.length ? (nilaiValid.reduce((a, b) => a + b, 0) / nilaiValid.length) : 0;
                const badgeAvg = avg >= Number(result[i].target) ? "badge-light-success" : "badge-light-danger";

                tableresult += `
                                    <tr class="main-row" data-index="${i}" style="cursor:pointer">
                                        <td class="text-center">${i + 1}</td>
                                        <td>
                                            <div class="fw-bold">${result[i].indikator || "-"}</div>
                                            <div class="text-muted fst-italic">${result[i].definisi || "-"}</div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-info">
                                                ${result[i].jenis}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                ${result[i].tahun}
                                                <span class="badge badge-light-${result[i].statuscolor}">
                                                    <i class="${result[i].statusicon} text-${result[i].statuscolor} me-1"></i>
                                                    ${result[i].status}
                                                </span>
                                            </div>
                                            <div class="text-muted fs-8 mt-1">
                                                ${result[i].statusdescription || ""}
                                            </div>
                                        </td>
                                        <td>${result[i].department}</td>
                                        <td>
                                            <span class="badge badge-light-info">
                                                ${result[i].target}%
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge ${badgeAvg}">
                                                ${avg.toFixed(2)}%
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-circle symbol-35px me-2">
                                                    <img
                                                        src="${avatar}"
                                                        class="w-100"
                                                        onerror="this.src='${avatarDefault}'">
                                                </div>
                                                <div>
                                                    <div class="fw-bold">${result[i].picname}</div>
                                                    <small class="text-muted">${result[i].dibuattgl}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <i class="ki-outline ki-right fs-2 toggle-icon"></i>
                                        </td>
                                    </tr>
                                    `;
            }

            $("#resultdataindikatorunit").html(tableresult);

            if ($.fn.DataTable.isDataTable("#dataindikatorunit_table")) {
                $("#dataindikatorunit_table").DataTable().destroy();
            }

            const table = $("#dataindikatorunit_table").DataTable({
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

            let openedRow = null;

            $("#dataindikatorunit_table tbody").off("click").on("click", "tr.main-row", function () {
                const tr = $(this), row = table.row(tr), data = result[tr.data("index")];

                row.child.isShown()
                    ? (row.child.hide(), tr.removeClass("table-active"), tr.find(".toggle-icon").removeClass("ki-down").addClass("ki-right"), openedRow = null)
                    : (openedRow && openedRow.row !== row && (openedRow.row.child.hide(), openedRow.tr.removeClass("table-active"), openedRow.tr.find(".toggle-icon").removeClass("ki-down").addClass("ki-right")),
                    row.child(createDetail(data)).show(), tr.addClass("table-active"), tr.find(".toggle-icon").removeClass("ki-right").addClass("ki-down"), openedRow = { row, tr });
            });

            initTableSearch("#dataindikatorunit_table", "#searchtable");
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
};