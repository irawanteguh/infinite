<?php

$hour = (int) date('G');

if ($hour >= 5 && $hour < 12) {
    $background = 'morning-city.png';
    $cardShadow = 'login-morning-shadow';
} elseif ($hour >= 12 && $hour < 16) {
    $background = 'sunrise-city.png';
    $cardShadow = 'login-sunrise-shadow';
} elseif ($hour >= 16 && $hour < 19) {
    $background = 'sunset-city.png';
    $cardShadow = 'login-sunset-shadow';
} else {
    $background = 'night-city.png';
    $cardShadow = 'login-night-shadow';
}

?>

<style>
    .login-morning-shadow {
        border-radius: 1rem;
        border: 1px solid rgba(255, 214, 140, .25);

        box-shadow:
            0 0 50px rgba(255, 236, 180, .70),
            0 0 120px rgba(255, 206, 120, .55),
            0 0 220px rgba(255, 176, 70, .35),
            0 0 320px rgba(255, 145, 40, .20),
            0 35px 80px rgba(0, 0, 0, .25);
    }

    .login-sunrise-shadow {
        border-radius: 1rem;
        border: 1px solid rgba(180, 220, 255, .22);

        box-shadow:
            0 0 45px rgba(220, 245, 255, .70),
            0 0 100px rgba(170, 220, 255, .55),
            0 0 180px rgba(120, 190, 255, .38),
            0 0 300px rgba(80, 165, 255, .22),
            0 35px 80px rgba(0, 0, 0, .22);
    }

    .login-sunset-shadow {
        border-radius: 1rem;
        border: 1px solid rgba(255, 190, 120, .22);

        box-shadow:
            0 0 45px rgba(255, 235, 185, .75),
            0 0 100px rgba(255, 195, 115, .60),
            0 0 180px rgba(255, 155, 80, .45),
            0 0 300px rgba(255, 120, 60, .28),
            0 0 420px rgba(220, 90, 40, .15),
            0 35px 80px rgba(0, 0, 0, .30);
    }

    .login-night-shadow {
        border-radius: 1rem;
        border: 1px solid rgba(110, 170, 255, .22);

        box-shadow:
            0 0 50px rgba(110, 180, 255, .70),
            0 0 120px rgba(80, 155, 255, .55),
            0 0 220px rgba(50, 120, 255, .38),
            0 0 320px rgba(35, 95, 220, .22),
            0 40px 90px rgba(0, 0, 0, .45);
    }
</style>

<div
    class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-cover bgi-attachment-fixed"
    style="background-image: url('<?= base_url('assets/media/ambient/' . $background) ?>');"
>

    <div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">

        <div class="w-lg-500px bg-body rounded p-10 p-lg-15 mx-auto <?= $cardShadow ?>">

            <form
                class="form w-100"
                novalidate="novalidate"
                id="kt_sign_in_form"
                action="<?= site_url('auth/signin') ?>"
                method="post"
            >

                <?= csrf_field() ?>

                <div class="text-center mb-3">
                    <a href="<?= site_url('auth/sign') ?>">
                        <img
                            alt="Logo"
                            src="<?= base_url('assets/media/logos/infinite_full.png') ?>"
                            class="h-70px"
                        />
                    </a>
                </div>

                <div class="text-center mb-10">
                    <h1 class="text-dark mb-3">
                        Welcome Back To INFINITE
                    </h1>

                    <div class="text-muted fw-semibold fs-6">
                        Integrated Information System
                    </div>
                </div>

                <div class="fv-row mb-10">

                    <label class="form-label fs-6 fw-bolder text-dark">
                        Username
                    </label>

                    <input
                        class="form-control form-control-lg form-control-solid"
                        type="text"
                        name="username"
                        autocomplete="username"
                        required
                    />

                </div>

                <div class="fv-row mb-10">

                    <div class="d-flex flex-stack mb-2">

                        <label class="form-label fw-bolder text-dark fs-6 mb-0">
                            Password
                        </label>

                    </div>

                    <input
                        class="form-control form-control-lg form-control-solid"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    />

                </div>

                <div class="text-center">

                    <button
                        type="submit"
                        id="kt_sign_in_submit"
                        class="btn btn-lg btn-primary w-100 mb-5"
                    >

                        <span class="indicator-label">
                            Sign In
                        </span>

                        <span class="indicator-progress">
                            Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>

                    </button>

                </div>

            </form>

        </div>

    </div>

    <div class="d-flex flex-center flex-column-auto p-10">

        <div class="d-flex align-items-center fw-semibold fs-7">

            <span class="text-muted me-2">
                Need assistance?
            </span>

            <a
                href="tel:+081288646630"
                class="text-primary text-hover-primary"
            >
                Contact Administrator
            </a>

        </div>

    </div>

</div>