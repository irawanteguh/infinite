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
    'scoreSeries' => [
        ['name' => 'PREM', 'data' => array_column($data['monthlyAverages'], 'PREM')],
        ['name' => 'PROM PRE / T0', 'data' => array_column($data['monthlyAverages'], 'T0')],
        ['name' => 'PROM POST / T1', 'data' => array_column($data['monthlyAverages'], 'T1')],
    ],
    'skm' => [
        'labels' => ['IGD', 'Rawat Inap', 'Rawat Jalan'],
        'counts' => array_column(array_values($data['skm']['services']), 'count'),
        'months' => array_keys($data['skm']['monthly']),
        'series' => array_map(static fn($code, $label) => ['name' => $label, 'data' => array_column($data['skm']['monthly'], $code)], ['IGD', 'RI', 'RJ'], ['IGD', 'Rawat Inap', 'Rawat Jalan']),
    ],
    'services' => array_values($data['services']),
    'serviceLabels' => array_values($services),
] : null;
?>
<main class="prem-page pe-dashboard">
    <div class="prem-container prem-main">
        <div class="pe-page-heading">
            <div><span class="prem-eyebrow">PATIENT EXPERIENCE</span><h1>Dashboard Patient Experience</h1><p>Pantau pengalaman pelayanan dan perkembangan kondisi pasien dalam satu tampilan.</p></div>
            <div class="pe-heading-actions"><a href="<?= esc(site_url('pe/skm') . '?' . http_build_query(['group_id' => session()->get('groupid'), 'org_id' => session()->get('orgid')]), 'attr') ?>" class="btn btn-light"><i class="bi bi-clipboard-check"></i> SKM</a><a href="<?= esc(site_url('pe/prem') . '?' . http_build_query(['group_id' => session()->get('groupid'), 'org_id' => session()->get('orgid')]), 'attr') ?>" class="btn btn-light"><i class="bi bi-clipboard-heart"></i> PREM</a><a href="<?= esc(site_url('pe/prom') . '?' . http_build_query(['group_id' => session()->get('groupid'), 'org_id' => session()->get('orgid')]), 'attr') ?>" class="btn btn-light"><i class="bi bi-activity"></i> PROM</a></div>
        </div>

        <section class="prem-card pe-filter-card" aria-label="Filter dan laporan">
        <div class="pe-filter-heading"><i class="bi bi-sliders"></i><h2>Filter dashboard</h2><span>Tentukan periode dan cakupan data</span></div>
        <form method="get" action="<?= esc(site_url('pe/dashboard'), 'attr') ?>" class="pe-filter">
            <div><label class="form-label" for="pe-start">Tanggal mulai</label><input class="form-control form-control-solid" id="pe-start" type="date" name="start" value="<?= esc($filters['start'], 'attr') ?>" required></div>
            <div><label class="form-label" for="pe-end">Tanggal akhir</label><input class="form-control form-control-solid" id="pe-end" type="date" name="end" value="<?= esc($filters['end'], 'attr') ?>" required></div>
            <div><label class="form-label" for="pe-service">Jenis pelayanan</label><select id="pe-service" name="service" class="form-select form-select-solid" data-control="select2"><option value="">Semua pelayanan</option><?php foreach (['RJ', 'RI', 'IGD'] as $service): ?><option value="<?= $service ?>"<?= $filters['service'] === $service ? ' selected' : '' ?>><?= $services[$service] ?></option><?php endforeach; ?></select></div>
            <div><label class="form-label" for="pe-instrument">Instrumen</label><select id="pe-instrument" name="instrument" class="form-select form-select-solid" data-control="select2"><option value="">Semua instrumen</option><?php foreach ($instruments as $instrument): ?><option value="<?= esc($instrument['instrument_id'], 'attr') ?>"<?= $filters['instrument'] === $instrument['instrument_id'] ? ' selected' : '' ?>><?= esc($instrument['name']) ?></option><?php endforeach; ?></select></div>
            <div class="pe-filter-actions"><button type="submit" class="btn prem-btn-primary"><i class="bi bi-funnel"></i> Terapkan</button><a href="<?= site_url('pe/dashboard') ?>" class="pe-reset" aria-label="Reset filter" title="Reset filter"><i class="bi bi-arrow-counterclockwise"></i></a></div>
        </form>

        <details class="pe-reports-toggle"><summary><span><i class="bi bi-file-earmark-text"></i> Laporan PDF</span><span class="pe-reports-toggle-label"><span class="pe-reports-show">Tampilkan laporan</span><span class="pe-reports-hide">Sembunyikan laporan</span><i class="bi bi-chevron-down"></i></span></summary><div class="pe-report-grid">
            <form method="get" action="<?= esc(site_url('pe/dashboard/annualreport'), 'attr') ?>" target="_blank" class="pe-report-form">
                <div class="pe-report-heading"><span class="pe-report-icon"><i class="bi bi-file-earmark-bar-graph"></i></span><div><h3>Laporan tahunan PE</h3><p>Ringkasan Patient Experience seluruh layanan dan instrumen.</p></div></div>
                <div class="pe-report-fields"><div><label for="pe-report-year" class="form-label">Tahun laporan</label><input id="pe-report-year" type="number" name="year" min="2000" max="<?= date('Y') ?>" value="<?= date('Y') ?>" class="form-control form-control-solid" required></div></div>
                <div class="pe-report-actions"><button type="submit" class="btn pe-report-button"><i class="bi bi-file-earmark-pdf"></i> Laporan tahunan PDF</button></div>
            </form>
            <form method="get" action="<?= esc(site_url('pe/dashboard/skmreport'), 'attr') ?>" target="_blank" class="pe-report-form pe-skm-report-form">
                <div class="pe-report-heading"><span class="pe-report-icon"><i class="bi bi-clipboard-data"></i></span><div><h3>Laporan SKM</h3><p>Seluruh layanan SKM. Pilih bulan untuk laporan per bulan.</p></div></div>
                <div class="pe-report-fields">
                    <div><label for="pe-skm-year" class="form-label">Tahun laporan</label><input id="pe-skm-year" type="number" name="year" min="2000" max="<?= date('Y') ?>" value="<?= date('Y') ?>" class="form-control form-control-solid" required></div>
                    <div><label for="pe-skm-month" class="form-label">Bulan</label><select id="pe-skm-month" name="month" class="form-select form-select-solid" data-control="select2"><?php foreach ($months as $value => $label): ?><option value="<?= $value ?>"<?= (string) $value === date('m') ? ' selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
                </div>
                <div class="pe-report-actions"><button type="submit" name="period" value="year" class="btn pe-report-button"><i class="bi bi-file-earmark-pdf"></i> Per tahun</button><button type="submit" name="period" value="month" class="btn pe-report-button"><i class="bi bi-file-earmark-pdf"></i> Per bulan</button></div>
            </form>
        </div></details>
        </section>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger mt-6" role="alert"><?= esc($error) ?></div>
        <?php elseif ($data): ?>
            <div class="pe-period"><span><i class="bi bi-calendar3"></i> <?= esc(date('d/m/Y', strtotime($filters['start']))) ?> – <?= esc(date('d/m/Y', strtotime($filters['end']))) ?></span><span><?= $number($data['total'], 0) ?> respons</span></div>

            <section class="prem-card pe-panel pe-skm-overview mb-6" aria-label="Analitik SKM per pelayanan">
                <div class="pe-panel-heading"><div><span class="prem-eyebrow">SURVEI KEPUASAN MASYARAKAT</span><h2>SKM menurut jenis pelayanan</h2><p>Perbandingan IGD, Rawat Inap, dan Rawat Jalan sesuai periode serta filter aktif.</p></div><span class="pe-panel-label"><?= $number($data['skm']['count'], 0) ?> RESPONS SKM</span></div>
                <div class="pe-skm-cards">
                    <?php foreach ($data['skm']['services'] as $code => $item): ?>
                    <article class="pe-skm-service"><h3><?= esc($services[$code]) ?></h3><strong><?= $number($item['average'], 2) ?><small> / 4</small></strong><p><?= $number($item['count'], 0) ?> respons <span>· <?= $number($item['scoredCount'], 0) ?> dinilai pada skala 4</span></p></article>
                    <?php endforeach; ?>
                </div>
                <div class="pe-skm-charts">
                    <div><h3>Jumlah respons per pelayanan</h3><div id="pe-skm-count-chart" class="pe-chart" role="img" aria-label="Grafik jumlah respons SKM IGD, Rawat Inap dan Rawat Jalan"></div></div>
                    <div><h3>Tren rerata SKM bulanan</h3><div id="pe-skm-score-chart" class="pe-chart" role="img" aria-label="Grafik tren rerata SKM bulanan untuk tiga pelayanan"></div></div>
                </div>
                <p class="pe-method">Rerata dihitung dari rerata tiap respons SKM skala 1–4, dengan bobot yang sama per respons. Bulan tanpa jawaban tidak diberi nilai. Jumlah respons mencakup seluruh skala SKM; data tidak dicampur dengan PREM/PROM.</p>
            </section>
            <section class="prem-card pe-panel mb-6" aria-label="Distribusi penilaian SKM">
                <div class="pe-panel-heading"><div><span class="prem-eyebrow">HASIL PENILAIAN SKM</span><h2>Distribusi penilaian SKM</h2><p>Penilaian per unsur dan komposisi kumulatif seluruh jawaban SKM sesuai filter aktif.</p></div><span class="pe-panel-label"><?= $number($data['skm']['answerCount'], 0) ?> JAWABAN</span></div>
                <div class="row g-5 pe-skm-distribution-grid">
                <div class="col-12 col-lg-6"><div class="pe-skm-distribution-panel"><h3 class="pe-skm-distribution-title">Penilaian per unsur kuesioner</h3>
                <?php if ($data['skm']['questionDistributions']): ?>
                <div class="pe-distribution-select"><label for="pe-distribution-question" class="form-label">Unsur kuesioner</label><select id="pe-distribution-question" class="form-select form-select-solid" data-control="select2"><?php foreach ($data['skm']['questionDistributions'] as $index => $item): ?><option value="<?= $index ?>"><?= esc($item['question']) ?></option><?php endforeach; ?></select></div>
                <?php foreach ($data['skm']['questionDistributions'] as $index => $item): ?>
                <div class="pe-question-distribution<?= $index ? ' d-none' : '' ?>" data-distribution-index="<?= $index ?>">
                    <h3><?= esc($item['question']) ?></h3><p class="pe-method"><?= $number($item['total'], 0) ?> jawaban pada unsur ini</p>
                    <div class="pe-distribution">
                    <?php foreach (array_reverse($item['options'], true) as $score => $label): $count = $item['counts'][$score]; $percent = $item['total'] ? $count / $item['total'] * 100 : 0; ?>
                        <div class="pe-dist-row"><span><?= esc($label) ?></span><div class="pe-dist-track" aria-hidden="true"><span class="pe-score-<?= $score + 1 ?>" style="width:<?= $percent ?>%"></span></div><strong><?= $number($percent) ?>%<small><?= $number($count, 0) ?></small></strong></div>
                    <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <p class="pe-method">Label mengikuti pilihan jawaban master untuk setiap unsur. Pertanyaan dan pilihan yang sama digabungkan antar layanan sesuai filter aktif. Persentase dihitung dari jawaban unsur terpilih.</p>
                <?php else: ?><div class="pe-panel-empty"><p>Belum ada jawaban SKM pada filter ini.</p></div><?php endif; ?>
                </div></div>
                <div class="col-12 col-lg-6"><div class="pe-skm-distribution-panel">
                    <h3 class="pe-skm-distribution-title">Penilaian kumulatif berdasarkan skor</h3>
                    <p class="pe-method">Gabungan seluruh unsur kuesioner, sesuai filter aktif.</p>
                    <div class="pe-skm-cumulative-total"><strong><?= $number($data['skm']['answerCount'], 0) ?></strong><span>total jawaban SKM</span></div>
                    <?php if ($data['skm']['answerCount']): ?>
                    <div class="pe-distribution">
                        <?php foreach ([4, 3, 2, 1] as $score): $count = $data['skm']['distribution'][$score]; $percent = $count / $data['skm']['answerCount'] * 100; ?>
                        <div class="pe-dist-row"><span>Skor <?= $score ?></span><div class="pe-dist-track" aria-hidden="true"><span class="pe-score-<?= $score + 1 ?>" style="width:<?= $percent ?>%"></span></div><strong><?= $number($percent) ?>%<small><?= $number($count, 0) ?></small></strong></div>
                        <?php endforeach; ?>
                    </div>
                    <p class="pe-method">Dihitung dari seluruh jawaban skala 1–4, bukan jumlah responden. Makna pilihan jawaban mengikuti masing-masing unsur; skor lebih tinggi menunjukkan penilaian lebih baik.</p>
                    <?php else: ?><div class="pe-panel-empty"><p>Belum ada jawaban SKM pada filter ini.</p></div><?php endif; ?>
                </div></div>
                </div>
            </section>
            <?php if (!empty($data['skm']['count'])): ?>
                <section class="prem-card pe-panel mb-6">
                    <div class="pe-panel-heading"><div><span class="prem-eyebrow">TRANSAKSI SKM · TERPISAH DARI PREM/PROM</span><h2>Survei Kepuasan Masyarakat</h2><p><?= $number($data['skm']['count'], 0) ?> respons SKM pada periode dan organisasi terpilih. Rerata dipisahkan berdasarkan instrumen dan skala.</p></div></div>
                    <div class="row g-4 pe-skm-tables">
                    <?php foreach ($data['skm']['groups'] as $skmGroup): ?>
                        <div class="col-12 col-lg-4"><article class="pe-skm-table-card">
                        <h3 class="pe-skm-table-title"><?= esc($skmGroup['name']) ?> <span class="pe-skm-table-summary">· <?= $skmGroup['count'] ?> respons · rerata <?= $number($skmGroup['average'], 2) ?> / <?= $skmGroup['scale'] ?></span></h3>
                        <div class="table-responsive"><table class="table pe-table"><thead><tr><th>Unsur pelayanan</th><th class="text-center">Rerata / <?= $skmGroup['scale'] ?></th><th class="text-end">Jawaban</th></tr></thead><tbody><?php foreach ($skmGroup['questions'] as $item): ?><tr><td><?= esc($item['question']) ?></td><td class="text-center"><?= $number((float) $item['average'], 2) ?></td><td class="text-end"><?= $number($item['answer_count'], 0) ?></td></tr><?php endforeach; ?></tbody></table></div>
                        </article></div>
                    <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            <section class="prem-card pe-panel mb-6" aria-label="Informasi poli SKM Rawat Jalan">
                <div class="pe-panel-heading"><div><span class="prem-eyebrow">POLI RAWAT JALAN</span><h2>Sebaran responden menurut poli</h2><p><?= $number($data['skm']['outpatientCount'], 0) ?> pengisian SKM Rawat Jalan sesuai filter aktif.</p></div><span class="pe-panel-label"><?= count(array_filter($data['skm']['clinics'], static fn($item) => $item['name'] !== 'Belum tercatat')) ?> POLI TERCATAT</span></div>
                <?php if ($data['skm']['clinics']): ?>
                <div class="table-responsive pe-clinic-table" tabindex="0" role="region" aria-label="Tabel sebaran poli, dapat digulir"><table class="table pe-table"><thead><tr><th>Poli / layanan</th><th class="text-end">Respons</th><th class="text-end">Persentase</th><th class="pe-clinic-bar-column">Komposisi</th></tr></thead><tbody>
                    <?php foreach ($data['skm']['clinics'] as $clinic): $share = $clinic['count'] / $data['skm']['outpatientCount'] * 100; ?>
                    <tr><td><?= esc($clinic['name']) ?></td><td class="text-end"><?= $number($clinic['count'], 0) ?></td><td class="text-end"><?= $number($share) ?>%</td><td class="pe-clinic-bar-column"><div class="pe-profile-track" aria-hidden="true"><span style="width:<?= $share ?>%"></span></div></td></tr>
                    <?php endforeach; ?>
                </tbody></table></div>
                <?php else: ?><div class="pe-panel-empty"><p>Belum ada respons SKM Rawat Jalan pada filter ini.</p></div><?php endif; ?>
            </section>
            <section class="prem-card pe-panel mb-6" aria-label="Demografi responden SKM">
                <div class="pe-panel-heading"><div><span class="prem-eyebrow">PROFIL RESPONDEN SKM</span><h2>Demografi responden</h2><p>Komposisi dari <?= $number($data['skm']['profileCount'], 0) ?> pengisian SKM sesuai filter aktif.</p></div></div>
                <div class="row g-4 pe-demographics">
                    <?php foreach ($data['skm']['demographics'] as $demographic): ?>
                    <div class="col-12 col-md-6 col-xl-4"><article class="pe-demographic-card"><h3><?= esc($demographic['label']) ?></h3><p><?= $number($demographic['total'], 0) ?> respons</p>
                        <?php if (!$demographic['total']): ?><p class="pe-demographic-empty">Belum ada data pada filter ini.</p><?php else: ?>
                        <ul class="pe-profile-list">
                        <?php foreach ($demographic['counts'] as $label => $count): if (!$count) continue; $share = $count / $demographic['total'] * 100; ?>
                            <li><div><span><?= esc($label) ?></span><strong><?= $number($count, 0) ?> <small>· <?= $number($share) ?>%</small></strong></div><div class="pe-profile-track" aria-hidden="true"><span style="width:<?= $share ?>%"></span></div></li>
                        <?php endforeach; ?>
                        </ul><?php endif; ?>
                    </article></div>
                    <?php endforeach; ?>
                </div>
                <p class="pe-method">Dihitung per pengisian SKM, bukan orang unik, dan tidak dihitung ulang dari PREM lanjutan. Data kosong ditampilkan sebagai “Belum tercatat”. Kelas layanan hanya ditampilkan untuk Rawat Inap; IGD dan Rawat Jalan tidak termasuk dalam perhitungan kelas.</p>
            </section>
            <div class="row g-6 mb-6 pe-feedback-row">
                <div class="col-12 col-lg-6"><section class="prem-card pe-panel h-100" aria-label="Word cloud saran dan masukan">
                    <div class="pe-panel-heading"><div><span class="prem-eyebrow">ANALISIS SARAN DAN MASUKAN</span><h2>Word cloud</h2><p>Kata yang paling banyak muncul pada masukan SKM dan PREM sesuai filter aktif.</p></div></div>
                    <?php if ($data['feedback']['words']): $maxWord = max(array_column($data['feedback']['words'], 'count')); ?>
                    <div class="pe-word-cloud" role="list" aria-label="Kata dan jumlah masukan yang memuatnya">
                        <?php foreach ($data['feedback']['words'] as $index => $word): ?>
                        <span role="listitem" class="pe-cloud-word pe-cloud-color-<?= $index % 4 ?>" style="font-size:<?= 13 + round(25 * sqrt($word['count'] / $maxWord)) ?>px" title="<?= esc($word['word'], 'attr') ?>: <?= $word['count'] ?> masukan"><?= esc($word['word']) ?><small><?= $word['count'] ?></small></span>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?><div class="pe-panel-empty"><p>Belum ada saran tertulis pada filter ini.</p></div><?php endif; ?>
                    <p class="pe-method">Maksimal 50 kata. Ukuran kata menunjukkan jumlah masukan yang memuatnya; pengulangan kata dalam satu masukan dihitung sekali. Kata umum dan penanda dummy diabaikan.</p>
                </section></div>
                <div class="col-12 col-lg-6"><section class="prem-card pe-panel h-100" aria-label="Estimasi sentimen saran dan masukan">
                    <div class="pe-panel-heading"><div><span class="prem-eyebrow">ESTIMASI SENTIMEN OTOMATIS</span><h2>Sentimen saran dan masukan</h2><p><?= $number($data['feedback']['total'], 0) ?> masukan tertulis · SKM <?= $number($data['feedback']['sources']['SKM'], 0) ?> · PREM <?= $number($data['feedback']['sources']['PREM'], 0) ?></p></div></div>
                    <?php if ($data['feedback']['total']): ?>
                    <div class="pe-sentiment-summary">
                        <?php foreach (['positive' => 'Positif', 'neutral' => 'Netral / campuran', 'negative' => 'Negatif'] as $key => $label): $count = $data['feedback']['sentiments'][$key]; $share = $count / $data['feedback']['total'] * 100; ?>
                        <div class="pe-sentiment-item"><div><span><?= $label ?></span><strong><?= $number($count, 0) ?> <small>· <?= $number($share) ?>%</small></strong></div><div class="pe-sentiment-track"><span class="pe-sentiment-<?= $key ?>" style="width:<?= $share ?>%"></span></div></div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?><div class="pe-panel-empty"><p>Belum ada saran tertulis untuk dianalisis.</p></div><?php endif; ?>
                    <p class="pe-method">Estimasi berbasis kata positif/negatif dan negasi sederhana Bahasa Indonesia. Netral/campuran mencakup kata yang belum dikenali atau skor berimbang. Hasil perlu ditinjau petugas, terutama untuk saran, konteks, dan sarkasme. Satu masukan dihitung sebagai satu analisis, bukan satu pasien unik.</p>
                </section></div>
            </div>
            <p class="pe-method mb-5">Ringkasan PREM/PROM berikut memakai skala 1–5. Transaksi SKM ditampilkan terpisah di atas dan tidak dicampurkan ke perhitungan PREM/PROM.</p>
            <?php if (!$data['total']): ?>
                <section class="prem-card pe-empty"><span class="prem-hero-icon"><i class="bi bi-bar-chart"></i></span><h2>Belum ada respons PREM/PROM pada periode ini</h2><p>Ubah periode atau filter pelayanan dan instrumen untuk melihat data yang tersedia.</p><a href="<?= site_url('pe/dashboard') ?>" class="btn prem-btn-primary">Reset filter</a></section>
            <?php else: ?>
            <section class="pe-kpis" aria-label="Ringkasan analitik">
                <article class="prem-card pe-kpi"><div class="pe-kpi-top"><span>Total respons</span><i class="bi bi-chat-square-text"></i></div><strong><?= $number($data['total'], 0) ?></strong><div class="pe-counts"><span>PREM <?= $data['counts']['PREM'] ?></span><span>PRE <?= $data['counts']['T0'] ?></span><span>POST <?= $data['counts']['T1'] ?></span></div><p>Jumlah pengisian, bukan pasien unik</p></article>
                <article class="prem-card pe-kpi"><div class="pe-kpi-top"><span>Rerata pengalaman · PREM</span><i class="bi bi-star"></i></div><strong><?= $number($data['premAverage'], 2) ?><small> / 5</small></strong><div class="pe-kpi-meter"><span style="width:<?= (float) ($data['premAverage'] ?? 0) * 20 ?>%"></span></div><p>Dari <?= $data['counts']['PREM'] ?> respons PREM</p></article>
                <article class="prem-card pe-kpi"><div class="pe-kpi-top"><span>Jawaban positif · PREM</span><i class="bi bi-hand-thumbs-up"></i></div><strong><?= $number($data['positive']) ?><small><?= $data['positive'] === null ? '' : '%' ?></small></strong><span class="pe-kpi-tag">Setuju &amp; sangat setuju</span><p>Skor 4–5 dari <?= $number($data['answerCount'], 0) ?> jawaban</p></article>
                <article class="prem-card pe-kpi pe-kpi-highlight"><div class="pe-kpi-top"><span>Perubahan kondisi · PROM</span><i class="bi bi-activity"></i></div><strong><?= $data['delta'] !== null && $data['delta'] > 0 ? '+' : '' ?><?= $number($data['delta'], 2) ?><small> poin</small></strong><span class="pe-kpi-tag"><?= $number($data['pairs'], 0) ?> pasangan PRE–POST</span><p>Rerata selisih POST dikurangi PRE</p></article>
            </section>

            <section class="prem-card pe-panel mb-6">
                <div class="pe-panel-heading"><div><span class="prem-eyebrow">PERKEMBANGAN PENILAIAN</span><h2>Tren nilai bulanan</h2><p>Rerata skor per bulan untuk PREM, PROM PRE / T0, dan PROM POST / T1.</p></div><span class="pe-panel-label">SKALA 1–5</span></div>
                <div id="pe-score-chart" class="pe-chart" role="img" aria-label="Grafik garis rerata skor PREM, PROM PRE, dan PROM POST per bulan"></div>
                <details class="pe-chart-table"><summary>Lihat nilai bulanan</summary><div class="table-responsive"><table class="table"><caption class="visually-hidden">Rerata skor per bulan, skala 1–5</caption><thead><tr><th>Bulan</th><th>PREM</th><th>PROM PRE / T0</th><th>PROM POST / T1</th></tr></thead><tbody><?php foreach ($data['monthlyAverages'] as $month => $averages): ?><tr><td><?= esc($month) ?></td><?php foreach (['PREM', 'T0', 'T1'] as $phase): ?><td><?= $number($averages[$phase], 2) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div></details>
                <p class="pe-method">Dihitung dari rerata tiap respons berdasarkan bulan pengisian dan filter aktif. Bulan tanpa jawaban tidak diberi nilai. Komposisi instrumen dan responden dapat berbeda tiap bulan; gunakan filter instrumen untuk melihat tren instrumen tertentu. Seri PROM mencakup seluruh respons, bukan hanya pasangan PRE–POST.</p>
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
                    <div class="pe-panel-heading"><div><span class="prem-eyebrow">HASIL PENILAIAN PREM</span><h2>Distribusi penilaian PREM</h2><p>Jumlah jawaban per skor, bukan jumlah responden.</p></div></div>
                    <?php if ($data['answerCount']): ?>
                        <div class="pe-distribution"><?php $labels = [5=>'Sangat setuju',4=>'Setuju',3=>'Ragu-ragu',2=>'Tidak setuju',1=>'Sangat tidak setuju']; foreach ($labels as $score => $label): $percent = $data['distribution'][$score] / $data['answerCount'] * 100; ?><div class="pe-dist-row"><span><?= $label ?></span><div class="pe-dist-track"><span class="pe-score-<?= $score ?>" style="width:<?= $percent ?>%"></span></div><strong><?= $number($percent) ?>%<small><?= $number($data['distribution'][$score], 0) ?></small></strong></div><?php endforeach; ?></div>
                    <?php else: ?><div class="pe-panel-empty"><p>Belum ada jawaban PREM pada filter ini.</p></div><?php endif; ?>
                </section>
            </div>

            <div class="row g-6 pe-insight-row"><div class="col-12 col-lg-6"><section class="prem-card pe-panel pe-domain-panel">
                <div class="pe-panel-heading"><div><span class="prem-eyebrow">TELUSURI LEBIH DALAM</span><h2>Penilaian per domain</h2><p>Rerata skor jawaban menurut instrumen dan domain, skala 1–5.</p></div><span class="pe-panel-label"><?= count($data['domains']) ?> DOMAIN INSTRUMEN</span></div>
                <div class="table-responsive" tabindex="0" role="region" aria-label="Tabel penilaian domain, dapat digulir"><table class="table pe-table align-middle"><thead><tr><th>Domain / instrumen</th><th>Jenis</th><th class="text-center">PREM</th><th class="text-center">PROM PRE</th><th class="text-center">PROM POST</th><th class="text-end">Jumlah pengisian</th></tr></thead><tbody><?php foreach ($data['domains'] as $domain): ?><tr><td><strong><?= esc($domain['name']) ?></strong><small><?= esc($domain['instrument']) ?></small></td><td><span class="pe-type"><?= esc($domain['type']) ?></span></td><?php foreach (['PREM', 'T0', 'T1'] as $phase): ?><td class="text-center"><span class="<?= $domain['averages'][$phase] !== null ? 'pe-score-pill' : 'pe-no-value' ?>"><?= $number($domain['averages'][$phase], 2) ?></span></td><?php endforeach; ?><td class="text-end"><?= $number($domain['submissionCount'], 0) ?></td></tr><?php endforeach; ?></tbody></table></div>
                <p class="pe-method">Jumlah pengisian menghitung setiap pengisian satu kali per domain, termasuk T0 dan T1 sebagai pengisian terpisah; bukan jumlah orang unik. Rerata domain PRE/POST mencakup seluruh jawaban pada filter ini, sehingga komposisi responden dapat berbeda. Perbandingan berpasangan tersedia pada kartu PROM di atas.</p>
            </section>

            </div><div class="col-12 col-lg-6"><section class="prem-card pe-panel pe-priorities">
                <div class="pe-panel-heading"><div><span class="prem-eyebrow">FOKUS PERBAIKAN</span><h2>Area pengalaman yang perlu ditinjau</h2><p>Lima pertanyaan PREM dengan rerata terendah pada periode terpilih.</p></div><i class="bi bi-lightbulb pe-panel-icon"></i></div>
                <?php if ($data['priorities']): ?><div class="pe-priority-list"><?php foreach ($data['priorities'] as $index => $item): ?><article><span class="pe-priority-number"><?= $index + 1 ?></span><div class="pe-priority-copy"><h3><?= esc($item['question']) ?></h3><p><?= esc($item['domain']) ?> · <?= esc($item['instrument']) ?></p></div><div class="pe-priority-score"><strong><?= $number($item['average'], 2) ?><small> / 5</small></strong><span><?= $number($item['positive']) ?>% positif · <?= $item['count'] ?> jawaban</span></div></article><?php endforeach; ?></div><?php else: ?><div class="pe-panel-empty"><p>Belum ada jawaban PREM untuk menentukan area tinjauan.</p></div><?php endif; ?>
            </section>
            </div></div><div class="pe-data-note"><i class="bi bi-info-circle"></i><p>Rerata PREM dihitung dari rerata tiap respons. Persentase positif memakai jawaban berskor 4–5. Hasil bersifat deskriptif dan bukan kesimpulan klinis. Nama instrumen, domain, dan pertanyaan mengikuti master terbaru.</p></div>
            <?php endif; ?>
            <script type="application/json" id="pe-chart-data"><?= json_encode($chart, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
        <?php endif; ?>
    </div>
</main>
