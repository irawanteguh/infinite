let table = null;
let opened = null;

dataindikatorunit();
datateam();

function dataindikatorunit() {
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
                    icon: "info",
                    title: "Information",
                    text: response.responDesc || "No indicator data found."
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

    function badgeMutu(status, text, color) {
        if (status !== "Y") return "";
        return "<span class='badge badge-light-" + color + " me-1 mb-1'>" + text + "</span>";
    }

    function renderNilaiTarget(nilai, target, status) {

        // Status selain 2 -> tidak boleh muncul tombol
        if (status != '2') {
            if (nilai === null || nilai === "" || nilai === undefined) {
                return "-";
            }

            const value = parseFloat(nilai);
            const targetValue = parseFloat(target);

            const badgeClass = value >= targetValue
                ? "badge-light-success"
                : "badge-light-danger";

            return `
                <span class="badge ${badgeClass}">
                    ${value.toFixed(2)}%
                </span>
            `;
        }

        // Status = 2
        if (nilai === null || nilai === "" || nilai === undefined) {
            return `
                <a href="#" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                    <i class="bi bi-plus-circle-dotted fs-4"></i>
                </a>
            `;
        }

        const value = parseFloat(nilai);
        const targetValue = parseFloat(target);

        const badgeClass = value >= targetValue
            ? "badge-light-success"
            : "badge-light-danger";

        return `
            <span class="badge ${badgeClass}">
                ${value.toFixed(2)}%
            </span>
        `;
    }

    function createDetail(d){

        // Status Draft / Belum Aktif
        if (d.statuscode == '1') {
            return `
                <div class="pt-4 pb-4">
                    <table class="table table-bordered table-sm mb-0">
                        <tbody>
                            <tr>
                                <td colspan="12" class="text-center">
                                    <div class="fw-bold"><span class='badge badge-light-info'><i class="${d.statusicon} text-${d.statuscolor} me-2"></i> ${d.status}</span></div>
                                    <div class="text-muted">
                                        ${d.statusdescription || "Belum dapat mengisi data indikator."}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
        }

        return `
            <div class="pt-4 pb-4">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr class="fw-bolder bg-primary">
                            <th class="text-center rounded-start">Jan</th>
                            <th class="text-center">Feb</th>
                            <th class="text-center">Mar</th>
                            <th class="text-center">Apr</th>
                            <th class="text-center">Mei</th>
                            <th class="text-center">Jun</th>
                            <th class="text-center">Jul</th>
                            <th class="text-center">Agu</th>
                            <th class="text-center">Sep</th>
                            <th class="text-center">Okt</th>
                            <th class="text-center">Nov</th>
                            <th class="text-center rounded-end">Des</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">${renderNilaiTarget(d.nilai01,d.target,d.statuscode)}</td>
                            <td class="text-center">${renderNilaiTarget(d.nilai02,d.target,d.statuscode)}</td>
                            <td class="text-center">${renderNilaiTarget(d.nilai03,d.target,d.statuscode)}</td>
                            <td class="text-center">${renderNilaiTarget(d.nilai04,d.target,d.statuscode)}</td>
                            <td class="text-center">${renderNilaiTarget(d.nilai05,d.target,d.statuscode)}</td>
                            <td class="text-center">${renderNilaiTarget(d.nilai06,d.target,d.statuscode)}</td>
                            <td class="text-center">${renderNilaiTarget(d.nilai07,d.target,d.statuscode)}</td>
                            <td class="text-center">${renderNilaiTarget(d.nilai08,d.target,d.statuscode)}</td>
                            <td class="text-center">${renderNilaiTarget(d.nilai09,d.target,d.statuscode)}</td>
                            <td class="text-center">${renderNilaiTarget(d.nilai10,d.target,d.statuscode)}</td>
                            <td class="text-center">${renderNilaiTarget(d.nilai11,d.target,d.statuscode)}</td>
                            <td class="text-center">${renderNilaiTarget(d.nilai12,d.target,d.statuscode)}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
    }

}

function datateam(){
    $.ajax({
        url     : url + "index.php/qi/indikatorunit/datateam",
        type    : "POST",
        dataType: "json",

        success: function(response){
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

$(document).on("submit", "#formaddindikatorunit", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'index.php/qi/indikatorunit/addindikatorunit',
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

            $('#modal_add_pengajuanindikatorunit').modal('hide');
            Swal.close();
		},
        complete: function () {
            Swal.close();
            dataindikatorunit();
            datateam();
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