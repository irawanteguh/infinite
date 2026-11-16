<?php
require dirname(__DIR__,2).'/app/Modules/Klaim/Services/PendingAnalytics.php';
use Modules\Klaim\Services\PendingAnalytics;
$n=0;function check($test,$message){global $n;if(!$test)throw new RuntimeException($message);$n++;}
$rows=[
 ['periode'=>2,'status'=>'PENDING','jnspel'=>'1','jumlah_kasus'=>10,'valued_count'=>4,'pengajuan'=>400],
 ['periode'=>2,'status'=>'TIDAK LAYAK','jnspel'=>'2','jumlah_kasus'=>3,'valued_count'=>0,'pengajuan'=>null],
 ['periode'=>3,'status'=>'PENDING','jnspel'=>'1','jumlah_kasus'=>5,'valued_count'=>5,'pengajuan'=>0],
 ['periode'=>3,'status'=>'TIDAK LAYAK','jnspel'=>'2','jumlah_kasus'=>2,'valued_count'=>1,'pengajuan'=>100],
 ['periode'=>3,'status'=>'STATUS SUMBER','jnspel'=>'','jumlah_kasus'=>1,'valued_count'=>0,'pengajuan'=>null],
 ['periode'=>3,'status'=>'','jnspel'=>'2','jumlah_kasus'=>1,'valued_count'=>0,'pengajuan'=>null],
];
$s=PendingAnalytics::build($rows);
check($s['total']['count']===22,'all statuses retained');
check($s['statuses']['TIDAK LAYAK']['count']===5,'unknown statuses not classified as ineligible');
check($s['statuses']['PENDING']['count']===15,'pending count');
check($s['total']['valued']===10,'valued count');
check($s['total']['amount']==500,'known amounts summed');
check($s['months'][1]===null,'absent month stays null');
check($s['months'][2]['statuses']['TIDAK LAYAK']['amount']===null,'missing values stay null');
check($s['months'][3]['statuses']['PENDING']['amount']===0.0,'known zero preserved');
check($s['months'][3]['total']['count']===9,'monthly aggregation');
check($s['monthsWithData']===2,'observed month coverage');
check($s['latest']===3&&$s['previous']===2,'consecutive calendar comparison');
check($s['change']['difference']===-4,'month count change');
check(abs($s['change']['percent']-(-4/13*100))<0.00001,'percentage denominator is previous recorded count');
check($s['services']['']===1,'missing service retained');
$gap=PendingAnalytics::build([$rows[0],array_replace($rows[2],['periode'=>4])]);
check($gap['change']===null&&$gap['previous']===null,'do not bridge missing month');
$empty=PendingAnalytics::build([]);
check($empty['total']['amount']===null&&$empty['total']['count']===0,'empty metrics');
check($empty['latest']===null&&$empty['change']===null,'empty trend');
check(count($empty['months'])===12,'all calendar months represented');
$alg=PendingAnalytics::algorithms([['algoritma'=>'# ALGORITMA -> Item A.|OTOVER -> Item B|Item A'],['algoritma'=>null],['algoritma'=>'ALGORITMA -> Item B.']]);
check(count($alg)===2,'algorithm normalization');
check($alg[0]['jumlah']===2&&$alg[1]['jumlah']===2,'multiple mentions counted without reclassifying claims');
echo "PASS: $n pending analytics checks; no database access.\n";
