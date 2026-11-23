<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class DataeklaimModel extends Model
{
    protected $DBGroup = null;

    public function periode()
    {
        return array_map(static fn($year) => (object) ['periode' => $year], range((int) date('Y'), 2014));
    }

    protected function scoped($koders, int $year, string $service = '', int $month = 0)
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month ?: 1);
        $endDate = $month ? date('Y-m-d', strtotime($startDate . ' +1 month')) : sprintf('%04d-01-01', $year + 1);
        $builder = $this->db->table('dt01_bpjs_ur_dt a')
            ->where('a.kode_rs', $koders)
            ->where('a.discharge_date >=', $startDate)
            ->where('a.discharge_date <', $endDate);
        if ($service !== '') $builder->where('a.ptd', $service);
        return $builder;
    }

    public function page($koders, int $year, int $start, int $length, string $term, bool $refreshSummary = false, string $service = '', int $month = 0)
    {
        $cache = service('cache');
        $key = 'eklaim_summary_' . hash('sha256', $koders . ':' . $year . ':' . $service . ':' . $month);
        $summary = $refreshSummary ? null : $cache->get($key);
        if (!is_array($summary)) {
            $summary = $this->scoped($koders, $year, $service, $month)
                ->select('COUNT(*) AS episodes, COALESCE(SUM(a.tarif_rs), 0) AS tarif_rs, COALESCE(SUM(a.total_tarif), 0) AS total_tarif, COALESCE(SUM(a.idrg_total_tarif), 0) AS idrg_total_tarif', false)
                ->get()->getRowArray();
            $cache->save($key, $summary, 30);
        }
        $builder = $this->scoped($koders, $year, $service, $month);
        if ($term !== '') {
            $builder->groupStart();
            foreach (['sep', 'nokartu', 'mrn', 'nama_pasien', 'dpjp', 'diaglist', 'proclist'] as $index => $column) {
                if ($index === 0) $builder->like('a.' . $column, $term);
                else $builder->orLike('a.' . $column, $term);
            }
            $builder->orLike("DATE_FORMAT(a.admission_date, '%d.%m.%Y')", $term)
                ->orLike("DATE_FORMAT(a.discharge_date, '%d.%m.%Y')", $term)->groupEnd();
        }
        $filtered = $term === '' ? (int) $summary['episodes'] : (clone $builder)->countAllResults();
        $rows = $builder->select('a.sep, a.nokartu, a.mrn, a.los, a.nama_pasien, a.ptd, a.dpjp, a.diaglist, a.proclist, a.tarif_rs, a.total_tarif, a.idrg_total_tarif')
            ->select("DATE_FORMAT(a.admission_date, '%d.%m.%Y') AS admission_date, DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date", false)
            ->orderBy('a.discharge_date', 'DESC')->orderBy('a.sep', 'ASC')->orderBy('a.mrn', 'ASC')
            ->limit($length, $start)->get()->getResultArray();
        return ['recordsTotal' => (int) $summary['episodes'], 'recordsFiltered' => $filtered, 'data' => $rows, 'summary' => $summary];
    }
}
