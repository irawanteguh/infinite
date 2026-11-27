<div id="help-center" class="d-flex flex-column gap-6">
    <section class="app-geometric-banner app-module-banner rounded-3 p-6 p-md-8" aria-labelledby="help-title">
        <h2 id="help-title" class="text-white fw-bolder fs-2 mb-2">Ada yang ingin Anda pelajari?</h2>
        <p class="text-white mb-5">Panduan modul dan jawaban untuk pekerjaan sehari-hari di Infinite.</p>
        <label for="help-search" class="text-white fw-bold mb-2">Cari dokumentasi</label>
        <input id="help-search" type="search" class="form-control form-control-solid"
               placeholder="Cari modul, impor, periode, hak akses…" autocomplete="off" aria-controls="help-articles">
    </section>
    <div class="help-layout">
        <nav class="card align-self-start" aria-label="Navigasi dokumentasi">
            <div class="card-body p-4">
                <h2 class="fs-6 fw-bold text-gray-600 px-3 mb-3">Jelajahi modul</h2>
                <div class="help-nav d-flex flex-column gap-1">
                    <a href="#help-articles" class="help-nav-link rounded px-3 py-2" data-help-all>Semua modul</a>
                    <?php foreach ($guides as $guide): ?>
                        <a href="#guide-<?= esc($guide['id'], 'attr') ?>" class="help-nav-link rounded px-3 py-2"
                           data-help-nav="<?= esc($guide['id'], 'attr') ?>"><?= esc($guide['module']) ?></a>
                    <?php endforeach ?>
                </div>
            </div>
        </nav>
        <section id="help-articles" aria-label="Panduan modul">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                <p id="help-count" class="text-gray-700 mb-0" role="status" aria-live="polite"><?= count($guides) ?> modul tersedia</p>
                <button type="button" id="help-reset" class="btn btn-sm btn-light-primary">Reset pencarian</button>
            </div>
            <div id="help-empty" class="alert alert-warning" hidden>
                <h2 class="fs-5">Panduan belum ditemukan</h2>
                <p class="mb-0">Gunakan kata lain atau pilih Reset pencarian untuk melihat semua modul.</p>
            </div>
            <div class="d-flex flex-column gap-4">
                <?php foreach ($guides as $guide): ?>
                    <article class="card help-article" id="guide-<?= esc($guide['id'], 'attr') ?>"
                             data-help-article="<?= esc($guide['id'], 'attr') ?>">
                        <div class="card-body p-5 p-md-6">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <span class="text-primary fw-bold fs-7"><?= esc($guide['module']) ?></span>
                                    <h2 class="fs-4 fw-bolder text-dark mt-1 mb-0"><?= esc($guide['name']) ?></h2>
                                </div>
                                <?php if ($guide['canOpen']): ?>
                                    <a href="<?= esc(site_url($guide['route']), 'attr') ?>" class="btn btn-sm btn-light-primary">Buka modul <i class="bi bi-arrow-up-right ms-1" aria-hidden="true"></i></a>
                                <?php endif ?>
                            </div>
                            <p class="text-gray-700"><?= esc($guide['description']) ?></p>
                            <p class="text-gray-600 fs-7 mb-4"><i class="bi bi-shield-check me-1" aria-hidden="true"></i><?= esc($guide['access']) ?></p>
                            <details class="help-detail">
                                <summary class="fw-bold text-dark py-3">Cara menggunakan dan filter</summary>
                                <ol class="text-gray-700 ps-5">
                                    <?php foreach ($guide['steps'] as $step): ?><li class="mb-2"><?= esc($step) ?></li><?php endforeach ?>
                                </ol>
                                <div class="bg-light rounded p-3 mb-3"><strong>Filter dan periode</strong><p class="mb-0 mt-1 text-gray-700"><?= esc($guide['filters']) ?></p></div>
                            </details>
                            <details class="help-detail">
                                <summary class="fw-bold text-dark py-3"><?= esc($guide['question']) ?></summary>
                                <p class="text-gray-700 mb-2"><?= esc($guide['answer']) ?></p>
                            </details>
                        </div>
                    </article>
                <?php endforeach ?>
            </div>
            <p class="text-gray-600 fs-7 mt-5 mb-0">Pilihan fitur mengikuti izin akun dan konfigurasi organisasi. Hasil integrasi serta ketersediaan data perlu diperiksa pada layanan dan periode yang digunakan.</p>
        </section>
    </div>
</div>
