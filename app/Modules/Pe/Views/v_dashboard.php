<?php
$number = static fn($value, $decimals = 1) => $value === null ? '—' : number_format($value, $decimals, ',', '.');
$services = ['RJ' => 'Rawat Jalan', 'RI' => 'Rawat Inap', 'IGD' => 'IGD', 'OTHER' => 'Belum tercatat'];
$months = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
$chart = $data ? [
    'labels' => array_map(static fn($month) => $months[substr($month, 5, 2)] . ' ' . substr($month, 0, 4), array_keys($data['monthly'])),
    'series' => [
        ['name' => 'PREM', 'data' => array_column($data['monthly'], 'PREM')],
        ['name' => 'PROM PRE / T0', 'data' => array_column($data['monthly'], 'T0')],
        ['name' => 'PROM POST / T1', 'data' => array_column($data['monthly'], 'T1')],
    ],
    'services' => array_values($data['services']),
    'serviceLabels' => array_values($services),
] : null;
?>
<main class="prem-page pe-dashboard">
    <div class="prem-container prem-main">
        <div class="pe-page-heading">
            <div><span class="prem-eyebrow">DARI SUARA PASIEN, MENUJU PERBAIKAN</span><h1>Dashboard Patient Experience</h1><p>Pantau pengalaman pelayanan dan perkembangan kondisi pasien dalam satu tampilan.</p></div>
            <div class="pe-heading-actions"><a href="<?= site_url('pe/prem') ?>" class="btn btn-light"><i class="bi bi-clipboard-heart"></i> PREM</a><a href="<?= site_url('pe/prom') ?>" class="btn btn-light"><i class="bi bi-activity"></i> PROM</a></div>
        </div>

        <form method="get" action="<?= esc(site_url('pe/dashboard'), 'attr') ?>" class="prem-card pe-filter">
            <div><label class="form-label" for="pe-start">Tanggal mulai</label><input class="form-control form-control-solid" id="pe-start" type="date" name="start" value="<?= esc($filters['start'], 'attr') ?>" required></div>
            <div><label class="form-label" for="pe-end">Tanggal akhir</label><input class="form-control form-control-solid" id="pe-end" type="date" name="end" value="<?= esc($filters['end'], 'attr') ?>" required></div>
            <div><label class="form-label" for="pe-service">Jenis pelayanan</label><select id="pe-service" name="service" class="form-select form-select-solid" data-control="select2"><option value="">Semua pelayanan</option><?php foreach (['RJ', 'RI', 'IGD'] as $service): ?><option value="<?= $service ?>"<?= $filters['service'] === $service ? ' selected' : '' ?>><?= $services[$service] ?></option><?php endforeach; ?></select></div>
            <div><label class="form-label" for="pe-instrument">Instrumen</label><select id="pe-instrument" name="instrument" class="form-select form-select-solid" data-control="select2"><option value="">Semua instrumen</option><?php foreach ($instruments as $instrument): ?><option value="<?= esc($instrument['instrument_id'], 'attr') ?>"<?= $filters['instrument'] === $instrument['instrument_id'] ? ' selected' : '' ?>><?= esc($instrument['name']) ?></option><?php endforeach; ?></select></div>
            <div class="pe-filter-actions"><button type="submit" class="btn prem-btn-primary"><i class="bi bi-funnel"></i> Terapkan</button><a href="<?= site_url('pe/dashboard') ?>" class="pe-reset" aria-label="Reset filter" title="Reset filter"><i class="bi bi-arrow-counterclockwise"></i></a></div>
        </form>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger mt-6" role="alert"><?= esc($error) ?></div>
        <?php elseif ($data): ?>
            <div class="pe-period"><span><i class="bi bi-calendar3"></i> <?= esc(date('d/m/Y', strtotime($filters['start']))) ?> – <?= esc(date('d/m/Y', strtotime($filters['end']))) ?></span><span><?= $number($data['total'], 0) ?> respons<?php if ($data['dummy']): ?> <span class="pe-dummy"><?= $number($data['dummy'], 0) ?> data dummy</span><?php endif; ?></span></div>

            <?php if (!$data['total']): ?>
                <section class="prem-card pe-empty"><span class="prem-hero-icon"><i class="bi bi-bar-chart"></i></span><h2>Belum ada respons pada periode ini</h2><p>Ubah periode atau filter pelayanan dan instrumen untuk melihat data yang tersedia.</p><a href="<?= site_url('pe/dashboard') ?>" class="btn prem-btn-primary">Reset filter</a></section>
            <?php else: ?>
            <section class="pe-kpis" aria-label="Ringkasan analitik">
                <article class="prem-card pe-kpi"><div class="pe-kpi-top"><span>Total respons</span><i class="bi bi-chat-square-text"></i></div><strong><?= $number($data['total'], 0) ?></strong><div class="pe-counts"><span>PREM <?= $data['counts']['PREM'] ?></span><span>PRE <?= $data['counts']['T0'] ?></span><span>POST <?= $data['counts']['T1'] ?></span></div><p>Jumlah pengisian, bukan pasien unik</p></article>
                <article class="prem-card pe-kpi"><div class="pe-kpi-top"><span>Rerata pengalaman · PREM</span><i class="bi bi-star"></i></div><strong><?= $number($data['premAverage'], 2) ?><small> / 5</small></strong><div class="pe-kpi-meter"><span style="width:<?= (float) ($data['premAverage'] ?? 0) * 20 ?>%"></span></div><p>Dari <?= $data['counts']['PREM'] ?> respons PREM</p></article>
                <article class="prem-card pe-kpi"><div class="pe-kpi-top"><span>Jawaban positif · PREM</span><i class="bi bi-hand-thumbs-up"></i></div><strong><?= $number($data['positive']) ?><small><?= $data['positive'] === null ? '' : '%' ?></small></strong><span class="pe-kpi-tag">Setuju &amp; sangat setuju</span><p>Skor 4–5 dari <?= $number($data['answerCount'], 0) ?> jawaban</p></article>
                <article class="prem-card pe-kpi pe-kpi-highlight"><div class="pe-kpi-top"><span>Perubahan kondisi · PROM</span><i class="bi bi-activity"></i></div><strong><?= $data['delta'] !== null && $data['delta'] > 0 ? '+' : '' ?><?= $number($data['delta'], 2) ?><small> poin</small></strong><span class="pe-kpi-tag"><?= $number($data['pairs'], 0) ?> pasangan PRE–POST</span><p>Rerata selisih POST dikurangi PRE</p></article>
            </section>

            <div class="pe-main-grid">
                <section class="prem-card pe-panel">
                    <div class="pe-panel-heading"><div><span class="prem-eyebrow">PARTISIPASI PASIEN</span><h2>Tren respons bulanan</h2><p>Perkembangan jumlah pengisian menurut jenis kuesioner.</p></div><span class="pe-panel-label">RESPONS / BULAN</span></div>
                    <div id="pe-trend-chart" class="pe-chart" role="img" aria-label="Grafik tren jumlah respons PREM, PROM PRE, dan PROM POST per bulan"></div>
                    <details class="pe-chart-table"><summary>Lihat data bulanan</summary><div class="table-responsive"><table class="table"><caption class="visually-hidden">Jumlah respons per bulan</caption><thead><tr><th>Bulan</th><th>PREM</th><th>PRE</th><th>POST</th></tr></thead><tbody><?php foreach ($data['monthly'] as $month => $counts): ?><tr><td><?= esc($month) ?></td><td><?= $counts['PREM'] ?></td><td><?= $counts['T0'] ?></td><td><?= $counts['T1'] ?></td></tr><?php endforeach; ?></tbody></table></div></details>
                </section>
                <section class="prem-card pe-panel pe-outcome">
                    <div class="pe-panel-heading"><div><span class="prem-eyebrow">HASIL PERAWATAN</span><h2>Perbandingan PROM</h2><p>Pasangan dengan instrumen, pelayanan, dan nomor rekam medis yang sama.</p></div></div>
                    <?php if ($data['pairs']): ?>
                        <div class="pe-pair-score"><div><span>PRE / T0</span><strong><?= $number($data['preAverage'], 2) ?></strong><small>Sebelum perawatan</small></div><i class="bi bi-arrow-right"></i><div><span>POST / T1</span><strong><?= $number($data['postAverage'], 2) ?></strong><small>Sesudah perawatan</small></div></div>
                        <div class="pe-improvement"><span class="pe-outcome-icon"><i class="bi bi-graph-up-arrow"></i></span><div><strong><?= $number($data['improvedPercent']) ?>% pasangan meningkat</strong><p><?= $data['improved'] ?> dari <?= $data['pairs'] ?> pasangan memiliki skor POST lebih tinggi.</p></div></div>
                    <?php else: ?><div class="pe-panel-empty"><i class="bi bi-link-45deg"></i><p>Belum ada pasangan PRE dan POST yang dapat dibandingkan pada filter ini.</p></div><?php endif; ?>
                    <p class="pe-method">Satu pasangan per rekam medis, instrumen, dan pelayanan: PRE terakhir dengan POST pertama setelahnya, keduanya dalam periode terpilih. Skala 1–5; nilai lebih tinggi menunjukkan kondisi yang lebih baik.</p>
                </section>
            </div>

            <div class="pe-secondary-grid">
                <section class="prem-card pe-panel">
                    <div class="pe-panel-heading"><div><span class="prem-eyebrow">CAKUPAN PELAYANAN</span><h2>Komposisi respons</h2><p>Seluruh pengisian PREM dan PROM.</p></div></div>
                    <div class="pe-service-layout"><div id="pe-service-chart" class="pe-donut" role="img" aria-label="Diagram komposisi respons berdasarkan jenis pelayanan"></div><div class="pe-service-legend"><?php $colorIndex = 0; foreach ($data['services'] as $service => $count): if (!$count && $service === 'OTHER') continue; ?><div><span class="pe-legend-name"><i class="pe-dot pe-dot-<?= $colorIndex++ ?>"></i><?= $services[$service] ?></span><strong><?= $number($count, 0) ?><small><?= $number($count / $data['total'] * 100) ?>%</small></strong></div><?php endforeach; ?></div></div>
                </section>
                <section class="prem-card pe-panel">
                    <div class="pe-panel-heading"><div><span class="prem-eyebrow">SUARA PASIEN</span><h2>Distribusi penilaian PREM</h2><p>Jumlah jawaban per skor, bukan jumlah responden.</p></div></div>
                    <?php if ($data['answerCount']): ?>
                        <div class="pe-distribution"><?php $labels = [5=>'Sangat setuju',4=>'Setuju',3=>'Ragu-ragu',2=>'Tidak setuju',1=>'Sangat tidak setuju']; foreach ($labels as $score => $label): $percent = $data['distribution'][$score] / $data['answerCount'] * 100; ?><div class="pe-dist-row"><span><?= $score ?> <small><?= $label ?></small></span><div class="pe-dist-track"><span class="pe-score-<?= $score ?>" style="width:<?= $percent ?>%"></span></div><strong><?= $number($percent) ?>%<small><?= $number($data['distribution'][$score], 0) ?></small></strong></div><?php endforeach; ?></div>
                    <?php else: ?><div class="pe-panel-empty"><p>Belum ada jawaban PREM pada filter ini.</p></div><?php endif; ?>
                </section>
            </div>

            <section class="prem-card pe-panel pe-domain-panel">
                <div class="pe-panel-heading"><div><span class="prem-eyebrow">TELUSURI LEBIH DALAM</span><h2>Penilaian per domain</h2><p>Rerata skor jawaban menurut instrumen dan domain, skala 1–5.</p></div><span class="pe-panel-label"><?= count($data['domains']) ?> DOMAIN INSTRUMEN</span></div>
                <div class="table-responsive" tabindex="0" role="region" aria-label="Tabel penilaian domain, dapat digulir"><table class="table pe-table align-middle"><thead><tr><th>Domain / instrumen</th><th>Jenis</th><th class="text-center">PREM</th><th class="text-center">PROM PRE</th><th class="text-center">PROM POST</th><th class="text-end">Jawaban</th></tr></thead><tbody><?php foreach ($data['domains'] as $domain): ?><tr><td><strong><?= esc($domain['name']) ?></strong><small><?= esc($domain['instrument']) ?></small></td><td><span class="pe-type"><?= esc($domain['type']) ?></span></td><?php foreach (['PREM', 'T0', 'T1'] as $phase): ?><td class="text-center"><span class="<?= $domain['averages'][$phase] !== null ? 'pe-score-pill' : 'pe-no-value' ?>"><?= $number($domain['averages'][$phase], 2) ?></span></td><?php endforeach; ?><td class="text-end"><?= $number($domain['count'], 0) ?></td></tr><?php endforeach; ?></tbody></table></div>
                <p class="pe-method">Rerata domain PRE/POST mencakup seluruh jawaban pada filter ini, sehingga komposisi responden dapat berbeda. Perbandingan berpasangan tersedia pada kartu PROM di atas.</p>
            </section>

            <section class="prem-card pe-panel pe-priorities">
                <div class="pe-panel-heading"><div><span class="prem-eyebrow">FOKUS PERBAIKAN</span><h2>Area pengalaman yang perlu ditinjau</h2><p>Lima pertanyaan PREM dengan rerata terendah pada periode terpilih.</p></div><i class="bi bi-lightbulb pe-panel-icon"></i></div>
                <?php if ($data['priorities']): ?><div class="pe-priority-list"><?php foreach ($data['priorities'] as $index => $item): ?><article><span class="pe-priority-number"><?= $index + 1 ?></span><div class="pe-priority-copy"><h3><?= esc($item['question']) ?></h3><p><?= esc($item['domain']) ?> · <?= esc($item['instrument']) ?></p></div><div class="pe-priority-score"><strong><?= $number($item['average'], 2) ?><small> / 5</small></strong><span><?= $number($item['positive']) ?>% positif · <?= $item['count'] ?> jawaban</span></div></article><?php endforeach; ?></div><?php else: ?><div class="pe-panel-empty"><p>Belum ada jawaban PREM untuk menentukan area tinjauan.</p></div><?php endif; ?>
            </section>
            <div class="pe-data-note"><i class="bi bi-info-circle"></i><p>Rerata PREM dihitung dari rerata tiap respons. Persentase positif memakai jawaban berskor 4–5. Hasil bersifat deskriptif dan bukan kesimpulan klinis. Nama instrumen, domain, dan pertanyaan mengikuti master terbaru.<?php if ($data['dummy']): ?> <strong>Periode ini mencakup data dummy untuk simulasi.</strong><?php endif; ?></p></div>
            <?php endif; ?>
            <script type="application/json" id="pe-chart-data"><?= json_encode($chart, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
        <?php endif; ?>
    </div>
</main>