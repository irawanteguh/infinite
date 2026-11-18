<?php
namespace Modules\Ur\Models;
use CodeIgniter\Model;

class TumorModel extends Model
{
    protected $DBGroup = null;

    public function periode()
    {
        return array_map(static fn($year) => (object) ['periode' => $year], range((int) date('Y'), 2014));
    }

    public function summary($koders, $year)
    {
        return $this->rawdata($koders, $year);
    }

    public function rawdata($koders, $year)
    {
        $params = [$koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1)];
        $condition = $this->caseCondition($params);
        return $this->db->query("SELECT a.ptd, a.los, a.mrn, a.nama_pasien, a.sep, a.nokartu, a.dpjp,
            a.diaglist, a.proclist, a.tarif_rs, a.total_tarif, a.idrg_total_tarif, a.sl,
            a.icu_los, a.vent_hour, MONTH(a.discharge_date) AS month,
            DATE_FORMAT(a.admission_date, '%d.%m.%Y') AS admission_date,
            DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date
            FROM dt01_bpjs_ur_dt a
            WHERE a.kode_rs = ? AND a.discharge_date >= ? AND a.discharge_date < ?
              AND ($condition)
            ORDER BY a.discharge_date DESC, a.sep", $params)->getResult();
    }

    private function caseCondition(array &$params): string
    {
        $rules = json_decode(file_get_contents(APPPATH . 'Modules/Ur/Config/tumor.json'), true, 512, JSON_THROW_ON_ERROR);
        $conditions = [];
        foreach ($rules as $rule) {
            $diagnoses = array_filter(array_map('trim', explode(';', $rule['diagnoses'])));
            $procedures = array_filter(array_map('trim', explode(';', $rule['procedures'])));
            if (!$diagnoses || !$procedures) continue;
            $conditions[] = "(TRIM(SUBSTRING_INDEX(a.diaglist, ';', 1)) REGEXP ? AND a.proclist REGEXP ?)";
            $params[] = '^(' . implode('|', array_map(static fn($code) => preg_quote($code, '/'), $diagnoses)) . ')';
            $params[] = '(^|;)[[:space:]]*(' . implode('|', array_map(static fn($code) => preg_quote($code, '/'), $procedures)) . ')([[:space:]]*;|[[:space:]]*$)';
        }
        return $conditions ? implode(' OR ', $conditions) : '0 = 1';
    }
}