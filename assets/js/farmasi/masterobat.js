datamasterobat();

function datamasterobat() {
    $.ajax({
        url       : url + "index.php/farmasi/masterobat/datamasterobat",
        type      : "POST",
        dataType  : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : "Processing",
                html             : "Loading data, please wait...",
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });

            if ($.fn.DataTable.isDataTable("#datamasterobat_table")) {
                $("#datamasterobat_table").DataTable().clear().destroy();
            }

            $("#resultdatamasterobat").empty();

        },

        success: function (response) {
            const result = Array.isArray(response.responResult) ? response.responResult : [];

            if (response.responCode !== "00") {
                Swal.fire({
                    icon : "info",
                    title: "Information",
                    text : response.responDesc
                });

                return;
            }

            let tableresult = "";

            for (var i in result) {
                
                const avatar        = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefault = `${url}assets/media/avatars/blank.png`;

                let status = result[i].active == "1" ? "<span class='badge badge-light-success'>Active</span>" : "<span class='badge badge-light-danger'>Inactive</span>";

                let btnaction = "";
                // btnaction += "<a class='dropdown-item' href='#' onclick=\"editobat('" + result[i].obat_id + "')\">";
                // btnaction += "<i class='fas fa-edit me-2 text-primary'></i>Edit";
                // btnaction += "</a>";

                // btnaction += "<a class='dropdown-item' href='#' onclick=\"deleteobat('" + result[i].obat_id + "')\">";
                // btnaction += "<i class='fas fa-trash me-2 text-danger'></i>Delete";
                // btnaction += "</a>";

                tableresult += "<tr>";

                tableresult += "<td class='ps-4'>"+(parseInt(i) + 1)+"</td>";
                tableresult += "<td>" + (result[i].kfa_id || "-") + "</td>";
                tableresult += "<td>" + (result[i].name || "-") + "</td>";
                tableresult += "<td>" + (result[i].produsen || "-") + "</td>";
                tableresult += "<td class='text-end'>" + Number(result[i].het || 0).toLocaleString("id-ID") + "</td>";
                tableresult += "<td class='text-end'>" + Number(result[i].hrg_distributor || 0).toLocaleString("id-ID") + "</td>";
                tableresult += "<td class='text-center'>" + (result[i].disc || 0) + "%</td>";
                tableresult += "<td class='text-center'>" + (result[i].ppn || 0) + "%</td>";
                tableresult += "<td class='text-end fw-bold'>" + Number(result[i].hrg_total || 0).toLocaleString("id-ID") + "</td>";
                tableresult += "<td>" + status + "</td>";
                
                tableresult += "<td>";
                    tableresult += "<div class='d-flex align-items-center'>";
                        tableresult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                            tableresult += "<div class='symbol-label'>";
                                tableresult += "<img ";
                                tableresult += "src='" + avatar + "' ";
                                tableresult += "class='w-100' ";
                                tableresult += "alt='" + (result[i].dibuatoleh || "") + "' ";
                                tableresult += "onerror=\"this.onerror=null;this.src='" + avatarDefault + "';\">";
                            tableresult += "</div>";
                        tableresult += "</div>";
                        tableresult += "<div class='d-flex flex-column'>";
                            tableresult += "<span class='text-gray-800 fw-bold'>";
                            tableresult += (result[i].dibuatoleh || "-");
                            tableresult += "</span>";
                            tableresult += "<span class='text-muted'>";
                            tableresult += (result[i].dibuattgl || "-");
                            tableresult += "</span>";
                        tableresult += "</div>";
                    tableresult += "</div>";
                tableresult += "</td>";

                // Action
                tableresult += "<td class='text-end'>";
                tableresult += "<div class='btn-group'>";
                tableresult += "<button type='button' class='btn btn-light-primary btn-sm dropdown-toggle' data-bs-toggle='dropdown'>";
                tableresult += "Actions";
                tableresult += "</button>";
                tableresult += "<div class='dropdown-menu dropdown-menu-end'>";
                tableresult += btnaction;
                tableresult += "</div>";
                tableresult += "</div>";
                tableresult += "</td>";

                tableresult += "</tr>";

            }

            $("#resultdatamasterobat").html(tableresult);

            const table = $("#datamasterobat_table").DataTable({
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

            // Search
            initTableSearch("#datamasterobat_table", "#searchtable");
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

    function badgeMutu(status, text, color) {
        if (status !== "Y") return "";
        return "<span class='badge badge-light-" + color + " me-1 mb-1'>" + text + "</span>";
    }

}