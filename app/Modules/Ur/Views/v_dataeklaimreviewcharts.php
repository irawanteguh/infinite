<div class="prem-page ur-dashboard ek-page ek-review">
    <header class="ur-hero app-page-heading">
        <div>
            <h1>Tren Temuan</h1>
            <p>Bandingkan jumlah episode yang perlu ditinjau dari bulan ke bulan.</p>
        </div>
    </header>
    <section class="prem-card ur-filterbar app-filter-card" aria-labelledby="trend-filter-title">
        <div class="app-filter-heading">
            <h2 id="trend-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter tren</h2>
        </div>
        <form id="review-filters" class="app-filter-fields" method="get" action="<?= esc(site_url('ur/dataeklaim/reviewcharts'), 'attr') ?>">
            <div class="ur-filter">
                <label for="trend-service">Jenis layanan</label>
                <select id="trend-service" name="service" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true">
                    <?php foreach (['' => 'Semua layanan', '1' => 'Rawat inap', '2' => 'Rawat jalan'] as $value => $label): ?>
                        <option value="<?= esc((string) $value, 'attr') ?>" <?= $filters['service'] === (string) $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </section>
    <section class="card ek-panel app-table-card" id="review-trends"
        aria-labelledby="review-trends-title"
        data-endpoint="<?= esc(site_url('ur/dataeklaim/reviewtrend'), 'attr') ?>"
        data-year="<?= $filters['year'] ?>" data-service="<?= esc($filters['service'], 'attr') ?>"
        data-current-month="<?= $filters['year'] === (int) date('Y') ? (int) date('n') : 0 ?>">
        <div class="app-table-heading">
            <div>
                <h2 id="review-trends-title" class="app-table-title">Tren temuan per kategori</h2>
                <p class="app-table-description">SEP unik dengan temuan per bulan pulang, sepanjang tahun dan jenis layanan terpilih. Satu SEP bisa muncul pada beberapa kategori.</p>
            </div>
        </div>
        <div class="ek-status" id="review-trend-status" role="status"></div>
        <div class="row g-4" id="review-trend-charts" hidden>
            <?php foreach ($categories as $category => $label): ?>
                <div class="col-12 col-xl-6">
                    <div class="card p-4 h-100">
                        <h3 class="fs-6"><?= esc($label) ?></h3>
                        <div class="text-muted fs-7" data-trend-change="<?= esc($category, 'attr') ?>"></div>
                        <div data-trend-category="<?= esc($category, 'attr') ?>" aria-label="<?= esc($label . ' per bulan', 'attr') ?>"></div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="col-12">
                <div class="ek-table-info mt-0" role="note">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    <div>Perbandingan memakai dua bulan berurutan yang memiliki data. Bulan berjalan bisa belum lengkap. Perubahan jumlah menunjukkan perubahan temuan pada data yang tersedia dan dapat dipengaruhi volume episode serta kelengkapan impor.</div>
                </div>
            </div>
        </div>
    </section>
</div>
