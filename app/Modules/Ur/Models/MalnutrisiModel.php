<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class MalnutrisiModel extends Model
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
        $token = "(^|;)[[:space:]]*E(40|41|42|43|44[.]0|44[.]1|45|46)([[:space:]]*;|[[:space:]]*$)";
        $primary = "^[[:space:]]*E(40|41|42|43|44[.]0|44[.]1|45|46)([[:space:]]*;|[[:space:]]*$)";
        $sql = "SELECT MONTH(discharge_date) AS month,
                       COALESCE(NULLIF(UPPER(TRIM(dpjp)), ''), 'Belum terisi') AS dpjp,
                       ptd, COUNT(*) AS all_episodes,
                       SUM(diaglist REGEXP ?) AS episodes,
                       SUM(diaglist REGEXP ?) AS primary_episodes,
                       SUM(diaglist REGEXP '(^|;)[[:space:]]*E(40|41|42|43)([[:space:]]*;|[[:space:]]*$)') AS severe,
                       SUM(diaglist REGEXP '(^|;)[[:space:]]*E44[.][01]([[:space:]]*;|[[:space:]]*$)') AS moderate_mild,
                       SUM(diaglist REGEXP '(^|;)[[:space:]]*E(45|46)([[:space:]]*;|[[:space:]]*$)') AS other_unspecified,
                       SUM(CASE WHEN diaglist REGEXP ? THEN COALESCE(los, 0) ELSE 0 END) AS total_los,
                       SUM(CASE WHEN diaglist REGEXP ? THEN COALESCE(tarif_rs, 0) ELSE 0 END) AS tarif_rs,
                       SUM(CASE WHEN diaglist REGEXP ? THEN COALESCE(total_tarif, 0) ELSE 0 END) AS total_tarif,
                       SUM(CASE WHEN diaglist REGEXP ? THEN COALESCE(idrg_total_tarif, 0) ELSE 0 END) AS idrg_total_tarif,
                       MIN(CASE WHEN diaglist REGEXP ? THEN discharge_date END) AS first_discharge,
                       MAX(CASE WHEN diaglist REGEXP ? THEN discharge_date END) AS last_discharge
                FROM dt01_bpjs_ur_dt
                WHERE kode_rs = ? AND discharge_date >= ? AND discharge_date < ?
                GROUP BY MONTH(discharge_date), COALESCE(NULLIF(UPPER(TRIM(dpjp)), ''), 'Belum terisi'), ptd
                ORDER BY MONTH(discharge_date), dpjp, ptd";

        return $this->db->query($sql, [
            $token, $primary, $token, $token, $token, $token, $token, $token,
            $koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1),
        ])->getResult();
    }
}
