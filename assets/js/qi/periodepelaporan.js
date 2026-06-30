let table = null;

dataperiodepelaporan();

function dataperiodepelaporan() {
    $.ajax({
        url: url + "index.php/qi/periodepelaporan/dataperiodepelaporan",
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

            if ($.fn.DataTable.isDataTable('#dataperiodepelaporan_table')) {
                $('#dataperiodepelaporan_table').DataTable().destroy();
            }

            $("#resultperiodepelaporan").empty();
        },

        success: function (response) {
            Swal.close();
            const result = Array.isArray(response.responResult) ? response.responResult : [];

            if (response.responCode !== "00") {
                $("#resultdatadepartment").html("");
                Swal.fire({
                    icon: "info",
                    title: "Information",
                    text: response.responDesc || "No user data found."
                });

                return;
            }

            let tableresult = "";

            for (var i in result) {

                const avatar           = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefault    = `${url}assets/media/avatars/blank.png`;
                const avatarpic        = `${url}assets/media/avatars/${result[i].user_id}.jpg`;
                const avatarDefaultpic = `${url}assets/media/avatars/blank.png`;

                let btnaction = "";

                tableresult += "<tr>";
                tableresult += "<td class='text-start ps-4'>"+(parseInt(i) + 1)+"</td>";
                tableresult += "<td>"+(result[i].tahun || "-")+"</td>";

                tableresult += "<td>";
                    tableresult += "<div class='d-flex align-items-center'>";
                        tableresult += "<div class='symbol symbol-circle symbol-50px overflow-hidden me-3'>";
                            tableresult += "<div class='symbol-label'>";
                                tableresult += "<img ";
                                tableresult += "src='" + avatarpic + "' ";
                                tableresult += "class='w-100' ";
                                tableresult += "alt='" + (result[i].pic || "") + "' ";
                                tableresult += "onerror=\"this.onerror=null;this.src='" + avatarDefaultpic + "';\">";
                            tableresult += "</div>";
                        tableresult += "</div>";
                        tableresult += "<div class='d-flex flex-column'>";
                            tableresult += "<span class='text-gray-800 fw-bold'>";
                            tableresult += (result[i].pic || "-");
                            tableresult += "</span>";
                            tableresult += "<span class='text-muted'>";
                            tableresult += (result[i].emailpic || "-");
                            tableresult += "</span>";
                        tableresult += "</div>";
                    tableresult += "</div>";
                tableresult += "</td>";

                tableresult += "<td>"+(result[i].active == 1 ? '<span class=\"badge badge-light-success\">Active</span>' : '<span class=\"badge badge-light-danger\">Inactive</span>')+"</td>";
                
                tableresult += "<td>";
                    tableresult += "<div class='d-flex align-items-center'>";
                        tableresult += "<div class='symbol symbol-circle symbol-50px overflow-hidden me-3'>";
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

                tableresult += "<td class='text-end'>";
                    tableresult += "<div class='btn-group'>";
                        tableresult += "<button type='button' class='btn btn-light-primary dropdown-toggle btn-sm' data-bs-toggle='dropdown'>Actions</button>";
                        tableresult += "<div class='dropdown-menu'>";
                            tableresult += btnaction;
                        tableresult += "</div>";
                    tableresult += "</div>";
                tableresult += "</td>";
                tableresult += "</tr>";
            }

            $("#resultperiodepelaporan").html(tableresult);
            table = $('#dataperiodepelaporan_table').DataTable({
                responsive: true,
                pageLength: 10,
                order: [],
                destroy: true,
                autoWidth: false,
                language: {
                    emptyTable: "No data available"
                }
            });

            initTableSearch('#dataperiodepelaporan_table', '#searchtable');

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

$(document).on("submit", "#formadddepartment", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'index.php/organization/department/adddepartment',
        data       : data,
        method     : "POST",
        dataType   : "JSON",
        cache      : false,
        processData: false,
        contentType: false,
        beforeSend : function () {
            Swal.fire({
                title: 'Processing',
                html : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });
        },
		success: function (response) {
            if (response.responCode !== "00") {
                Swal.fire({
                    title            : "<h1 class='font-weight-bold'>For Your Information</h1>",
                    html             : "<b>"+data.responDesc+"</b>",
                    icon             : data.responHead,
                    confirmButtonText: 'Please Try Again',
                    customClass      : {confirmButton: 'btn btn-danger'},
                    timerProgressBar : true,
                    timer            : 5000,
                    showClass        : {popup: "animate__animated animate__fadeInUp animate__faster"},
                    hideClass        : {popup: "animate__animated animate__fadeOutDown animate__faster"}
                });
                return;
            }

            $('#modal_add_department').modal('hide');
            Swal.close();
		},
        complete: function () {
            Swal.close();
            datadepartment();
		},
        error: function(xhr, status, error) {
            Swal.fire({
                icon : 'error',
                title: 'System Error',
                text : 'Failed to retrieve emergency visit data.'
            });
		}
	});
    return false;
});