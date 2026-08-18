// showProcessing("Loading Data", "Please wait while the system retrieves the requested data.");
// showProcessing("Saving Data", "Please wait while the system saves the data.");
// showProcessing("Updating Data", "Please wait while the system updates the data.");
// showProcessing("Deleting Data", "Please wait while the system deletes the data.");

function showProcessing(title = "Processing", message = "Please wait while the system processes your request.") {
    Swal.fire({
        title            : title,
        html             : message,
        allowOutsideClick: false,
        allowEscapeKey   : false,
        showConfirmButton: false,
        didOpen          : () => Swal.showLoading()
    });
}

function showResponse(response, options = {}) {

    const defaultOptions = {
        title            : "<h1 class='font-weight-bold'>For Your Information</h1>",
        confirmButtonText: "OK",
        timer            : 5000,
        timerProgressBar : true,
        customClass      : {confirmButton: "btn btn-primary"},
        showClass        : {popup: "animate__animated animate__fadeInUp animate__faster"},
        hideClass        : {popup: "animate__animated animate__fadeOutDown animate__faster"}
    };

    const config = $.extend(true, {}, defaultOptions, options);

    Swal.fire({
        title            : config.title,
        html             : (response.responDesc || "No response message"),
        icon             : response.responHead || "info",
        confirmButtonText: config.confirmButtonText,
        customClass      : config.customClass,
        timerProgressBar : config.timerProgressBar,
        timer            : config.timer,
        showClass        : config.showClass,
        hideClass        : config.hideClass
    });
}