$(document).on("submit", "#formadduser", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'index.php/hr/users/adduser',
        data       : data,
        method     : "POST",
        dataType   : "JSON",
        cache      : false,
        processData: false,
        contentType: false,
        beforeSend : function () {
            showProcessing("Saving Data", "Please wait while the system saves the data.");
        },
		success: function (response) {
            showResponse(response);

            if (response.responCode === "00") {
                window.location.reload(true);
            }
		},
        complete: function () {
            Swal.close();
		},
        error: function(xhr, status, error) {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "We were unable to process your request due to a server error. Please try again later. If the problem persists, contact your system administrator.",
                timer            : 5000,
                timerProgressBar : true,
                showConfirmButton: false
            });
		}
	});
    return false;
});

$(document).on("submit", "#formadduserroot", function (e) {
	e.preventDefault();
	var data = new  FormData(this);

    var orgid   = $("#modal_add_user_orgid").val();
    var groupid = $("#modal_add_user_orgid option:selected").data("groupid");

    data.set("modal_add_user_orgid", orgid);
    data.append("modal_add_user_groupid", groupid);

	$.ajax({
        url        : url+'index.php/developer/users/adduser',
        data       : data,
        method     : "POST",
        dataType   : "JSON",
        cache      : false,
        processData: false,
        contentType: false,
        beforeSend : function () {
            showProcessing("Saving Data", "Please wait while the system saves the data.");
        },
		success: function (response) {
            showResponse(response);

            if (response.responCode === "00") {
                window.location.reload(true);
            }
		},
        complete: function () {
            Swal.close();
		},
        error: function() {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "We were unable to process your request due to a server error. Please try again later. If the problem persists, contact your system administrator.",
                timer            : 5000,
                timerProgressBar : true,
                showConfirmButton: false
            });
		}
	});
    return false;
});

$(document).on("submit", "#formedituser", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'index.php/hr/users/edituser',
        data       : data,
        method     : "POST",
        dataType   : "JSON",
        cache      : false,
        processData: false,
        contentType: false,
        beforeSend : function () {
            showProcessing("Updating Data", "Please wait while the system updates the data.");
        },
		success: function (response) {
            showResponse(response);

            if (response.responCode === "00") {
                window.location.reload(true);
            }
		},
        complete: function () {
            Swal.close();
		},
        error: function() {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "We were unable to process your request due to a server error. Please try again later. If the problem persists, contact your system administrator.",
                timer            : 5000,
                timerProgressBar : true,
                showConfirmButton: false
            });
		}
	});
    return false;
});

function activation(el) {

    let userid     = el.data("userid");
    let active     = String(el.data("active"));
    let isDeactive = active === "0";

    Swal.fire({
        title             : isDeactive ? "Deactivate Account?"                                                                                                                                       : "Reactivate Account?",
        html              : isDeactive ? `User ini akan dinonaktifkan dan tidak dapat login ke sistem.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`: `User ini akan diaktifkan kembali dan dapat login ke sistem.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`,
        icon              : "warning",
        showCancelButton  : true,
        confirmButtonColor: isDeactive ? "#d33"                                                                                                                                                      : "#50CD89",
        cancelButtonColor : "#6c757d",
        confirmButtonText : isDeactive ? '<i class="bi bi-trash3 text-white"></i> Ya, Deactivate'                                                                                                    : '<i class="bi bi-check-circle text-white"></i> Ya, Reactivate',
        cancelButtonText  : "Batal",
        reverseButtons    : true,
        timer             : 10000,
        timerProgressBar  : true
    }).then((result) => {
        if (!result.isConfirmed) return;
        $.ajax({
            url     : url + "index.php/hr/users/activation",
            type    : "POST",
            dataType: "JSON",
            data    : {userid: userid,active: active},
            beforeSend: function () {
                showProcessing("Updating Data", "Please wait while the system updates the data.");
            },
            success: function (response) {
                showResponse(response);

                if (response.responCode === "00") {
                    window.location.reload(true);
                }
            },
            error: function () {
                Swal.fire({
                    icon             : "error",
                    title            : "Request Failed",
                    text             : "We were unable to process your request due to a server error. Please try again later. If the problem persists, contact your system administrator.",
                    timer            : 5000,
                    timerProgressBar : true,
                    showConfirmButton: false
                });
            }
        });
    });
};

function getdata(btn){
    var userid        = btn.attr("data-userid");
    var nikrs         = btn.attr("data-nikrs");
    var username      = btn.attr("data-username");
    var name          = btn.attr("data-name");
    var email         = btn.attr("data-email");
    var avatar        = url+"assets/media/avatars/"+userid+".jpg";
    var avatarDefault = url+"assets/media/avatars/blank.png";

	$(":hidden[name='modal_edit_user_userid']").val(userid);
    $("#modal_edit_user_username").val(username);
    $("#modal_edit_user_nikrs").val(nikrs === "null" || nikrs === null ? "" : nikrs);
    $("#modal_edit_user_name").val(name);
    $("#modal_edit_user_email").val(email);
    $("<img>").attr("src",avatar).on("load",function(){$("#avatar-preview-edit").css("background-image","url('"+avatar+"')");}).on("error",function(){$("#avatar-preview-edit").css("background-image","url('"+avatarDefault+"')");});
};