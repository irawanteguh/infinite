<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class LosModel extends Model
{
    protected $DBGroup = null;

    public function periode()
    {
        return array_map(static function ($year) {
            return (object) ['periode' => $year];
        }, range((int) date('Y'), 2014));
    }

    public function summary($koders, $year)
    {
        $sql = "SELECT MONTH(discharge_date) AS month,
                       COALESCE(NULLIF(UPPER(TRIM(dpjp)), ''), 'Belum terisi') AS dpjp,
                       COUNT(*) AS all_episodes,
                       SUM(CASE WHEN los < 2 THEN 1 ELSE 0 END) AS episodes,
                       SUM(CASE WHEN los < 2 THEN los ELSE 0 END) AS total_los,
                       SUM(CASE WHEN los < 2 AND los = 0 THEN 1 ELSE 0 END) AS los_0,
                       SUM(CASE WHEN los < 2 AND los > 0 THEN 1 ELSE 0 END) AS los_1,
                       SUM(CASE WHEN los < 2 THEN tarif_rs ELSE 0 END) AS tarif_rs,
                       SUM(CASE WHEN los < 2 THEN total_tarif ELSE 0 END) AS total_tarif,
                       SUM(CASE WHEN los < 2 THEN idrg_total_tarif ELSE 0 END) AS idrg_total_tarif,
                       MIN(discharge_date) AS first_discharge,
                       MAX(discharge_date) AS last_discharge
                FROM dt01_bpjs_ur_dt
                WHERE kode_rs = ?
                  AND discharge_date >= ? AND discharge_date < ?
                  AND ptd = 1
                GROUP BY MONTH(discharge_date), COALESCE(NULLIF(UPPER(TRIM(dpjp)), ''), 'Belum terisi')
                ORDER BY MONTH(discharge_date), dpjp";

        return $this->db->query($sql, [
            $koders,
            sprintf('%04d-01-01', $year),
            sprintf('%04d-01-01', $year + 1),
        ])->getResult();
    }

    public function rawdata($koders, $periode)
    {
        $query = "SELECT a.ptd, a.los, a.mrn, a.nama_pasien, a.sep, a.nokartu,
                         a.dpjp, a.diaglist, a.proclist, a.tarif_rs,
                         a.total_tarif, a.idrg_total_tarif,
                         DATE_FORMAT(a.admission_date, '%d.%m.%Y') AS admission_date,
                         DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date
                  FROM dt01_bpjs_ur_dt a
                  WHERE a.los < 2 AND a.ptd = 1 AND a.kode_rs = ?
                    AND a.discharge_date >= ? AND a.discharge_date < ?
                  ORDER BY a.admission_date ASC";

        return $this->db->query($query, [
            $koders,
            sprintf('%04d-01-01', $periode),
            sprintf('%04d-01-01', $periode + 1),
        ])->getResult();
    }
}
