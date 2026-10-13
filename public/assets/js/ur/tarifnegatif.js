load();

$('#selectperiode').on('change', function () {
    load();
});

function load(){
    rawdata();
};

function rawdata(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "ur/tarifnegatif/rawdata",
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

            $("#resultrawdatainpatient").empty();
            $("#resultrawdataoutpatient").empty();
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

            const selisihpendapatanRI = aggregate(
                result.filter(item => String(item.ptd).trim() === "1"),
                "sum",
                "periode",
                ["selisih"]
            );

            const selisihpendapatanRJ = aggregate(
                result.filter(item => String(item.ptd).trim() === "2"),
                "sum",
                "periode",
                ["selisih"]
            );

            const mapPendapatanRI       = {};
            const mapPendapatanRJ       = {};
            let   totalselisihRI        = 0;
            let   totalselisihRJ        = 0;
            let   tableResultInpatient  = "";
            let   tableResultOutpatient = "";
            let   noInpatient           = 1;
            let   noOutpatient          = 1;

            for (var i in result) {
                const item    = result[i];
                const ptd     = String(item.ptd ?? "").trim();
                const selisih = (parseFloat(item.selisih) || 0);

                if (ptd === "1") {
                    totalselisihRI += selisih;
                } else if (ptd === "2") {
                    totalselisihRJ += selisih;
                }

                let btnaction = "";
                let row       = "";
                let no        = result[i].ptd == 1 ? noInpatient++ : noOutpatient++;
               
                row += "<tr>";
                row += "<td class='text-start ps-4'>" + no + "</td>";
                row += "<td>" + (result[i].sep || "") + "</td>";
                row += "<td>" + (result[i].nokartu || "") + "</td>";
                row += "<td><div>" + (result[i].mrn || "") + "</div><div>" + (result[i].nama_pasien || "") + "</div></td>";
                row += "<td>" + (result[i].dpjp || "") + "</td>";
                row += "<td>" + (result[i].diaglist || "").split(";").join("<br>") + "</td>";
                row += "<td>" + (result[i].proclist || "").split(";").join("<br>") + "</td>";
                row += "<td>" + (result[i].admission_date || "") + "</td>";
                row += "<td>" + (result[i].discharge_date || "") + "</td>";
                row += "<td class='text-end'>" + todesimal(result[i].tarif_rs) + "</td>";
                row += "<td class='text-end'>" + todesimal(result[i].total_tarif) + "</td>";
                row += "<td class='text-end'>" + todesimal(result[i].idrg_total_tarif) + "</td>";
                row += "<td class='text-end'>" + todesimal((parseFloat(result[i].total_tarif) || 0) - (parseFloat(result[i].tarif_rs) || 0)) + "</td>";
                row += "<td class='text-end'>";
                row += "<div class='btn-group'>";
                row += "<button type='button' class='btn btn-light-primary dropdown-toggle btn-sm' data-bs-toggle='dropdown'>Actions</button>";
                row += "<div class='dropdown-menu'>" + btnaction + "</div>";
                row += "</div>";
                row += "</td>";
                row += "</tr>";

                if (result[i].ptd == 1) {
                    tableResultInpatient += row;
                } else if (result[i].ptd == 2) {
                    tableResultOutpatient += row;
                }
            }

            selisihpendapatanRI.forEach(function (item) {
                mapPendapatanRI[String(item.periode).trim()] = item;
            });

            selisihpendapatanRJ.forEach(function (item) {
                mapPendapatanRJ[String(item.periode).trim()] = item;
            });

            const chartpendapatan = bulanLengkap.map(function (bulan, index) {
                return {
                    periode: namaBulan[index],
                    value1 : mapPendapatanRI[bulan]?.selisih || 0,
                    value2 : mapPendapatanRJ[bulan]?.selisih || 0
                };
            });

            renderchartarea("trenpendapatan", chartpendapatan, "Discharge Date", "Selisih Pendapatan (Rp)", ["InPatient", "OutPatient"], ["value1", "value2"], 1, "Selisih Rawat Jalan (Rp)", null, "Rata-rata Selisih", null);

            $("#resultrawdatainpatient").html(tableResultInpatient);
            $("#resultrawdataoutpatient").html(tableResultOutpatient);

            initDataTable("#rawdatainpatient_table","#searchtable",10);
            initDataTable("#rawdataoutpatient_table","#searchtable",10);
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

function dataseveritylevel(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "ur/casemixindex/dataseveritylevel",
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

            $("#resultdataseveritylevel").empty();
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
                tableResult += "<tr>";
                tableResult += "<td class='text-start ps-4'>"+(parseInt(i) + 1)+"</td>";
                tableResult += "<td>"+(result[i].sl==="0"?"Rawat Jalan":result[i].sl==="I"?"Severity I":result[i].sl==="II"?"Severity II":result[i].sl==="III"?"Severity III":result[i].sl||"")+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].jan))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].feb))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].mar))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].apr))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].mei))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].jun))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].jul))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].agu))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].sep))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].okt))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].nov))+"</td>";
                tableResult += "<td class='text-end pe-4'>"+(todesimal(result[i].des))+"</td>";
                tableResult += "</tr>";
            }

            $("#resultdataseveritylevel").html(tableResult);

            initDataTable("#dataseveritylevel_table","#searchtable",50);
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

function datacmg(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "ur/casemixindex/datacmg",
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

            $("#resultdatacmg").empty();
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

                tableResult += "<tr>";
                tableResult += "<td class='text-start ps-4'>"+(parseInt(i) + 1)+"</td>";
                tableResult += "<td>"+(result[i].group_code || "")+"</td>";
                tableResult += "<td>"+(result[i].group_name || "")+"</td>";
                tableResult += "<td>"+(result[i].description || "")+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].jan))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].feb))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].mar))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].apr))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].mei))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].jun))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].jul))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].agu))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].sep))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].okt))+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].nov))+"</td>";
                tableResult += "<td class='text-end pe-4'>"+(todesimal(result[i].des))+"</td>";
                tableResult += "</tr>";
            }

            $("#resultdatacmg").html(tableResult);

            initDataTable("#datacmg_table","#searchtable",50);
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
