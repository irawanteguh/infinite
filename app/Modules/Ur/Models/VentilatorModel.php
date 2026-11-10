<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class VentilatorModel extends Model
{
    protected $DBGroup = 'default';

    public function periode()
    {
        return array_map(static function ($year) {
            return (object) ['periode' => $year];
        }, range((int) date('Y'), 2014));
    }

    public function summary($koders, $year)
    {
        $diagnosis = '(^|;)[[:space:]]*(J96([.][0-9A-Z]+)?|Z99[.]1)([[:space:]]*;|[[:space:]]*$)';
        $procedure = '(^|;)[[:space:]]*96[.]7[012]([[:space:]]*;|[[:space:]]*$)';
        $sql = "SELECT month, dpjp, ptd, COUNT(*) AS all_episodes,
                       SUM(has_diagnosis OR has_procedure) AS episodes,
                       SUM(has_diagnosis) AS diagnosis_episodes,
                       SUM(has_procedure) AS procedure_episodes,
                       SUM(has_diagnosis AND has_procedure) AS matched_episodes,
                       SUM(proc_9670) AS proc_9670, SUM(proc_9671) AS proc_9671, SUM(proc_9672) AS proc_9672,
                       SUM(diag_j96) AS diag_j96, SUM(diag_z991) AS diag_z991,
                       SUM(CASE WHEN has_diagnosis OR has_procedure THEN COALESCE(los, 0) ELSE 0 END) AS total_los,
                       SUM(CASE WHEN has_diagnosis OR has_procedure THEN COALESCE(tarif_rs, 0) ELSE 0 END) AS tarif_rs,
                       SUM(CASE WHEN has_diagnosis OR has_procedure THEN COALESCE(total_tarif, 0) ELSE 0 END) AS total_tarif,
                       SUM(CASE WHEN has_diagnosis OR has_procedure THEN COALESCE(idrg_total_tarif, 0) ELSE 0 END) AS idrg_total_tarif,
                       MIN(CASE WHEN has_diagnosis OR has_procedure THEN discharge_date END) AS first_discharge,
                       MAX(CASE WHEN has_diagnosis OR has_procedure THEN discharge_date END) AS last_discharge
                FROM (
                    SELECT MONTH(discharge_date) AS month,
                           COALESCE(NULLIF(UPPER(TRIM(dpjp)), ''), 'Belum terisi') AS dpjp,
                           ptd, los, tarif_rs, total_tarif, idrg_total_tarif, discharge_date,
                           diaglist REGEXP ? AS has_diagnosis,
                           proclist REGEXP ? AS has_procedure,
                           proclist REGEXP '(^|;)[[:space:]]*96[.]70([[:space:]]*;|[[:space:]]*$)' AS proc_9670,
                           proclist REGEXP '(^|;)[[:space:]]*96[.]71([[:space:]]*;|[[:space:]]*$)' AS proc_9671,
                           proclist REGEXP '(^|;)[[:space:]]*96[.]72([[:space:]]*;|[[:space:]]*$)' AS proc_9672,
                           diaglist REGEXP '(^|;)[[:space:]]*J96([.][0-9A-Z]+)?([[:space:]]*;|[[:space:]]*$)' AS diag_j96,
                           diaglist REGEXP '(^|;)[[:space:]]*Z99[.]1([[:space:]]*;|[[:space:]]*$)' AS diag_z991
                    FROM dt01_bpjs_ur_dt
                    WHERE kode_rs = ? AND discharge_date >= ? AND discharge_date < ?
                ) scoped
                GROUP BY month, dpjp, ptd
                ORDER BY month, dpjp, ptd";

        return $this->db->query($sql, [
            $diagnosis, $procedure, $koders,
            sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1),
        ])->getResult();
    }
}
