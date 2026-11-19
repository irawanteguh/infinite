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

    public function cases($koders, $year)
    {
        $rules = json_decode(file_get_contents(APPPATH . 'Modules/Ur/Config/' . $this->ruleSet . '.json'), true, 512, JSON_THROW_ON_ERROR);
        $compiled = [];
        $separator = $this->ruleSet === 'topup' ? ',' : ';';
        foreach ($rules as $index => $rule) {
            if (trim($rule['primary'] ?? '') === '' && trim($rule['procedure'] ?? '') === '') continue;
            $cbgs = array_filter(array_map('trim', explode(',', $rule['cbg'] ?? '')));
            $compiled[$index] = [
                'primary' => $this->pattern($rule['primary'] ?? '', $separator, true),
                'secondary' => $this->pattern($rule['secondary'] ?? '', $separator),
                'procedure' => $this->pattern($rule['procedure'] ?? '', $separator),
                'procedure2' => $this->pattern($rule['procedure2'] ?? '', $separator),
                'cbg' => $cbgs ? '(' . implode('|', array_map(static fn($code) => preg_quote($code, '/'), $cbgs)) . ')' : '',
                'care' => (string) ($rule['care'] ?? ''),
            ];
        }
        if (!$compiled) return [];
        $primaryPatterns = array_column($compiled, 'primary');
        $procedurePatterns = array_column($compiled, 'procedure');
        $candidateSql = '';
        $candidateParams = [];
        if (!in_array('', $primaryPatterns, true)) {
            $candidateSql = " AND TRIM(SUBSTRING_INDEX(a.diaglist, ';', 1)) REGEXP ?";
            $candidateParams[] = implode('|', array_unique($primaryPatterns));
        } elseif (!in_array('', $procedurePatterns, true)) {
            $candidateSql = ' AND a.proclist REGEXP ?';
            $candidateParams[] = implode('|', array_unique($procedurePatterns));
        }
        $extraData = $this->ruleSet === 'topup' ? 'a.c2' : "NULL AS c2";
        $query = "SELECT a.ptd, a.sep, a.mrn, a.nama_pasien, a.dpjp, a.los, a.diaglist, a.proclist,
            a.total_tarif, a.tarif_rs, a.idrg_total_tarif, a.sl, a.inacbg,
            a.in_sp, a.in_sr, a.in_si, a.in_sd, a.tarif_sp, a.tarif_sr, a.tarif_si, a.tarif_sd, $extraData,
            DATE_FORMAT(a.admission_date, '%d.%m.%Y') admission_date,
            DATE_FORMAT(a.discharge_date, '%d.%m.%Y') discharge_date,
            MONTH(a.discharge_date) month
            FROM dt01_bpjs_ur_dt a
            WHERE a.kode_rs = ? AND a.discharge_date >= ? AND a.discharge_date < ?
            $candidateSql";
        $params = [
            $koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1),
        ];
        $params = array_merge($params, $candidateParams);
        $rateSamples = [];
        $profiles = [];
        $episodes = [];
        foreach ($this->scopedRows($query, $params) as $row) {
            $profile = hash('sha256', json_encode([$row['ptd'],$row['diaglist'],$row['proclist'],$row['inacbg']]));
            if (!isset($profiles[$profile])) {
                $diagnoses = explode(';', (string) $row['diaglist'], 2);
                $values = [
                    'primary' => trim($diagnoses[0]),
                    'secondary' => $diagnoses[1] ?? '',
                    'procedure' => (string) $row['proclist'],
                    'procedure2' => (string) $row['proclist'],
                    'cbg' => (string) $row['inacbg'],
                ];
                $matches = [];
                foreach ($compiled as $index => $patterns) {
                    if ($patterns['care'] !== '' && (float) $patterns['care'] !== (float) $row['ptd']) continue;
                    foreach ($values as $field => $value) {
                        if ($patterns[$field] !== '' && preg_match('/' . $patterns[$field] . '/iu', $value) !== 1) continue 2;
                    }
                    $matches[] = $index;
                }
                $profiles[$profile] = $matches;
            }
            if (!$profiles[$profile]) continue;
            $key = hash('sha256', json_encode([$row['sep'],$row['mrn'],$row['admission_date'],$row['discharge_date'],$row['diaglist'],$row['proclist']]));
            $notes = [];
            $pending = [];
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
                    if ($status === 'Sudah di entry' && !$special && isset($row[$tariffField]) && (float) $row[$tariffField] > 0) {
                        $rateSamples[$rateKey][] = (float) $row[$tariffField];
                    } elseif ($status === 'Belum di entry') {
                        $pending[$field ?: (string) $index] = $special ? null : $rateKey;
                    }
                }
                $notes[] = $note;
            }
            if (!isset($episodes[$key])) {
                unset($row['c2'], $row['in_sp'], $row['in_sr'], $row['in_si'], $row['in_sd']);
                $row['audit_notes'] = [];
                $row['topup_actual'] = $actual;
                $row['topup_pending_keys'] = $pending;
                $episodes[$key] = $row;
            }
            $episodes[$key]['audit_notes'] = array_merge($episodes[$key]['audit_notes'], $notes);
        }

        foreach ($episodes as &$episode) {
            $episode['audit_notes'] = implode("\n\n", array_unique($episode['audit_notes']));
            $episode['topup_potential'] = 0;
            $episode['topup_unpriced'] = 0;
            foreach ($episode['topup_pending_keys'] as $rateKey) {
                if ($rateKey !== null && !empty($rateSamples[$rateKey])) {
                    $episode['topup_potential'] += array_sum($rateSamples[$rateKey]) / count($rateSamples[$rateKey]);
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