"use strict";

var KTSigninGeneral = (function () {
    var form;
    var submitButton;
    var validator;

    var setLoading = function (loading) {
        if (loading) {
            submitButton.setAttribute("data-kt-indicator", "on");
            submitButton.disabled = true;
            return;
        }

        submitButton.removeAttribute("data-kt-indicator");
        submitButton.disabled = false;
    };

    var showAlert = function (message, icon, options) {
        options = options || {};

        return Swal.fire({
            text: message,
            icon: icon,
            buttonsStyling: false,
            confirmButtonText: options.confirmButtonText || "OK",
            showConfirmButton: options.showConfirmButton !== false,
            timer: options.timer,
            timerProgressBar: options.timerProgressBar || false,
            customClass: {
                confirmButton: "btn btn-primary"
            }
        });
    };

    var clearForm = function () {
        form.reset();

        if (validator) {
            validator.resetForm(true);
        }
    };

    var signIn = function () {

        setLoading(true);

        $.ajax({
            url: form.getAttribute("action"),
            type: "POST",
            data: $(form).serialize(),
            dataType: "json"
        })
        .done(function (response) {

            let code    = response.responCode || "01";
            let icon    = response.responHead || "error";
            let message = response.responDesc
                ? response.responDesc.replace(/<br\s*\/?>/gi, "\n")
                : "Terjadi kesalahan.";

            if (code === "00") {

                showAlert(message, "success", {
                    timer: 3000,
                    timerProgressBar: true
                }).then(function () {
                    window.location.href = response.url;
                });

                return;
            }

            if (code === "02") {

                showAlert(message, "warning", {
                    confirmButtonText: "OK",
                    timer: 10000,
                    timerProgressBar: true
                }).then(function (result) {

                    if (
                        result.isConfirmed ||
                        result.dismiss === Swal.DismissReason.timer
                    ) {
                        window.location.href = response.url;
                    }

                });

                return;
            }

            showAlert(message, icon);

        })
        .fail(function () {

            showAlert(
                "Login belum berhasil diproses. Silakan coba lagi.",
                "error"
            );

        })
        .always(function () {

            clearForm();
            setLoading(false);

        });

    };

    var initValidation = function () {

        validator = FormValidation.formValidation(form, {
            fields: {
                username: {
                    validators: {
                        notEmpty: {
                            message: "Username wajib diisi."
                        },
                        stringLength: {
                            min: 3,
                            max: 50,
                            message: "Username harus 3 sampai 50 karakter."
                        }
                    }
                },
                password: {
                    validators: {
                        notEmpty: {
                            message: "Password wajib diisi."
                        }
                    }
                }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap: new FormValidation.plugins.Bootstrap5({
                    rowSelector: ".fv-row"
                })
            }
        });

    };

    var handleSubmit = function () {

        form.addEventListener("submit", function (event) {

            event.preventDefault();

            validator.validate().then(function (status) {

                if (status !== "Valid") {
                    showAlert(
                        "Mohon lengkapi username dan password.",
                        "error"
                    );
                    return;
                }

                signIn();

            });

        });

        submitButton.addEventListener("click", function (event) {
            event.preventDefault();
            form.dispatchEvent(new Event("submit", { cancelable: true }));
        });

    };

    return {
        init: function () {

            form = document.querySelector("#kt_sign_in_form");
            submitButton = document.querySelector("#kt_sign_in_submit");

            if (!form || !submitButton) {
                return;
            }

            initValidation();
            clearForm();
            handleSubmit();

        }
    };

})();

KTUtil.onDOMContentLoaded(function () {
    KTSigninGeneral.init();
});