<?php
namespace Modules\Pe\Models;

use CodeIgniter\Model;
use Modules\Pe\Libraries\FeedbackAnalysis;

class SkmReportModel extends Model
{
    public function report(string $group, string $org, string $start, string $end): array
    {
        $until = (new \DateTimeImmutable($end))->modify('+1 day')->format('Y-m-d');
        $skm = $this->db->table('dt01_pe_skm_response_hd h')->select('h.*, i.name AS instrument_name')
            ->join('dt01_pe_instrument_ms i', 'i.instrument_id=h.instrument_id AND i.group_id=h.group_id AND i.org_id=h.org_id')
            ->where('h.group_id', $group)->where('h.org_id', $org)->where('h.submitted_at >=', $start.' 00:00:00')->where('h.submitted_at <', $until.' 00:00:00')->get()->getResultArray();
        $prem = $this->db->table('dt01_pe_prem_response_hd p')->select('p.*, s.service_type AS source_service, s.submitted_at AS source_date, i.name AS instrument_name')
            ->join('dt01_pe_skm_response_hd s', 's.response_id=p.skm_response_id AND s.group_id=p.group_id AND s.org_id=p.org_id')
            ->join('dt01_pe_instrument_ms i', 'i.instrument_id=p.instrument_id AND i.group_id=p.group_id AND i.org_id=p.org_id')
            ->where('s.group_id', $group)->where('s.org_id', $org)->where('s.submitted_at >=', $start.' 00:00:00')->where('s.submitted_at <', $until.' 00:00:00')
            ->where('p.submitted_at <', $until.' 00:00:00')->get()->getResultArray();
        foreach ($prem as &$row) $row['service_type'] = $row['source_service'];
        unset($row);
        $skmDetails = $this->details('skm', $skm, $group, $org);
        $premDetails = $this->details('prem', $prem, $group, $org);
        $skmSummary = $this->summarize($skm, $skmDetails, 4, $start, $end);
        $premSummary = $this->summarize($prem, $premDetails, 5, $start, $end);
        $feedback = [];
        $sources = [];
        foreach (['SKM'=>$skm, 'PREM terkait'=>$prem] as $label=>$rows) {
            $comments = array_values(array_filter(array_column($rows, 'suggestions'), static fn($text) => trim((string)$text) !== '' && preg_match('/\p{L}/u', $text)));
            $sources[$label] = count($comments);
            $feedback = array_merge($feedback, $comments);
        }
        $dashboard = (new DashboardModel())->skmAnalytics($group, $org, ['start'=>$start,'end'=>$end,'service'=>'','instrument'=>'']);
        $cohortCount = count($skm);
        $linked = count(array_unique(array_column($prem, 'skm_response_id')));
        return ['dashboard'=>$dashboard, 'skm'=>$skmSummary, 'prem'=>$premSummary,
            'cohortCount'=>$cohortCount, 'linkedCount'=>$linked, 'pendingCount'=>max(0,$cohortCount-$linked),
            'completionRate'=>$cohortCount ? $linked/$cohortCount*100 : null,
            'feedback'=>FeedbackAnalysis::summarize($feedback)+['sources'=>$sources],
            'dummyCount'=>count(array_filter($skm, static fn($row)=>strpos($row['medical_record_number'], 'DUMMY') === 0)),
        ];
    }

    private function details(string $prefix, array $headers, string $group, string $org): array
    {
        if (!$headers) return [];
        return $this->db->table('dt01_pe_'.$prefix.'_response_dt r')
            ->select('r.response_id,r.question_id,r.domain_id,r.score,q.question,q.sort_order,d.name AS domain_name')
            ->join('dt01_pe_'.$prefix.'_response_hd h','h.response_id=r.response_id AND h.group_id=r.group_id AND h.org_id=r.org_id')
            ->join('dt01_pe_question_ms q','q.question_id=r.question_id AND q.instrument_id=h.instrument_id AND q.group_id=h.group_id AND q.org_id=h.org_id')
            ->join('dt01_pe_domain_ms d','d.domain_id=r.domain_id AND d.group_id=r.group_id AND d.org_id=r.org_id')
            ->where('r.group_id',$group)->where('r.org_id',$org)->whereIn('r.response_id',array_column($headers,'response_id'))->orderBy('q.sort_order')->get()->getResultArray();
    }

    private function summarize(array $headers, array $details, int $scale, string $start, string $end): array
    {
        $indexed=[];
        foreach($headers as $h) { $h['scores']=[]; $indexed[$h['response_id']]=$h; }
        $distribution=array_fill(1,$scale,0);
        $questions=[]; $domains=[];
        foreach($details as $detail) {
            $h=$indexed[$detail['response_id']];
            if((int)$h['score_scale']!==$scale) continue;
            $score=(int)$detail['score'];
            if($score<1||$score>$scale) continue;
            $indexed[$detail['response_id']]['scores'][]=$score;
            $distribution[$score]++;
            $key=$h['instrument_id'].'/'.$detail['question_id'];
            if(!isset($questions[$key])) $questions[$key]=['label'=>$detail['question'],'instrument'=>$h['instrument_name'],'domain'=>$detail['domain_name'],'order'=>(int)$detail['sort_order'],'scores'=>[]];
            $questions[$key]['scores'][]=$score;
            $key=$h['instrument_id'].'/'.$detail['domain_id'];
            if(!isset($domains[$key])) $domains[$key]=['label'=>$detail['domain_name'],'instrument'=>$h['instrument_name'],'scores'=>[]];
            $domains[$key]['scores'][]=$score;
        }
        $services=array_fill_keys(['IGD','RJ','RI'],['count'=>0,'scores'=>[],'distribution'=>array_fill(1,$scale,0),'linked'=>0]);
        $month=new \DateTimeImmutable(substr($start,0,7).'-01');$last=substr($end,0,7);$monthly=[];
        while($month->format('Y-m')<=$last){$monthly[$month->format('Y-m')]=array_fill_keys(['IGD','RJ','RI'],['count'=>0,'scores'=>[]]);$month=$month->modify('+1 month');}
        $scores=[]; $scored=0;
        foreach($indexed as $h){
            $service=$h['service_type'];$month=substr($h['source_date']??$h['submitted_at'],0,7);
            if(!isset($services[$service]))continue;
            $services[$service]['count']++;$monthly[$month][$service]['count']++;
            if(!$h['scores'])continue;
            $mean=array_sum($h['scores'])/count($h['scores']);$scored++;$scores[]=$mean;
            $services[$service]['scores'][]=$mean;$monthly[$month][$service]['scores'][]=$mean;
            foreach($h['scores'] as $s)$services[$service]['distribution'][$s]++;
        }
        foreach($services as &$service){$service['stats']=self::stats($service['scores']);$service['answerCount']=array_sum($service['distribution']);$service['highPercent']=$service['answerCount']?array_sum(array_slice($service['distribution'],$scale-2,null,true))/$service['answerCount']*100:null;unset($service['scores']);}unset($service);
        foreach($monthly as &$month)foreach($month as &$service){$service['average']=DashboardModel::mean($service['scores']);unset($service['scores']);}unset($month,$service);
        foreach($questions as &$q){$q['count']=count($q['scores']);$q['average']=DashboardModel::mean($q['scores']);$q['highPercent']=count(array_filter($q['scores'],static fn($v)=>$v>=$scale-1))/$q['count']*100;unset($q['scores']);}unset($q);
        foreach($domains as &$d){$d['count']=count($d['scores']);$d['average']=DashboardModel::mean($d['scores']);unset($d['scores']);}unset($d);
        $priorities=array_values($questions);usort($priorities,static fn($a,$b)=>($a['average']<=>$b['average'])?:strcmp($a['label'],$b['label']));
        $answers=array_sum($distribution);$high=$distribution[$scale]+$distribution[$scale-1];
        return ['count'=>count($headers),'scoredCount'=>$scored,'excludedCount'=>count($headers)-$scored,'stats'=>self::stats($scores),'scale'=>$scale,
            'distribution'=>$distribution,'answerCount'=>$answers,'highPercent'=>$answers?$high/$answers*100:null,'lowPercent'=>$answers?($answers-$high)/$answers*100:null,
            'services'=>$services,'monthly'=>$monthly,'questions'=>array_values($questions),'domains'=>array_values($domains),'priorities'=>array_slice($priorities,0,5)];
    }

    public static function stats(array $values): array
    {
        sort($values,SORT_NUMERIC);$n=count($values);
        if(!$n)return ['n'=>0,'mean'=>null,'median'=>null,'sd'=>null,'min'=>null,'max'=>null];
        $mean=array_sum($values)/$n;$median=$n%2?$values[intdiv($n,2)]:($values[$n/2-1]+$values[$n/2])/2;
        $ss=array_sum(array_map(static fn($v)=>($v-$mean)**2,$values));
        return ['n'=>$n,'mean'=>$mean,'median'=>$median,'sd'=>$n>1?sqrt($ss/($n-1)):null,'min'=>$values[0],'max'=>$values[$n-1]];
    }
}
