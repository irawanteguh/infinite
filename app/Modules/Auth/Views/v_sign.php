<?php
    $hour = (int) date('G');

    if ($hour >= 5 && $hour < 12) {
        $background = 'morning-city.png';
    } elseif ($hour >= 12 && $hour < 16) {
        $background = 'sunrise-city.png';
    } elseif ($hour >= 16 && $hour < 19) {
        $background = 'sunset-city.png';
    } else {
        $background = 'night-city.png';
    }
?>

<div class="login-page d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-cover bgi-attachment-fixed" style="background-image: url('<?= base_url('assets/media/ambient/' . $background) ?>');">
    <div class="login-shell d-flex flex-center flex-column flex-column-fluid p-10">
        <div class="login-card mx-auto">
            <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" action="<?= site_url('auth/signin') ?>" method="post">
                <?= csrf_field() ?>
                <div class="login-brand text-center mb-1">
                    <a href="<?= base_url() ?>"><img alt="RSUD Pasar Minggu" src="<?= base_url('assets/media/logos/rsudpasarminggu.png') ?>" class="login-logo" /></a>
                </div>
                <div class="login-heading"><span class="login-eyebrow">INFINITE WORKSPACE</span><h1>Selamat datang kembali.</h1><p>Masuk untuk melanjutkan aktivitas Anda.</p></div>
                <div class="login-field fv-row mb-10">
                    <label for="login-username" class="form-label fs-6 fw-bolder text-dark">Username</label>
                    <div class="login-input"><i class="bi bi-person" aria-hidden="true"></i><input class="form-control form-control-lg form-control-solid" id="login-username" type="text" name="username" autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" enterkeyhint="next" required  placeholder="Masukkan username" /></div>
                </div>
                <div class="login-field fv-row mb-10">
                    <div class="d-flex flex-stack mb-2">
                        <label for="login-password" class="form-label fw-bolder text-dark fs-6 mb-0">Password</label>
                    </div>
                    <div class="login-input"><i class="bi bi-lock" aria-hidden="true"></i><input class="form-control form-control-lg form-control-solid" id="login-password" type="password" name="password" autocomplete="current-password" enterkeyhint="go" required  placeholder="Masukkan password" /><button type="button" id="login-toggle-password" class="login-password-toggle" aria-label="Tampilkan password" aria-controls="login-password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div>
                </div>
                <div class="text-center">
                    <button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
                        <span class="indicator-label">Masuk ke workspace <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></span>
                        <span class="indicator-progress">Mohon tunggu... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                    <a href="<?= base_url() ?>" class="login-back fs-7 text-muted text-hover-primary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke beranda
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="login-footer d-flex flex-center flex-column-auto p-10">
        <div class="d-flex align-items-center fw-semibold fs-7">
            <span class="text-muted me-2">Butuh bantuan?</span>
            <a href="tel:+6281288646630" class="text-primary text-hover-primary">Hubungi Administrator</a>
        </div>
    </div>
</div>
