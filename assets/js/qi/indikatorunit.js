let   indikatorUnitData = [];
let   indikatorUnitMap  = {};

load();

function load() {
    dataindikatorunit();
    datateam();
}

function getdata(btn){
    var transaksiid = btn.attr("data-transaksiid");
    var bulan       = btn.attr("data-bulan");

	$(":hidden[name='modal_input_nilai_indikator_indikatorid']").val(transaksiid);
    $(":hidden[name='modal_input_nilai_indikator_bulan']").val(bulan);
};

$("#modal_add_pengajuanindikatorunit_numerator, #modal_add_pengajuanindikatorunit_denumerator").on("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "");
});

$("#modal_add_pengajuanindikatorunit_numerator, #modal_add_pengajuanindikatorunit_denumerator").on("paste", function (e) {
    const paste = (e.originalEvent || e).clipboardData.getData("text");
    if (!/^\d+$/.test(paste)) {
        e.preventDefault();

        Swal.fire({
            icon: "warning",
            title: "Invalid Input",
            text: "Numerator dan Denumerator hanya boleh berisi angka."
        });
    }
});

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
            indikatorUnitData = result;

            if (response.responCode !== "00") {
                Swal.fire({
                    icon : "info",
                    title: "Information",
                    text : response.responDesc
                });
                return;
            }

            let tableresult    = "";
            let totalAvg       = 0;
            let totalIndikator = 0;

            indikatorUnitMap = {};
            result.forEach(function(item){
                indikatorUnitMap[item.transaksi_id] = item;
            });

            if ($("#uuid").length > 0 && $("#uuid").val() !== "") {
                loadIndikatorUnitSubmit();
                return;
            }

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

                let btnaction      = "";

                if(result[i].status_id === "1"){
                    btnaction += "<a class='dropdown-item btn btn-sm text-danger' "+getvariabel+" onclick='activation($(this));'><i class='bi bi-trash3 text-danger me-4'></i>Deactive</a>";
                }

                if(result[i].status_id === "2"){
                    btnaction += "<a class='dropdown-item btn btn-sm text-primary' "+getvariabel+" href='"+url+"index.php/qi/indikatorunit?uuid="+result[i].transaksi_id+"'><i class='bi bi-pencil-square text-primary me-4'></i>Submit</a>";
                }

                tableresult += "<tr>";
                tableresult += "<td class='ps-4'>"+(parseInt(i) + 1)+"</td>";

                tableresult += "<td>";
                    tableresult += "<div class='fw-bold'>" + result[i].indikator + "</div><div class='text-muted fst-italic'>" + result[i].definisi + "</div>";
                    tableresult += "<div>";
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_keselamatan, "Keselamatan Pasien", "info");
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_waktu, "Tepat Waktu", "info");
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_efektif, "Efektif", "info");
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_efesien, "Efisien", "info");
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_pasien, "Berorientasi Pada Pasien", "info");
                        tableresult += badgeDimensiMutu(result[i].dimensi_mutu_integrasi, "Integrasi", "info");
                    tableresult += "</div>";
                tableresult += "</td>";
                    
                tableresult += "</td>";
                tableresult += "<td><span class='badge badge-light-info'>"+result[i].jenis+"</span></td>";
                tableresult += "<td><div>"+result[i].tahun+"<span class='badge badge-light-"+result[i].statuscolor+" ms-2'><i class='"+result[i].statusicon+" text-"+result[i].statuscolor+" me-1'></i>"+result[i].status+"</span></div><div class='text-muted fs-8 mt-1'>"+result[i].statusdescription+"</div></td>";
                tableresult += "<td>"+result[i].department+"</td>";
                tableresult += "<td><span class='badge badge-light-info'>"+result[i].target+(result[i].kode ||" ")+"</span></td>";    
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
                const result = Array.isArray(response.responResult) ? response.responResult : [];
                for (var i in result) {
                    html += renderteam(result[i].pic_list, 5);
                }
            }

            $("#teamindikatorunit").html(html);

            $('[data-bs-toggle="tooltip"]').each(function () {
                new bootstrap.Tooltip(this);
            });

        }
    });

}

function loadIndikatorUnitSubmit(){
    const uuid = $("#uuid").val();

    if(!uuid){
        Swal.fire({
            icon: "warning",
            title: "Warning",
            text: "UUID tidak ditemukan."
        });
        return;
    }

    const bulan       = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
    const data        = indikatorUnitMap[uuid];
    let   tableresult = "";

    console.log(data);

    const nilai = [
        data.nilai01,
        data.nilai02,
        data.nilai03,
        data.nilai04,
        data.nilai05,
        data.nilai06,
        data.nilai07,
        data.nilai08,
        data.nilai09,
        data.nilai10,
        data.nilai11,
        data.nilai12
    ];

    const numerator = [
        data.numerator01,
        data.numerator02,
        data.numerator03,
        data.numerator04,
        data.numerator05,
        data.numerator06,
        data.numerator07,
        data.numerator08,
        data.numerator09,
        data.numerator10,
        data.numerator11,
        data.numerator12
    ];

    const denumerator = [
        data.denumerator01,
        data.denumerator02,
        data.denumerator03,
        data.denumerator04,
        data.denumerator05,
        data.denumerator06,
        data.denumerator07,
        data.denumerator08,
        data.denumerator09,
        data.denumerator10,
        data.denumerator11,
        data.denumerator12
    ];

    const reason = [
        data.reason01,
        data.reason02,
        data.reason03,
        data.reason04,
        data.reason05,
        data.reason06,
        data.reason07,
        data.reason08,
        data.reason09,
        data.reason10,
        data.reason11,
        data.reason12
    ];

    const rtl = [
        data.rtl01,
        data.rtl02,
        data.rtl03,
        data.rtl04,
        data.rtl05,
        data.rtl06,
        data.rtl07,
        data.rtl08,
        data.rtl09,
        data.rtl10,
        data.rtl11,
        data.rtl12
    ];

    for (let i = 0; i < 12; i++) {
        let btnaction      = "";

        const pencapaian = nilai[i] == null ? 0 : parseFloat(nilai[i]);

        getvariabel =   "data-transaksiid='"+data.transaksi_id+"'"+
                        "data-bulan='" + String(i + 1).padStart(2, '0') + "'";

        btnaction += "<a class='dropdown-item btn btn-sm text-primary' data-bs-toggle='modal' data-bs-target='#modal_input_nilai_indikator' "+getvariabel+" onclick='getdata($(this));'><i class='bi bi-pencil text-primary me-4'></i>Submit</a>";

        tableresult += "<tr>";
            tableresult += "<td class='ps-4'>"+(parseInt(i) + 1)+"</td>";
            tableresult += "<td>"+bulan[i]+"</td>";
            tableresult += "<td class='text-center'>"+data.target+"%</td>";
            tableresult += "<td class='text-center'>"+(numerator[i] ?? "")+"</td>";
            tableresult += "<td class='text-center'>"+(denumerator[i] ?? "")+"</td>";
            tableresult += "<td>";
                tableresult += "<span class='badge " + (pencapaian >= parseFloat(data.target) ? "badge-light-success" : "badge-light-danger") + "'>";
                    tableresult += pencapaian.toFixed(2) + "%";
                tableresult += "</span>";
            tableresult += "</td>";
            tableresult += "<td>";
                tableresult += "<span class='badge " + (pencapaian >= parseFloat(data.target) ? "badge-light-success" : "badge-light-danger") + "'>";
                if (pencapaian >= parseFloat(data.target)) {
                    tableresult += "<i class='bi bi-check-circle-fill me-2 text-success'></i>Tercapai";
                } else {
                    tableresult += "<i class='bi bi-x-circle-fill me-2 text-danger'></i>Tidak Tercapai";
                }
                tableresult += "</span>";
            tableresult += "</td>";
            tableresult += "<td>"+(reason[i] ?? "")+"</td>";
            tableresult += "<td>"+(rtl[i] ?? "")+"</td>";

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

    $("#resultdataindikatorunitsubmit").html(tableresult);

    renderAreaChart({
        selector   : "#chartindikatormutu",
        categories : bulan,
        target     : data.target,
        series : [{
            name : "Pencapaian",
            data : nilai.map(function(v){
                return v == null ? 0 : parseFloat(v);
            })
        }]
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

$(document).on("submit", "#forminputnilaiindikator", function (e) {
    e.preventDefault();

    const numerator   = $("#modal_add_pengajuanindikatorunit_numerator").val().trim();
    const denumerator = $("#modal_add_pengajuanindikatorunit_denumerator").val().trim();

    // Wajib diisi
    if (numerator === "" || denumerator === "") {
        Swal.fire({
            icon: "warning",
            title: "Validation",
            text: "Numerator dan Denumerator wajib diisi."
        });
        return false;
    }

    // Harus angka
    if (!$.isNumeric(numerator) || !$.isNumeric(denumerator)) {
        Swal.fire({
            icon: "warning",
            title: "Validation",
            text: "Numerator dan Denumerator harus berupa angka."
        });
        return false;
    }

    // Tidak boleh negatif
    if (Number(numerator) < 0 || Number(denumerator) < 0) {
        Swal.fire({
            icon: "warning",
            title: "Validation",
            text: "Numerator dan Denumerator tidak boleh bernilai negatif."
        });
        return false;
    }

    // Denumerator tidak boleh 0
    if (Number(denumerator) === 0) {
        Swal.fire({
            icon: "warning",
            title: "Validation",
            text: "Denumerator tidak boleh bernilai 0."
        });
        return false;
    }

    // Numerator tidak boleh lebih besar dari Denumerator
    if (Number(numerator) > Number(denumerator)) {
        Swal.fire({
            icon: "warning",
            title: "Validation",
            text: "Numerator tidak boleh lebih besar dari Denumerator."
        });
        return false;
    }

    const data = new FormData(this);

    $.ajax({
        url         : url + "index.php/qi/indikatorunit/inputnilaiindikator",
        data        : data,
        method      : "POST",
        dataType    : "JSON",
        cache       : false,
        processData : false,
        contentType : false,

        beforeSend: function () {
            Swal.fire({
                title: "Processing",
                html: "Please wait while the system processes your request.",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });
        },

        success: function (response) {

            if (response.responCode !== "00") {
                Swal.fire({
                    title: "<h1 class='font-weight-bold'>For Your Information</h1>",
                    html: "<b>" + response.responDesc + "</b>",
                    icon: response.responHead,
                    confirmButtonText: "Please Try Again",
                    customClass: {
                        confirmButton: "btn btn-danger"
                    },
                    timerProgressBar: true,
                    timer: 5000
                });
                return;
            }

            $("#modal_input_nilai_indikator").modal("hide");

            Swal.fire({
                icon: "success",
                title: "Success",
                text: response.responDesc,
                timer: 2000,
                showConfirmButton: false
            });
        },

        complete: function () {
            load();
        },

        error: function () {
            Swal.fire({
                icon: "error",
                title: "System Error",
                text: "Failed to process request."
            });
        }
    });

    return false;
});