<?php
$esc = static function ($value): string { return htmlspecialchars((string) ($value ?? '-'), ENT_QUOTES, 'UTF-8'); };
$fmt = static function ($value, int $decimals = 2): string { return number_format((float) $value, $decimals, ',', '.'); };
$monthNames = [1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'Mei', 6=>'Jun', 7=>'Jul', 8=>'Agu', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des'];
$isMet = static function ($row): bool {
    if ($row->rata_pencapaian === null || $row->target === null || $row->target === '') return false;
    return ($row->target_capaian ?? '') === 'L'
        ? (float) $row->rata_pencapaian <= (float) $row->target
        : (float) $row->rata_pencapaian >= (float) $row->target;
};
$scored = array_values(array_filter($indicators, static function ($row) { return $row->rata_pencapaian !== null && $row->rata_pencapaian !== ''; }));
$indicatorCount = count($indicators);
$scoredCount = count($scored);
$average = $scoredCount ? array_sum(array_map(static function ($row) { return (float) $row->rata_pencapaian; }, $scored)) / $scoredCount : null;
$metCount = count(array_filter($scored, $isMet));
$metPct = $scoredCount ? round($metCount / $scoredCount * 100, 1) : null;
$measurementCount = array_sum(array_map(static function ($row) { return (int) $row->jumlah_penilaian; }, $indicators));
$coverage = $indicatorCount ? round($measurementCount / ($indicatorCount * 12) * 100, 1) : 0;
$coveredMonths = [];
foreach ($trend as $row) $coveredMonths[(int) $row->bulan] = true;
$monthCount = count($coveredMonths);
$nationalCount = count(array_filter($indicators, static function ($row) { return $row->kategori === 'Nasional Mutu'; }));
$fishboneCauses = array_sum(array_map(static function ($analysis) { return count($analysis->causes); }, $fishbones));
$causeCounts = [];
foreach ($fishbones as $analysis) foreach ($analysis->causes as $cause) {
    $category = trim((string) ($cause->category ?? 'Lainnya')) ?: 'Lainnya';
    $causeCounts[$category] = ($causeCounts[$category] ?? 0) + 1;
}
arsort($causeCounts);
$pdsaItems = 0; $pdsaCompleted = 0; $phaseCounts = ['Plan'=>0, 'Do'=>0, 'Study'=>0, 'Act'=>0];
foreach ($cycles as $cycle) foreach ($cycle->items as $item) {
    $pdsaItems++;
    if ((int) ($item->completed ?? 0) === 1) $pdsaCompleted++;
    $phase = ucfirst(strtolower((string) ($item->phase ?? '')));
    if (isset($phaseCounts[$phase])) $phaseCounts[$phase]++;
}
$pdsaCompletion = $pdsaItems ? round($pdsaCompleted / $pdsaItems * 100, 1) : null;
$summaryNarrative = 'Pada tahun ' . ($periode->tahun ?? '-') . ' terdapat ' . $indicatorCount . ' indikator mutu aktif pada periode pelaporan. ';
$summaryNarrative .= $scoredCount . ' indikator memiliki data capaian, dengan rata-rata sederhana ' . ($average === null ? 'belum dapat dihitung' : $fmt($average) . '%') . '. ';
$summaryNarrative .= $scoredCount ? $metCount . ' dari ' . $scoredCount . ' indikator terukur memenuhi target (' . $fmt($metPct, 1) . '%). ' : 'Belum terdapat capaian terukur yang dapat dibandingkan dengan target. ';
$summaryNarrative .= 'Kelengkapan pencatatan indikator mencapai ' . $fmt($coverage, 1) . '% dari rencana 12 bulan per indikator (' . $monthCount . ' bulan memiliki data). ';
$summaryNarrative .= count($fishbones) . ' analisis fishbone mendokumentasikan ' . $fishboneCauses . ' penyebab; ' . count($cycles) . ' PDSA mendokumentasikan ' . $pdsaItems . ' item, dengan ' . $pdsaCompleted . ' item ditandai selesai.';
$priorityIndicators = array_values(array_filter($scored, static function ($row) use ($isMet) { return !$isMet($row); }));
usort($priorityIndicators, static function ($a, $b) { return (float) $a->rata_pencapaian <=> (float) $b->rata_pencapaian; });
$categoryColors = ['Man'=>'#3978d4','Machine'=>'#8c62c9','Method'=>'#ed8b36','Material'=>'#21a68a','Measurement'=>'#d65067','Environment'=>'#708399'];
$categoryMatches = static function ($value, $category): bool {
    $value = strtolower((string) $value);
    $aliases = [
        'Man' => ['man', 'sdm', 'people', 'personel'],
        'Machine' => ['machine', 'mesin', 'peralatan'],
        'Method' => ['method', 'metode', 'prosedur'],
        'Material' => ['material', 'bahan'],
        'Measurement' => ['measurement', 'pengukuran', 'ukur'],
        'Environment' => ['environment', 'lingkungan'],
    ];
    foreach ($aliases[$category] ?? [$category] as $alias) if (strpos($value, $alias) !== false) return true;
    return false;
};
$areaChart = static function (array $values, string $color = '#1597a6'): string {
    $width = 720; $height = 235; $left = 48; $right = 16; $top = 24; $bottom = 38;
    $plotWidth = $width - $left - $right; $plotHeight = $height - $top - $bottom;
    $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
    $x = static function (int $month) use ($left, $plotWidth): float { return $left + (($month - 1) / 11) * $plotWidth; };
    $y = static function (float $value) use ($top, $plotHeight): float { return $top + ((100 - max(0, min(100, $value))) / 100) * $plotHeight; };
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' ' . $height . '" width="100%" height="235" role="img" aria-label="Grafik area capaian bulanan">';
    foreach ([0,25,50,75,100] as $tick) {
        $ty = $y((float) $tick);
        $svg .= '<line x1="' . $left . '" y1="' . $ty . '" x2="' . ($width - $right) . '" y2="' . $ty . '" stroke="#dce6ee" stroke-width="1"/>';
        $svg .= '<text x="' . ($left - 8) . '" y="' . ($ty + 3) . '" text-anchor="end" fill="#65798b" font-size="10">' . $tick . '%</text>';
    }
    for ($month = 1; $month <= 12; $month++) {
        $tx = $x($month);
        $svg .= '<text x="' . $tx . '" y="' . ($height - 12) . '" text-anchor="middle" fill="#65798b" font-size="10">' . $months[$month] . '</text>';
    }
    $segments = []; $segment = [];
    for ($month = 1; $month <= 12; $month++) {
        if (isset($values[$month]) && is_numeric($values[$month])) {
            $segment[$month] = max(0, min(100, (float) $values[$month]));
        } elseif ($segment) {
            $segments[] = $segment; $segment = [];
        }
    }
    if ($segment) $segments[] = $segment;
    foreach ($segments as $points) {
        $commands = [];
        foreach ($points as $month => $value) $commands[] = ($commands ? 'L ' : 'M ') . round($x((int) $month), 1) . ' ' . round($y($value), 1);
        $line = implode(' ', $commands);
        if (count($points) > 1) {
            $firstMonth = (int) array_key_first($points); $lastMonth = (int) array_key_last($points);
            $area = $line . ' L ' . round($x($lastMonth), 1) . ' ' . round($y(0), 1) . ' L ' . round($x($firstMonth), 1) . ' ' . round($y(0), 1) . ' Z';
            $svg .= '<path d="' . $area . '" fill="' . $color . '" fill-opacity="0.16" stroke="none"/>';
            $svg .= '<path d="' . $line . '" fill="none" stroke="' . $color . '" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/>';
        }
        foreach ($points as $month => $value) {
            $svg .= '<circle cx="' . round($x((int) $month), 1) . '" cy="' . round($y($value), 1) . '" r="4" fill="' . $color . '" stroke="#fff" stroke-width="1.5"/>';
            $svg .= '<text x="' . round($x((int) $month), 1) . '" y="' . max(12, round($y($value) - 8, 1)) . '" text-anchor="middle" fill="#34495e" font-size="8">' . round($value) . '%</text>';
        }
    }
    if (!$segments) $svg .= '<text x="360" y="112" text-anchor="middle" fill="#8494a4" font-size="13">Belum ada data capaian per bulan</text>';
    return $svg . '</svg>';
};
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
body{font-family:sans-serif;font-size:9pt;color:#25364a;line-height:1.45}h1{font-size:22pt;color:#123b5d;margin:3px 0 5px}h2{font-size:14pt;color:#123b5d;margin:20px 0 8px;border-bottom:2px solid #c9e1ed;padding-bottom:5px}h3{font-size:10.5pt;color:#126b78;margin:13px 0 5px}.muted{color:#65798b}.small{font-size:8pt}.cover{background:#eaf5fb;padding:18px;border-left:6px solid #168aad;margin-bottom:13px}.meta{width:100%;border-collapse:collapse}.meta td{padding:3px 7px}.kpis{width:100%;border-collapse:separate;border-spacing:5px}.kpis td{width:25%;background:#f0f7fa;padding:9px;text-align:center;border:1px solid #d8e7ef}.kpi-value{font-size:16pt;font-weight:bold;color:#087e8b}.kpi-label{font-size:8pt;color:#637588}table.data{width:100%;border-collapse:collapse;font-size:8pt}table.data th{background:#e7f0f5;color:#183b56;text-align:left}table.data th,table.data td{border:1px solid #cad8e0;padding:5px;vertical-align:top}.narrative{background:#f6fafc;border:1px solid #d8e7ef;padding:9px;margin:5px 0 8px}.callout{background:#fff8e9;border-left:4px solid #e2a63a;padding:8px;margin:7px 0}.page-break{page-break-before:always}.footer{margin-top:20px;border-top:1px solid #ccd8df;padding-top:6px;font-size:8pt;color:#64748b}.bar-row{width:100%;margin:3px 0}.bar-label{display:inline-block;width:14%;font-size:7.5pt}.bar-track{display:inline-block;width:72%;height:11px;background:#edf2f6;vertical-align:middle}.bar-fill{height:11px;background:#19a3a8}.bar-value{display:inline-block;width:12%;text-align:right;font-size:7.5pt}.phase-table{width:100%;border-collapse:separate;border-spacing:4px}.phase-table td{width:50%;border:1px solid #dce6ef;background:#f8fbfd;padding:7px;vertical-align:top}.phase-title{font-weight:bold;color:#fff;padding:4px 7px;margin:-7px -7px 6px}.fishbone{width:100%;border-collapse:collapse;margin:7px 0}.fishbone td{width:33.33%;border:1px solid #dbe5ed;padding:5px;vertical-align:top}.fishbone .spine{background:#eaf5fb;text-align:center;color:#123b5d;font-weight:bold}.check{color:#168a67;font-weight:bold}.pending{color:#9aa8b5}
</style>
</head>
<body>
<div class="cover">
    <div class="small muted">INFINITE · LAPORAN KINERJA MUTU DAN KESELAMATAN PASIEN</div>
    <h1>Laporan Tahunan PMKP</h1>
    <div class="muted"><?= $esc($groupName) ?> · Tahun <?= $esc($periode->tahun ?? '-') ?></div>
    <table class="meta">
        <tr><td><b>Penanggung jawab</b></td><td><?= $esc($periode->pic_name ?? '-') ?></td><td><b>Status periode</b></td><td><?= $esc($periode->status_name ?? 'Aktif') ?></td></tr>
        <tr><td><b>Tim mutu</b></td><td><?= (int) ($periode->jumlah_tim ?? 0) ?> anggota</td><td><b>Waktu pembuatan</b></td><td><?= $esc($generatedAt) ?></td></tr>
    </table>
</div>
<table class="kpis"><tr>
    <td><div class="kpi-value"><?= $indicatorCount ?></div><div class="kpi-label">Indikator mutu</div></td>
    <td><div class="kpi-value"><?= $scoredCount ?>/<?= $indicatorCount ?></div><div class="kpi-label">Indikator terukur</div></td>
    <td><div class="kpi-value"><?= $metPct === null ? '—' : $fmt($metPct, 1) . '%' ?></div><div class="kpi-label">Memenuhi target</div></td>
    <td><div class="kpi-value"><?= $fmt($coverage, 1) ?>%</div><div class="kpi-label">Kelengkapan data bulanan</div></td>
</tr></table>

<h2>1. Ringkasan eksekutif</h2>
<div class="narrative"><?= $esc($summaryNarrative) ?></div>
<p>Rata-rata capaian merupakan rata-rata sederhana antar indikator yang memiliki data. Status target memperhitungkan arah target indikator (lebih tinggi atau lebih rendah lebih baik). Kelengkapan dihitung dari jumlah rekaman indikator-bulan dibandingkan 12 bulan untuk setiap indikator aktif.</p>
<?php if ($monthCount < 12): ?><div class="callout"><b>Cakupan data:</b> data tersedia pada <?= $monthCount ?> dari 12 bulan. Karena tahun pelaporan belum lengkap atau pencatatan belum penuh, simpulan tahunan perlu dibaca sebagai capaian sementara untuk bulan yang sudah tersedia.</div><?php endif; ?>

<h2>2. Kinerja indikator mutu</h2>
<table class="data"><thead><tr><th>No.</th><th>Kategori / Unit</th><th>Indikator dan analisis</th><th>Target</th><th>Rerata capaian</th><th>Data</th><th>Status</th><th>Numerator / denominator</th></tr></thead><tbody>
<?php if (!$indicators): ?><tr><td colspan="8">Belum ada indikator aktif pada periode ini.</td></tr><?php endif; ?>
<?php foreach ($indicators as $index => $row): $hasScore = $row->rata_pencapaian !== null && $row->rata_pencapaian !== ''; $met = $hasScore && $isMet($row); ?>
<tr><td><?= $index + 1 ?></td><td><?= $esc($row->kategori) ?><br><span class="muted"><?= $esc($row->department) ?></span></td><td><b><?= $esc($row->indikator_code) ?></b><br><?= $esc($row->indikator) ?><div class="small muted" style="margin-top:4px"><b>Analisis:</b> <?php if (!$hasScore): ?>Belum ada capaian. Unit perlu melengkapi pengukuran dan memvalidasi sumber data.<?php elseif ($met): ?>Rerata memenuhi target. Pertahankan proses dan verifikasi konsistensi pencatatan selama <?= (int) $row->jumlah_bulan ?> bulan terukur.<?php else: ?>Rerata belum memenuhi target. Telaah penyebab bersama unit dan tetapkan uji perbaikan terukur melalui PDSA.<?php endif; ?><?= (int) $row->jumlah_bulan < 12 ? ' Data belum lengkap untuk 12 bulan sehingga interpretasi tahunan bersifat sementara.' : '' ?></div></td><td><?= $esc($row->target) ?> <?= $esc(trim((string) $row->satuan)) ?><br><span class="muted"><?= ($row->target_capaian ?? '') === 'L' ? 'Maksimal' : 'Minimal' ?></span></td><td><?= $hasScore ? $fmt($row->rata_pencapaian) . '%' : 'Belum dinilai' ?></td><td><?= (int) $row->jumlah_bulan ?>/12 bulan<br><?= (int) $row->jumlah_penilaian ?> rekaman</td><td><?= !$hasScore ? 'Belum ada data' : ($met ? 'Memenuhi' : 'Perlu perbaikan') ?></td><td><?= $fmt($row->total_numerator ?? 0, 0) ?> / <?= $fmt($row->total_denumerator ?? 0, 0) ?></td></tr>
<?php endforeach; ?>
</tbody></table>

<h3>Tren rata-rata capaian bulanan</h3>
<?php $overallTrendValues = []; foreach ($trend as $row) $overallTrendValues[(int) $row->bulan] = (float) $row->rata_pencapaian; ?>
<div style="border:1px solid #dbe5ed;border-radius:5px;padding:8px 10px;margin-bottom:6px"><?= $areaChart($overallTrendValues) ?></div>
<p class="small muted">Sumbu vertikal menunjukkan rerata persentase capaian semua indikator yang memiliki data pada bulan tersebut. Area yang kosong berarti tidak ada data pengukuran untuk bulan itu.</p>
<?php if ($measurements): ?><h3>Rincian pengukuran per bulan</h3>
<table class="data"><thead><tr><th>Bulan</th><th>Unit</th><th>Indikator</th><th>Numerator</th><th>Denominator</th><th>Capaian</th><th>Analisis / kendala</th><th>Rencana tindak lanjut</th></tr></thead><tbody>
<?php foreach ($measurements as $measurement): ?><tr><td><?= $esc($monthNames[(int) $measurement->bulan] ?? $measurement->bulan) ?></td><td><?= $esc($measurement->department) ?></td><td><?= $esc($measurement->indikator_code) ?> · <?= $esc($measurement->indikator) ?></td><td><?= $fmt($measurement->numerator ?? 0, 0) ?></td><td><?= $fmt($measurement->denumerator ?? 0, 0) ?></td><td><?= $fmt($measurement->capaian) ?>%</td><td><?= $esc($measurement->reason ?: '—') ?></td><td><?= $esc($measurement->rtl ?: '—') ?></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?>

<div class="page-break"></div>
<h2>Grafik area capaian tiap indikator</h2>
<p>Setiap grafik menampilkan rerata capaian indikator per bulan pada skala 0–100%. Celah pada grafik menandakan tidak ada pengukuran tercatat pada bulan tersebut.</p>
<?php foreach ($indicators as $index => $row): $values = $indicatorTrend[$row->transaksi_id] ?? []; ?>
<div style="border:1px solid #dbe5ed;border-radius:5px;padding:7px 10px;margin:8px 0;page-break-inside:avoid">
    <h3 style="margin-top:3px"><?= $index + 1 ?>. <?= $esc($row->indikator_code) ?> · <?= $esc($row->indikator) ?></h3>
    <div class="small muted"><?= $esc($row->department) ?> · Rerata tahunan <?= $row->rata_pencapaian === null ? 'belum ada data' : $fmt($row->rata_pencapaian) . '%' ?> · Target <?= $esc($row->target) ?><?= ($row->target_capaian ?? '') === 'L' ? ' maksimal' : ' minimal' ?></div>
    <?= $areaChart($values, ['#3978d4','#18a58b','#8a62c7','#e28a36','#d65067'][$index % 5]) ?>
</div>
<?php endforeach; ?>

<h3>Analisis indikator prioritas</h3>
<?php if (!$priorityIndicators): ?><div class="narrative"><?= $scoredCount ? 'Seluruh indikator yang memiliki data telah memenuhi target.' : 'Analisis prioritas belum dapat dibuat karena belum ada capaian terukur.' ?></div><?php else: ?>
<p><?= count($priorityIndicators) ?> indikator terukur belum memenuhi target dan menjadi fokus tindak lanjut. Urutan berikut dimulai dari nilai rerata terendah; indikator dengan data bulan yang lebih sedikit perlu divalidasi sebelum penetapan intervensi.</p>
<table class="data"><thead><tr><th>Indikator / Unit</th><th>Capaian dibanding target</th><th>Analisis dan fokus tindak lanjut</th></tr></thead><tbody>
<?php foreach (array_slice($priorityIndicators, 0, 8) as $row): ?><tr><td><b><?= $esc($row->indikator_code) ?></b> · <?= $esc($row->indikator) ?><br><?= $esc($row->department) ?></td><td><?= $fmt($row->rata_pencapaian) ?>% · target <?= $esc($row->target) ?><?= ($row->target_capaian ?? '') === 'L' ? ' maks.' : ' min.' ?></td><td>Evaluasi variasi capaian per bulan dan validasi numerator/denominator. Telusuri penyebab bersama unit pemilik, tetapkan intervensi PDSA yang terukur, lalu pantau perubahan capaian.</td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?>

<div class="page-break"></div>
<h2>3. Analisis akar masalah — Fishbone</h2>
<p><?= count($fishbones) ?> analisis fishbone memuat <?= $fishboneCauses ?> penyebab teridentifikasi. Sebaran penyebab menunjukkan kategori yang paling sering muncul sebagai area telaah lintas unit.</p>
<?php if ($causeCounts): ?><table class="data"><thead><tr><th>Kategori sebab (6M)</th><th>Jumlah penyebab</th><th>Proporsi</th></tr></thead><tbody>
<?php foreach ($causeCounts as $category => $count): ?><tr><td><?= $esc($category) ?></td><td><?= $count ?></td><td><?= $fishboneCauses ? $fmt($count / $fishboneCauses * 100, 1) : '0,0' ?>%</td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?>
<?php if (!$fishbones): ?><div class="narrative">Belum ada analisis fishbone pada periode ini.</div><?php endif; ?>
<?php foreach ($fishbones as $index => $analysis): ?>
<h3>3.<?= $index + 1 ?> <?= $esc($analysis->problem) ?></h3>
<p class="small"><b>Unit:</b> <?= $esc($analysis->department) ?> &nbsp; <b>Indikator:</b> <?= $esc($analysis->indikator ?: 'Tidak ditautkan') ?><?= $analysis->indikator_code ? ' (' . $esc($analysis->indikator_code) . ')' : '' ?></p>
<table class="fishbone"><tr>
<?php $categories = ['Man','Machine','Method','Material','Measurement','Environment']; foreach ($categories as $catIndex => $category): $items = array_values(array_filter($analysis->causes, static function ($cause) use ($category, $categoryMatches) { return $categoryMatches($cause->category ?? '', $category); })); ?>
<td style="border-top:3px solid <?= $categoryColors[$category] ?>"><b style="color:<?= $categoryColors[$category] ?>"><?= $esc($category) ?></b><?php if (!$items): ?><div class="muted small">Belum ada penyebab</div><?php else: foreach (array_slice($items, 0, 4) as $cause): ?><div class="small">• <?= $esc($cause->cause) ?> <span class="muted">(<?= $esc($cause->priority ?? 'medium') ?>)</span></div><?php endforeach; if (count($items) > 4): ?><div class="muted small">+<?= count($items) - 4 ?> penyebab lain</div><?php endif; endif; ?></td>
<?php if ($catIndex === 2): ?></tr><tr><td class="spine" colspan="3">◀ <?= $esc($analysis->problem) ?> · masalah utama</td></tr><tr><?php endif; endforeach; ?>
</tr></table>
<div class="narrative"><b>Analisis:</b> <?= $fishboneCauses ? 'Penyebab yang tercatat perlu dipilah berdasarkan bukti dan dampaknya. Prioritaskan penyebab berprioritas tinggi/urgent, verifikasi di unit, kemudian ubah penyebab yang dapat dikendalikan menjadi rencana uji perbaikan.' : 'Penyebab belum dicatat sehingga analisis akar masalah belum dapat disimpulkan.' ?></div>
<?php endforeach; ?>

<div class="page-break"></div>
<h2>4. Program perbaikan — PDSA</h2>
<p><?= count($cycles) ?> item/header perbaikan PDSA tercatat dengan <?= $pdsaItems ?> butir aktivitas. <?= $pdsaItems ? $pdsaCompleted . ' (' . $fmt($pdsaCompletion, 1) . '%) ditandai selesai.' : 'Belum ada butir aktivitas PDSA.' ?></p>
<table class="data"><thead><tr><th>Tahap</th><th>Butir aktivitas</th><th>Makna evaluasi</th></tr></thead><tbody>
<?php foreach (['Plan'=>'Rencana dan prediksi','Do'=>'Pelaksanaan uji perubahan','Study'=>'Analisis hasil','Act'=>'Keputusan tindak lanjut'] as $phase => $label): ?><tr><td><b><?= $phase ?></b> · <?= $label ?></td><td><?= $phaseCounts[$phase] ?> butir</td><td><?= $phaseCounts[$phase] ? 'Terdokumentasi pada siklus; tinjau kesesuaian rencana, bukti pelaksanaan, hasil, dan keputusan.' : 'Belum ada aktivitas pada tahap ini; lengkapi agar siklus perbaikan dapat dievaluasi secara utuh.' ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php if (!$cycles): ?><div class="narrative">Belum ada PDSA pada periode ini.</div><?php endif; ?>
<?php foreach ($cycles as $index => $cycle): $phaseStyle = ['Plan'=>['#3178c6','#edf5ff'],'Do'=>['#18a58b','#edfbf7'],'Study'=>['#8a62c7','#f5f0ff'],'Act'=>['#e28a36','#fff6eb']]; ?>
<h3>4.<?= $index + 1 ?> <?= $esc($cycle->title ?: 'PDSA') ?></h3>
<p class="small"><b>Unit:</b> <?= $esc($cycle->department) ?> &nbsp; <b>Indikator:</b> <?= $esc($cycle->indikator ?: 'Tidak ditautkan') ?> &nbsp; <b>Status:</b> <?= $esc($cycle->status) ?></p>
<p><b>Masalah:</b> <?= nl2br($esc($cycle->problem ?: 'Belum dirumuskan')) ?><br><b>Tujuan perbaikan:</b> <?= nl2br($esc($cycle->aim ?: 'Belum ditetapkan')) ?></p>
<table width="100%" style="border-collapse:collapse;text-align:center;margin:6px 0 3px"><tr>
<?php foreach (['Plan','Do','Study','Act'] as $phaseIndex => $phase): ?><td style="width:22%;padding:7px 2px"><div style="margin:auto;width:74px;height:74px;border-radius:50%;background:<?= $phaseStyle[$phase][1] ?>;border:4px solid <?= $phaseStyle[$phase][0] ?>;color:<?= $phaseStyle[$phase][0] ?>;font-size:15pt;font-weight:bold;line-height:66px"><?= $phase ?></div><div class="small" style="color:<?= $phaseStyle[$phase][0] ?>"><?= count(array_filter($cycle->items, static function ($item) use ($phase) { return strcasecmp((string) $item->phase, $phase) === 0; })) ?> item</div></td><?php if ($phaseIndex < 3): ?><td style="width:4%;font-size:19pt;color:#728499">›</td><?php endif; endforeach; ?>
</tr><tr><td colspan="7" style="text-align:right;padding-right:4%;font-size:8pt;color:#728499">Act menjadi masukan untuk perencanaan berikutnya ↻</td></tr></table>
<table class="phase-table"><tr><?php foreach (['Plan','Do','Study','Act'] as $phase): $items = array_values(array_filter($cycle->items, static function ($item) use ($phase) { return strcasecmp((string) $item->phase, $phase) === 0; })); ?>
<td><div class="phase-title" style="background:<?= $phaseStyle[$phase][0] ?>"><?= $phase ?> · <?= count($items) ?> item</div><?php if (!$items): ?><span class="muted">Belum diisi</span><?php else: foreach ($items as $item): ?><div style="margin:4px 0"><span class="<?= (int) ($item->completed ?? 0) === 1 ? 'check' : 'pending' ?>"><?= (int) ($item->completed ?? 0) === 1 ? '☑' : '□' ?></span> <?= $esc($item->item_text) ?></div><?php endforeach; endif; ?></td>
<?= $phase === 'Do' ? '</tr><tr>' : '' ?>
<?php endforeach; ?></tr></table>
<div class="narrative"><b>Analisis siklus:</b> <?= $pdsaItems ? 'Keterlacakan PDSA dinilai dari keberadaan aktivitas pada setiap tahap dan status penyelesaiannya. Untuk keputusan pimpinan, sertakan hasil ukur sebelum/sesudah intervensi pada tahap Study; lanjutkan, sesuaikan, atau hentikan perubahan pada tahap Act berdasarkan hasil tersebut.' : 'Tambahkan butir kegiatan untuk setiap tahap agar pelaksanaan dan hasil perbaikan dapat ditinjau.' ?></div>
<?php endforeach; ?>

<h2>5. Kesimpulan dan rekomendasi pimpinan</h2>
<div class="narrative">
<p><b>Kesimpulan.</b> <?= $esc($summaryNarrative) ?></p>
<ol>
<li>Fokuskan pembahasan mutu pada <?= count($priorityIndicators) ?> indikator yang belum memenuhi target atau belum memiliki data cukup; unit terkait diminta menyiapkan rencana perbaikan dan validasi data.</li>
<li>Gunakan temuan fishbone untuk menetapkan penyebab prioritas berdasarkan bukti, penanggung jawab, tenggat, serta kebutuhan sumber daya.</li>
<li>Pastikan setiap PDSA memuat rencana, pelaksanaan, hasil ukur tahap Study, dan keputusan Act; tinjau bukti penyelesaian serta dampak terhadap capaian indikator.</li>
<li>Tingkatkan kelengkapan pencatatan bulanan hingga 12 bulan agar evaluasi tren tahunan dan efektivitas perbaikan dapat disimpulkan secara utuh.</li>
</ol>
<p class="small muted">Laporan dibentuk otomatis dari data INFINITE sesuai periode dan organisasi pengguna. Interpretasi klinis, validasi numerator/denominator, serta pengesahan rekomendasi dilakukan oleh Komite/Tim PMKP dan pimpinan rumah sakit.</p>
</div>
<div class="footer">INFINITE · Laporan Tahunan PMKP <?= $esc($periode->tahun ?? '-') ?> · Dibuat <?= $esc($generatedAt) ?></div>
</body></html>
