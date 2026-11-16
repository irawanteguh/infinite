<section class="card organization-header mb-6" aria-labelledby="organization-header-title">
    <div class="card-body p-6 p-lg-7">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-5">
            <div class="organization-header-logo flex-shrink-0">
                <img src="<?= !empty(session()->get('orgid')) ? base_url('assets/media/logos/' . rawurlencode((string) session()->get('orgid')) . '.png') : base_url('assets/media/noimages.png') ?>" alt="Logo rumah sakit" onerror="this.onerror=null;this.src='<?= esc(base_url('assets/media/noimages.png'), 'attr') ?>';">
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <span class="organization-header-eyebrow">INFORMASI ORGANISASI</span>
                    <?php if (session()->get('trial') === 'Y'): ?>
                        <a href="#kt_modal_upgrade_plan" class="badge badge-light-warning" data-bs-toggle="modal" data-bs-target="#kt_modal_upgrade_plan">Masa uji coba</a>
                    <?php elseif (session()->get('trial') === 'N'): ?>
                        <span class="badge organization-header-verified"><i class="bi bi-patch-check-fill me-1" aria-hidden="true"></i>Terverifikasi</span>
                    <?php endif; ?>
                </div>
                <h1 id="organization-header-title"><?= esc(session()->get('orgname') ?: 'Rumah Sakit') ?></h1>
                <p class="organization-header-description mb-0">Identitas organisasi dan kontak pada ruang kerja Anda.</p>
            </div>
        </div>
        <div class="row g-4 mt-2">
            <?php foreach ([['bi-person-badge','Pimpinan','pimpinan'],['bi-geo-alt','Alamat','address'],['bi-envelope','Email','emailorg']] as $item): ?>
                <div class="col-md-4"><div class="organization-header-detail h-100"><span class="organization-header-icon"><i class="bi <?= $item[0] ?>" aria-hidden="true"></i></span><div class="min-w-0"><span class="organization-header-label"><?= $item[1] ?></span><p><?= esc(session()->get($item[2]) ?: '—') ?></p></div></div></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if (trim((string) ($menuorganization ?? '')) !== ''): ?>
        <nav class="organization-header-nav px-6 px-lg-7" aria-label="Menu organisasi"><div class="overflow-auto"><ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent flex-nowrap mb-0"><?= $menuorganization ?></ul></div></nav>
    <?php endif; ?>
</section>
