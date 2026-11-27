"use strict";

$(function () {

    const loginPage = document.querySelector('.login-page');
    if (loginPage) {
        const ecosystem = loginPage.querySelector('.login-ecosystem');
        const nodes = [...loginPage.querySelectorAll('[data-login-node]')];
        const connections = [...loginPage.querySelectorAll('[data-login-connection]')];
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const order = [0, 3, 4, 5, 2, 1];
        let position = 0;
        let timer = null;
        let visible = !('IntersectionObserver' in window);
        function showNode() {
            nodes.forEach(node => node.classList.toggle('is-active', Number(node.dataset.loginNode) === order[position]));
            connections.forEach(path => path.classList.toggle('is-active', Number(path.dataset.loginConnection) === order[position]));
        }
        function updateMotion() {
            loginPage.classList.toggle('login-motion-paused', document.hidden);
            ecosystem?.classList.toggle('login-ecosystem-paused', !visible || document.hidden || reducedMotion.matches);
            clearTimeout(timer);
            if (!visible || document.hidden || reducedMotion.matches || !nodes.length) return;
            timer = setTimeout(() => {
                position = (position + 1) % order.length;
                showNode();
                updateMotion();
            }, 4000);
        }
        nodes.forEach(node => {
            const connection = connections.find(path => path.dataset.loginConnection === node.dataset.loginNode);
            node.addEventListener('mouseenter', () => connection?.classList.add('is-emphasized'));
            node.addEventListener('mouseleave', () => connection?.classList.remove('is-emphasized'));
        });
        if (ecosystem && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver(entries => {
                visible = entries[0].isIntersecting;
                updateMotion();
            });
            observer.observe(ecosystem);
        }
        showNode();
        reducedMotion.addEventListener('change', updateMotion);
        document.addEventListener('visibilitychange', updateMotion);
        window.addEventListener('pagehide', () => clearTimeout(timer));
        updateMotion();
        loginPage.querySelectorAll('a[href]').forEach(function (link) {
            const target = new URL(link.href, window.location.href);
            if (target.origin !== window.location.origin || target.pathname !== new URL(url).pathname) return;

            link.addEventListener('click', function (event) {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0
                    || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                event.preventDefault();
                loginPage.classList.add('is-leaving');
                window.setTimeout(function () { window.location.assign(link.href); }, 220);
            });
        });
        window.addEventListener('pageshow', function () {
            loginPage.classList.remove('is-leaving');
            updateMotion();
        });
    }

    const passwordToggle = document.getElementById('login-toggle-password');
    const passwordInput = document.getElementById('login-password');
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function () {
            const visible = passwordInput.type === 'password';
            passwordInput.type = visible ? 'text' : 'password';
            passwordToggle.setAttribute('aria-pressed', String(visible));
            passwordToggle.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
            passwordToggle.querySelector('i').className = visible ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }

    const form   = $("#kt_sign_in_form");
    const button = $("#kt_sign_in_submit");
    form.on("input", "input", function () {
        $(this).removeAttr("aria-invalid");
    });

    form.trigger("reset");

    form.on("submit", function (e) {

        e.preventDefault();
        if (button.prop("disabled")) return;

        const username = $.trim($("[name='username']").val());
        const password = $.trim($("[name='password']").val());

        // ==============================
        // VALIDATION
        // ==============================

        if (!username) {
            $("[name='username']").attr("aria-invalid", "true");

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
            $("[name='password']").attr("aria-invalid", "true");

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
        form.attr("aria-busy", "true");

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

            const code = response.responseCode || "01";

            const message = (
                response.responseDesc ||
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
                icon              : "error",
                title             : "Sign In Failed",
                text              : message,
                confirmButtonText : "Try Again",
                confirmButtonColor: "#dc3545"
            })
        })

        // ==============================
        // AJAX ERROR
        // ==============================

        .fail(function (xhr) {

            let message =
                "Unable to process your sign-in request. Please try again.";

            if (xhr.responseJSON && xhr.responseJSON.responseDesc) {
                message = xhr.responseJSON.responseDesc;
            }

            Swal.fire({
                icon              : "error",
                title             : "Connection Error",
                text              : message,
                confirmButtonText : "Try Again",
                confirmButtonColor: "#dc3545"
            });

        })
        .always(function () {
            form.trigger("reset");
            button.prop("disabled", false).removeAttr("data-kt-indicator");
            form.attr("aria-busy", "false");
        });
    });
});
