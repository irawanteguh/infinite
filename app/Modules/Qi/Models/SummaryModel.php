<?php

namespace Modules\Qi\Models;

use CodeIgniter\Model;

class SummaryModel extends Model
{
    protected $DBGroup = 'default';

    function periods($groupid,$orgid){
        $query =
                "
                    select a.periode_id, tahun
                    from dt01_qi_periode_mutu a
                    where a.active='1'
                    and   a.status_id in ('3','4')
                    and   a.group_id='".$groupid."'
                    and   a.org_id='".$orgid."'
                    order by tahun desc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    public function summaryRows($groupid, $orgid, $year)
    {
        $sql = "
            SELECT
                a.transaksi_id,
                a.indikator_id,
                a.department_id,
                d.department,
                i.indikator_code,
                i.indikator,
                i.jenis_indikator_id,
                COALESCE(j.jenis, 'Lainnya') AS jenis,
                CASE
                    WHEN LOWER(COALESCE(j.jenis, '')) LIKE '%nasional%' THEN 'Nasional Mutu'
                    ELSE 'Departemen'
                END AS kategori,
                a.target,
                i.target_capaian,
                i.dimensi_mutu_keselamatan,
                i.dimensi_mutu_waktu,
                i.dimensi_mutu_efektif,
                i.dimensi_mutu_efesien,
                i.dimensi_mutu_pasien,
                i.dimensi_mutu_integrasi,
                CONCAT(s.satuan, ' /', s.keterangan) AS satuan,
                COUNT(DISTINCT CASE WHEN it.bulan IS NOT NULL THEN it.bulan END) AS jumlah_bulan,
                COUNT(DISTINCT CASE WHEN it.bulan IS NOT NULL THEN CONCAT(it.referensi_id, '-', it.bulan) END) AS jumlah_penilaian,
                SUM(COALESCE(it.numerator, 0)) AS total_numerator,
                SUM(COALESCE(it.denumerator, 0)) AS total_denominator,
                ROUND(AVG(
                    CASE
                        WHEN it.bulan IS NULL THEN NULL
                        WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100
                        WHEN COALESCE(it.denumerator, 0) = 0 THEN 0
                        ELSE LEAST(100, (it.numerator / it.denumerator) * 100)
                    END
                ), 2) AS rata_pencapaian
            FROM dt01_qi_indikator_hd a
            INNER JOIN dt01_qi_periode_mutu p
                ON p.periode_id = a.periode_id
                AND p.active = '1'
                AND p.tahun = ?
            LEFT JOIN dt01_gen_department_ms d
                ON d.department_id = a.department_id
            LEFT JOIN dt01_qi_indikator_ms i
                ON i.indikator_id = a.indikator_id
            LEFT JOIN dt01_qi_jenis_indikator_ms j
                ON j.jenis_indikator_id = i.jenis_indikator_id
            LEFT JOIN dt01_qi_satuan_ms s
                ON s.satuan_id = i.satuan_id
            LEFT JOIN dt01_qi_indikator_it it
                ON it.referensi_id = a.transaksi_id
                AND it.group_id = a.group_id
                AND it.org_id = a.org_id
            WHERE a.active = '1'
                AND a.group_id = ?
                AND a.org_id = ?
            GROUP BY
                a.transaksi_id,
                a.indikator_id,
                a.department_id,
                d.department,
                i.indikator_code,
                i.indikator,
                i.jenis_indikator_id,
                j.jenis,
                a.target,
                i.target_capaian,
                i.dimensi_mutu_keselamatan,
                i.dimensi_mutu_waktu,
                i.dimensi_mutu_efektif,
                i.dimensi_mutu_efesien,
                i.dimensi_mutu_pasien,
                i.dimensi_mutu_integrasi,
                s.satuan,
                s.keterangan
            ORDER BY kategori, d.department, i.indikator_code, i.indikator
        ";

        return $this->db->query($sql, [$year, $groupid, $orgid])->getResult();
    }

    public function monthlyTrend($groupid, $orgid, $year)
    {
        $sql = "
            SELECT
                it.bulan,
                CASE
                    WHEN LOWER(COALESCE(j.jenis, '')) LIKE '%nasional%' THEN 'Nasional Mutu'
                    ELSE 'Departemen'
                END AS kategori,
                ROUND(AVG(
                    CASE
                        WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100
                        WHEN COALESCE(it.denumerator, 0) = 0 THEN 0
                        ELSE (it.numerator / it.denumerator) * 100
                    END
                ), 2) AS rata_pencapaian,
                COUNT(DISTINCT CONCAT(a.transaksi_id, '-', it.bulan)) AS jumlah_penilaian
            FROM dt01_qi_indikator_it it
            INNER JOIN dt01_qi_indikator_hd a
                ON a.transaksi_id = it.referensi_id
                AND a.group_id = it.group_id
                AND a.org_id = it.org_id
                AND a.active = '1'
            INNER JOIN dt01_qi_periode_mutu p
                ON p.periode_id = a.periode_id
                AND p.active = '1'
                AND p.tahun = ?
            LEFT JOIN dt01_qi_indikator_ms i
                ON i.indikator_id = a.indikator_id
            LEFT JOIN dt01_qi_jenis_indikator_ms j
                ON j.jenis_indikator_id = i.jenis_indikator_id
            WHERE a.group_id = ?
                AND a.org_id = ?
            GROUP BY it.bulan, kategori
            ORDER BY it.bulan, kategori
        ";

        return $this->db->query($sql, [$year, $groupid, $orgid])->getResult();
    }

    public function compareIndicatorPeriods($groupid, $orgid, $indicatorId, $departmentId)
    {
        $departmentCondition = $departmentId === '' ? 'AND a.department_id IS NULL' : 'AND a.department_id = ?';
        $bindings = [$groupid, $orgid, $indicatorId];
        if ($departmentId !== '') {
            $bindings[] = $departmentId;
        }
        $sql = "
            SELECT
                p.periode_id,
                p.tahun,
                d.department,
                i.indikator_code,
                i.indikator,
                a.target,
                i.target_capaian,
                CONCAT(s.satuan, ' /', s.keterangan) AS satuan,
                COUNT(DISTINCT CASE WHEN it.bulan IS NOT NULL THEN it.bulan END) AS jumlah_bulan,
                COUNT(DISTINCT CASE WHEN it.bulan IS NOT NULL THEN CONCAT(it.referensi_id, '-', it.bulan) END) AS jumlah_penilaian,
                SUM(COALESCE(it.numerator, 0)) AS total_numerator,
                SUM(COALESCE(it.denumerator, 0)) AS total_denominator,
                ROUND(AVG(
                    CASE
                        WHEN it.bulan IS NULL THEN NULL
                        WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100
                        WHEN COALESCE(it.denumerator, 0) = 0 THEN 0
                        ELSE LEAST(100, (it.numerator / it.denumerator) * 100)
                    END
                ), 2) AS rata_pencapaian
            FROM dt01_qi_indikator_hd a
            INNER JOIN dt01_qi_periode_mutu p
                ON p.periode_id = a.periode_id
                AND p.active = '1'
                AND p.status_id IN ('3', '4')
            LEFT JOIN dt01_gen_department_ms d ON d.department_id = a.department_id
            LEFT JOIN dt01_qi_indikator_ms i ON i.indikator_id = a.indikator_id
            LEFT JOIN dt01_qi_satuan_ms s ON s.satuan_id = i.satuan_id
            LEFT JOIN dt01_qi_indikator_it it
                ON it.referensi_id = a.transaksi_id
                AND it.group_id = a.group_id
                AND it.org_id = a.org_id
            WHERE a.active = '1'
                AND a.group_id = ?
                AND a.org_id = ?
                AND a.indikator_id = ?
                {$departmentCondition}
            GROUP BY p.periode_id, p.tahun, d.department, i.indikator_code, i.indikator,
                a.target, i.target_capaian, s.satuan, s.keterangan
            ORDER BY p.tahun ASC
        ";

        return $this->db->query($sql, $bindings)->getResult();
    }
}
