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