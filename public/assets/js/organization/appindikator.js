datamasterindikator();

$('#selectperiode').on('change', function () {
    datamasterindikator();
});

function updateApproval(el) {
    const transaksiid = el.data("transaksiid");
    const status      = String(el.data("status"));

    const isApproval = status === '1';

    Swal.fire({
        title             : isApproval ? "Approve Indicator?" : "Decline Indicator?",
        html              : isApproval ? `Indikator ini akan disetujui.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`: `Indikator ini akan ditolak.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`,
        icon              : isApproval ? "question" : "warning",
        showCancelButton  : true,
        confirmButtonColor: isApproval ? "#50CD89" : "#d33",
        cancelButtonColor : "#6c757d",
        confirmButtonText : isApproval ? '<i class="bi bi-check-circle text-white"></i> Yes, Approve' : '<i class="bi bi-x-circle text-white"></i> Yes, Decline',
        cancelButtonText  : "Cancel",
        reverseButtons    : true,
        timer             : 10000,
        timerProgressBar  : true
    }).then(function(result) {

        if (result.isConfirmed) {
            $.ajax({
                url     : url + "organization/appindikator/updateapproval",
                type    : "POST",
                data    : {transaksiid: transaksiid,status: status},
                dataType: "JSON",
                success: function(response) {
                    if (response.responseCode === "00") {
                        Swal.fire({
                            title: "Success",
                            text: response.responseDesc,
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false,
                            didClose: function() {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire("Error", response.responseDesc, "error");
                    }
                },
                error: function(xhr) {
                    Swal.fire("Error", "Failed to update indicator status.", "error");
                }
            });
        }
    });
}

function datamasterindikator() {
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url       : url+"organization/appindikator/datamasterindikator",
        data      : {selectperiode:selectperiode},
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

            $("#resultdatamasterindikator").empty();
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
                const avatarvalidation = `${url}assets/media/avatars/${result[i].approval_by}.jpg`;
                const avatar           = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefault    = `${url}assets/media/avatars/blank.png`;

                var getvariabel = "";
                    getvariabel += " data-transaksiid='" + result[i].transaksi_id + "'";


                let btnaction = "";

                if (result[i].statusidindikatorunit === '0') {
                    btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm text-success' "+getvariabel+" data-status='1' onclick='updateApproval($(this));'><i class='bi bi-check-circle text-success me-4'></i>Approval</a>";
                    btnaction += "<a href='javascript:void(0);' class='dropdown-item btn btn-sm text-danger' "+getvariabel+" data-status='9' onclick='updateApproval($(this));'><i class='bi bi-x-circle text-danger me-4'></i>Decline</a>";
                }

                tableResult += "<tr>";

                    tableResult += "<td class='ps-4'>"+(parseInt(i) + 1)+"</td>";
                    tableResult += "<td>"+(result[i].tahun || "")+"</td>";
                    tableResult += "<td>"+(result[i].department || "")+"</td>";
                    tableResult += "<td>"+(result[i].indikator_code || "-")+"</td>";

                    tableResult += "<td>";
                        tableResult += "<div>"+(result[i].indikator || "-")+"</div>";
                        tableResult += "<div class='fst-italic'>"+(formathtml(result[i].definisi) || "-")+"</div>";
                        tableResult += "<div>";
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_keselamatan, "Keselamatan Pasien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_waktu, "Tepat Waktu", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_efektif, "Efektif", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_efesien, "Efisien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_pasien, "Berorientasi Pada Pasien", "info");
                        tableResult += badgeDimensiMutu(result[i].dimensi_mutu_integrasi, "Integrasi", "info");
                        tableResult += "</div>";
                        tableResult += result[i].jenis ? "<div><span class='badge badge-light-primary mt-4'>" + result[i].jenis + "</span></div>" : "";
                    tableResult += "</td>";

                    // tableResult += "<td>";

                    //     tableResult += "<div>";
                    //         tableResult += "<span class='fw-bold text-primary'>Dasar Pemikiran</span><br>";
                    //         tableResult += (result[i].dasar_pemikiran || "-");
                    //     tableResult += "</div>";

                    //     tableResult += "<hr class='my-2'>";

                    //     tableResult += "<div>";
                    //         tableResult += "<span class='fw-bold text-success'>Tujuan</span><br>";
                    //         tableResult += (result[i].tujuan || "-");
                    //     tableResult += "</div>";

                    // tableResult += "</td>";

                    // // Numerator Denominator
                    // tableResult += "<td>";

                    //     tableResult += "<div>";
                    //         tableResult += "<span class='fw-bold text-primary'>Numerator</span><br>";
                    //         tableResult += (result[i].numerator || "-");
                    //     tableResult += "</div>";

                    //     tableResult += "<hr class='my-2'>";

                    //     tableResult += "<div>";
                    //         tableResult += "<span class='fw-bold text-success'>Denominator</span><br>";
                    //         tableResult += (result[i].denumerator || "-");
                    //     tableResult += "</div>";

                    // tableResult += "</td>";

                    // tableResult += "<td>";

                    //     tableResult += "<div>";
                    //         tableResult += "<span class='fw-bold text-primary'>Inklusi</span><br>";
                    //         tableResult += (result[i].inklusi || "-");
                    //     tableResult += "</div>";

                    //     tableResult += "<hr class='my-2'>";

                    //     tableResult += "<div>";
                    //         tableResult += "<span class='fw-bold text-success'>Eksklusi</span><br>";
                    //         tableResult += (result[i].eksklusi || "-");
                    //     tableResult += "</div>";

                    // tableResult += "</td>";

                    // tableResult += "<td>"+(result[i].satuan || "")+"</td>";

                    // Status
                    tableResult += "<td>" + (result[i].statusidindikatorunit == "0" ? "<span class='badge badge-light-info'>Submitted</span>" : result[i].statusidindikatorunit == "1" ? "<span class='badge badge-light-success'>Approved</span>" : result[i].statusidindikatorunit == "9" ? "<span class='badge badge-light-danger'>Declined</span>" : "<span class='badge badge-light-secondary'>Unknown</span>") + "</td>";

                    tableResult += "<td>";
                        tableResult += "<div class='d-flex align-items-center'>";
                            tableResult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                                tableResult += "<div class='symbol-label'>";
                                    tableResult += "<img ";
                                    tableResult += "src='" + avatarvalidation + "' ";
                                    tableResult += "class='w-100' ";
                                    tableResult += "alt='" + (result[i].divalidasioleh || "") + "' ";
                                    tableResult += "onerror=\"this.onerror=null;this.src='" + avatarDefault + "';\">";
                                tableResult += "</div>";
                            tableResult += "</div>";
                            tableResult += "<div class='d-flex flex-column'>";
                                tableResult += "<span class='text-gray-800 fw-bold'>";
                                tableResult += (result[i].divalidasioleh || "-");
                                tableResult += "</span>";
                                tableResult += "<span class='text-muted'>";
                                tableResult += (result[i].approvaldate || "-");
                                tableResult += "</span>";
                            tableResult += "</div>";
                        tableResult += "</div>";
                    tableResult += "</td>";

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

                    // Action
                    tableResult += "<td class='text-end'>";
                        tableResult += "<div class='btn-group'>";
                            tableResult += "<button type='button' class='btn btn-light-primary btn-sm dropdown-toggle' data-bs-toggle='dropdown'> Actions</button>";
                            tableResult += "<div class='dropdown-menu dropdown-menu-end'>";
                                tableResult += btnaction;
                            tableResult += "</div>";
                        tableResult += "</div>";
                    tableResult += "</td>";
                
                tableResult += "</tr>";
            }

            $("#resultdatamasterindikator").html(tableResult);

            initDataTable("#datamasterindikator_table","#searchtable",10);
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