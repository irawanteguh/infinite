masterobat();

function masterobat() {
    showDataLoading('Memuat data', 'Daftar obat sedang disiapkan.');
    $.ajax({
        url: url + "farmasi/masterobat/masterobat",
        type: "POST",
        dataType: "JSON"
    }).done(function (response) {
        if (response.responseCode !== "00") {
            $("#resultmasterobat").empty();
            initDataTable("#masterobat_table", "#searchtable", 100);
            Swal.fire({
                icon             : "info",
                title            : "Data obat belum tersedia",
                text             : "Belum ada obat aktif pada daftar.",
                showConfirmButton: false,
                timer            : 2000
            });
            return;
        }

        const result = Array.isArray(response.responseResult) ? response.responseResult : [];

        let tableResult = "";

        for (var i in result) {
            const avatarDefault = `${url}assets/media/avatars/blank.png`;
            const avatar = result[i].created_by
                ? `${url}assets/media/avatars/${encodeURIComponent(result[i].created_by)}.jpg`
                : avatarDefault;

            tableResult += "<tr>";
            tableResult += "<td class='ps-4'>" + (parseInt(i)+1) + "</td>";
            tableResult += "<td>"+escapeHtml(result[i].name || "")+"</td>";
            tableResult += "<td>"+escapeHtml(result[i].kategori_id || "")+"</td>";
            tableResult += "<td>"+escapeHtml(result[i].distributor || "")+"</td>";
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
                            tableResult += "src='" + escapeHtml(avatar) + "' ";
                            tableResult += "class='w-100' ";
                            tableResult += "alt='" + escapeHtml(result[i].dibuatoleh || "") + "' ";
                            tableResult += "onerror=\"this.onerror=null;this.src='" + avatarDefault + "';\">";
                        tableResult += "</div>";
                    tableResult += "</div>";
                    tableResult += "<div class='d-flex flex-column'>";
                        tableResult += "<span class='text-gray-800 fw-bold'>";
                        tableResult += escapeHtml(result[i].dibuatoleh || "-");
                        tableResult += "</span>";
                        tableResult += "<span class='text-muted'>";
                        tableResult += escapeHtml(result[i].dibuattgl || "-");
                        tableResult += "</span>";
                    tableResult += "</div>";
                tableResult += "</div>";
            tableResult += "</td>";

            tableResult += "</tr>";
        }

        $("#resultmasterobat").html(tableResult);

        initDataTable("#masterobat_table","#searchtable",100);
    }).fail(function () {
        Swal.fire({
            icon: "error",
            title: "Daftar obat gagal dimuat",
            text: "Muat ulang halaman untuk mencoba kembali.",
            confirmButtonText: "OK"
        });
    });
}
