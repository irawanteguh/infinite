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
                ->where('i.type', $type)->where($type === 'PREM' ? 'h.score_scale = 5' : '1 = 1', null, false)->orderBy('h.submitted_at')->orderBy('h.response_id')->get()->getResultArray();
            foreach ($rows as $row) {
                $row['type'] = $type;
                $row['phase'] = $type === 'PREM' ? 'PREM' : $row['measurement_phase'];
                $row['scores'] = [];
                $responses[$row['response_id']] = $row;
            }
            $rows = $this->scoped($header, $group, $org, $filters)
                ->select('r.response_id, r.question_id, r.domain_id, r.score, q.question, q.answer_options, d.name AS domain_name')
                ->join('dt01_pe_instrument_ms i', 'i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id')
                ->join($detail . ' r', 'r.response_id = h.response_id AND r.group_id = h.group_id AND r.org_id = h.org_id')
                ->join('dt01_pe_question_ms q', 'q.question_id = r.question_id AND q.instrument_id = h.instrument_id AND q.group_id = h.group_id AND q.org_id = h.org_id')
                ->join('dt01_pe_domain_ms d', 'd.domain_id = r.domain_id AND d.group_id = h.group_id AND d.org_id = h.org_id')
                ->where('i.type', $type)->where($type === 'PREM' ? 'h.score_scale = 5' : '1 = 1', null, false)->get()->getResultArray();
            foreach ($rows as $row) {
                if (!isset($responses[$row['response_id']])) continue;
                $responses[$row['response_id']]['scores'][] = (int) $row['score'];
                $details[] = $row;
            }
        }
        $counts = ['PREM' => 0, 'T0' => 0, 'T1' => 0];
        $services = ['RJ' => 0, 'RI' => 0, 'IGD' => 0, 'OTHER' => 0];
        $monthly = [];
        $monthlyScores = [];
        $month = (new \DateTimeImmutable($filters['start']))->modify('first day of this month');
        $last = (new \DateTimeImmutable($filters['end']))->modify('first day of this month');
        while ($month <= $last) {
            $monthly[$month->format('Y-m')] = ['PREM' => 0, 'T0' => 0, 'T1' => 0];
            $monthlyScores[$month->format('Y-m')] = ['PREM' => [], 'T0' => [], 'T1' => []];
            $month = $month->modify('+1 month');
        }
        $premScores = [];
        $cohorts = [];
        $instrumentStats = [];
        foreach ($responses as &$row) {
            $row['average'] = $row['scores'] ? array_sum($row['scores']) / count($row['scores']) : null;
            $id = $row['instrument_id'];
            if (!isset($instrumentStats[$id])) $instrumentStats[$id] = [
                'name' => $row['instrument_name'], 'type' => $row['type'],
                'monthlyScores' => array_fill_keys(array_keys($monthly), ['PREM' => [], 'T0' => [], 'T1' => []]),
                'monthlyCounts' => array_fill_keys(array_keys($monthly), ['PREM' => 0, 'T0' => 0, 'T1' => 0]),
                'counts' => ['PREM' => 0, 'T0' => 0, 'T1' => 0],
                'scores' => ['PREM' => [], 'T0' => [], 'T1' => []],
            ];
            $instrumentStats[$id]['counts'][$row['phase']]++;
            $responseMonth = substr($row['submitted_at'], 0, 7);
            $instrumentStats[$id]['monthlyCounts'][$responseMonth][$row['phase']]++;
            if ($row['average'] !== null) {
                $instrumentStats[$id]['scores'][$row['phase']][] = $row['average'];
                $instrumentStats[$id]['monthlyScores'][$responseMonth][$row['phase']][] = $row['average'];
            }
            $counts[$row['phase']]++;
            $services[isset($services[$row['service_type']]) ? $row['service_type'] : 'OTHER']++;
            $monthly[substr($row['submitted_at'], 0, 7)][$row['phase']]++;
            if ($row['average'] !== null) {
                $monthlyScores[substr($row['submitted_at'], 0, 7)][$row['phase']][] = $row['average'];
            }
            if ($row['type'] === 'PREM' && $row['average'] !== null) $premScores[] = $row['average'];
            if ($row['type'] === 'PROM' && trim((string) $row['medical_record_number']) !== '' && $row['average'] !== null) {
                $key = $row['instrument_id'] . '/' . $row['service_type'] . '/' . $row['medical_record_number'];
                $cohorts[$key][$row['phase']][] = $row;
            }
        }
        unset($row);
        // Match the PREM KPI: each completed response has equal weight.
        $monthlyAverages = array_map(static fn($phases) => array_map([self::class, 'mean'], $phases), $monthlyScores);
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
        foreach ($instrumentStats as $id => &$item) {
            $item['averages'] = array_map([self::class, 'mean'], $item['scores']);
            $item['monthlyAverages'] = array_map(static fn($phases) => array_map([self::class, 'mean'], $phases), $item['monthlyScores']);
            $item['scoredCounts'] = array_map('count', $item['scores']);
            $instrumentPairs = array_values(array_filter($pairs, static fn($pair) => $pair['instrument_id'] === $id));
            $item['pairs'] = count($instrumentPairs);
            $item['delta'] = self::mean(array_map(static fn($pair) => $pair['post'] - $pair['pre'], $instrumentPairs));
            unset($item['scores'], $item['monthlyScores']);
        }
        unset($item);
        $domains = [];
        $questionStats = [];
        $promQuestionStats = [];
        $promDistributions = [];
        $distribution = array_fill(1, 5, 0);
        foreach ($details as $detail) {
            $response = $responses[$detail['response_id']];
            $phase = $response['phase'];
            $key = $response['instrument_id'] . '/' . $detail['domain_id'];
            if (!isset($domains[$key])) $domains[$key] = [
                'name' => $detail['domain_name'], 'instrument' => $response['instrument_name'],
                'instrument_id' => $response['instrument_id'],
                'responseIds' => [],
                'type' => $response['type'], 'scores' => ['PREM' => [], 'T0' => [], 'T1' => []],
            ];
            $score = (int) $detail['score'];
            $domains[$key]['scores'][$phase][] = $score;
            $domains[$key]['responseIds'][$detail['response_id']] = true;
            if ($phase !== 'PREM') {
                $instrumentId = $response['instrument_id'];
                $answerOptions = \Modules\Pe\Config\SkmQuestionnaire::options($detail);
                $distributionKey = $instrumentId . '/' . hash('sha256', json_encode($answerOptions));
                if (!isset($promDistributions[$distributionKey])) $promDistributions[$distributionKey] = [
                    'instrument' => $response['instrument_name'], 'options' => $answerOptions,
                    'scores' => ['T0' => array_fill(1, 5, 0), 'T1' => array_fill(1, 5, 0)],
                ];
                $promDistributions[$distributionKey]['scores'][$phase][$score]++;

                $questionKey = $response['instrument_id'] . '/' . $detail['question_id'];
                if (!isset($promQuestionStats[$questionKey])) $promQuestionStats[$questionKey] = [
                    'instrument_id' => $response['instrument_id'], 'instrument' => $response['instrument_name'],
                    'question' => $detail['question'], 'domain' => $detail['domain_name'],
                    'scores' => ['T0' => [], 'T1' => []],
                ];
                $promQuestionStats[$questionKey]['scores'][$phase][] = $score;
                continue;
            }
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
        $promPriorities = [];
        foreach ($promQuestionStats as $item) {
            $id = $item['instrument_id'];
            if (!isset($promPriorities[$id])) $promPriorities[$id] = ['instrument' => $item['instrument'], 'questions' => []];
            // Rank only observed POST scores, within the same instrument.
            if (!$item['scores']['T1']) continue;
            $item['averages'] = array_map([self::class, 'mean'], $item['scores']);
            $item['counts'] = array_map('count', $item['scores']);
            unset($item['scores']);
            $promPriorities[$id]['questions'][] = $item;
        }
        foreach ($promPriorities as &$item) {
            usort($item['questions'], static fn($a, $b) => ($a['averages']['T1'] <=> $b['averages']['T1']) ?: strcmp($a['question'], $b['question']));
            $item['questions'] = array_slice($item['questions'], 0, 5);
        }
        unset($item);
        foreach ($domains as &$domain) {
            $domain['averages'] = array_map([self::class, 'mean'], $domain['scores']);
            $domain['submissionCount'] = count($domain['responseIds']);
            unset($domain['scores'], $domain['responseIds']);
        }
        unset($domain);
        $positive = $distribution[4] + $distribution[5];
        $answerCount = array_sum($distribution);
        $delta = $pairs ? self::mean(array_map(static fn($pair) => $pair['post'] - $pair['pre'], $pairs)) : null;
        $improved = count(array_filter($pairs, static fn($pair) => $pair['post'] > $pair['pre']));
        return [
            'skm' => $this->skmAnalytics($group, $org, $filters),
            'feedback' => $this->feedbackAnalytics($group, $org, $filters),
            'total' => count($responses), 'counts' => $counts, 'services' => $services,
            'premAverage' => self::mean($premScores), 'positive' => $answerCount ? $positive / $answerCount * 100 : null,
            'answerCount' => $answerCount, 'distribution' => $distribution, 'monthly' => $monthly, 'monthlyAverages' => $monthlyAverages,
            'pairs' => count($pairs), 'preAverage' => self::mean(array_column($pairs, 'pre')),
            'postAverage' => self::mean(array_column($pairs, 'post')), 'delta' => $delta,
            'improved' => $improved, 'improvedPercent' => $pairs ? $improved / count($pairs) * 100 : null,
            'instrumentStats' => array_values($instrumentStats),
            'unchanged' => count(array_filter($pairs, static fn($pair) => $pair['post'] == $pair['pre'])),
            'declined' => count(array_filter($pairs, static fn($pair) => $pair['post'] < $pair['pre'])),
            'promPriorities' => array_values($promPriorities),
            'promDistributions' => array_values($promDistributions),
            'domains' => array_values($domains), 'priorities' => array_slice($questionStats, 0, 5),
        ];
    }

    public function skmAnalytics(string $group, string $org, array $filters): array
    {
        $header = 'dt01_pe_skm_response_hd';
        $distribution = array_fill(1, 4, 0);
        $scoreCounts = $this->scoped($header, $group, $org, $filters)
            ->select('r.score, COUNT(*) AS answer_count')
            ->join('dt01_pe_skm_response_dt r', 'r.response_id = h.response_id AND r.group_id = h.group_id AND r.org_id = h.org_id')
            ->where('h.score_scale', 4)->whereIn('r.score', [1, 2, 3, 4])->groupBy('r.score')->get()->getResultArray();
        foreach ($scoreCounts as $row) $distribution[(int) $row['score']] = (int) $row['answer_count'];
        $responses = $this->scoped($header, $group, $org, $filters)
            ->select('h.response_id, h.instrument_id, h.score_scale, h.service_type, h.submitted_at, i.name AS instrument_name, AVG(r.score) AS average')
            ->join('dt01_pe_instrument_ms i', 'i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id')
            ->join('dt01_pe_skm_response_dt r', 'r.response_id = h.response_id AND r.group_id = h.group_id AND r.org_id = h.org_id')
            ->groupBy('h.response_id, h.instrument_id, h.score_scale, h.service_type, h.submitted_at, i.name')->get()->getResultArray();
        $questions = $this->scoped($header, $group, $org, $filters)
            ->select('h.instrument_id, h.score_scale, q.question_id, q.question, AVG(r.score) AS average, COUNT(*) AS answer_count')
            ->join('dt01_pe_skm_response_dt r', 'r.response_id = h.response_id AND r.group_id = h.group_id AND r.org_id = h.org_id')
            ->join('dt01_pe_question_ms q', 'q.question_id = r.question_id AND q.instrument_id = h.instrument_id AND q.group_id = h.group_id AND q.org_id = h.org_id')
            ->groupBy('h.instrument_id, h.score_scale, q.question_id, q.question, q.sort_order')->orderBy('q.sort_order')->get()->getResultArray();
        $groups = [];
        foreach ($responses as $row) {
            $key = $row['instrument_id'] . '/' . $row['score_scale'];
            if (!isset($groups[$key])) $groups[$key] = [
                'name' => $row['instrument_name'], 'scale' => (int) $row['score_scale'], 'count' => 0, 'scores' => [], 'questions' => [],
            ];
            $groups[$key]['count']++;
            $groups[$key]['scores'][] = (float) $row['average'];
        }
        foreach ($questions as $row) {
            $key = $row['instrument_id'] . '/' . $row['score_scale'];
            if (isset($groups[$key])) $groups[$key]['questions'][] = $row;
        }
        foreach ($groups as &$item) {
            $item['average'] = self::mean($item['scores']);
            unset($item['scores']);
        }
        unset($item);
        $services = array_fill_keys(['IGD', 'RI', 'RJ'], ['count' => 0, 'scores' => []]);
        $monthly = [];
        $month = (new \DateTimeImmutable($filters['start']))->modify('first day of this month');
        $last = (new \DateTimeImmutable($filters['end']))->modify('first day of this month');
        while ($month <= $last) {
            $monthly[$month->format('Y-m')] = array_fill_keys(array_keys($services), []);
            $month = $month->modify('+1 month');
        }
        foreach ($responses as $row) {
            $service = $row['service_type'];
            if (!isset($services[$service])) continue;
            $services[$service]['count']++;
            // Compare only SKM responses using the current four-point scale.
            if ((int) $row['score_scale'] !== 4) continue;
            $services[$service]['scores'][] = (float) $row['average'];
            $monthly[substr($row['submitted_at'], 0, 7)][$service][] = (float) $row['average'];
        }
        foreach ($services as &$service) {
            $service['average'] = self::mean($service['scores']);
            $service['scoredCount'] = count($service['scores']);
            unset($service['scores']);
        }
        unset($service);
        $monthly = array_map(static fn($rows) => array_map([self::class, 'mean'], $rows), $monthly);
        $profiles = $this->scoped($header, $group, $org, $filters)
            ->select('h.service_type, h.clinic, h.respondent_gender, h.respondent_age, h.occupation, h.education, h.insurance, h.ward_class')
            ->join('dt01_pe_instrument_ms i', 'i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id')->get()->getResultArray();
        $demographics = [];
        $definitions = \Modules\Pe\Config\InpatientRespondent::OPTIONS;
        foreach (['respondent_gender', 'respondent_age', 'occupation', 'education', 'insurance', 'ward_class'] as $field) {
            if ($field === 'ward_class' && in_array($filters['service'], ['RJ', 'IGD'], true)) continue;
            $labels = $field === 'respondent_age' ? array_fill_keys(['0–17 tahun', '18–25 tahun', '26–35 tahun', '36–45 tahun', '46–55 tahun', '56–65 tahun', '>65 tahun'], 0)
                : array_fill_keys(array_values($definitions[$field]['choices']), 0);
            $labels['Belum tercatat'] = 0;
            $total = 0;
            foreach ($profiles as $profile) {
                // Class demographics apply only to Rawat Inap.
                if ($field === 'ward_class' && $profile['service_type'] !== 'RI') continue;
                $value = $profile[$field];
                if ($field === 'respondent_age') {
                    $age = $value === null || $value === '' ? null : (int) $value;
                    $label = $age === null ? 'Belum tercatat' : ($age <= 17 ? '0–17 tahun' : ($age <= 25 ? '18–25 tahun' : ($age <= 35 ? '26–35 tahun' : ($age <= 45 ? '36–45 tahun' : ($age <= 55 ? '46–55 tahun' : ($age <= 65 ? '56–65 tahun' : '>65 tahun'))))));
                } else {
                    $label = $definitions[$field]['choices'][$value ?? ''] ?? 'Belum tercatat';
                }
                $labels[$label]++;
                $total++;
            }
            $demographics[$field] = ['label' => $field === 'respondent_age' ? 'Kelompok usia' : ($field === 'ward_class' ? 'Kelas layanan · Rawat Inap' : $definitions[$field]['label']), 'total' => $total, 'counts' => $labels];
        }
        $clinics = [];
        $outpatientCount = 0;
        foreach ($profiles as $profile) {
            if ($profile['service_type'] !== 'RJ') continue;
            $outpatientCount++;
            $label = trim((string) ($profile['clinic'] ?? '')) ?: 'Belum tercatat';
            if (!isset($clinics[$label])) $clinics[$label] = ['name' => $label, 'count' => 0];
            $clinics[$label]['count']++;
        }
        uasort($clinics, static fn($a, $b) => ($b['count'] <=> $a['count']) ?: strcmp($a['name'], $b['name']));
        return ['count' => count($responses), 'groups' => array_values($groups), 'services' => $services, 'monthly' => $monthly,
            'distribution' => $distribution, 'answerCount' => array_sum($distribution),
            'questionDistributions' => $this->skmQuestionDistributions($group, $org, $filters),
            'profileCount' => count($profiles), 'demographics' => $demographics, 'clinics' => array_values($clinics), 'outpatientCount' => $outpatientCount];
    }
    private function skmQuestionDistributions(string $group, string $org, array $filters): array
    {
        $rows = $this->scoped('dt01_pe_skm_response_hd', $group, $org, $filters)
            ->select('q.question, q.answer_options, q.sort_order, r.score, COUNT(*) AS answer_count')
            ->join('dt01_pe_skm_response_dt r', 'r.response_id=h.response_id AND r.group_id=h.group_id AND r.org_id=h.org_id')
            ->join('dt01_pe_question_ms q', 'q.question_id=r.question_id AND q.instrument_id=h.instrument_id AND q.group_id=h.group_id AND q.org_id=h.org_id')
            ->where('h.score_scale', 4)->whereIn('r.score', [1,2,3,4])
            ->groupBy('q.question, q.answer_options, q.sort_order, r.score')->orderBy('q.sort_order')->orderBy('q.question')->get()->getResultArray();
        $groups = [];
        foreach ($rows as $row) {
            $options = \Modules\Pe\Config\SkmQuestionnaire::options($row);
            $key = hash('sha256', $row['question'] . json_encode($options));
            if (!isset($groups[$key])) $groups[$key] = ['question'=>$row['question'], 'options'=>$options, 'counts'=>array_fill_keys(array_keys($options),0), 'total'=>0];
            $score = (int) $row['score'];
            if (!isset($options[$score])) continue;
            $groups[$key]['counts'][$score] += (int) $row['answer_count'];
            $groups[$key]['total'] += (int) $row['answer_count'];
        }
        return array_values($groups);
    }

    private function feedbackAnalytics(string $group, string $org, array $filters): array
    {
        $comments = [];
        $sources = [];
        foreach (['SKM' => 'skm', 'PREM' => 'prem'] as $label => $prefix) {
            $rows = $this->scoped('dt01_pe_' . $prefix . '_response_hd', $group, $org, $filters)
                ->select('h.suggestions')
                ->join('dt01_pe_instrument_ms i', 'i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id')
                ->where('h.suggestions IS NOT NULL', null, false)->get()->getResultArray();
            $values = array_values(array_filter(array_column($rows, 'suggestions'), static fn($text) => trim($text) !== '' && preg_match('/\p{L}/u', $text)));
            $sources[$label] = count($values);
            $comments = array_merge($comments, $values);
        }
        return \Modules\Pe\Libraries\FeedbackAnalysis::summarize($comments) + ['sources' => $sources];
    }

    public static function mean(array $values): ?float
    {
        return $values ? array_sum($values) / count($values) : null;
    }
}