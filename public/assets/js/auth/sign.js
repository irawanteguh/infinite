"use strict";

$(function () {

    const form   = $("#kt_sign_in_form");
    const button = $("#kt_sign_in_submit");

    form.trigger("reset");

    form.on("submit", function (e) {

        e.preventDefault();

        const username = $.trim($("[name='username']").val());
        const password = $.trim($("[name='password']").val());

        // ==============================
        // VALIDATION
        // ==============================

        if (!username) {

            Swal.fire({
                icon             : "warning",
                title            : "Username Required",
                text             : "Please enter your username.",
                confirmButtonText: "OK"
            });

            $("[name='username']").trigger("focus");

            return;
        }

        if (!password) {

            Swal.fire({
                icon             : "warning",
                title            : "Password Required",
                text             : "Please enter your password.",
                confirmButtonText: "OK"
            });

            $("[name='password']").trigger("focus");

            return;
        }

        // ==============================
        // LOADING
        // ==============================

        button
            .prop("disabled", true)
            .attr("data-kt-indicator", "on");

        $.ajax({

            url     : form.attr("action"),
            type    : "POST",
            data    : form.serialize(),
            dataType: "JSON"

        })

        // ==============================
        // SUCCESS RESPONSE
        // ==============================

        .done(function (response) {

            const code = response.responCode || "01";

            const message = (
                response.responDesc ||
                "An unexpected error occurred. Please try again."
            ).replace(/<br\s*\/?>/gi, "\n");

            // ==============================
            // LOGIN SUCCESS
            // ==============================

            if (code === "00") {
                Swal.fire({
                    icon             : "success",
                    title            : "Welcome Back!",
                    text             : message,
                    confirmButtonText: "Continue",
                    timer            : 2000,
                    timerProgressBar : true,
                    allowOutsideClick: false,
                    allowEscapeKey   : false,
                    customClass      : {confirmButton: "btn btn-primary"},
                    buttonsStyling   : false
                }).then(function () {
                    if (response.url) {
                        window.location.href = response.url;
                    }
                });

                return;
            }

            if (code === "02") {
                Swal.fire({
                    icon             : "warning",
                    title            : "Account Deactivated",
                    text             : message,
                    confirmButtonText: "OK",
                    allowOutsideClick: false
                }).then(function () {
                    if (response.url) {
                        window.location.href = response.url;
                    }
                });
                return;
            }

            Swal.fire({
                icon             : "error",
                title            : "Sign In Failed",
                text             : message,
                confirmButtonText: "Try Again"
            });
        })

        // ==============================
        // AJAX ERROR
        // ==============================

        .fail(function (xhr) {

            let message =
                "Unable to process your sign-in request. Please try again.";

            if (xhr.responseJSON && xhr.responseJSON.responDesc) {

                message = xhr.responseJSON.responDesc;

            }

            Swal.fire({

                icon: "error",

                title: "Connection Error",

                text: message,

                confirmButtonText: "Try Again"

            });

        })

        // ==============================
        // FINISH
        // ==============================

        .always(function () {

            form.trigger("reset");

            button
                .prop("disabled", false)
                .removeAttr("data-kt-indicator");

        });

    });

});