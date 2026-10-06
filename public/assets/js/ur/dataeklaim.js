dataraweklaim();

$('#selectperiode').on('change', function () {
    dataraweklaim();
});

function dataraweklaim(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "ur/dataeklaim/dataraweklaim",
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
                tableResult += "<td>"+(result[i].sep || "")+"</td>";
                tableResult += "<td>"+(result[i].nokartu || "")+"</td>";
                tableResult += "<td>"+(result[i].mrn || "")+"</td>";
                tableResult += "<td>"+(result[i].nama_pasien || "")+"</td>";
                tableResult += "<td>"+(result[i].addmissiondate || "")+"</td>";
                tableResult += "<td>"+(result[i].ptd == 1 ? "Outpatient" : "Inpatient")+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].tarif_rs) || "0")+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].total_tarif) || "0")+"</td>";
                tableResult += "<td class='text-end'>"+(todesimal(result[i].idrg_total_tarif) || "0")+"</td>";
                tableResult += "<td class='text-end'>";
                        tableResult += "<div class='btn-group'>";
                            tableResult += "<button type='button' class='btn btn-light-primary dropdown-toggle btn-sm' data-bs-toggle='dropdown'>Actions</button>";
                            tableResult += "<div class='dropdown-menu'>";
                                tableResult += btnaction;
                            tableResult += "</div>";
                        tableResult += "</div>";
                    tableResult += "</td>";
                tableResult += "</td>";
                tableResult += "</tr>";
            }

            $("#resultdataraweklaim").html(tableResult);

            initDataTable("#dataraweklaim_table","#searchtable",50);
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
