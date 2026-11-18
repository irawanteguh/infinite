<?php
namespace Modules\Ur\Models;
use CodeIgniter\Model;

class DiagnosaModel extends Model
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
        $rules = json_decode(file_get_contents(APPPATH . 'Modules/Ur/Config/ppk1.json'), true, 512, JSON_THROW_ON_ERROR);
        $codes = [];
        foreach ($rules as $rule) {
            foreach (explode(';', $rule['diagnoses']) as $code) {
                if (trim($code) !== '') $codes[trim($code)] = true;
            }
        }
        $params[] = '^(' . implode('|', array_map(static fn($code) => preg_quote($code, '/'), array_keys($codes))) . ')$';
        return "TRIM(SUBSTRING_INDEX(a.diaglist, ';', 1)) REGEXP ?";
    }
}