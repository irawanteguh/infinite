function contoh() {
    $.ajax({
        url       : url+"developer/masterjenisindikator/datamasterjenisindikator",
        type      : "POST",
        dataType  : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : "Processing",
                html             : "Please wait while the system displays the requested data.",
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });

            $("#resultdatamasterjenisindikator").empty();
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

            let tableResult = "";

            for (var i in result) {
                const avatar        = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefault = `${url}assets/media/avatars/blank.png`;

                var getvariabel = "";
                    getvariabel += " data-indikatorid='" + result[i].indikator_id + "'";
                    getvariabel += " data-indikator=\"" + result[i].indikator + "\"";
                    getvariabel += " data-dasar_pemikiran=\"" + result[i].dasar_pemikiran + "\"";
                    getvariabel += " data-tujuan=\"" + result[i].tujuan + "\"";
                    getvariabel += " data-definisi=\"" + result[i].definisi + "\"";
                    getvariabel += " data-numerator=\"" + result[i].numerator + "\"";
                    getvariabel += " data-denumerator=\"" + result[i].denumerator + "\"";
                    getvariabel += " data-formula=\"" + result[i].formula + "\"";
                    getvariabel += " data-populasi=\"" + result[i].populasi + "\"";
                    getvariabel += " data-metode=\"" + result[i].metode + "\"";
                    getvariabel += " data-inklusi=\"" + result[i].inklusi + "\"";
                    getvariabel += " data-eksklusi=\"" + result[i].eksklusi + "\"";
                    getvariabel += " data-instrument=\"" + result[i].instrument + "\"";
                    getvariabel += " data-satuan_id=\"" + result[i].satuan_id + "\"";
                    getvariabel += " data-frekuensi_id=\"" + result[i].frekuensi_id + "\"";
                    getvariabel += " data-sumber_id=\"" + result[i].sumber_id + "\"";
                    getvariabel += " data-donabedian_id=\"" + result[i].donabedian_id + "\"";
                    getvariabel += " data-target_capaian=\"" + result[i].target_capaian + "\"";
                    getvariabel += " data-benchmark_id=\"" + result[i].benchmark_id + "\"";
                    getvariabel += " data-active=\"" + result[i].active + "\"";
                    getvariabel += " data-dimensi_keselamatan=\"" + result[i].dimensi_mutu_keselamatan + "\"";
                    getvariabel += " data-dimensi_waktu=\"" + result[i].dimensi_mutu_waktu + "\"";
                    getvariabel += " data-dimensi_efektif=\"" + result[i].dimensi_mutu_efektif + "\"";
                    getvariabel += " data-dimensi_efesien=\"" + result[i].dimensi_mutu_efesien + "\"";
                    getvariabel += " data-dimensi_pasien=\"" + result[i].dimensi_mutu_pasien + "\"";
                    getvariabel += " data-dimensi_integrasi=\"" + result[i].dimensi_mutu_integrasi + "\"";


                let btnaction = "";

                btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm "+(result[i].active === '1' ? 'text-danger' : 'text-success')+"' "+getvariabel+" data-active='"+result[i].active+"' onclick='updateActivation($(this));'><i class='bi "+(result[i].active === '1' ? 'bi-x-circle text-danger' : 'bi-check-circle text-success')+" me-4'></i>"+(result[i].active === '1' ? 'Non Active' : 'Active')+"</a>";
                btnaction += "<a class='dropdown-item btn btn-sm text-primary' data-bs-toggle='modal' data-bs-target='#modal_edit_masterindikator' "+getvariabel+" onclick='getdata($(this));'><i class='bi bi-pencil text-primary me-4'></i>Edit</a>";

            }

            $("#resultdatamasterjenisindikator").html(tableResult);

            initDataTable("#datamasterjenisindikator_table","#searchtable",10);
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