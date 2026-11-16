<?php
namespace Modules\Klaim\Services;

/** Descriptive snapshot metrics. No paid/resolved status or missing amount is inferred. */
class PendingAnalytics
{
    public static function build(array $rows): array
    {
        $empty = static fn() => ['count'=>0,'valued'=>0,'amount'=>null];
        $months = array_fill(1,12,null);
        $statuses = ['PENDING'=>$empty(),'TIDAK LAYAK'=>$empty()];
        $services = []; $total=$empty();
        foreach ($rows as $row) {
            $month=(int)$row['periode']; if($month<1||$month>12)continue;
            $status=trim((string)($row['status']??'')); $service=trim((string)($row['jnspel']??''));
            $count=(int)$row['jumlah_kasus']; $valued=(int)$row['valued_count'];
            $amount=$valued ? (float)$row['pengajuan'] : null;
            if($months[$month]===null)$months[$month]=['total'=>$empty(),'statuses'=>['PENDING'=>$empty(),'TIDAK LAYAK'=>$empty()]];
            if(!isset($statuses[$status]))$statuses[$status]=$empty();
            if(!isset($months[$month]['statuses'][$status]))$months[$month]['statuses'][$status]=$empty();
            if(!isset($services[$service]))$services[$service]=0;
            $services[$service]+=$count;
            foreach ([&$total,&$statuses[$status],&$months[$month]['total'],&$months[$month]['statuses'][$status]] as &$metric) {
                $metric['count']+=$count; $metric['valued']+=$valued;
                if($amount!==null)$metric['amount']=($metric['amount']??0)+$amount;
            }
            unset($metric);
        }
        $available=array_keys(array_filter($months,static fn($month)=>$month!==null));
        $latest=$available?end($available):null;
        $previous=$latest!==null&&$latest>1&&$months[$latest-1]!==null?$latest-1:null;
        $change=null;
        if($previous!==null){
            $now=$months[$latest]['total']['count'];$before=$months[$previous]['total']['count'];
            $change=['difference'=>$now-$before,'percent'=>$before ? ($now-$before)/$before*100 : null];
        }
        return compact('months','statuses','services','total','latest','previous','change')+['monthsWithData'=>count($available)];
    }

    public static function algorithms(array $rows): array
    {
        $counts=[];
        foreach($rows as $row)foreach(explode('|',(string)($row['algoritma']??'')) as $text){
            $text=preg_replace('/^(?:#\s*ALGORITMA|ALGORITMA|OTOVER)\s*->\s*/iu','',trim($text));
            $text=mb_strtoupper(rtrim(trim($text),'.'),'UTF-8');
            if($text==='')continue;
            $counts[$text]=($counts[$text]??0)+1;
        }
        $result=[];foreach($counts as $text=>$count)$result[]=['algoritma'=>$text,'jumlah'=>$count];
        usort($result,static fn($a,$b)=>($b['jumlah']<=>$a['jumlah'])?:strcmp($a['algoritma'],$b['algoritma']));
        return $result;
    }
}
