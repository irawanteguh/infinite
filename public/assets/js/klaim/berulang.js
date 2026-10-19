dataraweklaimdetail();

$('#selectperiode').on('change', function () {
    dataraweklaimdetail();
});

function dataraweklaimdetail(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "klaim/berulang/dataraweklaimdetail",
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

            const result       = Array.isArray(response.responseResult) ? response.responseResult : [];
            const bulanLengkap = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
            const namaBulan    = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
           
            const mapCase     = {};
            let   tableResult = "";

            for (var i in result) {
                const periode = String(result[i].periode ?? "").trim();

                mapCase[periode] = (mapCase[periode] || 0) + 1;

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

            const chartcase = bulanLengkap.map(function (bulan, index) {
                return {
                    periode: namaBulan[index],
                    value1 : mapCase[bulan] || 0
                };
            });

            renderchartarea("trencase",chartcase,"Discharge Date","Case",["Case"],["value1"],null,"","","",null);

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