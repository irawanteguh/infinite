masterobat();

function masterobat() {
    $.ajax({
        url       : url+"farmasi/masterobat/masterobat",
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

            $("#resultdatapasientransit").empty();
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
                const avatar           = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefault    = `${url}assets/media/avatars/blank.png`;

                tableResult += "<tr>";
                tableResult += "<td class='ps-4'>" + (parseInt(i)+1) + "</td>";
                tableResult += "<td>"+(result[i].name||"")+"</td>";
                tableResult += "<td>"+(result[i].kategori_id||"")+"</td>";
                tableResult += "<td>"+(result[i].distributor||"")+"</td>";
                tableResult += "<td class='text-end'>"+todesimal(result[i].hrg_distributor||0)+"</td>";
                tableResult += "<td class='text-end'>"+todesimal(result[i].disc||0)+"</td>";
                tableResult += "<td class='text-end'>"+todesimal(result[i].ppn||0)+"</td>";
                tableResult += "<td class='text-end'>"+todesimal(result[i].hrg_total||0)+"</td>";
                tableResult += "<td class='text-end'>"+todesimal(result[i].hrg_asuransi||0)+"</td>";
                tableResult += "<td class='text-end'>"+todesimal(result[i].hrg_umum||0)+"</td>";
                tableResult += "<td class='text-end'>"+todesimal(result[i].total_pemakaian||0)+"</td>";

                tableResult += "<td>";
                    tableResult += "<div class='d-flex align-items-center'>";
                        tableResult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                            tableResult += "<div class='symbol-label'>";
                                tableResult += "<img ";
                                tableResult += "src='" + avatar + "' ";
                                tableResult += "class='w-100' ";
                                tableResult += "alt='" + (result[i].dibuatoleh || "") + "' ";
                                tableResult += "onerror=\"this.onerror=null;this.src='" + avatarDefault + "';\">";
                            tableResult += "</div>";
                        tableResult += "</div>";
                        tableResult += "<div class='d-flex flex-column'>";
                            tableResult += "<span class='text-gray-800 fw-bold'>";
                            tableResult += (result[i].dibuatoleh || "-");
                            tableResult += "</span>";
                            tableResult += "<span class='text-muted'>";
                            tableResult += (result[i].dibuattgl || "-");
                            tableResult += "</span>";
                        tableResult += "</div>";
                    tableResult += "</div>";
                tableResult += "</td>";

                tableResult += "</tr>";
            }

            $("#resultmasterobat").html(tableResult);

            initDataTable("#masterobat_table","#searchtable",100);
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