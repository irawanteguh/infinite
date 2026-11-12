<?php

namespace Modules\Pe\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    public function instruments(string $group, string $org): array
    {
        return $this->db->table('dt01_pe_instrument_ms')->select('instrument_id, name, type')
            ->where('group_id', $group)->where('org_id', $org)->orderBy('type')->orderBy('name')->get()->getResultArray();
    }

    private function scoped(string $table, string $group, string $org, array $filters)
    {
        $builder = $this->db->table($table . ' h')
            ->where('h.group_id', $group)->where('h.org_id', $org)
            ->where('h.submitted_at >=', $filters['start'] . ' 00:00:00')
            ->where('h.submitted_at <', (new \DateTimeImmutable($filters['end']))->modify('+1 day')->format('Y-m-d') . ' 00:00:00');
        if ($filters['service'] !== '') $builder->where('h.service_type', $filters['service']);
        if ($filters['instrument'] !== '') $builder->where('h.instrument_id', $filters['instrument']);
        return $builder;
    }

    public function analytics(string $group, string $org, array $filters): array
    {
        $responses = [];
        $details = [];
        foreach (['PREM' => 'prem', 'PROM' => 'prom'] as $type => $prefix) {
            $header = 'dt01_pe_' . $prefix . '_response_hd';
            $detail = 'dt01_pe_' . $prefix . '_response_dt';
            $rows = $this->scoped($header, $group, $org, $filters)
                ->select('h.*, i.name AS instrument_name')
                ->join('dt01_pe_instrument_ms i', 'i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id')
                ->where('i.type', $type)->orderBy('h.submitted_at')->orderBy('h.response_id')->get()->getResultArray();
            foreach ($rows as $row) {
                $row['type'] = $type;
                $row['phase'] = $type === 'PREM' ? 'PREM' : $row['measurement_phase'];
                $row['scores'] = [];
                $responses[$row['response_id']] = $row;
            }
            $rows = $this->scoped($header, $group, $org, $filters)
                ->select('r.response_id, r.question_id, r.domain_id, r.score, q.question, d.name AS domain_name')
                ->join('dt01_pe_instrument_ms i', 'i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id')
                ->join($detail . ' r', 'r.response_id = h.response_id')
                ->join('dt01_pe_question_ms q', 'q.question_id = r.question_id AND q.instrument_id = h.instrument_id')
                ->join('dt01_pe_domain_ms d', 'd.domain_id = r.domain_id AND d.group_id = h.group_id AND d.org_id = h.org_id')
                ->where('i.type', $type)->get()->getResultArray();
            foreach ($rows as $row) {
                if (!isset($responses[$row['response_id']])) continue;
                $responses[$row['response_id']]['scores'][] = (int) $row['score'];
                $details[] = $row;
            }
        }
        $counts = ['PREM' => 0, 'T0' => 0, 'T1' => 0];
        $services = ['RJ' => 0, 'RI' => 0, 'IGD' => 0, 'OTHER' => 0];
        $monthly = [];
        $month = (new \DateTimeImmutable($filters['start']))->modify('first day of this month');
        $last = (new \DateTimeImmutable($filters['end']))->modify('first day of this month');
        while ($month <= $last) {
            $monthly[$month->format('Y-m')] = ['PREM' => 0, 'T0' => 0, 'T1' => 0];
            $month = $month->modify('+1 month');
        }
        $dummy = 0;
        $premScores = [];
        $cohorts = [];
        foreach ($responses as &$row) {
            $row['average'] = $row['scores'] ? array_sum($row['scores']) / count($row['scores']) : null;
            $counts[$row['phase']]++;
            $services[isset($services[$row['service_type']]) ? $row['service_type'] : 'OTHER']++;
            $monthly[substr($row['submitted_at'], 0, 7)][$row['phase']]++;
            if (strpos((string) $row['medical_record_number'], 'DUMMY-') === 0) $dummy++;
            if ($row['type'] === 'PREM' && $row['average'] !== null) $premScores[] = $row['average'];
            if ($row['type'] === 'PROM' && trim((string) $row['medical_record_number']) !== '' && $row['average'] !== null) {
                $key = $row['instrument_id'] . '/' . $row['service_type'] . '/' . $row['medical_record_number'];
                $cohorts[$key][$row['phase']][] = $row;
            }
        }
        unset($row);
        $pairs = [];
        foreach ($cohorts as $cohort) {
            if (empty($cohort['T0']) || empty($cohort['T1'])) continue;
            // One comparison per MR, instrument and service: latest PRE, then first subsequent POST.
            $pre = end($cohort['T0']);
            foreach ($cohort['T1'] as $post) {
                if ($post['submitted_at'] < $pre['submitted_at']) continue;
                $pairs[] = ['pre' => $pre['average'], 'post' => $post['average'], 'instrument_id' => $pre['instrument_id']];
                break;
            }
        }
        $domains = [];
        $questionStats = [];
        $distribution = array_fill(1, 5, 0);
        foreach ($details as $detail) {
            $response = $responses[$detail['response_id']];
            $phase = $response['phase'];
            $key = $response['instrument_id'] . '/' . $detail['domain_id'];
            if (!isset($domains[$key])) $domains[$key] = [
                'name' => $detail['domain_name'], 'instrument' => $response['instrument_name'],
                'type' => $response['type'], 'scores' => ['PREM' => [], 'T0' => [], 'T1' => []],
            ];
            $score = (int) $detail['score'];
            $domains[$key]['scores'][$phase][] = $score;
            if ($phase !== 'PREM') continue;
            $distribution[$score]++;
            $key = $response['instrument_id'] . '/' . $detail['question_id'];
            if (!isset($questionStats[$key])) $questionStats[$key] = [
                'question' => $detail['question'], 'domain' => $detail['domain_name'],
                'instrument' => $response['instrument_name'], 'scores' => [],
            ];
            $questionStats[$key]['scores'][] = $score;
        }
        foreach ($questionStats as &$item) {
            $item['average'] = self::mean($item['scores']);
            $item['count'] = count($item['scores']);
            $item['positive'] = count(array_filter($item['scores'], static fn($score) => $score >= 4)) / $item['count'] * 100;
            unset($item['scores']);
        }
        unset($item);
        usort($questionStats, static fn($a, $b) => $a['average'] <=> $b['average']);
        foreach ($domains as &$domain) {
            $domain['averages'] = array_map([self::class, 'mean'], $domain['scores']);
            $domain['count'] = array_sum(array_map('count', $domain['scores']));
            unset($domain['scores']);
        }
        unset($domain);
        $positive = $distribution[4] + $distribution[5];
        $answerCount = array_sum($distribution);
        $delta = $pairs ? self::mean(array_map(static fn($pair) => $pair['post'] - $pair['pre'], $pairs)) : null;
        $improved = count(array_filter($pairs, static fn($pair) => $pair['post'] > $pair['pre']));
        return [
            'total' => count($responses), 'counts' => $counts, 'services' => $services, 'dummy' => $dummy,
            'premAverage' => self::mean($premScores), 'positive' => $answerCount ? $positive / $answerCount * 100 : null,
            'answerCount' => $answerCount, 'distribution' => $distribution, 'monthly' => $monthly,
            'pairs' => count($pairs), 'preAverage' => self::mean(array_column($pairs, 'pre')),
            'postAverage' => self::mean(array_column($pairs, 'post')), 'delta' => $delta,
            'improved' => $improved, 'improvedPercent' => $pairs ? $improved / count($pairs) * 100 : null,
            'domains' => array_values($domains), 'priorities' => array_slice($questionStats, 0, 5),
        ];
    }

    public static function mean(array $values): ?float
    {
        return $values ? array_sum($values) / count($values) : null;
    }
}