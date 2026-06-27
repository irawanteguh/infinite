<div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed" style="background-image: url('<?= base_url('assets/media/illustrations/sketchy-1/14.png') ?>')">    
    <div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
        <a href="<?= site_url('auth/sign') ?>" class="mb-12">
            <img alt="Logo" src="<?= base_url('assets/media/logos/infinite_full.png') ?>" class="h-70px" />
        </a>
        <div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
            <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" action="<?= site_url('auth/sign/signin') ?>" data-redirect-url="<?= site_url('additional/welcomepage') ?>">
                <div class="text-center mb-10">
                    <h1 class="text-dark mb-3">Welcome Back To Infinite</h1>
                </div>
                <div class="fv-row mb-10">
                    <label class="form-label fs-6 fw-bolder text-dark">Username</label>
                    <input class="form-control form-control-lg form-control-solid" type="text" name="username" autocomplete="username" />
                </div>
                <div class="fv-row mb-10">
                    <div class="d-flex flex-stack mb-2">
                        <label class="form-label fw-bolder text-dark fs-6 mb-0">Password</label>
                    </div>
                    <input class="form-control form-control-lg form-control-solid" type="password" name="password" autocomplete="current-password" />
                </div>
                <div class="text-center">
                    <button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
                        <span class="indicator-label">Continue</span>
                        <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="d-flex flex-center flex-column-auto p-10">
        <div class="d-flex align-items-center fw-bold fs-6">
            <a href="#" class="text-muted text-hover-primary px-2">About</a>
            <a href="#" class="text-muted text-hover-primary px-2">Contact</a>
        </div>
    </div>
</div>
