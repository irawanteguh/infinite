<div class="card mb-5 mb-xl-10">
    <div class="card-body p-6 p-lg-8">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-6">
            <div class="symbol symbol-80px symbol-lg-100px symbol-fixed flex-shrink-0">
                <img
                    src="<?= !empty(session()->get('orgid')) ? base_url('assets/media/logos/' . rawurlencode((string) session()->get('orgid')) . '.png') : base_url('assets/media/noimages.png') ?>"
                    alt="Logo rumah sakit"
                    class="rounded"
                    onerror="this.onerror=null;this.src='<?= esc(base_url('assets/media/noimages.png'), 'attr') ?>';">
            </div>

            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge badge-light-primary fw-bold">INFORMASI MUTU RUMAH SAKIT</span>
                    <?php if (session()->get('trial') === 'Y') : ?>
                        <a href="#kt_modal_upgrade_plan" class="badge badge-light-danger text-hover-danger" data-bs-toggle="modal" data-bs-target="#kt_modal_upgrade_plan">Masa uji coba</a>
                    <?php elseif (session()->get('trial') === 'N') : ?>
                        <span class="badge badge-light-success"><i class="bi bi-patch-check-fill me-1 text-primary"></i> Terverifikasi</span>
                    <?php endif; ?>
                </div>
                <h1 class="text-gray-900 fw-bolder mb-2">
                    <?= esc(session()->get('orgname') ?: 'Rumah Sakit') ?>
                </h1>
                <p class="text-gray-600 fs-6 mb-0">
                    Pantau pengelolaan indikator mutu, penanggung jawab, dan pelaporan berkala rumah sakit.
                </p>
            </div>

            <div class="d-flex flex-column gap-3 min-w-lg-250px">
                <div class="d-flex align-items-start">
                    <i class="bi bi-person-badge fs-4 text-primary me-3 mt-1"></i>
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Pimpinan</div>
                        <div class="text-gray-800 fw-semibold"><?= esc(session()->get('pimpinan') ?: '-') ?></div>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <i class="bi bi-geo-alt fs-4 text-primary me-3 mt-1"></i>
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Alamat</div>
                        <div class="text-gray-800 fw-semibold"><?= esc(session()->get('address') ?: '-') ?></div>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <i class="bi bi-envelope fs-4 text-primary me-3 mt-1"></i>
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Email</div>
                        <div class="text-gray-800 fw-semibold text-break"><?= esc(session()->get('emailorg') ?: '-') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="separator my-7"></div>

        <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-5 mb-6">
            <div>
                <h2 class="fs-3 fw-bolder text-gray-900 mb-1">Siklus pengelolaan indikator mutu</h2>
                <p class="text-muted mb-0">Gunakan menu di bawah untuk mengelola indikator dari penetapan hingga evaluasi.</p>
            </div>
            <div class="d-flex align-items-center text-primary bg-light-primary rounded p-4">
                <i class="bi bi-clipboard2-pulse fs-2 me-3"></i>
                <span class="fw-bold">Fokus pada mutu dan keselamatan pasien</span>
            </div>
        </div>

        <div class="row g-4 mb-7">
            <div class="col-md-4">
                <div class="border border-gray-300 border-dashed rounded h-100 p-5">
                    <div class="d-flex align-items-center mb-3">
                        <span class="symbol symbol-40px me-3">
                            <span class="symbol-label bg-light-primary"><i class="bi bi-list-check fs-3 text-primary"></i></span>
                        </span>
                        <h3 class="fs-5 fw-bolder text-gray-900 mb-0">Tetapkan indikator</h3>
                    </div>
                    <p class="text-gray-600 mb-0">Tentukan definisi operasional, target, dan dimensi mutu yang dipantau.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border border-gray-300 border-dashed rounded h-100 p-5">
                    <div class="d-flex align-items-center mb-3">
                        <span class="symbol symbol-40px me-3">
                            <span class="symbol-label bg-light-success"><i class="bi bi-people fs-3 text-success"></i></span>
                        </span>
                        <h3 class="fs-5 fw-bolder text-gray-900 mb-0">Tugaskan penanggung jawab</h3>
                    </div>
                    <p class="text-gray-600 mb-0">Pastikan unit dan PIC memahami proses pengumpulan data indikator.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border border-gray-300 border-dashed rounded h-100 p-5">
                    <div class="d-flex align-items-center mb-3">
                        <span class="symbol symbol-40px me-3">
                            <span class="symbol-label bg-light-warning"><i class="bi bi-graph-up-arrow fs-3 text-warning"></i></span>
                        </span>
                        <h3 class="fs-5 fw-bolder text-gray-900 mb-0">Laporkan dan evaluasi</h3>
                    </div>
                    <p class="text-gray-600 mb-0">Catat capaian per periode, lakukan verifikasi, dan tindak lanjuti hasilnya.</p>
                </div>
            </div>
        </div>

        <div class="d-flex overflow-auto h-55px">
            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bolder flex-nowrap">
                <?= $menuorganization ?>
            </ul>
        </div>
    </div>
</div>
