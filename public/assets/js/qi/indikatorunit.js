let   indikatorUnitData = [];
let   indikatorUnitMap  = {};

load();

$(document).on("click", ".indikatorunit-fishbone", function () {
    const item = indikatorUnitMap[String($(this).data("uuid"))] || {};
    $("#fishbone_indicator_ref_id").val(item.transaksi_id || "");
    $("#fishbone_indicator_period").val(item.periode_id || "").trigger("change");
    $("#fishbone_indicator_problem").val("");
    bootstrap.Modal.getOrCreateInstance(document.getElementById("modal_create_fishbone_from_indicator")).show();
});

$(document).on("click", ".indikatorunit-edit-pic", function (e) {
    e.preventDefault();
    const transaksiId = String($(this).data("uuid") || "");
    const $select = $("#edit_indikator_pic_user_id").empty().append(new Option("Memuat daftar PIC...", ""));

    $.ajax({
        url: url + "qi/indikatorunit/picusers",
        method: "POST",
        dataType: "JSON",
        data: { transaksi_id: transaksiId },
        success: function (response) {
            if (response.responseCode !== "00") {
                Swal.fire("Tidak dapat mengubah PIC", response.responseDesc || "Indikator tidak dapat diubah.", "warning");
                return;
            }

            const result = response.responseResult || {};
            $select.empty().append(new Option("Pilih PIC", ""));
            (Array.isArray(result.users) ? result.users : []).forEach(function (user) {
                $select.append(new Option(user.name || user.user_id, user.user_id));
            });
            $("#edit_indikator_pic_transaksi_id").val(transaksiId);
            $select.val(String(result.currentPic || "")).trigger("change");
            bootstrap.Modal.getOrCreateInstance(document.getElementById("modal_edit_indikator_pic")).show();
        },
        error: function () {
            Swal.fire("Gagal memuat PIC", "Daftar PIC untuk indikator ini tidak dapat dimuat.", "error");
        }
    });
});

$(document).on("submit", "#form_edit_indikator_pic", function (e) {
    e.preventDefault();
    const $button = $("#btn_update_indikator_pic").prop("disabled", true);
    $.ajax({
        url: url + "qi/indikatorunit/updatepic",
        method: "POST",
        dataType: "JSON",
        data: $(this).serialize(),
        success: function (response) {
            if (response.responseCode !== "00") {
                Swal.fire("Tidak dapat mengganti PIC", response.responseDesc || "PIC gagal diperbarui.", response.responseHead || "warning");
                return;
            }
            bootstrap.Modal.getOrCreateInstance(document.getElementById("modal_edit_indikator_pic")).hide();
            Swal.fire({ icon: "success", title: "PIC diperbarui", text: response.responseDesc, timer: 1600, showConfirmButton: false });
            load();
        },
        error: function () {
            Swal.fire("Gagal menyimpan", "Perubahan PIC indikator tidak dapat disimpan.", "error");
        },
        complete: function () {
            $button.prop("disabled", false);
        }
    });
});

$(document).on("submit", "#form_create_fishbone_from_indicator", function (e) {
    e.preventDefault();
    const $button = $("#btn_save_fishbone_from_indicator").prop("disabled", true);
    $.post(url + "qi/fishbone/saveanalysis", $(this).serialize())
        .done(function (response) {
            if (response.responseCode !== "00") {
                Swal.fire("Gagal menyimpan", response.responseDesc || "Analisis Fishbone gagal disimpan.", "error");
                return;
            }
            bootstrap.Modal.getOrCreateInstance(document.getElementById("modal_create_fishbone_from_indicator")).hide();
            window.location.href = url + "qi/fishbone";
        })
        .fail(function () { Swal.fire("Error", "Analisis Fishbone gagal disimpan.", "error"); })
        .always(function () { $button.prop("disabled", false); });
});

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

function getdata(btn) {
    $("#modal_edit_pengajuanindikatorunit_uuid").val(btn.data("uuid"));
    $("#modal_edit_pengajuanindikatorunit_periodeid").val(btn.data("periode_id")).trigger("change");
    $("#modal_edit_pengajuanindikatorunit_departmentid").val(btn.data("department_id")).trigger("change");
    $("#modal_edit_pengajuanindikatorunit_userid").val(btn.data("userid")).trigger("change");
    $("#modal_edit_pengajuanindikatorunit_target").val(btn.data("target"));
    $("#modal_edit_pengajuanindikatorunit_indikatorid").val(btn.data("indikator_id")).trigger("change");
}

function getdatapenilaian(btn){
    const referenceId = String(btn.data("transaksiid"));
    const month = String(btn.data("bulan")).padStart(2, "0");
    const data = indikatorUnitMap[referenceId] || {};
    const numerator = data["numerator" + month];
    const denumerator = data["denumerator" + month];
    const reason = data["reason" + month];
    const rtl = data["rtl" + month];
    const hasSubmitted = numerator !== null && numerator !== undefined && numerator !== "";

    $("#modal_input_nilai_indikator_referensiid").val(referenceId);
    $("#modal_input_nilai_indikator_indikatorid").val(btn.data("indikatoriid"));
    $("#modal_input_nilai_indikator_bulan").val(month);
    $("#modal_add_pengajuanindikatorunit_numerator").val(numerator ?? "");
    $("#modal_add_pengajuanindikatorunit_denumerator").val(denumerator ?? "");
    $("#modal_add_pengajuanindikatorunit_alasan").val(reason ?? "");
    $("#modal_add_pengajuanindikatorunit_rtl").val(rtl ?? "");
    $("#modal_input_nilai_indikator_dokumen").val("");
    $("#modal_input_nilai_indikator_title").text(hasSubmitted ? "Edit Penilaian Indikator" : "Submit Penilaian Indikator");
    $("#btn_submit_nilai_indikator").val(hasSubmitted ? "UPDATE" : "SUBMIT");

};

function load(){
    dataindikatorunit();
    datateam();
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

    const chartnilai = bulan.map((nama, index) => ({
        periode: nama,
        value1: parseFloat(nilai[index]) || 0
    }));

    renderchartarea("chartindikatormutu",chartnilai, "Month", "Achievement (%)",["Achievement"],["value1"],null,"","","",null);

    for (let i = 0; i < 12; i++) {
        let btnaction      = "";

        const pencapaian = nilai[i] == null ? 0 : parseFloat(nilai[i]);

         var getvariabel = "";
            getvariabel += " data-transaksiid='" + data.transaksi_id + "'";
            getvariabel += " data-bulan='" + String(i + 1).padStart(2, '0') + "'";
            getvariabel += " data-indikatoriid='" + data.indikator_id + "'";

        const hasSubmitted = numerator[i] !== null && numerator[i] !== undefined && numerator[i] !== "";
        const target = Number(data.target);
        const targetMet = hasSubmitted && (target === 0 ? pencapaian === 0
            : data.target_capaian === 'L' ? pencapaian <= target : pencapaian >= target);
        const monthKey = String(i + 1).padStart(2, "0");
        const inputStatus = (data.inputStatuses || {})[monthKey];
        const canInput = inputStatus && inputStatus.allowed === true;
        const statusLabels = {
            unlimited: ['badge-light-success', 'Tanpa batas tanggal'],
            open: ['badge-light-success', 'Masih dibuka'],
            upcoming: ['badge-light-secondary', 'Belum masuk periode'],
            granted: ['badge-light-warning', 'Izin khusus 24 jam'],
            closed: ['badge-light-danger', 'Batas waktu berakhir']
        };
        const statusDisplay = inputStatus && statusLabels[inputStatus.status]
            ? statusLabels[inputStatus.status] : ['badge-light-secondary', 'Status tidak tersedia'];
        if (canInput) {
        btnaction += "<a class='dropdown-item btn btn-sm text-primary' data-bs-toggle='modal' data-bs-target='#modal_input_nilai_indikator' "+getvariabel+" onclick='getdatapenilaian($(this));'><i class='bi bi-pencil text-primary me-4'></i>"+(hasSubmitted ? "Edit Penilaian" : "Submit Penilaian")+"</a>";
        }

        const attachments = data["attachments" + monthKey] || [];
        const attachmentCell = attachments.length
            ? attachments.map(function (file, fileIndex) {
                const link = url+"qi/indikatorunit/viewattachment?reference_id="+encodeURIComponent(data.transaksi_id)+"&bulan="+monthKey+"&file="+encodeURIComponent(file);
                const label = attachments.length > 1 ? "View " + (fileIndex + 1) : "View";
                return "<a class='btn btn-sm btn-light-primary me-1 mb-1' target='_blank' rel='noopener' href='"+link+"'><i class='bi bi-paperclip me-1'></i>"+label+"</a>";
            }).join("")
            : "<span class='text-muted'>-</span>";

        tableresult += "<tr>";
            tableresult += "<td class='ps-4'>"+(parseInt(i) + 1)+"</td>";
            tableresult += "<td>"+bulan[i]+"</td>";
            tableresult += "<td class='text-center'>"+data.target+"%</td>";
            tableresult += "<td class='text-center'>"+(numerator[i] ?? "")+"</td>";
            tableresult += "<td class='text-center'>"+(denumerator[i] ?? "")+"</td>";
            tableresult += hasSubmitted
                ? "<td><span class='badge " + (targetMet ? "badge-light-success" : "badge-light-danger") + "'>" + pencapaian.toFixed(2) + "%</span></td>"
                : "<td><span class='text-muted'>-</span></td>";
            tableresult += "<td>";
            if (!hasSubmitted) {
                tableresult += "<span class='badge badge-light-secondary'>Belum diisi</span>";
            } else {
                tableresult += "<span class='badge " + (targetMet ? "badge-light-success" : "badge-light-danger") + "'>";
                tableresult += targetMet ? "<i class='bi bi-check-circle-fill me-2 text-success'></i>Achieved" : "<i class='bi bi-x-circle-fill me-2 text-danger'></i>Not Achieved";
                tableresult += "</span>";
            }
            tableresult += "</td>";
            tableresult += "<td><span class='badge " + statusDisplay[0] + "'>" + statusDisplay[1] + "</span></td>";
            tableresult += "<td>"+(reason[i] ?? "")+"</td>";
            tableresult += "<td>"+(rtl[i] ?? "")+"</td>";
            tableresult += "<td>"+attachmentCell+"</td>";

            tableresult += "<td class='text-end'>";
            if (canInput) {
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
            } else {
                tableresult += "<span class='text-muted'>-</span>";
            }
            tableresult += "</td>";

        tableresult += "</tr>";
    }
    $("#resultdataindikatorunitsubmit").html(tableresult);
}

function deleteIndikator(el) {
    const transaksiid = el.data("uuid");

    Swal.fire({
        title             : "Delete Indicator?",
        html              : `Indikator ini akan dihapus dan tidak dapat digunakan kembali.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`,
        icon              : "warning",
        showCancelButton  : true,
        confirmButtonColor: "#d33",
        cancelButtonColor : "#6c757d",
        confirmButtonText : '<i class="bi bi-trash3 text-white"></i> Yes, Delete',
        cancelButtonText  : "Cancel",
        reverseButtons    : true,
        timer             : 10000,
        timerProgressBar  : true
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url     : url + "qi/indikatorunit/deleteindikator",
                type    : "POST",
                data    : {transaksiid: transaksiid},
                dataType: "JSON",
                success : function(response) {
                    if (response.responseCode === "00") {
                        Swal.fire({
                            title            : "Success",
                            text             : response.responseDesc,
                            icon             : "success",
                            timer            : 1500,
                            showConfirmButton: false,
                            didClose         : function() {
                                load();
                            }
                        });
                    } else {
                        Swal.fire("Error", response.responseDesc, "error");
                    }
                },
                error: function(xhr) {
                    Swal.fire("Error", "Failed to delete indicator.", "error");
                }
            });
        }
    });
}

function dataindikatorunit() {
    $.ajax({
        url       : url+"qi/indikatorunit/dataindikatorunit",
        type      : "POST",
        dataType  : "JSON",
        data      : { uuid: $("#uuid").val() || "" },
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
            indikatorUnitData = result;

            let tableResult    = "";
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
                const avatar        = `${url}assets/media/avatars/${result[i].pic}.jpg`;
                const avatarDefault = `${url}assets/media/avatars/blank.png`;

                var getvariabel = "";
                    getvariabel += " data-uuid='" + result[i].transaksi_id + "'";
                    getvariabel += " data-periode_id='" + result[i].periode_id + "'";
                    getvariabel += " data-department_id='" + result[i].department_id + "'";
                    getvariabel += " data-userid='" + result[i].pic + "'";
                    getvariabel += " data-target='" + result[i].target + "'";
                    getvariabel += " data-indikator_id='" + result[i].indikator_id + "'";

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
                }

                totalIndikator++;

                const overallAvg = totalIndikator ? (totalAvg / totalIndikator) : 0;
                const progressColor = overallAvg >= 80 ? "success" : overallAvg >= 60 ? "warning" : "danger";

                $("#overallpencapaian").html(`
                    <span class="fs-7 text-gray-700 fw-bolder pe-4 ps-1">Achievement:</span>
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

                    <span class="badge badge-light-${progressColor} ms-3">${totalIndikator} Indicator</span>
                `);
                

                let btnaction      = "";

                if(result[i].status_id==="1"){
                    if(result[i].statusidindikatorunit==="0"){
                        btnaction += "<a class='dropdown-item btn btn-sm text-primary' data-bs-toggle='modal' data-bs-target='#modal_edit_pengajuanindikatorunit' "+getvariabel+" onclick='getdata($(this));'><i class='bi bi-pencil text-primary me-4'></i>Edit</a>";
                        btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm text-danger' "+getvariabel+" onclick='deleteIndikator($(this));'><i class='bi bi-trash3 text-danger me-4'></i>Delete</a>";
                    }
                }

                if(result[i].status_id==="3"){
                    if(result[i].statusidindikatorunit==="1"){
                        btnaction += "<a class='dropdown-item btn btn-sm text-primary' "+getvariabel+" href='"+url+"qi/indikatorunit?uuid="+result[i].transaksi_id+"'><i class='bi bi-pencil-square text-primary me-4'></i>Submit</a>";
                        btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm text-primary indikatorunit-edit-pic' "+getvariabel+"><i class='bi bi-person-gear text-primary me-4'></i>Edit PIC Indikator</a>";
                    }
                }

                btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm text-primary indikatorunit-fishbone' data-uuid='"+String(result[i].transaksi_id || "").replace(/&/g,"&amp;").replace(/'/g,"&#39;")+"'><i class='bi bi-diagram-3 text-primary me-4'></i>Fishbone</a>";
                btnaction += "<a class='dropdown-item btn btn-sm text-primary' href='"+url+"qi/pdsa?indikator_ref_id="+encodeURIComponent(result[i].transaksi_id)+"'><i class='bi bi-arrow-repeat text-primary me-4'></i>Siklus PDSA</a>";
                

                tableResult += "<tr>";
                tableResult += "<td class='ps-4'>"+(parseInt(i) + 1)+"</td>";

                tableResult += "<td>";
                    tableResult += "<div class='fw-bold'>" + result[i].indikator + "</div><div class='text-muted fst-italic'>" + formathtml(result[i].definisi) + "</div>";
                    tableResult += "<div>";
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_keselamatan, "Keselamatan Pasien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_waktu, "Tepat Waktu", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_efektif, "Efektif", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_efesien, "Efisien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_pasien, "Berorientasi Pada Pasien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_integrasi, "Integrasi", "info");
                    tableResult += "</div>";
                    tableResult += "<div><span class='badge badge-light-primary mt-4'>"+result[i].jenis+"</span></div>";
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
            initDataTable("#dataindikatorunit_table","#searchtable",10);
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

function datateam(){
    $.ajax({
        url     : url+"qi/indikatorunit/datateam",
        type    : "POST",
        dataType: "JSON",
        success : function(response){

            let html = "";
            if(response.responseCode === "00"){
                const result = Array.isArray(response.responseResult) ? response.responseResult : [];
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

$(document).on("submit", "#formaddindikatorunit", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'qi/indikatorunit/addindikatorunit',
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

            $('#modal_add_pengajuanindikatorunit').modal('hide');
            Swal.close();
			load();
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

$(document).on("submit", "#formeditindikatorunit", function (e) {
    e.preventDefault();
    var data = new FormData(this);
    $.ajax({
        url        : url + 'qi/indikatorunit/updateindikatorunit',
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
                    html             : "<b>" + response.responseDesc + "</b>",
                    icon             : response.responseHead,
                    confirmButtonText: 'Please Try Again',
                    customClass      : { confirmButton: 'btn btn-danger' },
                    timerProgressBar : true,
                    timer            : 5000,
                    showClass        : { popup: "animate__animated animate__fadeInUp animate__faster" },
                    hideClass        : { popup: "animate__animated animate__fadeOutDown animate__faster" }
                });
                return;
            }

            $('#modal_edit_pengajuanindikatorunit').modal('hide');
            Swal.close();
			load();
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

$(document).on("submit", "#forminputnilaiindikator", function (e) {
    e.preventDefault();

    const numerator   = $("#modal_add_pengajuanindikatorunit_numerator").val().trim();
    const denumerator = $("#modal_add_pengajuanindikatorunit_denumerator").val().trim();

    if (numerator === "" || denumerator === "") {
        Swal.fire({
            icon: "warning",
            title: "Validation",
            text: "Numerator dan Denumerator wajib diisi."
        });
        return false;
    }

    if (!$.isNumeric(numerator) || !$.isNumeric(denumerator)) {
        Swal.fire({
            icon: "warning",
            title: "Validation",
            text: "Numerator dan Denumerator harus berupa angka."
        });
        return false;
    }

    if (Number(numerator) < 0 || Number(denumerator) < 0) {
        Swal.fire({
            icon: "warning",
            title: "Validation",
            text: "Numerator dan Denumerator tidak boleh bernilai negatif."
        });
        return false;
    }

    // if (Number(denumerator) === 0) {
    //     Swal.fire({
    //         icon: "warning",
    //         title: "Validation",
    //         text: "Denumerator tidak boleh bernilai 0."
    //     });
    //     return false;
    // }

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
        url        : url+"qi/indikatorunit/inputnilaiindikator",
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
                    html             : "<b>" + response.responseDesc + "</b>",
                    icon             : response.responseHead,
                    confirmButtonText: 'Please Try Again',
                    customClass      : { confirmButton: 'btn btn-danger' },
                    timerProgressBar : true,
                    timer            : 5000,
                    showClass        : { popup: "animate__animated animate__fadeInUp animate__faster" },
                    hideClass        : { popup: "animate__animated animate__fadeOutDown animate__faster" }
                });
                return;
            }

            $("#modal_input_nilai_indikator").modal("hide");
            Swal.close();
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
