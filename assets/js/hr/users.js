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

                const avatar = `${url}assets/media/avatars/${result[i].user_id}.jpg`;
                const avatarDefault = `${url}assets/media/avatars/blank.png`;

                tableresult += "<tr>";

                tableresult += "<td class='ps-4'>" + (parseInt(i) + 1) + "</td>";

                tableresult += "<td>" + (result[i].username || "-") + "</td>";

                tableresult += `
                    <td class="d-flex align-items-center">

                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                            <div class="symbol-label">
                                <img src="${avatar}"
                                    class="w-100"
                                    alt="${result[i].name || ''}"
                                    onerror="this.onerror=null;this.src='${avatarDefault}';">
                            </div>
                        </div>

                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-bold mb-1">
                                ${result[i].name || "-"}
                            </span>
                            <span class="text-muted">
                                ${result[i].email || "-"}
                            </span>
                        </div>

                    </td>
                `;

                tableresult += "<td>" + (result[i].email || "-") + "</td>";

                tableresult += `
                    <td class="text-end">

                        <button class="btn btn-icon btn-sm btn-light-primary me-1" title="Edit">
                            <i class="ki-outline ki-pencil fs-5"></i>
                        </button>

                        <button class="btn btn-icon btn-sm btn-light-danger" title="Delete">
                            <i class="ki-outline ki-trash fs-5"></i>
                        </button>

                    </td>
                `;

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