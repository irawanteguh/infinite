let globalrawdata  = [];

load();

$('#selectperiode').on('change', function () {
    load();
});

$("#btndownloaddataraw_table").on("click", function () {
    exportToExcel(
        null,
        null,
        "Quality_Cost_DPJP.xlsx",
        {
            multiSheet: [
                {
                    name: "DPJP",
                    data: globalrawdata,
                    formatter: (item, index) => {
                        const jmlpasienrawatinap  = Number(item.jmlpasienrawatinap ?? 0);
                        const jmlpasienrawatjalan = Number(item.jmlpasienrawatjalan ?? 0);
                        const jmlpasientotal      = Number(item.jmlpasientotal ?? 0);
                        const tarifrstotal        = Number(item.tarifrstotal ?? 0);
                        const tarifklaimtotal     = Number(item.tarifklaimtotal ?? 0);
                        const selisihtotal        = Number(item.selisihtotal ?? 0);
                        const efisiensi_persen    = Number(item.efisiensi_persen ?? 0);
                        const avg_tarif_rs        = Number(item.avg_tarif_rs ?? 0);
                        const avg_tarif_klaim     = Number(item.avg_tarif_klaim ?? 0);

                        return {
                            "No"                 : index + 1,
                            "Attending Physician": item.dpjp ?? "",
                            "Inpatient"          : jmlpasienrawatinap,
                            "Outpatient"         : jmlpasienrawatjalan,
                            "Total Patients"     : jmlpasientotal,
                            "Hospital Cost"      : tarifrstotal,
                            "INA-CBG"            : tarifklaimtotal,
                            "Difference"         : selisihtotal,
                            "Efficiency (%)"     : efisiensi_persen,
                            "Avg. Hospital Cost" : avg_tarif_rs,
                            "Avg. INA-CBG"       : avg_tarif_klaim
                        };

                    }
                }
            ]
        }
    );

});
function load(){
    rawdata();
};

function rawdata(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "ur/biaya/rawdata",
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

            $("#resultdataraw").empty();
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
            globalrawdata = result;

            let tableResult = "";

            for (var i in result) {

                tableResult += "<tr>";
                tableResult += "<td class='text-start ps-4'>"+(parseInt(i) + 1)+"</td>";
                tableResult += "<td>" + (result[i].dpjp || "") + "</td>";
                tableResult += "<td class='text-center'>" + (result[i].jmlpasienrawatinap || 0) + "</td>";
                tableResult += "<td class='text-center'>" + (result[i].jmlpasienrawatjalan || 0) + "</td>";
                tableResult += "<td class='text-center'>" + (result[i].jmlpasientotal || 0) + "</td>";
                tableResult += "<td class='text-end'>" + todesimal(result[i].tarifrstotal) + "</td>";
                tableResult += "<td class='text-end'>" + todesimal(result[i].tarifklaimtotal) + "</td>";
                tableResult += "<td class='text-end'>" + todesimal(result[i].selisihtotal) + "</td>";
                tableResult += "<td class='text-end'>" + (result[i].efisiensi_persen || 0) + "%</td>";
                tableResult += "<td class='text-end'>" + todesimal(result[i].avg_tarif_rs) + "</td>";
                tableResult += "<td class='text-end pe-4'>" + todesimal(result[i].avg_tarif_klaim) + "</td>";
                tableResult += "</tr>";
            }

            $("#resultdataraw").html(tableResult);

            initDataTable("#dataraw_table","#searchtable",100);
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
