load();

function load(){
    dataindikatorunit();
    datateam();
}

function activation(el) {
    let transaksiid = el.data('transaksiid');
    Swal.fire({
        title: "Deactivate Indicator?",
        html: `
            This indicator will be deactivated and can no longer be used.<br>
            <small class="text-muted">
                This dialog will close automatically in <b>10 seconds</b>.
            </small>
        `,
        icon              : "warning",
        showCancelButton  : true,
        confirmButtonColor: "#d33",
        cancelButtonColor : "#6c757d",
        confirmButtonText : '<i class="bi bi-trash3 text-white"></i> Yes, Deactivate',
        cancelButtonText  : "Cancel",
        reverseButtons    : true,
        timer             : 10000,
        timerProgressBar  : true
    }).then((result) => {

        if (!result.isConfirmed) return;

        $.ajax({
            url     : url + "index.php/qi/indikatorunit/activation",
            type    : "POST",
            dataType: "json",
            data    : {transaksiid: transaksiid},
            success : function (response) {
                Swal.fire({
                    icon             : response.responHead,
                    title            : response.responDesc,
                    timer            : 2000,
                    timerProgressBar : true,
                    showConfirmButton: false
                });

                if (response.responCode === "00") {
                    load();
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

            let tableresult    = "";
            let btnaction      = "";
            let totalAvg       = 0;
            let totalIndikator = 0;

            

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
                
                if(nilaiValid.length > 0){
                    totalAvg += nilaiValid.reduce((a,b) => a+b,0) / nilaiValid.length;
                    totalIndikator++;
                }

                getvariabel =   "data-transaksiid='"+result[i].transaksi_id+"'";

                btnaction += "<a class='dropdown-item btn btn-sm text-danger' "+getvariabel+" data-active='0' onclick='activation($(this));'><i class='bi bi-trash3 text-danger me-4'></i>Deactive</a>";

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

function datateam(){
    $.ajax({
        url     : url + "index.php/qi/indikatorunit/datateam",
        type    : "POST",
        dataType: "json",
        success : function(response){
            let html = "";
            if(response.responCode === "00"){
                $.each(response.responResult, function(i, row){
                    const avatar = url + "assets/media/avatars/" + row.pic + ".jpg";
                    const blank  = url + "assets/media/avatars/blank.png";

                    html += `
                        <div class="symbol symbol-circle symbol-35px" data-bs-toggle="tooltip" title="${row.picname}">
                            <img src="${avatar}" alt="${row.picname}" onerror="this.onerror=null;this.src='${blank}';">
                        </div>
                    `;
                });
            }

            $("#teamindikatorunit").html(html);

            $('[data-bs-toggle="tooltip"]').each(function () {
                new bootstrap.Tooltip(this);
            });

        }
    });

}