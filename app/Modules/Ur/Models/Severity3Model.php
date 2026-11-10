<?php
namespace Modules\Ur\Models;
use CodeIgniter\Model;

class Severity3Model extends Model
{
    protected $DBGroup = 'default';
    public function periode()
    {
        return array_map(static function ($year) { return (object) ['periode' => $year]; }, range((int) date('Y'), 2014));
    }

    public function summary($koders, $year)
    {
        $sql = "SELECT MONTH(discharge_date) AS month,
                       COALESCE(NULLIF(UPPER(TRIM(dpjp)), ''), 'Belum terisi') AS dpjp,
                       COUNT(*) AS all_episodes,
                       SUM(CASE WHEN los < 4 THEN 1 ELSE 0 END) AS episodes,
                       SUM(CASE WHEN los < 4 THEN los ELSE 0 END) AS total_los,
                       MIN(CASE WHEN los < 4 THEN los ELSE NULL END) AS min_los,
                       SUM(CASE WHEN los < 4 AND los <= 1 THEN 1 ELSE 0 END) AS los_up_to_1,
                       SUM(CASE WHEN los < 4 AND los > 1 THEN 1 ELSE 0 END) AS los_1_to_4,
                       SUM(CASE WHEN los < 4 THEN tarif_rs ELSE 0 END) AS tarif_rs,
                       SUM(CASE WHEN los < 4 THEN total_tarif ELSE 0 END) AS total_tarif,
                       SUM(CASE WHEN los < 4 THEN idrg_total_tarif ELSE 0 END) AS idrg_total_tarif,
                       MIN(discharge_date) AS first_discharge, MAX(discharge_date) AS last_discharge
                FROM dt01_bpjs_ur_dt
                WHERE kode_rs = ? AND discharge_date >= ? AND discharge_date < ? AND ptd = 1 AND sl = 'III'
                GROUP BY MONTH(discharge_date), COALESCE(NULLIF(UPPER(TRIM(dpjp)), ''), 'Belum terisi')
                ORDER BY MONTH(discharge_date), dpjp";
        return $this->db->query($sql, [$koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1)])->getResult();
    }

    function rawdata($koders,$periode){
        $query = "
                    select a.ptd, los, mrn, nama_pasien, sep, nokartu, dpjp, diaglist, proclist, tarif_rs, total_tarif, idrg_total_tarif,
                           DATE_FORMAT(admission_date, '%d.%m.%Y') AS admission_date,
                           DATE_FORMAT(discharge_date, '%d.%m.%Y') AS discharge_date
                    from dt01_bpjs_ur_dt a
                    where a.los < 4
                    and   a.sl='III'
                    and   a.ptd='1'
                    and   a.kode_rs = ?
                    and   a.discharge_date >= ? AND a.discharge_date < ?
                    order by admission_date asc
                ";

        $recordset = $this->db->query($query, [$koders, sprintf('%04d-01-01', $periode), sprintf('%04d-01-01', $periode + 1)]);
        return $recordset->getResult();
    }
    
}
