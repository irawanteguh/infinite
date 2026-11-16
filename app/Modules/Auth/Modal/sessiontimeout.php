<?php if (session()->get('loggedin')): ?>
<?php
if (!session()->get('idle_session_key')) session()->set('idle_session_key', bin2hex(random_bytes(16)));
$idleKey = hash('sha256', (string) session()->get('userid') . ':' . session()->get('idle_session_key'));
?>
<div class="modal fade" id="session-idle-modal" tabindex="-1" aria-labelledby="session-idle-title" aria-describedby="session-idle-description" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-session-key="<?= esc($idleKey, 'attr') ?>" data-logout-url="<?= esc(site_url('auth/logout'), 'attr') ?>">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h2 class="modal-title" id="session-idle-title">Anda masih di sini?</h2></div>
            <div class="modal-body">
                <p id="session-idle-description">Tidak ada aktivitas selama 1 menit. Pilih Tetap masuk untuk melanjutkan.</p>
                <p class="mb-0 text-muted" role="timer" aria-live="off">Logout otomatis dalam <strong id="session-idle-countdown">10</strong> detik.</p>
                <div class="visually-hidden" id="session-idle-announcement" role="status" aria-live="polite"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-danger" id="session-idle-logout">Logout</button>
                <button type="button" class="btn btn-primary" id="session-idle-stay">Tetap masuk</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>