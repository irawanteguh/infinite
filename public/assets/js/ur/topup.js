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
        url      : url + "ur/Topup/rawdata",
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
                tableResult += "<td class='text-start ps-4'>"+(parseInt(i) + 1)+"</td>";
                tableResult += "<td><div>" + (result[i].sep || "") + "</div><div>" + (result[i].nokartu || "") + "</div><div><span class='badge badge-info'>" + (result[i].ptd == 1 ? "Inpatient" : "Outpatient") + "</span></div></td>";
                tableResult += "<td><div>" + (result[i].mrn || "") + "</div><div>" + (result[i].nama_pasien || "") + "</div></td>";
                tableResult += "<td>" + (result[i].umur_tahun || "") + "</td>";
                tableResult += "<td>" + (result[i].dpjp || "") + "</td>";
                tableResult += "<td>" + (result[i].diaglist || "").split(";").join("<br>") + "</td>";
                tableResult += "<td>" + (result[i].proclist || "").split(";").join("<br>") + "</td>";
                tableResult += "<td>" + (result[i].admission_date || "") + "</td>";
                tableResult += "<td>" + (result[i].discharge_date || "") + "</td>";
                tableResult += "<td>" + (result[i].los || "") + "</td>";
                tableResult += "<td>" + (result[i].sl || "") + "</td>";
                tableResult += "<td class='text-end'>" + todesimal(result[i].tarif_rs) + "</td>";
                tableResult += "<td class='text-end'>" + todesimal(result[i].total_tarif) + "</td>";
                tableResult += "<td class='text-end'>" + todesimal((parseFloat(result[i].total_tarif) || 0) - (parseFloat(result[i].tarif_rs) || 0)) + "</td>";
                tableResult += "<td>" + (result[i].case_notes || "") + (result[i].topup ? '<br><span class="badge bg-info me-2">Top Up: ' + result[i].topup + '</span>' : "")  + (Number(result[i].potensibiaya) > 0 ? '<span class="badge bg-success">Nilai TopUp: Rp. ' + todesimal(result[i].potensibiaya) + '</span>' : "") + "</td>";
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