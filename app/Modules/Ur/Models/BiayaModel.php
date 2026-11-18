<?php
namespace Modules\Ur\Models;
use CodeIgniter\Model;

class BiayaModel extends Model
{
    protected $DBGroup = null;

    public function periode()
    {
        return array_map(static fn($year) => (object) ['periode' => $year], range((int) date('Y'), 2014));
    }

    public function summary($koders, $year)
    {
        return $this->db->query("SELECT MONTH(discharge_date) AS month, ptd, dpjp, sl,
            COUNT(*) AS episodes, SUM(los) AS los, SUM(tarif_rs) AS tarif_rs,
            SUM(total_tarif) AS total_tarif, SUM(idrg_total_tarif) AS idrg_total_tarif
            FROM dt01_bpjs_ur_dt
            WHERE kode_rs = ? AND discharge_date >= ? AND discharge_date < ? AND ptd IN (1,2)
            GROUP BY MONTH(discharge_date), ptd, dpjp, sl", [
                $koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year+1),
            ])->getResult();
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
        return 'a.ptd IN (1, 2)';
    }
    public function components($koders, $year)
    {
        $fields = ['tarif_rs','prosedur_non_bedah','prosedur_bedah','konsultasi','tenaga_ahli',
            'keperawatan','penunjang','radiologi','laboratorium','pelayanan_darah','rehabilitasi',
            'kamar_akomodasi','rawat_intensif','obat','alkes','bmhp','sewa_alat','obat_kronis','obat_kemo'];
        $field = request()->getPost('field') ?? 'tarif_rs';
        $group = request()->getPost('group') ?? 'inacbg';
        $groups = ['inacbg'=>'inacbg', 'diagnosis'=>"TRIM(SUBSTRING_INDEX(diaglist, ';', 1))",
            'procedure_code'=>"TRIM(SUBSTRING_INDEX(proclist, ';', 1))"];
        if (!is_string($field) || !in_array($field, $fields, true) || !is_string($group) || !isset($groups[$group])) {
            throw new \InvalidArgumentException('Invalid cost component filter');
        }
        $fields = [$field];
        $expression = $groups[$group];
        $select = [];
        foreach ($fields as $field) {
            foreach (['MIN'=>'min','MAX'=>'max','AVG'=>'avg','STDDEV_POP'=>'stdev'] as $function => $suffix) {
                $select[] = "$function($field) AS ${field}_$suffix";
            }
        }
        return $this->db->query("SELECT MONTH(discharge_date) AS month, ptd, $expression AS $group,
            COUNT(*) AS episodes, AVG(los) AS alos, " . implode(', ', $select) . "
            FROM dt01_bpjs_ur_dt WHERE kode_rs = ? AND discharge_date >= ? AND discharge_date < ?
            GROUP BY MONTH(discharge_date), ptd, $expression", [
                $koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1),
            ])->getResult();
    }}