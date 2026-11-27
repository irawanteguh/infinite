<?php

namespace Modules\Ca\Models;

use CodeIgniter\Model;
use Modules\Ca\Database\CreateScreeningTransactions;

class DashboardModel extends Model
{
    public function years(string $group, string $org): array
    {
        $rows = $this->db->table(CreateScreeningTransactions::HEADER)
            ->select('SUBSTR(screened_at, 1, 4) AS year', false)
            ->where('group_id', $group)->where('org_id', $org)->where('active', '1')
            ->distinct()->get()->getResultArray();
        return array_column($rows, 'year');
    }

    public function instruments(string $group, string $org): array
    {
        return $this->db->table('dt01_ca_instrument_ms')->where('group_id', $group)->where('org_id', $org)
            ->orderBy('code')->get()->getResultArray();
    }

    private function scoped(string $group, string $org, array $filters)
    {
        $builder = $this->db->table(CreateScreeningTransactions::HEADER . ' h')
            ->join('dt01_ca_instrument_ms i', 'i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id')
            ->where('h.group_id', $group)->where('h.org_id', $org)->where('h.active', '1')
            ->where('h.screened_at >=', $filters['start'] . ' 00:00:00')
            ->where('h.screened_at <', (new \DateTimeImmutable($filters['end']))->modify('+1 day')->format('Y-m-d') . ' 00:00:00');
        if ($filters['instrument'] !== '') $builder->where('i.instrument_id', $filters['instrument']);
        if ($filters['domicile'] === 'UNKNOWN') {
            $builder->groupStart()->where('h.domicile', null)->orWhere('h.domicile', '')->groupEnd();
        } elseif ($filters['domicile'] !== '') {
            $builder->where('h.domicile', $filters['domicile']);
        }
        return $builder;
    }

    private function riskExpression(string $group, string $org): string
    {
        $cases = [];
        foreach ($this->instruments($group, $org) as $instrument) {
            $ranges = empty($instrument['risk_ranges'])
                ? [] : json_decode($instrument['risk_ranges'], true, 512, JSON_THROW_ON_ERROR);
            if (!$ranges) {
                continue;
            }
            $categories = [];
            foreach ($ranges as [$min, $max, $label]) {
                $condition = 'h.total_score <= ' . (int) $max;
                if ($min !== null) {
                    $condition .= ' AND h.total_score >= ' . (int) $min;
                }
                $categories[] = 'WHEN ' . $condition . ' THEN ' . $this->db->escape($label);
            }
            $cases[] = 'WHEN i.instrument_id = ' . $this->db->escape($instrument['instrument_id'])
                . ' THEN CASE ' . implode(' ', $categories) . ' ELSE NULL END';
        }

        return $cases ? 'CASE ' . implode(' ', $cases) . ' ELSE h.risk_level END' : 'h.risk_level';
    }

    public function analytics(string $group, string $org, array $filters, int $page): array
    {
        $summaries = $this->scoped($group, $org, $filters)
            ->select('i.code, i.name, COUNT(*) AS total, COUNT(h.total_score) AS scored, AVG(h.total_score) AS average', false)
            ->groupBy(['i.instrument_id', 'i.code', 'i.name'])->get()->getResultArray();
        $total = array_sum(array_column($summaries, 'total'));
        $scored = array_sum(array_column($summaries, 'scored'));
        $riskExpression = $this->riskExpression($group, $org);
        $risks = $this->scoped($group, $org, $filters)->select('i.code, ' . $riskExpression . ' AS risk_level, COUNT(*) AS total', false)
            ->groupBy('i.code')->groupBy($riskExpression, false)->get()->getResultArray();
        $classified = 0;
        foreach ($risks as $risk) {
            if ($risk['risk_level'] !== null && $risk['risk_level'] !== '') $classified += (int) $risk['total'];
        }
        $monthlyInstruments = array_filter($this->instruments($group, $org),
            static fn (array $instrument): bool => $filters['instrument'] === ''
                || (string) $instrument['instrument_id'] === $filters['instrument']);
        $instrumentNames = array_column($monthlyInstruments, 'name', 'code');
        $emptyCounts = array_fill_keys(array_keys($instrumentNames), 0);
        $monthly = [];
        $cursor = (new \DateTimeImmutable($filters['start']))->modify('first day of this month');
        $last = substr($filters['end'], 0, 7);
        while ($cursor->format('Y-m') <= $last) {
            $monthly[$cursor->format('Y-m')] = $emptyCounts;
            $cursor = $cursor->modify('+1 month');
        }
        $rows = $this->scoped($group, $org, $filters)->select("SUBSTR(h.screened_at, 1, 7) AS month, i.code, COUNT(*) AS total", false)
            ->groupBy(['month', 'i.code'])->get()->getResultArray();
        foreach ($rows as $row) {
            if (isset($monthly[$row['month']][$row['code']])) $monthly[$row['month']][$row['code']] = (int) $row['total'];
        }
        $domiciles = $this->scoped($group, $org, $filters)->select('h.domicile, COUNT(*) AS total', false)
            ->groupBy('h.domicile')->get()->getResultArray();
        $page = min($page, max(1, (int) ceil($total / 20)));
        $details = $this->scoped($group, $org, $filters)
            ->select('h.response_id, h.screened_at, h.respondent_name, h.domicile, h.total_score, ' . $riskExpression . ' AS risk_level, i.code, i.name AS instrument_name', false)
            ->orderBy('h.screened_at', 'DESC')->orderBy('h.response_id', 'DESC')->limit(20, ($page - 1) * 20)->get()->getResultArray();
        return compact('total', 'scored', 'classified', 'summaries', 'risks', 'monthly', 'instrumentNames', 'domiciles', 'page', 'details');
    }
}
