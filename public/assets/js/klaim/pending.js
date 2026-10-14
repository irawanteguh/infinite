load();

$('#selectperiode').on('change', function () {
    load();
});

function load(){
    dataraweklaim();
    dataalgoritmatype();
    dataalgoritmabreakdown();
    dataraweklaimdetail();
};

function dataraweklaim(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "klaim/pending/dataraweklaim",
        data     : {selectperiode:selectperiode},
        type     : "POST",
        dataType : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : 'Processing',
                html             : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });
        },
        success: function (response) {

            if (!response || response.responseCode !== "00") {
                Swal.fire({
                    icon             : 'warning',
                    title            : 'No Records Found',
                    text             : 'No records are available for the selected period.',
                    showConfirmButton: false,
                    timer            : 2000
                });
                return;
            }

            const result       = Array.isArray(response.responseResult) ? response.responseResult : [];
            const bulanLengkap = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
            const namaBulan    = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
            
            const mapKunjunganPendingRI    = {};
            const mapKunjunganPendingRJ    = {};
            const mapKunjunganTidakLayakRI = {};
            const mapKunjunganTidakLayakRJ = {};

            let jmlPending = 0;
            let jmlTidakLayak = 0;
            let nilaiPending = 0;
            let nilaiTidakLayak = 0;
            
            for (const item of result) {
                const periode   = String(item.periode ?? "").trim();
                const jnspel    = String(item.jnspel ?? "").trim();
                const status    = String(item.status ?? "").trim();
                const jumlah    = parseInt(item.jumlah_kasus, 10) || 0;
                const pengajuan = Number(item.pengajuan) || 0;

                if(status==="PENDING"){
                    jmlPending += jumlah;
                    nilaiPending += pengajuan;

                    if (jnspel === "1") {
                        mapKunjunganPendingRI[periode] = (mapKunjunganPendingRI[periode] || 0) + jumlah;
                    } else if (jnspel === "2") {
                        mapKunjunganPendingRJ[periode] = (mapKunjunganPendingRJ[periode] || 0) + jumlah;
                    } 
                }else{
                    jmlTidakLayak += jumlah;
                    nilaiTidakLayak += pengajuan;

                    if (jnspel === "1") {
                        mapKunjunganTidakLayakRI[periode] = (mapKunjunganTidakLayakRI[periode] || 0) + jumlah;
                    } else if (jnspel === "2") {
                        mapKunjunganTidakLayakRJ[periode] = (mapKunjunganTidakLayakRJ[periode] || 0) + jumlah;
                    } 
                }
            }

            const chartkunjungan = bulanLengkap.map(function (bulan, index) {
                return {
                    periode: namaBulan[index],
                    value1 : mapKunjunganPendingRI[bulan] || 0,
                    value2 : mapKunjunganPendingRJ[bulan] || 0,
                    value3 : mapKunjunganTidakLayakRI[bulan] || 0,
                    value4 : mapKunjunganTidakLayakRJ[bulan] || 0
                };
            });

            // Update 4 kartu summary
            $('#jmlpending').text(jmlPending.toLocaleString('id-ID'));
            $('#jmltidaklayak').text(jmlTidakLayak.toLocaleString('id-ID'));
            $('#nilaipending').text(nilaiPending.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
            $('#nilaitidaklayak').text(nilaiTidakLayak.toLocaleString('id-ID', { maximumFractionDigits: 0 }));

            renderchartarea("trenpending",chartkunjungan,"Discharge Date","InPatient",["InPatient", "OutPatient"],["value1", "value2"],true,"OutPatient","","",null);
            renderchartarea("trentidaklayak",chartkunjungan,"Discharge Date","InPatient",["InPatient", "OutPatient"],["value3", "value4"],true,"OutPatient","","",null);
        },
        complete: function () {
            Swal.close();
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
};

function dataalgoritmatype(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "klaim/pending/dataalgoritmatype",
        data     : {selectperiode:selectperiode},
        type     : "POST",
        dataType : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : 'Processing',
                html             : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });
        },
        success: function (response) {

            if (!response || response.responseCode !== "00") {
                Swal.fire({
                    icon             : 'warning',
                    title            : 'No Records Found',
                    text             : 'No records are available for the selected period.',
                    showConfirmButton: false,
                    timer            : 2000
                });
                return;
            }

            const result = Array.isArray(response.responseResult) ? response.responseResult : [];

            function buildChartData(statusTarget) {
                const grouped = {};

                result.forEach(function (item) {
                    const status = String(item.status || '').trim().toUpperCase();

                    if (status !== statusTarget) {
                        return;
                    }

                    const algoritma = String(item.algoritma_type || 'LAINNYA').trim().toUpperCase();
                    const jnspel    = String(item.jnspel ?? '').trim();
                    const jumlah    = Number(item.jumlah_kasus) || 0;

                    if (!grouped[algoritma]) {
                        grouped[algoritma] = {
                            algoritma_type: algoritma,
                            rawat_inap: 0,
                            rawat_jalan: 0
                        };
                    }

                    if (jnspel === '1') {
                        grouped[algoritma].rawat_inap += jumlah;
                    } else if (jnspel === '2') {
                        grouped[algoritma].rawat_jalan += jumlah;
                    }
                });

                return Object.values(grouped).sort(function (a, b) {
                    return (
                        b.rawat_inap + b.rawat_jalan
                    ) - (
                        a.rawat_inap + a.rawat_jalan
                    );
                });
            }

            const dataPending = buildChartData('PENDING');
            renderBarHorizontal(
                'trenpendingalgoritma',
                [
                    { name: 'Rawat Inap', field: 'rawat_inap' },
                    { name: 'Rawat Jalan', field: 'rawat_jalan' }
                ],
                dataPending,
                'algoritma_type',
                'Jumlah Kasus Pending'
            );

            const dataTidakLayak = buildChartData('TIDAK LAYAK');
            renderBarHorizontal(
                'trentidaklayakalgoritma',
                [
                    { name: 'Rawat Inap', field: 'rawat_inap' },
                    { name: 'Rawat Jalan', field: 'rawat_jalan' }
                ],
                dataTidakLayak,
                'algoritma_type',
                'Jumlah Kasus Tidak Layak'
            );            
        },
        complete: function () {
            Swal.close();
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
};

function dataalgoritmabreakdown(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "klaim/pending/dataalgoritmabreakdown",
        data     : {selectperiode:selectperiode},
        type     : "POST",
        dataType : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : 'Processing',
                html             : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });
        },
        success: function (response) {

            if (!response || response.responseCode !== "00") {
                Swal.fire({
                    icon             : 'warning',
                    title            : 'No Records Found',
                    text             : 'No records are available for the selected period.',
                    showConfirmButton: false,
                    timer            : 2000
                });
                return;
            }

            const result = Array.isArray(response.responseResult) ? response.responseResult : [];

            const dataAlgorithmBreakdown = result
                .map(function (item) {
                    return {
                        algoritma_detail: String(item.algoritma || '').trim(),
                        jumlah: Number(item.jumlah) || 0
                    };
                })
                .filter(function (item) {
                    return item.algoritma_detail !== '';
                })
                .sort(function (a, b) {
                    return b.jumlah - a.jumlah;
                })
                .slice(0, 6);

            renderBarHorizontal(
                'trenalgoritmabreakdown',
                [
                    { name: 'Jumlah Algoritma', field: 'jumlah' }
                ],
                dataAlgorithmBreakdown,
                'algoritma_detail',
                'Jumlah Kemunculan Algoritma'
            );        
        },
        complete: function () {
            Swal.close();
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
};

function dataraweklaimdetail(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "klaim/pending/dataraweklaimdetail",
        data     : {selectperiode:selectperiode},
        type     : "POST",
        dataType : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : 'Processing',
                html             : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });

            $("#resultdataraweklaim").empty();
        },
        success: function (response) {

            if (!response || response.responseCode !== "00") {
                Swal.fire({
                    icon             : 'warning',
                    title            : 'No Records Found',
                    text             : 'No records are available for the selected period.',
                    showConfirmButton: false,
                    timer            : 2000
                });
                return;
            }

            const result = Array.isArray(response.responseResult) ? response.responseResult : [];

            let tableResult = "";

            for (var i in result) {

                let btnaction = "";

                tableResult += "<tr>";
                tableResult += "<td class='text-start ps-4'>" + (parseInt(i) + 1) + "</td>";
                tableResult += "<td>" + (result[i].sep || "") + "</td>";
                tableResult += "<td>" + (result[i].nama_pasien || "") + "</td>";
                tableResult += "<td>" + (result[i].admission_date || "") + "</td>";
                tableResult += "<td>" + (result[i].discharge_date || "") + "</td>";
                tableResult += "<td>" + (result[i].verification_date || "") + "</td>";
                tableResult += "<td>" + (String(result[i].jnspel || "").trim() === "1" ? "InPatient" : String(result[i].jnspel || "").trim() === "2" ? "OutPatient" : "") + "</td>";
                tableResult += "<td>" + (result[i].kdinacbg || "") + "</td>";
                tableResult += "<td>" + (result[i].sl || "") + "</td>";
                
                tableResult += "<td>" + (result[i].algoritma_type || "") + "</td>";
                tableResult += "<td>" + (result[i].algoritma || "").split("|").join("<br>") + "</td>";
                tableResult += "<td>" + (result[i].keterangan_verifikasi || "") + "</td>";
                tableResult += "<td class='text-end'>" + todesimal(result[i].pengajuan) + "</td>";
                tableResult += "<td>" + (String(result[i].status || "").trim().toUpperCase() === "PENDING" ? "<span class='badge badge-light-warning'>Pending</span>" : String(result[i].status || "").trim().toUpperCase() === "TIDAK LAYAK" ? "<span class='badge badge-light-danger'>Tidak Layak</span>" : "") + "</td>";
                tableResult += "<td class='text-end'>";
                tableResult += "<div class='btn-group'>";
                tableResult += "<button type='button' class='btn btn-light-primary dropdown-toggle btn-sm' data-bs-toggle='dropdown'>Actions</button>";
                tableResult += "<div class='dropdown-menu'>" + btnaction + "</div>";
                tableResult += "</div>";
                tableResult += "</td>";
                tableResult += "</tr>";
            }

            $("#resultdataraweklaim").html(tableResult);

            initDataTable("#dataraweklaim_table","#searchtable",10);
        },
        complete: function () {
            Swal.close();
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
};