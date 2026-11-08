<?php

namespace Modules\Qi\Models;

use CodeIgniter\Model;

class SummaryreportModel extends Model
{
    protected $DBGroup = 'default';

    public function periodeHeader($groupId, $orgId, $periodeId): ?object
    {
        $row = $this->db->table('dt01_qi_periode_mutu p')
            ->select('p.*, p.tahun AS tahun, u.name AS pic_name, u.email AS pic_email, s.master_name AS status_name, s.description AS status_description')
            ->join('dt01_gen_user_data u', 'u.user_id = p.user_id', 'left')
            ->join('dt01_gen_master_ms s', 's.code = p.status_id AND s.active = "1"', 'left')
            ->where(['p.periode_id' => $periodeId, 'p.group_id' => $groupId, 'p.org_id' => $orgId, 'p.active' => '1'])
            ->get()->getRow();
        if ($row) {
            $row->jumlah_tim = (int) $this->db->table('dt01_qi_komite_mutu_hd')
                ->where(['periode_id' => $periodeId, 'active' => '1'])->countAllResults();
        }
        return $row;
    }

    public function listPeriodePelaporan($groupId, $orgId)
    {
        return $this->db->table('dt01_qi_periode_mutu p')
            ->select('p.periode_id, p.tahun, p.user_id, p.status_id, p.last_update_by, p.last_update_date, u1.name AS pic, u2.name AS dibuatoleh, s.master_name AS status, s.color AS statuscolor, s.icon AS statusicon, s.description AS statusdescription, (SELECT GROUP_CONCAT(DISTINCT CONCAT(t.user_id, ":", COALESCE(ut.name, "")) SEPARATOR ";") FROM dt01_qi_komite_mutu_hd t LEFT JOIN dt01_gen_user_data ut ON ut.user_id = t.user_id WHERE t.periode_id = p.periode_id AND t.active = "1") AS komite')
            ->join('dt01_gen_user_data u1', 'u1.user_id = p.user_id', 'left')
            ->join('dt01_gen_user_data u2', 'u2.user_id = p.last_update_by', 'left')
            ->join('dt01_gen_master_ms s', 's.code = p.status_id AND s.active = "1"', 'left')
            ->where(['p.group_id' => $groupId, 'p.org_id' => $orgId, 'p.active' => '1'])
            ->orderBy('p.tahun', 'DESC')->get()->getResult();
    }

    public function summaryRowsByPeriode($groupId, $orgId, $periodeId)
    {
        $sql = "SELECT a.transaksi_id, d.department, i.indikator_code, i.indikator,
            COALESCE(j.jenis, 'Lainnya') AS jenis,
            CASE WHEN LOWER(COALESCE(j.jenis, '')) LIKE '%nasional%' THEN 'Nasional Mutu' ELSE 'Departemen' END AS kategori,
            a.target, i.target_capaian, CONCAT(COALESCE(s.satuan, ''), ' ', COALESCE(s.keterangan, '')) AS satuan,
            COUNT(DISTINCT CASE WHEN it.bulan IS NOT NULL THEN it.bulan END) AS jumlah_bulan,
            COUNT(DISTINCT CASE WHEN it.bulan IS NOT NULL THEN CONCAT(it.referensi_id, '-', it.bulan) END) AS jumlah_penilaian,
            SUM(COALESCE(it.numerator, 0)) AS total_numerator, SUM(COALESCE(it.denumerator, 0)) AS total_denumerator,
            ROUND(AVG(CASE WHEN it.bulan IS NULL THEN NULL WHEN COALESCE(it.numerator,0)=0 AND COALESCE(it.denumerator,0)=0 THEN 100 WHEN COALESCE(it.denumerator,0)=0 THEN 0 ELSE (it.numerator/it.denumerator)*100 END), 2) AS rata_pencapaian
            FROM dt01_qi_indikator_hd a
            INNER JOIN dt01_qi_periode_mutu p ON p.periode_id=a.periode_id AND p.active='1' AND p.periode_id=?
            LEFT JOIN dt01_gen_department_ms d ON d.department_id=a.department_id
            LEFT JOIN dt01_qi_indikator_ms i ON i.indikator_id=a.indikator_id
            LEFT JOIN dt01_qi_jenis_indikator_ms j ON j.jenis_indikator_id=i.jenis_indikator_id
            LEFT JOIN dt01_qi_satuan_ms s ON s.satuan_id=i.satuan_id
            LEFT JOIN dt01_qi_indikator_it it ON it.referensi_id=a.transaksi_id AND it.group_id=a.group_id AND it.org_id=a.org_id AND it.bulan IS NOT NULL
            WHERE a.active='1' AND a.group_id=? AND a.org_id=?
            GROUP BY a.transaksi_id, d.department, i.indikator_code, i.indikator, j.jenis, a.target, i.target_capaian, s.satuan, s.keterangan
            ORDER BY kategori, d.department, i.indikator_code, i.indikator";
        return $this->db->query($sql, [$periodeId, $groupId, $orgId])->getResult();
    }

    public function monthlyTrendByPeriode($groupId, $orgId, $periodeId)
    {
        $sql = "SELECT it.bulan,
            ROUND(AVG(CASE WHEN COALESCE(it.numerator,0)=0 AND COALESCE(it.denumerator,0)=0 THEN 100 WHEN COALESCE(it.denumerator,0)=0 THEN 0 ELSE (it.numerator/it.denumerator)*100 END),2) AS rata_pencapaian,
            COUNT(DISTINCT CONCAT(a.transaksi_id,'-',it.bulan)) AS jumlah_penilaian
            FROM dt01_qi_indikator_it it
            INNER JOIN dt01_qi_indikator_hd a ON a.transaksi_id=it.referensi_id AND a.group_id=it.group_id AND a.org_id=it.org_id AND a.active='1'
            INNER JOIN dt01_qi_periode_mutu p ON p.periode_id=a.periode_id AND p.active='1' AND p.periode_id=?
            WHERE a.group_id=? AND a.org_id=? AND it.bulan IS NOT NULL
            GROUP BY it.bulan ORDER BY CAST(it.bulan AS UNSIGNED)";
        return $this->db->query($sql, [$periodeId, $groupId, $orgId])->getResult();
    }

    public function monthlyTrendByIndicator($groupId, $orgId, $periodeId)
    {
        $sql = "SELECT a.transaksi_id, it.bulan,
            ROUND(AVG(CASE WHEN COALESCE(it.numerator,0)=0 AND COALESCE(it.denumerator,0)=0 THEN 100 WHEN COALESCE(it.denumerator,0)=0 THEN 0 ELSE (it.numerator/it.denumerator)*100 END),2) AS rata_pencapaian,
            COUNT(*) AS jumlah_penilaian
            FROM dt01_qi_indikator_it it
            INNER JOIN dt01_qi_indikator_hd a ON a.transaksi_id=it.referensi_id AND a.group_id=it.group_id AND a.org_id=it.org_id AND a.active='1'
            INNER JOIN dt01_qi_periode_mutu p ON p.periode_id=a.periode_id AND p.active='1' AND p.periode_id=?
            WHERE a.group_id=? AND a.org_id=? AND it.bulan IS NOT NULL
            GROUP BY a.transaksi_id, it.bulan
            ORDER BY a.transaksi_id, CAST(it.bulan AS UNSIGNED)";
        return $this->db->query($sql, [$periodeId, $groupId, $orgId])->getResult();
    }

    public function fishboneAnalysesForPeriode($groupId, $orgId, $periodeId)
    {
        return $this->db->table('dt01_qi_fishbone_hd x')
            ->select('x.fishbone_id, x.group_id, x.org_id, x.department_id, x.periode_id, x.problem, x.created_date, x.indikator_ref_id, d.department, p.tahun, i.indikator_code, i.indikator')
            ->join('dt01_gen_department_ms d', 'd.department_id=x.department_id', 'left')
            ->join('dt01_qi_periode_mutu p', 'p.periode_id=x.periode_id', 'left')
            ->join('dt01_qi_indikator_hd ih', 'ih.transaksi_id=x.indikator_ref_id AND ih.group_id=x.group_id AND ih.org_id=x.org_id', 'left')
            ->join('dt01_qi_indikator_ms i', 'i.indikator_id=ih.indikator_id', 'left')
            ->where(['x.group_id' => $groupId, 'x.org_id' => $orgId, 'x.periode_id' => $periodeId, 'x.active' => '1'])
            ->orderBy('x.created_date', 'DESC')->get()->getResult();
    }

    public function fishboneCausesForAnalysis($fishboneId)
    {
        return $this->db->table('dt01_qi_fishbone_dt')->where(['fishbone_id' => $fishboneId, 'active' => '1'])
            ->orderBy('category')->orderBy('priority', 'ASC')->orderBy('created_date')->get()->getResult();
    }

    public function pdsaCyclesForPeriode($groupId, $orgId, $periodeId)
    {
        return $this->db->table('dt01_qi_pdsa x')
            ->select('x.pdsa_id, x.group_id, x.org_id, x.department_id, x.periode_id, x.indikator_ref_id, x.title, x.problem, x.aim, x.cycle_no, x.status, x.created_date, x.plan, x.do_result, x.study, x.act, d.department, p.tahun, i.indikator_code, i.indikator')
            ->join('dt01_gen_department_ms d', 'd.department_id=x.department_id', 'left')
            ->join('dt01_qi_periode_mutu p', 'p.periode_id=x.periode_id', 'left')
            ->join('dt01_qi_indikator_hd ih', 'ih.transaksi_id=x.indikator_ref_id AND ih.group_id=x.group_id AND ih.org_id=x.org_id', 'left')
            ->join('dt01_qi_indikator_ms i', 'i.indikator_id=ih.indikator_id', 'left')
            ->where(['x.group_id' => $groupId, 'x.org_id' => $orgId, 'x.periode_id' => $periodeId, 'x.active' => '1'])
            ->orderBy('x.created_date', 'DESC')->get()->getResult();
    }

    public function pdsaItemsForCycle($pdsaId)
    {
        return $this->db->table('dt01_qi_pdsa_items')->where(['pdsa_id' => $pdsaId, 'active' => '1'])
            ->orderBy('phase')->orderBy('sort_order')->orderBy('created_date')->get()->getResult();
    }

    public function measurementDetails($groupId, $orgId, $periodeId)
    {
        $sql = "SELECT d.department, i.indikator_code, i.indikator, it.bulan, it.numerator, it.denumerator, it.reason, it.rtl,
            CASE WHEN COALESCE(it.numerator,0)=0 AND COALESCE(it.denumerator,0)=0 THEN 100 WHEN COALESCE(it.denumerator,0)=0 THEN 0 ELSE ROUND((it.numerator/it.denumerator)*100,2) END AS capaian
            FROM dt01_qi_indikator_it it
            INNER JOIN dt01_qi_indikator_hd a ON a.transaksi_id=it.referensi_id AND a.group_id=it.group_id AND a.org_id=it.org_id AND a.active='1'
            INNER JOIN dt01_qi_periode_mutu p ON p.periode_id=a.periode_id AND p.active='1' AND p.periode_id=?
            LEFT JOIN dt01_gen_department_ms d ON d.department_id=a.department_id
            LEFT JOIN dt01_qi_indikator_ms i ON i.indikator_id=a.indikator_id
            WHERE a.group_id=? AND a.org_id=? AND it.bulan IS NOT NULL
            ORDER BY d.department,i.indikator_code,CAST(it.bulan AS UNSIGNED)";
        return $this->db->query($sql, [$periodeId, $groupId, $orgId])->getResult();
    }

    public function periodeExists($periodeId, $groupId, $orgId): bool
    {
        return $this->db->table('dt01_qi_periode_mutu')->where(['periode_id' => $periodeId, 'group_id' => $groupId, 'org_id' => $orgId, 'active' => '1'])->countAllResults() > 0;
    }
}
