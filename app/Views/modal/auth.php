<div class="modal fade" id="modal_logout" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Konfirmasi Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-5">
                <?php
                    $userid      = session()->get('userid') ?? '';
                    $nama        = session()->get('name') ?? 'User';
                    $avatar      = !empty($userid) ? base_url('assets/media/avatars/' . $userid . '.jpg') : base_url('assets/media/avatars/blank.png');
                    $avatarBlank = base_url('assets/media/avatars/blank.png');
                ?>

                <img src="<?php echo $avatar; ?>" alt="User" class="rounded-circle object-fit-cover mb-4" width="80" height="80" onerror="this.onerror=null;this.src='<?php echo $avatarBlank; ?>';">

                <div class="fw-bold fs-4 mb-1">
                    <?php echo htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>
                </div>

                <div class="text-muted">Apakah Anda yakin ingin logout?</div>
                <div class="text-muted fs-7">Sesi Anda akan diakhiri dan Anda perlu login kembali.</div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-5">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="logout()">Ya, Logout</button>
            </div>
        </div>
    </div>
</div>

<?php if (session('loggedin')): ?>
<?php
    $minimumPasswordLength = \Modules\Auth\Models\SignModel::PASSWORD_MIN_LENGTH;
    $maximumPasswordBytes = \Modules\Auth\Models\SignModel::PASSWORD_MAX_BYTES;
?>
<div class="modal fade" id="modal_change_password" tabindex="-1" aria-labelledby="change_password_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="change_password_form" class="modal-content border-0 shadow" method="post" action="<?= esc(site_url('auth/changepassword'), 'attr') ?>" data-max-password-bytes="<?= $maximumPasswordBytes ?>">
            <div class="modal-header align-items-start">
                <div class="d-flex align-items-center gap-3">
                    <span class="symbol symbol-45px">
                        <span class="symbol-label bg-light-primary"><i class="bi bi-shield-lock text-primary fs-3" aria-hidden="true"></i></span>
                    </span>
                    <div>
                        <h2 class="modal-title fs-4" id="change_password_title">Ganti Password</h2>
                        <p class="text-muted fs-7 mb-0">Perbarui password akun Anda.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <?= csrf_field() ?>
                <div class="alert d-none" data-password-message role="status" aria-live="polite"></div>
                <?php foreach (['current_password' => 'Password lama', 'new_password' => 'Password baru', 'confirm_password' => 'Konfirmasi password baru'] as $field => $label): ?>
                    <div class="<?= $field === 'confirm_password' ? '' : 'mb-4' ?>">
                        <label for="<?= $field ?>" class="form-label fw-semibold"><?= esc($label) ?></label>
                        <div class="input-group input-group-solid">
                            <input id="<?= $field ?>" name="<?= $field ?>" class="form-control" type="password"
                                autocomplete="<?= $field === 'current_password' ? 'current-password' : 'new-password' ?>"
                                <?= $field === 'new_password' ? 'minlength="' . $minimumPasswordLength . '" aria-describedby="new_password_hint"' : '' ?> required>
                            <button type="button" class="btn btn-light" data-password-toggle="<?= $field ?>" aria-controls="<?= $field ?>" aria-pressed="false" aria-label="Tampilkan <?= esc(mb_strtolower($label), 'attr') ?>">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <?php if ($field === 'new_password'): ?>
                            <div class="bg-light rounded p-4 mt-3" id="new_password_hint">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                    <span class="fw-semibold">Kerumitan password</span>
                                    <span class="text-muted fs-7" data-password-level aria-live="polite">Belum diisi</span>
                                </div>
                                <div class="progress h-6px mb-3" aria-hidden="true">
                                    <div class="progress-bar w-0" data-password-progress></div>
                                </div>
                                <ul class="row g-2 list-unstyled mb-0">
                                    <li class="col-12 text-muted" data-password-length><i class="bi bi-circle me-2" aria-hidden="true"></i><?= $minimumPasswordLength ?> karakter atau lebih, maksimal <?= $maximumPasswordBytes ?> byte</li>
                                    <?php foreach (\Modules\Auth\Models\SignModel::PASSWORD_COMPLEXITY as $rule): ?>
                                        <li class="col-6 text-muted" data-password-rule="<?= esc($rule['pattern'], 'attr') ?>"><i class="bi bi-circle me-2" aria-hidden="true"></i><?= esc($rule['label']) ?></li>
                                    <?php endforeach ?>
                                </ul>
                            </div>
                        <?php endif ?>
                    </div>
                <?php endforeach ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary">Simpan Password</button>
            </div>
        </form>
    </div>
</div>
<script defer src="<?= esc(base_url('assets/js/auth/change-password.js?v=' . filemtime(FCPATH . 'assets/js/auth/change-password.js')), 'attr') ?>"></script>
<?php endif ?>
