let table = null;

// Load pertama
datausers();

// Reload setelah modal ditutup
$('#modal_add_user').on('hidden.bs.modal', function () {
    datausers();
});

function datausers() {

    $.ajax({
        url: url + "index.php/hr/users/datausers",
        type: "POST",
        dataType: "json",

        beforeSend: function () {

            Swal.fire({
                title: "Processing",
                html: "Loading data, please wait...",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            // Destroy DataTable jika sudah ada
            if ($.fn.DataTable.isDataTable('#datausers_table')) {
                $('#datausers_table').DataTable().destroy();
            }

            $("#resultdatausers").empty();
        },

        success: function (response) {

            Swal.close();

            const result = Array.isArray(response.responResult)
                ? response.responResult
                : [];

            if (response.responCode !== "00") {

                $("#resultdatausers").html("");

                Swal.fire({
                    icon: "info",
                    title: "Information",
                    text: response.responDesc || "No user data found."
                });

                return;
            }

            let tableresult = "";

            for (var i in result) {

                const avatar                 = `${url}assets/media/avatars/${result[i].user_id}.jpg`;
                const avatarDefault          = `${url}assets/media/avatars/blank.png`;
                const avatarcreatedby        = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefaultcreatedby = `${url}assets/media/avatars/blank.png`;

                let btnaction = "";

                btnaction += "<a href='#' class='dropdown-item'><i class='ki-outline ki-eye fs-6 me-2'></i>Detail</a>";
                btnaction += "<a href='#' class='dropdown-item'><i class='ki-outline ki-pencil fs-6 me-2'></i>Edit</a>";
                btnaction += "<a href='#' class='dropdown-item text-danger'><i class='ki-outline ki-trash fs-6 me-2'></i>Delete</a>";

                tableresult += "<tr>";
                tableresult += "<td class='ps-4'>" + (parseInt(i) + 1) + "</td>";
                tableresult += "<td>" + (result[i].username || "-") + "</td>";

                tableresult += "<td>";
                    tableresult += "<div class='d-flex align-items-center'>";
                        tableresult += "<div class='symbol symbol-circle symbol-50px overflow-hidden me-3'>";
                            tableresult += "<div class='symbol-label'>";
                                tableresult += "<img ";
                                tableresult += "src='" + avatar + "' ";
                                tableresult += "class='w-100' ";
                                tableresult += "alt='" + (result[i].name || "") + "' ";
                                tableresult += "onerror=\"this.onerror=null;this.src='" + avatarDefault + "';\">";
                            tableresult += "</div>";
                        tableresult += "</div>";
                        tableresult += "<div class='d-flex flex-column'>";
                            tableresult += "<span class='text-gray-800 fw-bold'>";
                            tableresult += (result[i].name || "-");
                            tableresult += "</span>";
                            tableresult += "<span class='text-muted'>";
                            tableresult += (result[i].email || "-");
                            tableresult += "</span>";
                        tableresult += "</div>";
                    tableresult += "</div>";
                tableresult += "</td>";

                tableresult += "<td>";
                if (result[i].active == "1") {
                    tableresult += "<span class='badge badge-light-success'>";
                    tableresult += "Active";
                    tableresult += "</span>";
                } else {
                    tableresult += "<span class='badge badge-light-danger'>";
                    tableresult += "Inactive";
                    tableresult += "</span>";
                }
                tableresult += "</td>";

                tableresult += "<td>";
                    tableresult += "<div class='d-flex align-items-center'>";
                        tableresult += "<div class='symbol symbol-circle symbol-50px overflow-hidden me-3'>";
                            tableresult += "<div class='symbol-label'>";
                                tableresult += "<img ";
                                tableresult += "src='" + avatarcreatedby + "' ";
                                tableresult += "class='w-100' ";
                                tableresult += "alt='" + (result[i].dibuatoleh || "") + "' ";
                                tableresult += "onerror=\"this.onerror=null;this.src='" + avatarDefaultcreatedby + "';\">";
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

                tableresult += "<td class='text-end pe-4'>";
                    tableresult += "<div class='btn-group'>";
                        tableresult += "<button ";
                        tableresult += "type='button' ";
                        tableresult += "class='btn btn-light-primary btn-sm dropdown-toggle' ";
                        tableresult += "data-bs-toggle='dropdown'>";
                        tableresult += "Actions";
                        tableresult += "</button>";
                        tableresult += "<div class='dropdown-menu dropdown-menu-end'>";
                        tableresult += btnaction;
                        tableresult += "</div>";
                    tableresult += "</div>";
                tableresult += "</td>";
                tableresult += "</tr>";

                tableresult += "</tr>";
            }

            // Isi tbody
            $("#resultdatausers").html(tableresult);

            // Inisialisasi ulang DataTable
            table = $('#datausers_table').DataTable({
                responsive: true,
                pageLength: 10,
                order: [],
                destroy: true,
                autoWidth: false,
                language: {
                    emptyTable: "No data available"
                }
            });

            // Aktifkan search
            initTableSearch('#datausers_table', '#searchtable');

        },

        complete: function () {
            Swal.close();
        },

        error: function () {

            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Unable to retrieve user data."
            });

        }
    });

}