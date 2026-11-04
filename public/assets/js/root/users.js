function activationuser(el) {

    let userid     = el.data("userid");
    let active     = String(el.data("active"));
    let isDeactive = active === "Y";

    Swal.fire({
        title             : isDeactive ? "Suspend Account?"                                                                                                                                       : "Reactivate Account?",
        html              : isDeactive ? `User ini akan dinonaktifkan dan tidak dapat login ke sistem.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`: `User ini akan diaktifkan kembali dan dapat login ke sistem.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`,
        icon              : "warning",
        showCancelButton  : true,
        confirmButtonColor: isDeactive ? "#d33"                                                                                                                                                      : "#50CD89",
        cancelButtonColor : "#6c757d",
        confirmButtonText : isDeactive ? '<i class="bi bi-trash3 text-white"></i> Ya, Suspend'                                                                                                    : '<i class="bi bi-check-circle text-white"></i> Ya, Reactivate',
        cancelButtonText  : "Batal",
        reverseButtons    : true,
        timer             : 10000,
        timerProgressBar  : true
    }).then((result) => {
        if (!result.isConfirmed) return;
        $.ajax({
            url     : url + "hr/users/activation",
            type    : "POST",
            dataType: "JSON",
            data    : {userid: userid,active:active},
            success: function (response) {
                if (response.responseCode === "00") {
                    Swal.fire({
                        title             : "Success",
                        text              : response.responseDesc,
                        icon              : "success",
                        timer             : 1500,
                        showConfirmButton : false,
                        didClose          : function() {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire("Error", response.responseDesc, "error");
                }
            }
        });
    });
};

function deleteuser(el) {

    let userid     = el.data("userid");
    let active     = String(el.data("active"));
    let isDeactive = active === "0";

    Swal.fire({
        title             : isDeactive ? "Delete Account?"                                                                                                                                       : "Reactivate Account?",
        html              : isDeactive ? `User ini akan dinonaktifkan dan tidak dapat login ke sistem.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`: `User ini akan diaktifkan kembali dan dapat login ke sistem.<br><small class="text-muted">Dialog ini akan tertutup otomatis dalam <b>10 detik</b>.</small>`,
        icon              : "warning",
        showCancelButton  : true,
        confirmButtonColor: isDeactive ? "#d33"                                                                                                                                                      : "#50CD89",
        cancelButtonColor : "#6c757d",
        confirmButtonText : isDeactive ? '<i class="bi bi-trash3 text-white"></i> Ya, Delete'                                                                                                    : '<i class="bi bi-check-circle text-white"></i> Ya, Reactivate',
        cancelButtonText  : "Batal",
        reverseButtons    : true,
        timer             : 10000,
        timerProgressBar  : true
    }).then((result) => {
        if (!result.isConfirmed) return;
        $.ajax({
            url     : url + "hr/users/deleteuser",
            type    : "POST",
            dataType: "JSON",
            data    : {userid: userid,active:active},
            success: function (response) {
                if (response.responseCode === "00") {
                    Swal.fire({
                        title             : "Success",
                        text              : response.responseDesc,
                        icon              : "success",
                        timer             : 1500,
                        showConfirmButton : false,
                        didClose          : function() {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire("Error", response.responseDesc, "error");
                }
            }
        });
    });
};

function getdatausers(btn){
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
    $("#modal_edit_user_email").val(email === "null" || email === null ? "" : email);
    $("<img>").attr("src",avatar).on("load",function(){$("#avatar-preview-edit").css("background-image","url('"+avatar+"')");}).on("error",function(){$("#avatar-preview-edit").css("background-image","url('"+avatarDefault+"')");});

    $(":hidden[name='modal_edit_user_userid_root']").val(userid);
    $("#modal_edit_user_username_root").val(username);
    $("#modal_edit_user_nikrs_root").val(nikrs === "null" || nikrs === null ? "" : nikrs);
    $("#modal_edit_user_name_root").val(name);
    $("#modal_edit_user_email_root").val(email === "null" || email === null ? "" : email);
    $("<img>").attr("src",avatar).on("load",function(){$("#avatar-preview-edit-root").css("background-image","url('"+avatar+"')");}).on("error",function(){$("#avatar-preview-edit").css("background-image","url('"+avatarDefault+"')");});
};

$(document).on("submit", "#formadduser", function (e) {
	e.preventDefault();
	var data = new  FormData(this);
	$.ajax({
        url        : url+'hr/users/adduser',
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
                    html             : "<b>"+response.responDesc+"</b>",
                    icon             : response.responHead,
                    confirmButtonText: 'Please Try Again',
                    customClass      : {confirmButton: 'btn btn-danger'},
                    timerProgressBar : true,
                    timer            : 5000,
                    showClass        : {popup: "animate__animated animate__fadeInUp animate__faster"},
                    hideClass        : {popup: "animate__animated animate__fadeOutDown animate__faster"}
                });
                return;
            }

            $('#modal_add_user').modal('hide');
            Swal.close();
            location.reload();
		},
        error: function () {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "An error occurred while processing your request.",
                confirmButtonText: "OK"
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
                    html             : "<b>"+response.responDesc+"</b>",
                    icon             : response.responHead,
                    confirmButtonText: 'Please Try Again',
                    customClass      : {confirmButton: 'btn btn-danger'},
                    timerProgressBar : true,
                    timer            : 5000,
                    showClass        : {popup: "animate__animated animate__fadeInUp animate__faster"},
                    hideClass        : {popup: "animate__animated animate__fadeOutDown animate__faster"}
                });
                return;
            }

            $('#modal_edit_user').modal('hide');
            Swal.close();
            location.reload();
		},
        error: function () {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "An error occurred while processing your request.",
                confirmButtonText: "OK"
            });
        }
	});
    return false;
});