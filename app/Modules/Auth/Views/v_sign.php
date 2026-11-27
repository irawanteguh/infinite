<div id="infinite-login" class="login-page">
    <main class="login-main">
        <header class="login-main-header">
        <a class="login-wordmark" href="<?= base_url() ?>" aria-label="INFINITE beranda">INFINITE<span>Healthcare enterprise platform</span></a>
        <a href="<?= base_url() ?>" class="login-back">Kembali ke beranda</a>
        </header>
        <div class="login-card">
            <div class="login-heading">
                <h1>Selamat datang <br>kembali.</h1>
                <p>Masuk untuk melanjutkan pekerjaan Anda.</p>
            </div>
            <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" action="<?= site_url('auth/signin') ?>" method="post" aria-busy="false">
                <?= csrf_field() ?>
                <div class="login-field fv-row">
                    <label for="login-username" class="form-label">Username</label>
                    <input class="form-control" id="login-username" type="text" name="username" autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" enterkeyhint="next" required autofocus placeholder="Masukkan username">
                </div>
                <div class="login-field fv-row">
                    <label for="login-password" class="form-label">Password</label>
                    <div class="login-input">
                        <input class="form-control" id="login-password" type="password" name="password" autocomplete="current-password" enterkeyhint="go" required placeholder="Masukkan password">
                        <button type="button" id="login-toggle-password" class="login-password-toggle" aria-label="Tampilkan password" aria-controls="login-password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                    </div>
                </div>
                <button type="submit" id="kt_sign_in_submit" class="btn btn-primary w-100">
                    <span class="indicator-label">Masuk ke workspace</span>
                    <span class="indicator-progress" role="status">Memproses… <span class="spinner-border spinner-border-sm align-middle ms-2" aria-hidden="true"></span></span>
                </button>
                <p class="login-access-note"><i class="bi bi-shield-lock" aria-hidden="true"></i>Akses modul mengikuti akun dan organisasi Anda.</p>
            </form>
        </div>
        <footer class="login-footer">
            <div><span>Butuh bantuan akses?</span><a href="tel:+6281288646630">Hubungi Administrator</a></div>
            <small>&copy; <?= date('Y') ?> INFINITE</small>
        </footer>
    </main>

    <aside class="login-story app-geometric-banner" aria-labelledby="login-story-title">
        <div class="login-story-content">
            <p class="login-eyebrow">Connected healthcare workspace</p>
            <h2 id="login-story-title">Satu platform. <br><em>Perspektif terhubung.</em></h2>
            <p class="login-story-description">Kelola mutu, pahami data pelayanan, dan jalankan integrasi melalui ruang kerja digital rumah sakit Anda.</p>
            <div class="login-ecosystem" aria-label="Kelompok kapabilitas Infinite dalam satu platform.">
                <svg viewBox="0 0 560 320" preserveAspectRatio="none" aria-hidden="true">
                    <?php foreach (['M85 50H190Q280 50 280 160', 'M85 160H280', 'M85 270H190Q280 270 280 160', 'M475 50H370Q280 50 280 160', 'M475 160H280', 'M475 270H370Q280 270 280 160'] as $index => $path): ?>
                        <path data-login-connection="<?= $index ?>" d="<?= $path ?>" fill="none"/>
                    <?php endforeach ?>
                </svg>
                <div class="login-ecosystem-center"><strong>INFINITE</strong><span>Healthcare platform</span></div>
                <?php foreach ([
                    ['bi-clipboard2-pulse', 'Mutu & perbaikan'],
                    ['bi-chat-heart', 'Pengalaman pasien'],
                    ['bi-graph-up', 'Analitik & klaim'],
                    ['bi-intersect', 'Interoperabilitas'],
                    ['bi-braces', 'API & integrasi'],
                    ['bi-shield-check', 'Risiko & tata kelola'],
                ] as $index => [$icon, $label]): ?>
                    <span class="login-domain login-domain-<?= $index ?>" data-login-node="<?= $index ?>"><i class="bi <?= $icon ?>" aria-hidden="true"></i><span><?= esc($label) ?></span></span>
                <?php endforeach ?>
            </div>
        </div>
        <p class="login-story-footer">Modern Integrated Healthcare Enterprise Platform</p>
    </aside>
</div>
