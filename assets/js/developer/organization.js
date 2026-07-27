dataorganization();
toggleParentOrganization();

function toggleParentOrganization() {
    if ($("#modal_add_organization_type").val() == "Y") {
        $("#parentOrganizationContainer").hide();
        $("#modal_add_organization_header")
            .prop("required", false)
            .val("")
            .trigger("change");
    } else {
        $("#parentOrganizationContainer").show();
        $("#modal_add_organization_header").prop("required", true);
    }
}

$("#modal_add_organization_type").on("change", function () {
    toggleParentOrganization();
});

function dataorganization() {
    $.ajax({
        url: url + "index.php/developer/organization/dataorganization",
        type: "POST",
        dataType: "json",

        beforeSend: function () {

            Swal.fire({
                title            : "Processing",
                html             : "Loading data, please wait...",
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });

            if ($.fn.DataTable.isDataTable("#dataorganization_table")) {
                $("#dataorganization_table").DataTable().clear().destroy();
            }

            $("#resultdataorganization").empty();
        },

        success: function (response) {
        
            if (response.responCode !== "00") {
                $("#resultdataorganization").html("");
                Swal.fire({
                    icon: "info",
                    title: "Information",
                    text: response.responDesc || "No user data found."
                });

                return;
            }

            const result = Array.isArray(response.responResult) ? response.responResult : [];

            let tableresult = "";

            for (var i in result) {
                const iconrs           = `${url}assets/media/logos/${result[i].org_id}.png`;
                const iconrsDefault    = `${url}assets/media/logos/infinite.png`;

                const avatar           = `${url}assets/media/avatars/${result[i].created_by}.jpg`;
                const avatarDefault    = `${url}assets/media/avatars/blank.png`;
                const avatarpic        = `${url}assets/media/avatars/${result[i].user_id}.jpg`;
                const avatarDefaultpic = `${url}assets/media/avatars/blank.png`;

                let btnaction = "";

                tableresult += "<tr>";
                    tableresult += "<td class='text-start ps-4'>"+(parseInt(i) + 1)+"</td>";

                    tableresult += "<td>";
                        tableresult += "<div class='d-flex align-items-center'>";
                            tableresult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
                                tableresult += "<div class='symbol-label'>";
                                    tableresult += "<img src='" + iconrs + "' class='w-100' alt='" + (result[i].org_name || "") + "' onerror=\"this.onerror=null;this.src='" + iconrsDefault + "';\">";
                                tableresult += "</div>";
                            tableresult += "</div>";
                            tableresult += "<div class='d-flex flex-column align-items-start'>";
                                tableresult += "<span class='text-gray-800 fw-bold'>" + (result[i].org_name || "-") + "</span>";
                                tableresult += (result[i].holding === "Y"
                                    ? '<span class="badge badge-light-primary mt-1">Headquarters</span>'
                                    : '<span class="badge badge-light-info mt-1">Branch</span>');
                            tableresult += "</div>";
                        tableresult += "</div>";
                    tableresult += "</td>";

                    tableresult += "<td><div><a href='"+(result[i].website || "-")+"'target='_blank'>"+(result[i].website || "-")+"</a></div><div>"+(result[i].email || "-")+"</div></td>";
                    tableresult += "<td>"+(result[i].address || "-")+"</td>";

                    tableresult += "<td>";
                        tableresult += "<div class='d-flex align-items-center'>";
                            tableresult += "<div class='symbol symbol-circle symbol-35px overflow-hidden me-3'>";
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

            $("#resultdataorganization").html(tableresult);

            const table = $("#dataorganization_table").DataTable({
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

            initTableSearch("#dataorganization_table", "#searchtable");

        },

        complete: function () {
            Swal.close();
        },

        error: function () {
            Swal.fire({
                icon: "error",
                title: "Request Failed",
                text: "We were unable to process your request due to a server error. Please try again later. If the problem persists, contact your system administrator.",
                confirmButtonText: "OK"
            });
        }
    });

}

$(document).on("submit", "#formaddorganization", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'index.php/developer/organization/addorganization',
        data       : data,
        method     : "POST",
        dataType   : "JSON",
        cache      : false,
        processData: false,
        contentType: false,
        beforeSend : function () {
            Swal.fire({
                title            : 'Processing',
                html             : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
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

            $('#modal_add_organization').modal('hide');
		},
        complete: function () {
            Swal.close();
            dataorganization();
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
    return false;
});