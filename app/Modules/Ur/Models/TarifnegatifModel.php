<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class TarifnegatifModel extends Model
{
    protected $DBGroup = null;

    public function periode()
    {
        return array_map(static function ($year) { return (object) ['periode' => $year]; }, range((int) date('Y'), 2014));
    }

    public function summary($koders, $year)
    {
        $sql = "SELECT MONTH(discharge_date) AS month, ptd,
                       COALESCE(NULLIF(UPPER(TRIM(dpjp)), ''), 'Belum terisi') AS dpjp,
                       COUNT(*) AS all_episodes,
                       SUM(CASE WHEN total_tarif < tarif_rs THEN 1 ELSE 0 END) AS negative_episodes,
                       SUM(CASE WHEN total_tarif < tarif_rs THEN tarif_rs ELSE 0 END) AS tarif_rs,
                       SUM(CASE WHEN total_tarif < tarif_rs THEN total_tarif ELSE 0 END) AS total_tarif,
                       SUM(CASE WHEN total_tarif < tarif_rs THEN idrg_total_tarif ELSE 0 END) AS idrg_total_tarif,
                       SUM(CASE WHEN total_tarif < tarif_rs THEN total_tarif - tarif_rs ELSE 0 END) AS negative_gap,
                       MIN(discharge_date) AS first_discharge, MAX(discharge_date) AS last_discharge
                FROM dt01_bpjs_ur_dt
                WHERE kode_rs = ? AND discharge_date >= ? AND discharge_date < ?
                GROUP BY MONTH(discharge_date), ptd, COALESCE(NULLIF(UPPER(TRIM(dpjp)), ''), 'Belum terisi')
                ORDER BY MONTH(discharge_date), ptd, dpjp";
        return $this->db->query($sql, [$koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1)])->getResult();
    }

    private function filters($koders, $year, $month, $service, $search)
    {
        $start = $month ? sprintf('%04d-%02d-01', $year, $month) : sprintf('%04d-01-01', $year);
        $end = $month ? date('Y-m-d', strtotime($start . ' +1 month')) : sprintf('%04d-01-01', $year + 1);
        $where = 'a.kode_rs = ? AND a.discharge_date >= ? AND a.discharge_date < ? AND a.total_tarif < a.tarif_rs';
        $params = [$koders, $start, $end];
        if ($service !== '') {
            $where .= ' AND a.ptd = ?';
            $params[] = $service;
        }
        if ($search !== '') {
            $where .= " AND (a.sep LIKE ? ESCAPE '!' OR a.mrn LIKE ? ESCAPE '!' OR a.nama_pasien LIKE ? ESCAPE '!' OR a.dpjp LIKE ? ESCAPE '!')";
            $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
            $params = array_merge($params, [$term, $term, $term, $term]);
        }
        return [$where, $params];
    }

    public function cases($koders, $year, $month, $service, $search, $offset, $limit, $sort)
    {
        [$where, $params] = $this->filters($koders, $year, $month, $service, $search);
        $total = (int) $this->db->query('SELECT COUNT(*) AS total FROM dt01_bpjs_ur_dt a WHERE ' . $where, $params)->getRow()->total;
        $orders = ['gap' => '(a.total_tarif - a.tarif_rs) ASC', 'date' => 'a.discharge_date DESC', 'tariff' => 'a.total_tarif DESC'];
        $order = $orders[$sort] ?? $orders['gap'];
        $offset = $total ? min($offset, (int) (floor(($total - 1) / $limit) * $limit)) : 0;
        $sql = "SELECT a.ptd, a.mrn, a.nama_pasien, a.sep, a.nokartu, a.dpjp, a.diaglist, a.proclist,
                       a.tarif_rs, a.total_tarif, a.idrg_total_tarif,
                       (a.total_tarif - a.tarif_rs) AS negative_gap,
                       DATE_FORMAT(a.admission_date, '%d.%m.%Y') AS admission_date,
                       DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date
                FROM dt01_bpjs_ur_dt a WHERE " . $where . ' ORDER BY ' . $order . ', a.sep ASC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        return ['rows' => $this->db->query($sql, $params)->getResult(), 'total' => $total, 'offset' => $offset, 'limit' => $limit];
    }

    // Retain the legacy endpoint contract for existing callers.
    public function rawdata($koders, $year)
    {
        [$where, $params] = $this->filters($koders, $year, 0, '', '');
        return $this->db->query("SELECT a.ptd, a.mrn, a.nama_pasien, a.sep, a.nokartu, a.dpjp, a.diaglist, a.proclist,
            LPAD(MONTH(a.discharge_date), 2, '0') AS periode, ABS(a.total_tarif - a.tarif_rs) AS selisih,
            a.tarif_rs, a.total_tarif, a.idrg_total_tarif,
            DATE_FORMAT(a.admission_date, '%d.%m.%Y') AS admission_date,
            DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date
            FROM dt01_bpjs_ur_dt a WHERE " . $where . ' ORDER BY a.admission_date ASC', $params)->getResult();
    }
}
