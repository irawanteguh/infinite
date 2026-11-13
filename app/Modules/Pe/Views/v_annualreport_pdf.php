<?php
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$n = static fn($v, $d = 2) => $v === null ? '-' : number_format($v, $d, ',', '.');
$months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$phases = ['PREM'=>'PREM', 'T0'=>'PROM PRE / T0', 'T1'=>'PROM POST / T1'];
$activeMonths = count(array_filter($data['monthly'], static fn($counts) => array_sum($counts) > 0));
$chart = static function ($averages) use ($months) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="680" height="230" viewBox="0 0 680 230">';
    for ($score = 1; $score <= 5; $score++) {
        $y = 185 - ($score - 1) * 40;
        $svg .= '<line x1="35" y1="'.$y.'" x2="655" y2="'.$y.'" stroke="#e2e8f0"/><text x="15" y="'.($y+4).'" font-size="11">'.$score.'</text>';
    }
    foreach ($months as $i => $label) $svg .= '<text x="'.(35+$i*56).'" y="210" font-size="10">'.$label.'</text>';
    foreach (['PREM'=>'#087e82','T0'=>'#527fbc','T1'=>'#cb8b37'] as $phase => $color) {
        $previous = null;
        foreach (array_values($averages) as $i => $values) {
            if ($values[$phase] === null) { $previous = null; continue; }
            $x = 42+$i*56; $y = 185-($values[$phase]-1)*40;
            if ($previous) $svg .= '<line x1="'.$previous[0].'" y1="'.$previous[1].'" x2="'.$x.'" y2="'.$y.'" stroke="'.$color.'" stroke-width="2"/>';
            $svg .= '<circle cx="'.$x.'" cy="'.$y.'" r="3" fill="'.$color.'"/>';
            $previous = [$x,$y];
        }
    }
    return $svg.'</svg>';
};
// SVG keeps charts sharp in mPDF without a browser or external chart service.
$barChart = static function (array $items, float $maximum, string $unit, int $labelWidth = 150) use ($e, $n): string {
    $plotWidth = 530 - $labelWidth;
    $rows = []; $height = 12;
    foreach ($items as $item) {
        $lines = []; $remaining = trim($item['label']);
        $limit = max(18, (int) ($labelWidth / 6.5));
        while (mb_strlen($remaining, 'UTF-8') > $limit) {
            $prefix = mb_substr($remaining, 0, $limit, 'UTF-8');
            $space = mb_strrpos($prefix, ' ', 0, 'UTF-8');
            $length = $space !== false && $space > $limit/2 ? $space : $limit;
            $lines[] = mb_substr($remaining, 0, $length, 'UTF-8');
            $remaining = ltrim(mb_substr($remaining, $length, null, 'UTF-8'));
        }
        $lines[] = $remaining;
        $rowHeight = max(32, count($lines)*14+8);
        $rows[] = ['item'=>$item, 'lines'=>$lines, 'y'=>$height, 'height'=>$rowHeight];
        $height += $rowHeight;
    }
    $height += 25;
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="680" height="'.$height.'" viewBox="0 0 680 '.$height.'">';
    foreach ([0, 0.25, 0.5, 0.75, 1] as $fraction) {
        $x = $labelWidth + $plotWidth * $fraction;
        $svg .= '<line x1="'.$x.'" y1="5" x2="'.$x.'" y2="'.($height-25).'" stroke="#e2e8f0"/>';
        $svg .= '<text x="'.$x.'" y="'.($height-7).'" font-size="10" fill="#718096">'.$e($n($maximum*$fraction, $unit === '/ 5' ? 2 : 0)).'</text>';
    }
    foreach ($rows as $row) {
        $item = $row['item']; $y = $row['y'] + ($row['height']-22)/2;
        $width = $maximum > 0 ? max(0, min($plotWidth, $item['value'] / $maximum * $plotWidth)) : 0;
        foreach ($row['lines'] as $lineIndex=>$line) $svg .= '<text x="5" y="'.($row['y']+14+$lineIndex*14).'" font-size="12" fill="#243b53">'.$e($line).'</text>';
        $svg .= '<rect x="'.$labelWidth.'" y="'.$y.'" width="'.$plotWidth.'" height="22" fill="#f1f5f9"/>';
        if ($width > 0) $svg .= '<rect x="'.$labelWidth.'" y="'.$y.'" width="'.$width.'" height="22" fill="'.$item['color'].'"/>';
        $svg .= '<text x="545" y="'.($y+15).'" font-size="12" fill="#243b53">'.$e($n($item['value'], $unit === '/ 5' ? 2 : 1).' '.$unit).'</text>';
    }
    return $svg.'</svg>';
};
$groupedPhaseChart = static function (array $domains, float $maximum = 5, string $unit = '/ 5') use ($e, $n): string {
    $rows = []; $cursor = 10;
    foreach ($domains as $domain) {
        $lines = []; $remaining = trim($domain['name']);
        while (mb_strlen($remaining, 'UTF-8') > 36) {
            $prefix = mb_substr($remaining, 0, 36, 'UTF-8');
            $space = mb_strrpos($prefix, ' ', 0, 'UTF-8');
            $length = $space !== false && $space > 18 ? $space : 36;
            $lines[] = mb_substr($remaining, 0, $length, 'UTF-8');
            $remaining = ltrim(mb_substr($remaining, $length, null, 'UTF-8'));
        }
        $lines[] = $remaining;
        $height = max(64, count($lines)*14+16);
        $rows[] = ['domain'=>$domain, 'lines'=>$lines, 'y'=>$cursor, 'height'=>$height];
        $cursor += $height;
    }
    $height = $cursor + 60;
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="680" height="'.$height.'" viewBox="0 0 680 '.$height.'">';
    foreach ([0, 1, 2, 3, 4, 5] as $tick) {
        $x = 260 + $tick*54;
        $svg .= '<line x1="'.$x.'" y1="5" x2="'.$x.'" y2="'.$cursor.'" stroke="#e2e8f0"/><text x="'.$x.'" y="'.($cursor+16).'" font-size="10" fill="#718096">'.($tick*$maximum/5).'</text>';
    }
    foreach ($rows as $row) {
        $middle = $row['y'] + $row['height']/2;
        $labelY = $middle - (count($row['lines'])-1)*7+4;
        foreach ($row['lines'] as $i=>$line) $svg .= '<text x="5" y="'.($labelY+$i*14).'" font-size="12" fill="#243b53">'.$e($line).'</text>';
        foreach (['T0'=>'#527fbc','T1'=>'#cb8b37'] as $phase=>$color) {
            $y = $middle + ($phase === 'T0' ? -21 : 3);
            $value = $row['domain']['averages'][$phase];
            $svg .= '<rect x="260" y="'.$y.'" width="270" height="18" fill="#f1f5f9"/>';
            if ($value !== null) $svg .= '<rect x="260" y="'.$y.'" width="'.max(0,min(270,$value/$maximum*270)).'" height="18" fill="'.$color.'"/>';
            $svg .= '<text x="545" y="'.($y+13).'" font-size="12" fill="#243b53">'.$e($n($value, $unit === '%' ? 1 : 2).' '.$unit).'</text>';
        }
    }
    $legendY = $cursor+36;
    $svg .= '<rect x="260" y="'.$legendY.'" width="12" height="12" fill="#527fbc"/><text x="280" y="'.($legendY+11).'" font-size="12" fill="#243b53">T0 / PRE</text>';
    $svg .= '<rect x="390" y="'.$legendY.'" width="12" height="12" fill="#cb8b37"/><text x="410" y="'.($legendY+11).'" font-size="12" fill="#243b53">T1 / POST</text>';
    return $svg.'</svg>';
};
$serviceLabels = ['RJ'=>'Rawat Jalan','RI'=>'Rawat Inap','IGD'=>'IGD','OTHER'=>'Belum tercatat'];
$serviceColors = ['RJ'=>'#087e82','RI'=>'#527fbc','IGD'=>'#d29a42','OTHER'=>'#94a3b8'];
$pieChart = static function (array $counts, array $colors, string $emptyLabel = 'Belum ada pengisian') use ($e, $n): string {
    $total = array_sum($counts);
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="260" height="240" viewBox="0 0 260 240">';
    if (!$total) return $svg.'<circle cx="130" cy="115" r="95" fill="#edf2f7"/><text x="66" y="120" font-size="12" fill="#718096">'.$e($emptyLabel).'</text></svg>';
    $angle = -M_PI / 2;
    foreach ($counts as $key => $count) {
        if ($count <= 0) continue;
        $fraction = $count / $total;
        $end = $angle + $fraction * 2 * M_PI;
        $color = $colors[$key];
        if ($count === $total) {
            $svg .= '<circle cx="130" cy="115" r="95" fill="'.$color.'"/>';
        } else {
            $x1 = 130 + 95*cos($angle); $y1 = 115 + 95*sin($angle);
            $x2 = 130 + 95*cos($end); $y2 = 115 + 95*sin($end);
            $svg .= '<path d="M 130 115 L '.$x1.' '.$y1.' A 95 95 0 '.($fraction > 0.5 ? 1 : 0).' 1 '.$x2.' '.$y2.' Z" fill="'.$color.'" stroke="#ffffff" stroke-width="2"/>';
        }
        // Tiny slices remain legible through the adjacent color-keyed table.
        if ($fraction >= 0.07) {
            $middle = ($angle+$end)/2;
            $svg .= '<text x="'.(130+60*cos($middle)-15).'" y="'.(119+60*sin($middle)).'" font-size="12" fill="#ffffff">'.$n($fraction*100,1).'%</text>';
        }
        $angle = $end;
    }
    return $svg.'</svg>';
};
$distributionBars = [];
foreach ($data['distribution'] as $score=>$count) $distributionBars[] = [
    'label'=>\Modules\Pe\Config\SkmQuestionnaire::defaultOptions()[$score], 'value'=>$data['answerCount'] ? $count/$data['answerCount']*100 : 0,
    'color'=>[1=>'#be6464',2=>'#cd9475',3=>'#cbac55',4=>'#64a99b',5=>'#087e82'][$score],
];

$domainGroups = [];
foreach ($data['domains'] as $domain) {
    $key = $domain['instrument_id'];
    if (!isset($domainGroups[$key])) $domainGroups[$key] = ['name'=>$domain['instrument'], 'type'=>$domain['type'], 'domains'=>[]];
    $domainGroups[$key]['domains'][] = $domain;
}
foreach ($domainGroups as &$group) {
    $sortPhase = $group['type'] === 'PREM' ? 'PREM' : 'T1';
    usort($group['domains'], static function ($a, $b) use ($sortPhase) {
        $av = $a['averages'][$sortPhase]; $bv = $b['averages'][$sortPhase];
        if ($av === null && $bv === null) return strcmp($a['name'], $b['name']);
        if ($av === null) return 1;
        if ($bv === null) return -1;
        return ($av <=> $bv) ?: strcmp($a['name'], $b['name']);
    });
}
unset($group);

?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><style>
body{font-family:dejavusans;font-size:9pt;color:#243b53;line-height:1.55}h1{font-size:25pt;color:#087e82;margin:12px 0}h2{font-size:16pt;color:#087e82;border-bottom:2px solid #d8e9e9;padding-bottom:7px}h3{font-size:11pt;color:#24536a;margin:14px 0 8px}h4{font-size:10pt;color:#24536a;margin:12px 0 7px}.organization{font-size:11pt;font-weight:bold;color:#24536a}p{margin:8px 0}.muted{color:#718096}.small{font-size:8pt}.tag{font-size:10pt;color:#087e82;letter-spacing:2px}.box{background:#edf7f6;border-left:4px solid #087e82;padding:14px;margin:14px 0}.warning{background:#fff6df;border-left:4px solid #cb8b37;padding:12px}.data{width:100%;border-collapse:collapse;margin:12px 0;font-size:8pt}.data th{background:#087e82;color:white;padding:8px;text-align:left}.data td{padding:7px;border-bottom:1px solid #e2e8f0;vertical-align:top}.kpis{width:100%;margin:10px 0}.kpis td{background:#edf7f6;padding:8px;width:25%;font-size:8pt}.value{font-size:19pt;color:#087e82;font-weight:bold}.break{page-break-before:always}.legend{font-size:8pt;margin-bottom:8px}li{margin-bottom:8px}
</style></head><body>
<div class="tag">PATIENT EXPERIENCE</div><h1>Laporan Tahunan PE <?= $e($year) ?></h1><p class="organization"><?= $e($organization) ?></p>
<p>Periode 1 Januari - 31 Desember <?= $e($year) ?> | Seluruh instrumen dan pelayanan organisasi</p><p class="small muted">Dibuat <?= $e($generatedAt) ?>. Data mengikuti kondisi saat laporan dibuat.</p>
<?php if ($isCurrentYear): ?><div class="warning">Tahun berjalan: laporan ini bersifat sementara. Bulan yang belum berlangsung tidak menunjukkan kekurangan pencatatan.</div><?php endif; ?>

<h2>1. Ringkasan eksekutif</h2>
<p><b><?= $n($data['total'],0) ?> pengisian</b> tercatat pada <?= $activeMonths ?> bulan dalam tahun laporan. Jumlah pengisian bukan jumlah orang unik.</p>
<h3>1.1 Ringkasan PREM dan PROM</h3>
<table class="data"><thead><tr><th>Jenis penilaian</th><th>Pengisian</th><th>Hasil utama</th></tr></thead><tbody>
<tr><td><b>PREM</b><br>Pengalaman pelayanan</td><td><?= $n($data['counts']['PREM'],0) ?></td><td><?php if($data['premAverage'] !== null): ?>Rerata <b><?= $n($data['premAverage']) ?> / 5</b><br><?= $n($data['positive'],1) ?>% jawaban bernilai 4-5<?php else: ?>Belum ada nilai PREM.<?php endif; ?></td></tr>
<tr><td><b>PROM</b><br>Kondisi pasien sebelum dan sesudah perawatan</td><td><?= $n($data['counts']['T0']+$data['counts']['T1'],0) ?><br>T0: <?= $n($data['counts']['T0'],0) ?> | T1: <?= $n($data['counts']['T1'],0) ?></td><td><?php if($data['pairs']): ?><b><?= $n($data['pairs'],0) ?> pasangan</b> dapat dibandingkan<br>Perubahan rerata <b><?= $data['delta'] > 0 ? '+' : '' ?><?= $n($data['delta']) ?> poin</b><?php else: ?>Belum ada pasangan T0-T1 yang dapat dibandingkan.<?php endif; ?></td></tr>
</tbody></table>
<h3>1.2 Detail PREM - Patient-Reported Experience Measures</h3>
<table class="data"><thead><tr><th>Ukuran</th><th>Hasil</th><th>Penjelasan</th></tr></thead><tbody>
<tr><td>Jumlah pengisian</td><td><?= $n($data['counts']['PREM'],0) ?></td><td>Kuesioner PREM yang dikirim.</td></tr>
<tr><td>Rerata skor</td><td><?= $n($data['premAverage']) ?> / 5</td><td>Rerata dari rerata skor setiap pengisian.</td></tr>
<tr><td>Jawaban positif</td><td><?= $n($data['positive'],1) ?><?= $data['positive'] === null ? '' : '%' ?></td><td><?= $n($data['distribution'][4]+$data['distribution'][5],0) ?> dari <?= $n($data['answerCount'],0) ?> jawaban bernilai 4 atau 5.</td></tr>
</tbody></table>
<h3>1.3 Detail PROM - Patient-Reported Outcome Measures</h3>
<table class="data"><thead><tr><th>Ukuran</th><th>Hasil</th><th>Penjelasan</th></tr></thead><tbody>
<tr><td>Pengisian T0 / T1</td><td><?= $n($data['counts']['T0'],0) ?> / <?= $n($data['counts']['T1'],0) ?></td><td>T0: awal perawatan. T1: saat pulang.</td></tr>
<tr><td>Pasangan yang dibandingkan</td><td><?= $n($data['pairs'],0) ?></td><td>Instrumen, pelayanan, dan nomor rekam medis yang sama.</td></tr>
<tr><td>Rerata PRE menuju POST</td><td><?= $n($data['preAverage']) ?> → <?= $n($data['postAverage']) ?></td><td>Skala 1-5, dihitung dari pasangan yang sama.</td></tr>
<tr><td>Perubahan rerata</td><td><?= $data['delta'] !== null && $data['delta'] > 0 ? '+' : '' ?><?= $n($data['delta']) ?> poin</td><td>Rerata selisih POST dikurangi PRE.</td></tr>
<tr><td>Arah perubahan skor</td><td>Naik: <?= $n($data['improved'],0) ?><br>Tetap: <?= $n($data['unchanged'],0) ?><br>Turun: <?= $n($data['declined'],0) ?></td><td><?php if($data['pairs']): ?><?= $n($data['improvedPercent'],1) ?>% dari <?= $n($data['pairs'],0) ?> pasangan memiliki skor POST lebih tinggi.<?php else: ?>Belum tersedia pasangan untuk menghitung arah perubahan.<?php endif; ?></td></tr>
</tbody></table>
<p class="small muted">Tanda - berarti nilai belum tersedia. Jumlah T0/T1 mencakup pengisian yang belum berpasangan. Perubahan PROM dibaca sesuai instrumen, bukan kesimpulan klinis atau bukti dampak intervensi.</p>

<div class="break"></div><h2>2. Perkembangan bulanan</h2>
<h3>2.1 Tren nilai dan jumlah pengisian</h3>
<div class="legend"><span style="color:#087e82">PREM</span> &nbsp; | &nbsp; <span style="color:#527fbc">PROM PRE / T0</span> &nbsp; | &nbsp; <span style="color:#cb8b37">PROM POST / T1</span></div>
<?= $chart($data['monthlyAverages']) ?>
<table class="data"><thead><tr><th>Bulan</th><th>n PREM</th><th>Nilai PREM</th><th>n T0</th><th>Nilai T0</th><th>n T1</th><th>Nilai T1</th></tr></thead><tbody><?php foreach ($data['monthly'] as $month=>$counts): ?><tr><td><?= $months[(int)substr($month,5,2)-1] ?></td><?php foreach ($phases as $phase=>$label): ?><td><?= $counts[$phase] ?></td><td><?= $n($data['monthlyAverages'][$month][$phase]) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table>
<p class="small muted">n adalah jumlah pengisian. Nilai adalah rerata dari rerata jawaban tiap respons yang memiliki skor. Tanda - berarti belum ada nilai; garis terputus pada bulan kosong.</p>
<div class="break"></div><h3>2.2 Analisis perubahan nilai bulanan</h3>
<p>Bagian ini menunjukkan arah perubahan rerata skor sepanjang tahun. Baca bersama jumlah pengisian pada tabel bulanan agar perubahan nilai memiliki konteks.</p>
<?php foreach ($phases as $phase=>$label):
    $phaseNumber = array_search($phase, array_keys($phases), true) + 1;
    $values = array_filter(array_column($data['monthlyAverages'], $phase), static fn($v) => $v !== null);
    $keys = array_keys($values);
?>
<h4>2.2.<?= $phaseNumber ?> <?= $e($label) ?></h4>
<?php if (count($values) < 2): ?>
<p><?= count($values) ? 'Baru satu bulan memiliki nilai. Perubahan antarbulan belum dapat dinilai.' : 'Belum ada nilai pada tahun ini. Tren belum dapat dinilai.' ?></p>
<?php else:
    $first = reset($values); $last = end($values); $delta = $last - $first;
    $firstMonth = $months[$keys[0]]; $lastMonth = $months[end($keys)];
    $low = min($values); $high = max($values);
    $lowMonths = []; $highMonths = [];
    foreach ($values as $index => $value) {
        if ($value == $low) $lowMonths[] = $months[$index];
        if ($value == $high) $highMonths[] = $months[$index];
    }
    $flat = abs($delta) < 0.005;
?>
<table class="data"><thead><tr><th>Awal pengamatan</th><th>Akhir pengamatan</th><th>Perubahan</th></tr></thead><tbody><tr>
<td><?= $firstMonth ?><br><b><?= $n($first) ?> / 5</b></td>
<td><?= $lastMonth ?><br><b><?= $n($last) ?> / 5</b></td>
<td><b><?= $flat ? 'Relatif tetap' : ($delta > 0 ? 'Naik ' : 'Turun ') . $n(abs($delta)) . ' poin' ?></b></td>
</tr></tbody></table>
<p><b>Gambaran sepanjang tahun:</b> <?php if (abs($high-$low) < 0.005): ?>Rerata bulanan relatif sama, sekitar <?= $n($low) ?> dari 5.<?php else: ?>Nilai tertinggi tercatat pada <?= implode(', ', $highMonths) ?> (<?= $n($high) ?>), sedangkan nilai terendah pada <?= implode(', ', $lowMonths) ?> (<?= $n($low) ?>).<?php endif; ?> Sebanyak <?= count($values) ?> dari 12 bulan kalender memiliki nilai.</p>
<p><b>Maknanya:</b> <?php if ($phase === 'PREM'): ?><?php if ($flat): ?>Rerata pengalaman pelayanan pada bulan terakhir hampir sama dengan bulan pertama.<?php elseif ($delta < 0): ?>Rerata pengalaman pelayanan pada <?= $lastMonth ?> lebih rendah daripada <?= $firstMonth ?>. Tinjau pertanyaan dan domain yang nilainya rendah untuk menentukan bagian pelayanan yang perlu ditelusuri.<?php else: ?>Rerata pengalaman pelayanan pada <?= $lastMonth ?> lebih tinggi daripada <?= $firstMonth ?>. Tinjau domain dan pertanyaan untuk melihat bagian yang berkontribusi pada kenaikan tersebut.<?php endif; ?><?php elseif ($phase === 'T0'): ?>Ini adalah skor kondisi pasien pada awal perawatan. <?php if ($flat): ?>Rerata kondisi awal pada bulan terakhir hampir sama dengan bulan pertama.<?php else: ?>Rerata kondisi awal pada <?= $lastMonth ?> <?= $delta < 0 ? 'lebih rendah' : 'lebih tinggi' ?> daripada <?= $firstMonth ?>.<?php endif; ?> Perubahan ini dapat berkaitan dengan perbedaan kelompok pasien atau instrumen; belum menunjukkan hasil perawatan.<?php else: ?>Ini adalah skor kondisi pasien saat pulang. <?php if ($flat): ?>Rerata skor saat pulang pada bulan terakhir hampir sama dengan bulan pertama.<?php else: ?>Rerata skor saat pulang pada <?= $lastMonth ?> <?= $delta < 0 ? 'lebih rendah' : 'lebih tinggi' ?> daripada <?= $firstMonth ?>.<?php endif; ?> Untuk menilai perubahan selama perawatan, lihat hasil pasangan T0-T1 pada bagian PROM berpasangan.<?php endif; ?></p>
<?php endif; endforeach; ?>
<div class="box small"><b>Catatan pembacaan:</b> Perubahan awal-akhir tidak berarti nilai selalu naik atau turun setiap bulan. Bulan tanpa nilai tidak dianggap nol. Orang yang mengisi dan komposisi instrumen dapat berbeda antarbulan. Hasil ini bersifat deskriptif; belum menentukan penyebab perubahan atau membuktikan perubahan yang bermakna secara statistik maupun klinis.</div>

<div class="break"></div><h2>3. Analisis instrumen dan domain</h2>
<h3>3.1 Komposisi pelayanan</h3>
<p class="small muted">Proporsi pengisian menurut jenis pelayanan, dari seluruh pengisian PREM dan PROM.</p>
<table width="100%" style="border-collapse:collapse"><tr>
<td width="44%" style="vertical-align:middle;padding:0 10px 0 0"><?= $pieChart($data['services'], $serviceColors) ?></td>
<td width="56%" style="vertical-align:middle;padding:0">
<table class="data"><thead><tr><th>Pelayanan</th><th>Pengisian</th><th>Proporsi</th></tr></thead><tbody>
<?php foreach ($serviceLabels as $key=>$label): ?><tr><td><span style="color:<?= $serviceColors[$key] ?>">&#9632;</span> <?= $label ?></td><td><?= $n($data['services'][$key],0) ?></td><td><?= $n($data['total'] ? $data['services'][$key]/$data['total']*100 : null,1) ?><?= $data['total'] ? '%' : '' ?></td></tr><?php endforeach; ?>
</tbody></table></td></tr></table>

<h3>3.2 Ringkasan per instrumen</h3>
<table class="data"><thead><tr><th>Instrumen</th><th>PREM n / nilai</th><th>T0 n / nilai</th><th>T1 n / nilai</th><th>Pasangan / selisih</th></tr></thead><tbody><?php foreach($data['instrumentStats'] as $item): ?><tr><td><?= $e($item['name']) ?><br><?= $e($item['type']) ?></td><?php foreach($phases as $phase=>$label): ?><td><?= $item['counts'][$phase] ?> / <?= $n($item['averages'][$phase]) ?></td><?php endforeach; ?><td><?= $item['pairs'] ?> / <?= $n($item['delta']) ?></td></tr><?php endforeach; ?><?php if(!$data['instrumentStats']): ?><tr><td colspan="5">Belum ada respons instrumen.</td></tr><?php endif; ?></tbody></table>
<p class="small muted">Setiap instrumen dibaca sesuai isi pertanyaannya. Selisih adalah rerata perubahan pasangan PROM, bukan selisih seluruh rerata T1 dan T0.</p>
<?php if ($data['instrumentStats']): ?><h3>3.3 Tren bulanan per instrumen</h3><?php endif; ?>
<?php foreach ($data['instrumentStats'] as $instrumentIndex => $item): $instrumentPhases = $item['type'] === 'PREM' ? ['PREM'=>'PREM'] : ['T0'=>'PROM PRE / T0', 'T1'=>'PROM POST / T1']; ?>
<h4>3.3.<?= $instrumentIndex + 1 ?> <?= $e($item['name']) ?></h4>
<p class="small muted"><?= $e($item['type']) ?> | Rerata skor tiap pengisian menurut bulan pengiriman, skala 1-5.</p>
<div class="legend"><?php if ($item['type'] === 'PREM'): ?><span style="color:#087e82">PREM</span><?php else: ?><span style="color:#527fbc">PROM PRE / T0</span> &nbsp; | &nbsp; <span style="color:#cb8b37">PROM POST / T1</span><?php endif; ?></div>
<?= $chart($item['monthlyAverages']) ?>
<table class="data"><thead><tr><th>Bulan</th><?php foreach ($instrumentPhases as $phase=>$label): ?><th><?= $e($label) ?> - pengisian</th><th>Rerata skor</th><?php endforeach; ?></tr></thead><tbody>
<?php foreach ($item['monthlyAverages'] as $month=>$averages): ?><tr><td><?= $months[(int) substr($month,5,2)-1] ?></td><?php foreach ($instrumentPhases as $phase=>$label): ?><td><?= $n($item['monthlyCounts'][$month][$phase],0) ?></td><td><?= $n($averages[$phase]) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
</tbody></table>
<p class="small muted">Tanda - berarti belum ada nilai. Garis terputus pada bulan tanpa nilai; titik tunggal tetap ditampilkan. Setiap pengisian bernilai memiliki bobot sama. Untuk PROM, grafik mencakup seluruh pengisian T0/T1, bukan hanya pasangan pasien yang sama.</p>
<?php endforeach; ?>

<h3>3.4 Penilaian per domain</h3>
<table class="data"><thead><tr><th>Domain / instrumen</th><th>PREM</th><th>T0</th><th>T1</th><th>Jumlah pengisian</th></tr></thead><tbody><?php foreach($data['domains'] as $domain): ?><tr><td><?= $e($domain['name']) ?><br><span class="muted"><?= $e($domain['instrument']) ?></span></td><?php foreach($phases as $phase=>$label): ?><td><?= $n($domain['averages'][$phase]) ?></td><?php endforeach; ?><td><?= $domain['submissionCount'] ?></td></tr><?php endforeach; ?><?php if(!$data['domains']): ?><tr><td colspan="5">Belum ada jawaban domain.</td></tr><?php endif; ?></tbody></table>
<p>Jumlah pengisian dihitung satu kali per domain; T0 dan T1 terpisah, bukan orang unik. Rerata domain memakai seluruh jawaban. Kelompok pengisi T0/T1 dapat berbeda; verifikasi konteksnya bersama unit terkait.</p>
<?php $domainGroupNumber = 0; foreach ($domainGroups as $group): $domainGroupNumber++; ?>
<?php $domains = $group['domains'];
    $domainBars = [];
    $domainPhases = $group['type'] === 'PREM' ? ['PREM'=>'PREM'] : ['T0'=>'T0', 'T1'=>'T1'];
    foreach ($domains as $index => $domain) {
        foreach ($domainPhases as $phase=>$label) $domainBars[] = [
            'label'=>$domain['name'].($group['type'] === 'PROM' ? ' - '.$label : ''), 'value'=>$domain['averages'][$phase],
            'color'=>['PREM'=>'#087e82','T0'=>'#527fbc','T1'=>'#cb8b37'][$phase],
        ];
    }
?>
<div class="break"></div>
<?php if ($domainGroupNumber === 1): ?><h3>3.5 Grafik penilaian domain</h3><?php endif; ?>
<h4>3.5.<?= $domainGroupNumber ?> <?= $e($group['name']) ?></h4>
<p class="small muted"><?php if ($group['type'] === 'PREM'): ?>Domain diurutkan dari rerata PREM terendah. Mulai telaah dari domain di bagian atas; urutan ini bukan penilaian gagal memenuhi target.<?php else: ?>Domain diurutkan dari rerata T1 terendah; domain tanpa T1 berada di akhir. Biru: T0, jingga: T1. Perbedaan skor belum menunjukkan perubahan pasien yang sama.<?php endif; ?></p>
<?= $group['type'] === 'PROM' ? $groupedPhaseChart($domains) : $barChart($domainBars, 5, '/ 5', 260) ?>
<table class="data"><thead><tr><th>Domain</th><?php foreach ($domainPhases as $phase=>$label): ?><th><?= $label ?></th><?php endforeach; ?><th>Pengisian</th></tr></thead><tbody>
<?php foreach ($domains as $index=>$domain): ?><tr><td><?= $e($domain['name']) ?></td><?php foreach ($domainPhases as $phase=>$label): ?><td><?= $n($domain['averages'][$phase]) ?></td><?php endforeach; ?><td><?= $n($domain['submissionCount'],0) ?></td></tr><?php endforeach; ?>
</tbody></table>
<p class="small muted">Skala jawaban 1-5; batang dimulai dari 0 untuk perbandingan visual. Tanda - berarti belum ada nilai. Rerata dihitung dari seluruh jawaban domain. Pertimbangkan jumlah pengisian dan makna skor instrumen saat menentukan tindak lanjut; tidak ada ambang prioritas yang ditetapkan otomatis.</p>
<?php endforeach; ?>
<div class="break"></div><h2>4. Perubahan PROM berpasangan</h2>
<p><?= $data['pairs'] ?> pasangan dapat dibandingkan. Rerata PRE <?= $n($data['preAverage']) ?>; POST <?= $n($data['postAverage']) ?>; selisih <?= $n($data['delta']) ?> poin.</p>
<?php if ($data['pairs']): ?>
<h3>4.1 Rerata skor PRE dan POST</h3>
<?= $barChart([
    ['label'=>'PRE / T0', 'value'=>$data['preAverage'], 'color'=>'#527fbc'],
    ['label'=>'POST / T1', 'value'=>$data['postAverage'], 'color'=>'#087e82'],
], 5, '/ 5') ?>
<p class="small muted">Skor dari pasangan yang sama. Sumbu dimulai dari 0 agar panjang batang dapat dibandingkan; nilai jawaban tetap berskala 1-5.</p>
<?php endif; ?>
<h3>4.2 Arah perubahan pasangan</h3>
<?php $directionColors = ['improved'=>'#087e82','unchanged'=>'#cbac55','declined'=>'#be6464']; ?>
<table width="100%" style="border-collapse:collapse"><tr>
<td width="44%" style="vertical-align:middle;padding:0 10px 0 0"><?= $pieChart([
    'improved'=>$data['improved'], 'unchanged'=>$data['unchanged'], 'declined'=>$data['declined'],
], $directionColors, 'Belum ada pasangan') ?></td>
<td width="56%" style="vertical-align:middle;padding:0">
<table class="data"><thead><tr><th>Arah skor POST dibanding PRE</th><th>Pasangan</th><th>Proporsi</th></tr></thead><tbody><?php foreach(['improved'=>'Skor naik','unchanged'=>'Skor tetap','declined'=>'Skor turun'] as $key=>$label): ?><tr><td><span style="color:<?= $directionColors[$key] ?>">&#9632;</span> <?= $label ?></td><td><?= $data[$key] ?></td><td><?= $n($data['pairs'] ? $data[$key]/$data['pairs']*100 : null,1) ?><?= $data['pairs'] ? '%' : '' ?></td></tr><?php endforeach; ?></tbody></table>
</td></tr></table>
<p class="small muted">Satu pasangan per instrumen, jenis pelayanan, dan nomor rekam medis: T0 terakhir dengan T1 pertama pada atau setelah waktu T0, keduanya dalam tahun laporan. Tanpa identitas episode perawatan, pasangan belum menjamin kunjungan yang sama. Arah skor perlu ditafsirkan sesuai instrumen; laporan ini tidak menetapkan ambang perubahan klinis.</p>
<div class="break"></div><h2>5. Area PREM dan PROM yang perlu ditinjau</h2>
<h3>5.1 Prioritas pertanyaan PREM</h3>
<p>Lima pertanyaan PREM dengan rerata terendah. Peringkat bersifat relatif terhadap data tahun ini, bukan penilaian gagal memenuhi target.</p>
<table class="data"><thead><tr><th>Pertanyaan / domain / instrumen</th><th>Rerata</th><th>Positif</th><th>Jawaban</th></tr></thead><tbody><?php foreach($data['priorities'] as $item): ?><tr><td><?= $e($item['question']) ?><br><span class="muted"><?= $e($item['domain']) ?> / <?= $e($item['instrument']) ?></span></td><td><?= $n($item['average']) ?></td><td><?= $n($item['positive'],1) ?>%</td><td><?= $item['count'] ?></td></tr><?php endforeach; ?><?php if(!$data['priorities']): ?><tr><td colspan="4">Belum ada jawaban PREM.</td></tr><?php endif; ?></tbody></table>
<h3>5.2 Distribusi jawaban PREM</h3>
<?php if ($data['answerCount']): ?>
<p class="small muted">Proporsi jawaban pada setiap skor. Skor 4-5 merupakan jawaban positif PREM.</p>
<?= $barChart($distributionBars, 100, '%') ?>
<?php endif; ?>
<table class="data"><thead><tr><th>Pilihan jawaban</th><th>Jumlah jawaban</th><th>Proporsi</th></tr></thead><tbody><?php foreach($data['distribution'] as $score=>$count): ?><tr><td><?= $e(\Modules\Pe\Config\SkmQuestionnaire::defaultOptions()[$score]) ?></td><td><?= $count ?></td><td><?= $n($data['answerCount'] ? $count/$data['answerCount']*100 : null,1) ?><?= $data['answerCount'] ? '%' : '' ?></td></tr><?php endforeach; ?></tbody></table>
<div class="break"></div><h3>5.3 Area PROM yang perlu ditinjau</h3>
<p>Untuk setiap instrumen, berikut maksimal lima pertanyaan dengan rerata T1 terendah. Nilai T0 ditampilkan sebagai konteks kondisi awal, bukan pembanding pasien yang sama.</p>
<?php foreach ($data['promPriorities'] as $priorityIndex=>$instrument): ?>
<h4>5.3.<?= $priorityIndex+1 ?> <?= $e($instrument['instrument']) ?></h4>
<table class="data"><thead><tr><th>Pertanyaan / domain</th><th>Rerata T0</th><th>Pengisian T0</th><th>Rerata T1</th><th>Pengisian T1</th></tr></thead><tbody>
<?php foreach ($instrument['questions'] as $item): ?><tr><td><?= $e($item['question']) ?><br><span class="muted"><?= $e($item['domain']) ?></span></td><td><?= $n($item['averages']['T0']) ?></td><td><?= $n($item['counts']['T0'],0) ?></td><td><b><?= $n($item['averages']['T1']) ?></b></td><td><?= $n($item['counts']['T1'],0) ?></td></tr><?php endforeach; ?>
<?php if (!$instrument['questions']): ?><tr><td colspan="5">Belum ada jawaban T1 untuk menentukan area tinjauan instrumen ini.</td></tr><?php endif; ?>
</tbody></table>
<?php if ($instrument['questions']): $lowest = $instrument['questions'][0]; ?>
<p><b>Fokus telaah:</b> Pertanyaan dengan rerata T1 terendah pada instrumen ini berada pada domain <b><?= $e($lowest['domain']) ?></b>, dengan nilai <?= $n($lowest['averages']['T1']) ?> dari <?= $n($lowest['counts']['T1'],0) ?> pengisian. Tinjau isi pertanyaan, makna skor, dan konteks klinis sebelum menetapkan tindakan.</p>
<?php endif; endforeach; ?>
<?php if (!$data['promPriorities']): ?><p>Belum ada jawaban PROM pada tahun laporan.</p><?php endif; ?>
<div class="box small"><b>Cara menggunakan hasil:</b> Urutan hanya dibandingkan dalam instrumen yang sama. Rerata T1 rendah merupakan area untuk ditelaah, bukan bukti bahwa pelayanan gagal. Pastikan arah skor sesuai definisi instrumen. Jumlah dan kelompok pengisi T0/T1 dapat berbeda; perubahan pasien yang sama ditinjau melalui analisis berpasangan. Diskusikan temuan bersama tim klinis, lalu tentukan tindak lanjut dan waktu evaluasi berdasarkan hasil validasi.</div>
<?php if (!$data['promDistributions']): ?><h3>5.4 Distribusi jawaban PROM</h3><p>Belum ada jawaban PROM pada tahun laporan.</p><?php endif; ?>
<?php foreach ($data['promDistributions'] as $distributionIndex=>$instrument):
    $totals = array_map('array_sum', $instrument['scores']);
    $scoreGroups = [];
    foreach ($instrument['options'] as $score => $answerLabel) {
        $values = [];
        foreach (['T0','T1'] as $phase) $values[$phase] = $totals[$phase] ? $instrument['scores'][$phase][$score]/$totals[$phase]*100 : null;
        $scoreGroups[] = ['name'=>$instrument['options'][$score], 'averages'=>$values];
    }
?>
<div class="break"></div>
<?php if ($distributionIndex === 0): ?><h3>5.4 Distribusi jawaban PROM</h3><?php endif; ?>
<h4>5.4.<?= $distributionIndex+1 ?> <?= $e($instrument['instrument']) ?></h4>
<p>Grafik menunjukkan proporsi jawaban skor 1-5 pada T0 dan T1. Setiap persentase memakai total jawaban pada tahap yang bersangkutan.</p>
<?= $groupedPhaseChart($scoreGroups, 100, '%') ?>
<table class="data"><thead><tr><th>Pilihan jawaban</th><th>Jawaban T0</th><th>Proporsi T0</th><th>Jawaban T1</th><th>Proporsi T1</th></tr></thead><tbody>
<?php foreach ($instrument['options'] as $score => $answerLabel): ?><tr><td><?= $e($instrument['options'][$score]) ?></td><?php foreach (['T0','T1'] as $phase): ?><td><?= $n($instrument['scores'][$phase][$score],0) ?></td><td><?= $n($totals[$phase] ? $instrument['scores'][$phase][$score]/$totals[$phase]*100 : null,1) ?><?= $totals[$phase] ? '%' : '' ?></td><?php endforeach; ?></tr><?php endforeach; ?>
<tr><td><b>Total</b></td><?php foreach (['T0','T1'] as $phase): ?><td><b><?= $n($totals[$phase],0) ?></b></td><td><?= $totals[$phase] ? '100%' : '-' ?></td><?php endforeach; ?></tr>
</tbody></table>
<p class="small muted">Jumlah jawaban adalah jumlah butir pertanyaan yang dijawab, bukan jumlah pengisian atau orang unik. Grafik mencakup seluruh jawaban, termasuk pengisian yang belum berpasangan. Tanda - berarti belum ada jawaban pada tahap tersebut. Makna skor mengikuti instrumen; distribusi ini tidak menetapkan kategori positif atau negatif untuk PROM.</p>
<?php endforeach; ?>
<div class="break"></div><h2>6. Kesimpulan dan usulan tindak lanjut</h2>
<h3>6.1 Kesimpulan berdasarkan hasil pengukuran</h3>
<h4>6.1.1 Cakupan pengisian</h4>
<p>Terdapat <b><?= $n($data['total'],0) ?> pengisian</b>, terdiri atas <?= $n($data['counts']['PREM'],0) ?> PREM dan <?= $n($data['counts']['T0']+$data['counts']['T1'],0) ?> PROM (T0: <?= $n($data['counts']['T0'],0) ?>; T1: <?= $n($data['counts']['T1'],0) ?>). Data tersedia pada <b><?= $activeMonths ?> bulan kalender</b>.<?php if ($isCurrentYear): ?> Tahun masih berjalan, sehingga hasil ini belum mewakili satu tahun penuh.<?php endif; ?> Jumlah tersebut menunjukkan volume pengisian, bukan jumlah orang unik atau persentase pasien yang berpartisipasi.</p>
<h4>6.1.2 PREM - pengalaman pelayanan</h4>
<?php if ($data['premAverage'] !== null): ?>
<p>Rerata PREM adalah <b><?= $n($data['premAverage']) ?> dari 5</b>. Dari <?= $n($data['answerCount'],0) ?> jawaban, <b><?= $n($data['distribution'][4]+$data['distribution'][5],0) ?> jawaban (<?= $n($data['positive'],1) ?>%)</b> bernilai 4-5. Sisanya terdiri atas <?= $n($data['distribution'][3],0) ?> jawaban skor 3 dan <?= $n($data['distribution'][1]+$data['distribution'][2],0) ?> jawaban skor 1-2. Angka ini membantu melihat sebaran pengalaman, bukan hanya reratanya.</p>
<?php foreach ($domainGroups as $group): if ($group['type'] !== 'PREM' || !$group['domains']) continue; $lowestDomain = $group['domains'][0]; ?>
<p><b><?= $e($group['name']) ?>:</b> domain dengan rerata terendah adalah <b><?= $e($lowestDomain['name']) ?></b> (<?= $n($lowestDomain['averages']['PREM']) ?> / 5; <?= $n($lowestDomain['submissionCount'],0) ?> pengisian). Domain ini dapat menjadi titik awal telaah pertanyaan dan proses pelayanan; skor relatif rendah belum membuktikan penyebab tertentu.</p>
<?php endforeach; else: ?><p>Belum ada nilai PREM untuk menyimpulkan pengalaman pelayanan atau menentukan domain yang perlu ditinjau.</p><?php endif; ?>
<h4>6.1.3 PROM - perubahan skor selama perawatan</h4>
<?php if ($data['pairs']): ?>
<p>Dari <b><?= $n($data['pairs'],0) ?> pasangan T0-T1</b>, rerata PRE adalah <?= $n($data['preAverage']) ?> dan POST <?= $n($data['postAverage']) ?>, dengan rerata perubahan <b><?= $data['delta'] > 0 ? '+' : '' ?><?= $n($data['delta']) ?> poin</b>. Skor POST lebih tinggi pada <b><?= $n($data['improved'],0) ?> pasangan (<?= $n($data['improvedPercent'],1) ?>%)</b>, tetap pada <?= $n($data['unchanged'],0) ?> pasangan (<?= $n($data['unchanged']/$data['pairs']*100,1) ?>%), dan lebih rendah pada <?= $n($data['declined'],0) ?> pasangan (<?= $n($data['declined']/$data['pairs']*100,1) ?>%).</p>
<p>Artinya, pada kelompok yang dapat dipasangkan, <b><?= $data['delta'] > 0 ? 'skor rata-rata bergerak naik' : ($data['delta'] < 0 ? 'skor rata-rata bergerak turun' : 'skor rata-rata tidak berubah') ?></b>. Ini menggambarkan arah skor; manfaat klinis dan penyebab perubahan tetap harus dibaca sesuai instrumen serta kondisi pasien.</p>
<?php else: ?><p>Terdapat <?= $n($data['counts']['T0'],0) ?> pengisian T0 dan <?= $n($data['counts']['T1'],0) ?> pengisian T1, tetapi belum ada pasangan yang dapat dibandingkan. Perubahan selama perawatan belum dapat dihitung; jumlah pengisian T0/T1 saja belum cukup untuk menyimpulkan perubahan pasien.</p><?php endif; ?>
<?php if ($data['counts']['T0'] || $data['counts']['T1']): ?>
<table class="data"><thead><tr><th>Instrumen PROM</th><th>Pengisian T0 / T1</th><th>Pasangan</th><th>Perubahan rerata</th></tr></thead><tbody>
<?php foreach ($data['instrumentStats'] as $item): if ($item['type'] !== 'PROM') continue; ?><tr><td><?= $e($item['name']) ?></td><td><?= $n($item['counts']['T0'],0) ?> / <?= $n($item['counts']['T1'],0) ?></td><td><?= $n($item['pairs'],0) ?></td><td><?= $item['delta'] !== null && $item['delta'] > 0 ? '+' : '' ?><?= $n($item['delta']) ?> poin</td></tr><?php endforeach; ?>
</tbody></table>
<?php endif; ?>
<div class="break"></div><h3>6.2 Usulan tindak lanjut berdasarkan temuan</h3>
<p>Usulan berikut menghubungkan hasil pengukuran dengan langkah yang dapat dilakukan. Penanggung jawab, target, dan jadwal ditetapkan organisasi setelah temuan divalidasi.</p>
<table class="data"><thead><tr><th>Temuan kuantitatif</th><th>Usulan tindakan</th><th>Yang dipantau saat evaluasi</th></tr></thead><tbody>
<tr><td><b>PREM</b><br><?php if ($data['answerCount']): ?><?= $n($data['positive'],1) ?>% jawaban bernilai 4-5; <?= $n($data['distribution'][1]+$data['distribution'][2],0) ?> jawaban bernilai 1-2.<?php else: ?>Belum ada jawaban PREM.<?php endif; ?></td><td><?= $data['answerCount'] ? 'Mulai dari domain dan pertanyaan dengan rerata terendah pada instrumen yang sama. Konfirmasi kendala melalui telaah proses dan diskusi pasien sebelum memilih perubahan pelayanan.' : 'Verifikasi ketersediaan instrumen dan proses pengumpulan jawaban PREM.' ?></td><td>Rerata domain/pertanyaan, persentase jawaban 4-5, dan jumlah pengisian pada instrumen serta pelayanan yang sama.</td></tr>
<tr><td><b>PROM berpasangan</b><br><?= $n($data['pairs'],0) ?> pasangan; <?= $n($data['declined'],0) ?> memiliki skor POST lebih rendah.</td><td><?= $data['pairs'] ? 'Telaah pasangan dengan skor turun dan periksa makna skor instrumen serta konteks klinis. Pastikan T0 dan T1 terkait episode perawatan yang sesuai.' : 'Perkuat pengisian awal dan akhir perawatan serta konsistensi instrumen, jenis pelayanan, dan nomor rekam medis.' ?></td><td>Jumlah pasangan, rerata selisih POST-PRE, serta proporsi skor naik/tetap/turun per instrumen.</td></tr>
<?php foreach ($data['promPriorities'] as $instrument): if (!$instrument['questions']) continue; $lowest = $instrument['questions'][0]; ?>
<tr><td><b><?= $e($instrument['instrument']) ?></b><br>Rerata T1 terendah <?= $n($lowest['averages']['T1']) ?> / 5 pada domain <?= $e($lowest['domain']) ?> (<?= $n($lowest['counts']['T1'],0) ?> pengisian).</td><td>Tinjau pertanyaan pada bagian 5.3 bersama tim klinis. Pastikan arah skor dan kebutuhan pasien sebelum menentukan fokus tindak lanjut.</td><td>Rerata dan distribusi jawaban T1 pada pertanyaan yang sama, jumlah pengisian, serta perubahan berpasangan bila tersedia.</td></tr>
<?php endforeach; ?>
<tr><td><b>Kesinambungan data</b><br><?= $activeMonths ?> bulan memiliki pengisian; T0 <?= $n($data['counts']['T0'],0) ?> dan T1 <?= $n($data['counts']['T1'],0) ?>.</td><td>Tinjau bulan yang sudah berlangsung tetapi belum memiliki data. Periksa alur pengumpulan T0/T1; jumlah T0 dan T1 tidak otomatis menunjukkan jumlah pasien yang gagal ditindaklanjuti.</td><td>Jumlah pengisian per bulan, ketersediaan nilai, dan jumlah pasangan yang dapat dibandingkan.</td></tr>
</tbody></table>
<h3>6.3 Metode dan batas interpretasi</h3>
<div class="box">Sumber: respons PREM/PROM dan master PE organisasi pengguna. Rerata PREM serta tren bulanan memberi bobot sama pada setiap pengisian bernilai. Positif PREM = jawaban skor 4-5 dibagi seluruh jawaban PREM. Rerata domain memakai seluruh jawaban domain. Pasangan PROM memakai T0 terakhir dan T1 pertama sesudahnya untuk instrumen, pelayanan, dan nomor rekam medis yang sama dalam tahun laporan; tanpa identitas episode, keterkaitan kunjungan perlu diverifikasi. Nama dan pertanyaan mengikuti master terbaru. Tidak tersedia jumlah pasien yang memenuhi syarat, sehingga tingkat partisipasi tidak dihitung. Target mutu, ambang perubahan klinis, dan penyebab tidak ditetapkan otomatis. Hasil bersifat deskriptif dan memerlukan validasi organisasi.</div>
</body></html>
