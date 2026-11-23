<?php

namespace Modules\Ur\Models;

use Modules\Ur\Services\EpisodeReview;

class InternalReviewModel extends DataeklaimModel
{
    public function queue(string $hospital, string $provider, array $filters): array
    {
        if ($filters['category'] === 'topup') {
            return $this->pendingTopupQueue($hospital, $provider, $filters);
        }

        $review = new EpisodeReview();
        $audit = (new AuditkodingModel())->reviewEvaluator();
        $topup = (new TopupModel())->reviewEvaluator();
        $builder = $this->scoped($hospital, $filters['year'], $filters['service'], $filters['month'])
            ->select('a.sep, a.mrn, a.nama_pasien, a.dpjp, a.admission_date, a.discharge_date, a.ptd, a.diaglist, a.proclist, a.inacbg,
                a.vent_hour, a.los, a.sl, a.icu_indikator, a.icu_los, a.tarif_rs, a.total_tarif, a.idrg_total_tarif, a.in_sp, a.in_sr, a.in_si, a.in_sd, a.c2')
            ->orderBy('a.sep', 'ASC')->orderBy('a.discharge_date', 'DESC');
        $builder->where('a.sep !=', '');
        if ($filters['category'] === 'klaim') {
            if ($provider === '') return ['rows' => [], 'next' => '', 'history' => []];
            $providerScope = $this->db->escape($provider);
            $builder->where("EXISTS (SELECT 1 FROM dt01_bpjs_pending_klaim p WHERE p.KODEPPK = $providerScope AND p.NOSEP = a.sep)", null, false);
        }
        if ($filters['category'] === 'data') {
            $builder->groupStart()
                ->where("TRIM(COALESCE(a.diaglist, '')) = ''", null, false)
                ->orWhere("TRIM(COALESCE(a.proclist, '')) = ''", null, false)
                ->groupEnd();
        }
        $scanLimit = 50;
        $rows = [];
        $cursor = $filters['after'];
        $seen = [];
        $next = '';
        $shownHistory = [];
        $usesClaimFindings = in_array($filters['category'], ['', 'klaim'], true);
        $startedAt = microtime(true);
        do {
            $batch = clone $builder;
            if ($cursor !== '') $batch->where('a.sep >', $cursor);
            $candidates = $batch->get($scanLimit + 1)->getResultArray();
            $next = '';
            $batchHistory = $usesClaimFindings ? $this->history($provider, $candidates) : [];
            foreach ($candidates as $index => $row) {
                if ($index === $scanLimit || count($rows) === 25) {
                    $next = $cursor;
                    break;
                }

                $cursor = (string) $row['sep'];
                $sep = trim($cursor);
                if ($sep === '' || isset($seen[$sep])) continue;
                $seen[$sep] = true;

                $baseFindings = $review->findings($row);
                if (in_array($filters['category'], ['data', 'klinis'], true)
                    && !in_array($filters['category'], array_column($baseFindings, 'category'), true)) continue;
                $claimHistory = $batchHistory[$sep] ?? [];
                $claimFindings = $review->claimFindings($claimHistory);
                if ($filters['category'] === 'klaim' && !$claimFindings) continue;
                $findings = array_merge($baseFindings, $audit($row), $topup($row), $claimFindings);
                if (!$findings) continue;
                if ($filters['category'] !== '' && !in_array($filters['category'], array_column($findings, 'category'), true)) continue;

                $row['sep'] = $sep;
                $row['findings'] = $findings;
                $rows[] = $row;
                if ($claimHistory) $shownHistory[$sep] = $claimHistory;
            }
        } while ($filters['category'] === 'klaim' && !$rows && $next !== '' && microtime(true) - $startedAt < 2);

        $history = $usesClaimFindings ? $shownHistory : $this->history($provider, $rows);
        return ['rows' => $rows, 'next' => $next, 'history' => $history];
    }

    public function trend(string $hospital, string $provider, int $year, string $service): array
    {
        $cache = service('cache');
        $ruleVersion = '';
        foreach (['topup', 'auditkoding', 'ppk1', 'tumor'] as $catalog) {
            $ruleVersion .= hash_file('sha256', APPPATH . 'Modules/Ur/Config/' . $catalog . '.json');
        }
        $cacheKey = 'review_trend_v3_' . hash('sha256', serialize([$hospital, $provider, $year, $service, $ruleVersion]));
        $cached = $cache->get($cacheKey);
        if (is_array($cached)) return $cached;

        $review = new EpisodeReview();
        $audit = (new AuditkodingModel())->reviewEvaluator();
        $topup = (new TopupModel())->reviewEvaluator(true);
        $builder = $this->scoped($hospital, $year, $service)
            ->select('a.sep, MONTH(a.discharge_date) AS discharge_month, a.ptd, a.diaglist, a.proclist, a.inacbg,
                a.vent_hour, a.los, a.sl, a.icu_indikator, a.in_sp, a.in_sr, a.in_si, a.in_sd')
            ->select("SUBSTRING_INDEX(a.c2, '#', -1) AS c2", false)
            ->where('a.sep !=', '')
            ->orderBy('a.sep', 'ASC')->orderBy('a.discharge_date', 'DESC');
        if ($provider !== '') {
            // NOSEP is unique in BAHV. Keep the PPK condition in the join so UR-only episodes also count.
            $providerScope = $this->db->escape($provider);
            $builder->join('dt01_bpjs_pending_klaim p', "p.NOSEP = a.sep AND p.KODEPPK = $providerScope", 'left', false)
                ->select('p.NOSEP AS claim_sep, p.STATUS AS status, p.ALGORITMA AS reason');
        }

        $months = [];
        $seen = [];
        $previousMode = $this->db->resultMode;
        $result = null;
        try {
            $this->db->resultMode = MYSQLI_USE_RESULT;
            $result = $builder->get();
            while ($row = $result->getUnbufferedRow('array')) {
                $sep = trim((string) $row['sep']);
                if ($sep === '' || isset($seen[$sep])) continue;
                $seen[$sep] = true;

                $month = (int) $row['discharge_month'];
                $months[$month]['episodes'] = ($months[$month]['episodes'] ?? 0) + 1;
                $claim = ($row['claim_sep'] ?? null) === $sep ? $review->claimFindings([$row]) : [];
                $findings = array_merge($review->findings($row), $audit($row), $topup($row), $claim);
                foreach (array_unique(array_column($findings, 'category')) as $category) {
                    $months[$month][$category] = ($months[$month][$category] ?? 0) + 1;
                }
            }
        } finally {
            if ($result !== null) $result->freeResult();
            $this->db->resultMode = $previousMode;
        }

        $cache->save($cacheKey, $months, 60);
        return $months;
    }

    private function pendingTopupQueue(string $hospital, string $provider, array $filters): array
    {
        $cache = service('cache');
        $cacheKey = 'review_topup_v1_' . hash('sha256', $hospital . ':' . $filters['year'] . ':'
            . hash_file('sha256', APPPATH . 'Modules/Ur/Config/topup.json'));
        $episodes = $cache->get($cacheKey);
        if (!is_array($episodes)) {
            $episodes = (new TopupModel())->cases($hospital, $filters['year'], true);
            $cache->save($cacheKey, $episodes, 30);
        }
        $bySep = [];
        foreach ($episodes as $episode) {
            if ($filters['month'] && (int) $episode['month'] !== $filters['month']) continue;
            if ($filters['service'] !== '' && (string) $episode['ptd'] !== $filters['service']) continue;
            $sep = trim((string) $episode['sep']);
            if ($sep === '') continue;
            $episode['sep'] = $sep;
            $episode['discharge_date'] = implode('-', array_reverse(explode('.', $episode['discharge_date'])));
            if (!isset($bySep[$sep]) || $episode['discharge_date'] > $bySep[$sep]['discharge_date']) {
                $bySep[$sep] = $episode;
            }
        }
        ksort($bySep, SORT_STRING);

        $rows = [];
        $next = '';
        foreach ($bySep as $row) {
            if (strcmp($row['sep'], $filters['after']) <= 0) continue;
            if (count($rows) === 25) {
                $next = $rows[24]['sep'];
                break;
            }
            $rows[] = $row;
        }

        return [
            'rows' => $rows, 'next' => $next, 'total' => count($bySep),
            'history' => $this->history($provider, $rows),
        ];
    }

    private function history(string $provider, array $rows): array
    {
        if ($provider === '' || !$rows) return [];

        $history = [];
        $results = $this->db->table('dt01_bpjs_pending_klaim')
            ->select('NOSEP AS sep, STATUS AS status, ALGORITMA AS reason, KETERANGAN_VERIFIKASI AS verification_note, TGLVERIF AS verified_at')
            ->where('KODEPPK', $provider)->whereIn('NOSEP', array_values(array_unique(array_column($rows, 'sep'))))
            ->orderBy('TGLVERIF', 'DESC')->get()->getResultArray();
        foreach ($results as $entry) {
            $history[$entry['sep']][] = $entry;
        }
        return $history;
    }
}
