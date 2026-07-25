$(document).ready(function () {

    $("#btnSearch").on("click", function () {
        getallproductkfa();
    });

    $("#search").on("keypress", function (e) {
        if (e.which == 13) {
            e.preventDefault();
            getallproductkfa();
        }
    });

});

function getallproductkfa() {
    var keyword = $("#search").val();
    var type    = $("#type").val();

    $.ajax({
        url       : url + "index.php/satusehat/masterkfa/getallproductkfa",
        data      : {keyword:keyword,type:type},
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
        },
        success: function (response) {
            const result = Array.isArray(response.responResult) ? response.responResult : [];

            if (response.responCode !== "00") {
                $("#resultdatausers").html("");
                Swal.fire({
                    icon: "info",
                    title: "Information",
                    text: response.responDesc || "No user data found."
                });
                return;
            }

            let html = "";
            for (var i in result) {
                var zatAktif = "-";
                var kekuatan = "-";
                var tags     = "";

                if (result[i].active_ingredients && result[i].active_ingredients.length > 0) {
                    zatAktif  = result[i].active_ingredients[0].zat_aktif || "-";
                    kekuatan = result[i].active_ingredients[0].kekuatan_zat_aktif || "-";
                }

                if (result[i].tags && result[i].tags.length > 0) {
                    $.each(result[i].tags, function(j, tag){
                        if(tag.name){
                            tags += "<span class='badge badge-light-primary me-1 mb-1'>";
                            tags += "<i class='fas fa-tag me-1 text-primary'></i>" + tag.name;
                            tags += "</span>";
                        }
                    });
                }

                html += "<a href='#' class='list-group-item list-group-item-action py-3'>";
                    html += "<div class='d-flex align-items-start'>";

                        // ==========================
                        // Informasi Produk
                        // ==========================
                        html += "<div class='flex-grow-1'>";

                            // Nama Produk
                            html += "<div class='fw-bold fs-4 mb-3'>";
                                html += result[i].name;
                            html += "</div>";

                            // Zat Aktif
                            html += "<div class='small lh-lg mb-3'>";

                                html += "<i class='fas fa-capsules text-primary me-1'></i>";
                                html += "<strong>Zat Aktif</strong> : " + zatAktif;

                                html += "<span class='mx-3 text-muted'>|</span>";

                                html += "<i class='fas fa-weight-hanging text-danger me-1'></i>";
                                html += "<strong>Kekuatan</strong> : " + kekuatan;

                                html += "<span class='mx-3 text-muted'>|</span>";

                                html += "<i class='fas fa-syringe text-danger me-1'></i>";
                                html += "<strong>Sediaan</strong> : " + (result[i].dosage_form?.name || "-");

                                html += "<span class='mx-3 text-muted'>|</span>";

                                html += "<i class='fas fa-box text-info me-1'></i>";
                                html += "<strong>Kemasan</strong> : " + (result[i].uom?.name || "-");

                            html += "</div>";

                            // Produsen & Registrar
                            html += "<div class='small lh-lg mb-3'>";

                                html += "<i class='fas fa-industry text-primary me-1'></i>";
                                html += "<strong>Produsen</strong> : " + (result[i].manufacturer || "-");

                                html += "<span class='mx-3 text-muted'>|</span>";

                                html += "<i class='fas fa-building text-success me-1'></i>";
                                html += "<strong>Registrar</strong> : " + (result[i].registrar || "-");

                            html += "</div>";

                            // NIE, KFA & HS Code
                            html += "<div class='small lh-lg mb-0'>";

                                html += "<i class='fas fa-id-card text-danger me-1'></i>";
                                html += "<strong>NIE</strong> : " + (result[i].nie || "-");

                                html += "<span class='mx-3 text-muted'>|</span>";

                                html += "<i class='fas fa-barcode text-warning me-1'></i>";
                                html += "<strong>KFA</strong> : " + (result[i].kfa_code || "-");

                                html += "<span class='mx-3 text-muted'>|</span>";

                                html += "<i class='fas fa-globe-asia text-info me-1'></i>";
                                html += "<strong>HS Code</strong> : " + (result[i].farmalkes_hscode || "-");

                            html += "</div>";

                        html += "</div>";

                        // ==========================
                        // Harga HET
                        // ==========================
                        html += "<div class='ms-4 text-end d-flex flex-column justify-content-center align-items-end' style='min-width:260px;'>";

                            html += "<div class='fw-bold text-info fs-2 mb-1'>";
                                html += "Rp. " + (result[i].het_price ? Number(result[i].het_price).toLocaleString('id-ID') : "-");
                            html += "</div>";

                            html += "<small class='text-muted mb-3'>";
                                html += "Harga Eceran Tertinggi (HET)";
                            html += "</small>";

                            // Status
                            html += "<div>";

                                html += "<span class='badge bg-light-success text-success me-1'>";
                                html += (result[i].farmalkes_type?.name || "Obat");
                                html += "</span>";

                                html += "<span class='badge bg-light-primary text-primary me-1'>";
                                html += (result[i].state || "-").toUpperCase();
                                html += "</span>";

                                html += "<span class='badge bg-light-warning text-warning me-1'>";
                                html += (result[i].produksi_buatan || "-").toUpperCase();
                                html += "</span>";

                                html += "<span class='badge " + (result[i].generik ? "bg-light-info text-info" : "bg-light-danger text-danger") + "'>";
                                html += result[i].generik ? "GENERIK" : "NON GENERIK";
                                html += "</span>";

                            html += "</div>";

                            if(tags != ""){
                                html += "<div class='mt-2'>";
                                    html += tags;
                                html += "</div>";
                            }

                            html += "<div class='mt-3'>";
                                html += "<button type='button' ";
                                html += "class='btn btn-sm btn-primary btn-update' ";
                                html += "data-kfa='" + result[i].kfa_code + "' ";
                                html += "data-name=\"" + result[i].name.replace(/"/g, '&quot;') + "\">";
                                html += "<i class='fas fa-sync-alt me-2'></i>Update";
                                html += "</button>";
                            html += "</div>";

                        html += "</div>";

                    html += "</div>";
                html += "</a>";
            }

            $("#listkfa").html(html);
            
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

}