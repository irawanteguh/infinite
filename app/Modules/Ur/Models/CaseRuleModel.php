<?php
namespace Modules\Ur\Models;

use CodeIgniter\Model;

class CaseRuleModel extends Model
{
    protected $ruleSet = '';

    public function periode()
    {
        return array_map(static fn($year) => (object) ['periode' => $year], range((int) date('Y'), 2014));
    }

    private function pattern($value, $separator, $primary = false)
    {
        $codes = array_filter(array_map('trim', explode($separator, (string) $value)));
        if (!$codes) return '';
        $alternatives = implode('|', array_map(static fn($code) => preg_quote($code, '/'), $codes));
        return $primary ? '^(' . $alternatives . ')([.]|$)' : '(^|;)[[:space:]]*(' . $alternatives . ')([[:space:]]*;|[[:space:]]*$)';
    }

    private function compileRules(): array
    {
        $rules = json_decode(file_get_contents(APPPATH . 'Modules/Ur/Config/' . $this->ruleSet . '.json'), true, 512, JSON_THROW_ON_ERROR);
        $compiled = [];
        $separator = $this->ruleSet === 'topup' ? ',' : ';';
        foreach ($rules as $index => $rule) {
            if (trim($rule['primary'] ?? '') === '' && trim($rule['procedure'] ?? '') === '') continue;
            $cbgs = array_filter(array_map('trim', explode(',', $rule['cbg'] ?? '')));
            $compiled[$index] = [
                'cbg' => $cbgs ? '(' . implode('|', array_map(static fn($code) => preg_quote($code, '/'), $cbgs)) . ')' : '',
                'primary' => $this->pattern($rule['primary'] ?? '', $separator, true),
                'secondary' => $this->pattern($rule['secondary'] ?? '', $separator),
                'procedure' => $this->pattern($rule['procedure'] ?? '', $separator),
                'procedure2' => $this->pattern($rule['procedure2'] ?? '', $separator),
                'care' => (string) ($rule['care'] ?? ''),
            ];
        }
        return [$rules, $compiled];
    }

    private function matchingRules(array $row, array $compiled): array
    {
        $diagnoses = explode(';', (string) $row['diaglist'], 2);
        $values = [
            'cbg' => (string) $row['inacbg'],
            'primary' => trim($diagnoses[0]),
            'secondary' => $diagnoses[1] ?? '',
            'procedure' => (string) $row['proclist'],
            'procedure2' => (string) $row['proclist'],
        ];
        $matches = [];
        foreach ($compiled as $index => $patterns) {
            if ($patterns['care'] !== '' && (float) $patterns['care'] !== (float) $row['ptd']) continue;
            foreach ($values as $field => $value) {
                if ($patterns[$field] !== '' && preg_match('/' . $patterns[$field] . '/iu', $value) !== 1) continue 2;
            }
            $matches[] = $index;
        }
        return $matches;
    }

    public function reviewEvaluator(bool $pendingOnly = false): callable
    {
        [$rules, $compiled] = $this->compileRules();

        $primaryGroups = [];
        $unrestricted = [];
        $separator = $this->ruleSet === 'topup' ? ',' : ';';
        foreach ($compiled as $index => $rule) {
            $primaryCodes = array_filter(array_map('trim', explode($separator, $rules[$index]['primary'] ?? '')));
            if (!$primaryCodes) {
                $unrestricted[$index] = $rule;
            }
            foreach ($primaryCodes as $code) {
                $root = strtoupper(explode('.', $code, 2)[0]);
                if (preg_match('/[^\x00-\x7f]/', $root)) {
                    $unrestricted[$index] = $rule;
                } else {
                    $primaryGroups[$root][$index] = $rule;
                }
            }
        }

        $profiles = [];
        return function (array $row) use ($rules, $compiled, $primaryGroups, $unrestricted, $pendingOnly, &$profiles): array {
            $care = (string) $row['ptd'];
            $cbg = (string) $row['inacbg'];
            $primary = trim(explode(';', (string) $row['diaglist'], 2)[0]);
            $root = strtoupper(explode('.', $primary, 2)[0]);
            // Preserve the regex matcher's Unicode case folding for historical non-ASCII codes.
            $candidates = preg_match('/[^\x00-\x7f]/', $root)
                ? $compiled : (($primaryGroups[$root] ?? []) + $unrestricted);
            if (!$candidates) return [];

            $profile = hash('sha256', serialize([$care, $cbg, $row['diaglist'], $row['proclist']]));
            if (!isset($profiles[$profile])) {
                if (count($profiles) >= 10000) $profiles = [];
                $matches = $this->matchingRules($row, $candidates);
                sort($matches);
                $profiles[$profile] = $matches;
            }
            $findings = [];
            foreach ($profiles[$profile] as $index) {
                $rule = $rules[$index];
                $note = $rule['recommendation'] ?? '';
                if ($this->ruleSet === 'topup') {
                    $status = $this->entryStatus($row, $rule);
                    if ($status === 'Sudah di entry' || ($pendingOnly && $status !== 'Belum di entry')) continue;
                    $note = trim($status . '. ' . $note);
                }
                $findings[] = [
                    'category' => $this->ruleSet === 'topup' ? 'topup' : 'koding',
                    'title' => $rule['description'] ?? '',
                    'note' => $note,
                ];
            }
            return $findings;
        };
    }

    private function candidatePredicate(array $compiled): array
    {
        $primaryPatterns = array_column($compiled, 'primary');
        $procedurePatterns = array_column($compiled, 'procedure');
        $cbgPatterns = array_column($compiled, 'cbg');
        $candidateSql = '';
        $candidateParams = [];
        if (!in_array('', $cbgPatterns, true)) {
            // Retain substring matching from the PHP rule, including legacy CBG values.
            $candidateSql = 'UPPER(a.inacbg) REGEXP ?';
            $candidateParams[] = strtoupper(implode('|', array_unique($cbgPatterns)));
            // Rules with a primary diagnosis must match it. Procedure-only rules
            // contribute their procedure patterns to the other candidate branch.
            $primaryCandidates = array_filter($primaryPatterns);
            $procedureCandidates = array_column(array_filter(
                $compiled, static fn($rule) => $rule['primary'] === ''
            ), 'procedure');
            $candidateBranches = [];
            if ($primaryCandidates) {
                $candidateBranches[] = "UPPER(TRIM(SUBSTRING_INDEX(a.diaglist, ';', 1))) REGEXP ?";
                $candidateParams[] = implode('|', array_unique($primaryCandidates));
            }
            if ($procedureCandidates) {
                $candidateBranches[] = 'UPPER(a.proclist) REGEXP ?';
                $candidateParams[] = implode('|', array_unique($procedureCandidates));
            }
            $candidateSql .= ' AND (' . implode(' OR ', $candidateBranches) . ')';
        } elseif (!in_array('', $primaryPatterns, true)) {
            $candidateSql = "TRIM(SUBSTRING_INDEX(a.diaglist, ';', 1)) REGEXP ?";
            $candidateParams[] = implode('|', array_unique($primaryPatterns));
        } elseif (!in_array('', $procedurePatterns, true)) {
            $candidateSql = 'a.proclist REGEXP ?';
            $candidateParams[] = implode('|', array_unique($procedurePatterns));
        }
        return [$candidateSql, $candidateParams];
    }

    public function cases($koders, $year, bool $pendingOnly = false, bool $summaryOnly = false)
    {
        [$rules, $compiled] = $this->compileRules();
        if (!$compiled) return [];
        [$predicate, $candidateParams] = $this->candidatePredicate($compiled);
        $candidateSql = $predicate === '' ? '' : ' AND (' . $predicate . ')';
        $extraData = $this->ruleSet === 'topup' ? 'a.c2' : "NULL AS c2";
        $reviewFields = $pendingOnly ? ', a.vent_hour, a.icu_indikator, a.icu_los' : '';
        $query = "SELECT a.ptd, a.sep, a.mrn, a.nama_pasien, a.dpjp, a.los, a.diaglist, a.proclist,
            a.total_tarif, a.tarif_rs, a.idrg_total_tarif, a.sl, a.inacbg,
            a.in_sp, a.in_sr, a.in_si, a.in_sd, a.tarif_sp, a.tarif_sr, a.tarif_si, a.tarif_sd, $extraData,
            DATE_FORMAT(a.admission_date, '%d.%m.%Y') admission_date,
            DATE_FORMAT(a.discharge_date, '%d.%m.%Y') discharge_date,
            MONTH(a.discharge_date) month $reviewFields
            FROM dt01_bpjs_ur_dt a
            WHERE a.kode_rs = ? AND a.discharge_date >= ? AND a.discharge_date < ?
            $candidateSql";
        $params = [
            $koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1),
        ];
        $params = array_merge($params, $candidateParams);
        $rateSamples = [];
        $profiles = [];
        $cbgRules = [];
        $episodes = [];
        foreach ($this->scopedRows($query, $params) as $row) {
            $profile = hash('sha256', json_encode([$row['ptd'],$row['diaglist'],$row['proclist'],$row['inacbg']]));
            if (!isset($profiles[$profile])) {
                $cbgKey = (string) $row['ptd'] . ':' . (string) $row['inacbg'];
                if (!isset($cbgRules[$cbgKey])) {
                    $cbgRules[$cbgKey] = array_filter($compiled, static function ($rule) use ($row) {
                        return ($rule['care'] === '' || (float) $rule['care'] === (float) $row['ptd'])
                            && ($rule['cbg'] === '' || preg_match('/' . $rule['cbg'] . '/iu', (string) $row['inacbg']) === 1);
                    });
                }
                $profiles[$profile] = $this->matchingRules($row, $cbgRules[$cbgKey]);
            }
            if (!$profiles[$profile]) continue;
            $key = hash('sha256', json_encode([$row['sep'],$row['mrn'],$row['admission_date'],$row['discharge_date'],$row['diaglist'],$row['proclist']]));
            $notes = [];
            $pending = [];
            $pendingFindings = [];
            $actual = $this->ruleSet === 'topup'
                ? (float) $row['tarif_sp'] + (float) $row['tarif_sr'] + (float) $row['tarif_si'] + (float) $row['tarif_sd'] : 0;
            foreach ($profiles[$profile] as $index) {
                $rule = $rules[$index];
                $note = trim(($rule['description'] ?? '') . ' — ' . ($rule['recommendation'] ?? ''));
                if ($this->ruleSet === 'topup') {
                    $status = $this->entryStatus($row, $rule);
                    $note .= ' [' . $status . ']';
                    $field = strtolower(trim($rule['status_field'] ?? ''));
                    $tariffField = str_replace('in_', 'tarif_', $field);
                    $rateKey = $index . ':' . $row['inacbg'] . ':' . $row['ptd'];
                    $special = ($rule['description'] ?? '') === 'Alteplase'
                        || strpos($rule['description'] ?? '', 'Kantong Darah') !== false;
                    if (!$pendingOnly && $status === 'Sudah di entry' && !$special && isset($row[$tariffField]) && (float) $row[$tariffField] > 0) {
                        $sample = $rateSamples[$rateKey] ?? ['sum' => 0.0, 'count' => 0];
                        $sample['sum'] += (float) $row[$tariffField];
                        $sample['count']++;
                        $rateSamples[$rateKey] = $sample;
                    } elseif ($status === 'Belum di entry') {
                        $pending[$field ?: (string) $index] = $special ? null : $rateKey;
                        if ($pendingOnly) {
                            $pendingFindings[] = [
                                'category' => 'topup', 'title' => $rule['description'],
                                'note' => 'Belum di entry. ' . ($rule['recommendation'] ?? ''),
                            ];
                        }
                    }
                }
                $notes[] = $note;
            }
            if (!isset($episodes[$key])) {
                unset($row['c2'], $row['in_sp'], $row['in_sr'], $row['in_si'], $row['in_sd']);
                $row['audit_notes'] = [];
                $row['topup_actual'] = $actual;
                $row['topup_pending_keys'] = $pending;
                if ($pendingOnly) $row['findings'] = $pendingFindings;
                if ($summaryOnly) {
                    unset($row['sep'], $row['mrn'], $row['nama_pasien'], $row['diaglist'], $row['proclist'],
                        $row['admission_date'], $row['discharge_date'], $row['inacbg']);
                }
                $episodes[$key] = $row;
            }
            if (!$summaryOnly) $episodes[$key]['audit_notes'] = array_merge($episodes[$key]['audit_notes'], $notes);
        }

        if ($pendingOnly) {
            return array_values(array_filter($episodes, static fn($episode) => $episode['topup_pending_keys'] !== []));
        }

        foreach ($episodes as &$episode) {
            $episode['audit_notes'] = implode("\n\n", array_unique($episode['audit_notes']));
            $episode['topup_potential'] = 0;
            $episode['topup_unpriced'] = 0;
            foreach ($episode['topup_pending_keys'] as $rateKey) {
                if ($rateKey !== null && !empty($rateSamples[$rateKey])) {
                    $episode['topup_potential'] += $rateSamples[$rateKey]['sum'] / $rateSamples[$rateKey]['count'];
                } else {
                    $episode['topup_unpriced']++;
                }
            }
            $episode['topup_pending_count'] = count($episode['topup_pending_keys']);
            unset($episode['topup_pending_keys']);
        }
        unset($episode);
        return array_values($episodes);
    }
    private function scopedRows(string $query, array $params): \Generator
    {
        $previousMode = $this->db->resultMode;
        $this->db->resultMode = MYSQLI_USE_RESULT;
        $result = null;
        try {
            $result = $this->db->query($query, $params);
            while ($row = $result->getUnbufferedRow('array')) {
                yield $row;
            }
        } finally {
            if ($result !== null) $result->freeResult();
            $this->db->resultMode = $previousMode;
        }
    }
    private function entryStatus(array $row, array $rule): string
    {
        $description = $rule['description'] ?? '';
        if ($description === 'Alteplase' || strpos($description, 'Kantong Darah') !== false) {
            $chunks = explode('#', $row['c2'] ?? '');
            $extra = json_decode(end($chunks), true);
            if (!is_array($extra)) return 'Data tambahan perlu diperiksa';
            $key = $description === 'Alteplase' ? 'alteplase_ind' : 'kantong_darah';
            return !empty($extra[$key]) ? 'Sudah di entry' : 'Belum di entry';
        }
        $fields = ['IN_SP'=>'in_sp','IN_SR'=>'in_sr','IN_SI'=>'in_si','IN_SD'=>'in_sd'];
        $entered = static fn($value) => $value !== null && !in_array(strtolower(trim((string) $value)), ['', 'none', '0'], true);
        if (in_array($description, ['Hip Implant','Knee Implant','Hip Replacement/Kne e Replacement'], true)) {
            return $entered($row['in_sp']) && $entered($row['in_sr']) ? 'Sudah di entry' : 'Belum di entry';
        }
        $field = $fields[strtoupper(trim($rule['status_field'] ?? ''))] ?? null;
        return $field ? ($entered($row[$field]) ? 'Sudah di entry' : 'Belum di entry') : 'Status perlu diperiksa';
    }

    public function caseSummary($koders, $year)
    {
        return $this->cases($koders, $year);
    }
}