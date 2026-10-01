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

<div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-cover bgi-attachment-fixed" style="background-image: url('<?= base_url('assets/media/ambient/' . $background) ?>');">
    <div class="d-flex flex-center flex-column flex-column-fluid p-10">
        <div class="w-lg-500px bg-body rounded p-10 p-lg-15 mx-auto <?= $cardShadow ?>">
            <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" action="<?= site_url('auth/signin') ?>" method="post">
                <?= csrf_field() ?>
                <div class="text-center mb-1">
                    <a href="<?= site_url('auth/sign') ?>"><img alt="Logo" src="<?= base_url('assets/media/logos/infinite_landscape.png') ?>" class="h-150px" /></a>
                </div>
                <!-- <div class="text-center mb-10">
                    <h2 class="text-dark mb-3">Integrated Information System</h2>
                </div> -->
                <div class="fv-row mb-10">
                    <label class="form-label fs-6 fw-bolder text-dark">Username</label>
                    <input class="form-control form-control-lg form-control-solid" type="text" name="username" autocomplete="username" required />
                </div>
                <div class="fv-row mb-10">
                    <div class="d-flex flex-stack mb-2">
                        <label class="form-label fw-bolder text-dark fs-6 mb-0">Password</label>
                    </div>
                    <input class="form-control form-control-lg form-control-solid" type="password" name="password" autocomplete="current-password" required />
                </div>
                <div class="text-center">
                    <button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
                        <span class="indicator-label">Sign In</span>
                        <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex flex-center flex-column-auto p-10">
        <div class="d-flex align-items-center fw-semibold fs-7">
            <span class="text-muted me-2">Need assistance?</span>
            <a href="tel:+081288646630" class="text-primary text-hover-primary">Contact Administrator</a>
        </div>
    </div>
</div>