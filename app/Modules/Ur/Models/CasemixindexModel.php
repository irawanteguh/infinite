<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class CasemixindexModel extends Model
{
    protected $DBGroup = null;
    private $aggregateCache = [];

    public function periode()
    {
        return array_map(static function ($year) {
            return (object) ['periode' => $year];
        }, range((int) date('Y'), 2014));
    }

    public function aggregate($koders, $periode)
    {
        $cacheKey = $koders . ':' . $periode;
        if (isset($this->aggregateCache[$cacheKey])) {
            return $this->aggregateCache[$cacheKey];
        }
        $query = "SELECT MONTH(a.discharge_date) AS month,
                         a.ptd, a.cg, a.cmg, a.sl,
                         COUNT(*) AS episodes,
                         SUM(a.total_tarif) AS total_tarif,
                         SUM(a.tarif_rs) AS tarif_rs,
                         SUM(CASE WHEN a.ptd = 1 THEN a.los ELSE 0 END) AS total_los,
                         SUM(CASE WHEN a.ptd = 1 AND a.los IS NOT NULL THEN 1 ELSE 0 END) AS los_cases,
                         SUM(CASE WHEN a.ptd = 1 AND a.idrg_cost_weight > 0 THEN a.idrg_cost_weight ELSE 0 END) AS weight_sum,
                         SUM(CASE WHEN a.ptd = 1 AND a.idrg_cost_weight > 0 THEN 1 ELSE 0 END) AS weight_cases,
                         MIN(a.discharge_date) AS first_discharge,
                         MAX(a.discharge_date) AS last_discharge
                  FROM dt01_bpjs_ur_dt a
                  WHERE a.kode_rs = ?
                    AND a.discharge_date >= ? AND a.discharge_date < ?
                  GROUP BY MONTH(a.discharge_date), a.ptd, a.cg, a.cmg, a.sl
                  ORDER BY MONTH(a.discharge_date), a.ptd, a.cg, a.cmg, a.sl";
        return $this->aggregateCache[$cacheKey] = $this->db->query($query, [
            $koders, sprintf('%04d-01-01', $periode), sprintf('%04d-01-01', $periode + 1)
        ])->getResult();
    }

    public function summary($koders, $periode)
    {
        return [
            'rows' => $this->aggregate($koders, $periode),
            'casegroups' => $this->db->query('SELECT id_cg, name_case_group, notes FROM dt01_inacbg_casegroup_ms ORDER BY CAST(id_cg AS UNSIGNED)')->getResult(),
            'cmgs' => $this->db->query('SELECT group_code, group_name, description FROM dt01_inacbg_cmg_ms ORDER BY group_code')->getResult(),
        ];
    }

    // Preserve the monthly response contracts of existing endpoints.
    private function monthly($koders, $periode, $field, $key, $master = [])
    {
        $months = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
        $result = [];
        foreach ($master as $item) {
            $item = (array) $item;
            $result[(string) $item[$key]] = array_merge($item, array_fill_keys($months, 0));
        }
        foreach ($this->aggregate($koders, $periode) as $row) {
            $code = (string) $row->$field;
            if (!isset($result[$code])) {
                $result[$code] = array_merge([$key => $code], array_fill_keys($months, 0));
                if ($field === 'cmg') {
                    $result[$code]['group_name'] = 'Belum terdaftar di master';
                    $result[$code]['description'] = '';
                } elseif ($field === 'cg') {
                    $result[$code]['name_case_group'] = 'Belum terdaftar di master';
                    $result[$code]['notes'] = '';
                }
            }
            $result[$code][$months[(int) $row->month - 1]] += (int) $row->episodes;
        }
        return array_map(static function ($item) { return (object) $item; }, array_values($result));
    }

    public function datakelompokkasus($koders, $periode)
    {
        $master = $this->db->query('SELECT id_cg, name_case_group, notes FROM dt01_inacbg_casegroup_ms ORDER BY CAST(id_cg AS UNSIGNED)')->getResult();
        return $this->monthly($koders, $periode, 'cg', 'id_cg', $master);
    }

    public function datacmg($koders, $periode)
    {
        $master = $this->db->query('SELECT group_code, group_name, description FROM dt01_inacbg_cmg_ms ORDER BY group_code')->getResult();
        return $this->monthly($koders, $periode, 'cmg', 'group_code', $master);
    }

    public function dataseveritylevel($koders, $periode)
    {
        return $this->monthly($koders, $periode, 'sl', 'sl');
    }
}
