<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class DashboardModel extends Model{
    protected $DBGroup = 'default';

    function periode(){
        $query = "
                    with recursive periode as (
                        select 2014 as periode
                        union all
                        select periode + 1
                        from periode
                        where periode < year(curdate())
                    )
                    select periode
                    from periode
                    order by periode desc

                ";

        return array_map(static function ($year) {
            return (object) ['periode' => $year];
        }, range((int) date('Y'), 2014));
    }

    function dataraweklaim($koders,$periode){
        $query = "
                    SELECT
                        LPAD(MONTH(a.discharge_date), 2, '0') AS periode,
                        a.ptd,
                        a.sl,
                        COUNT(*) AS jumlah_kasus,
                        SUM(a.tarif_rs) AS tarif_rs,
                        MIN(a.discharge_date) AS first_discharge,
                        MAX(a.discharge_date) AS last_discharge,
                        SUM(CASE WHEN a.total_tarif < a.tarif_rs THEN 1 ELSE 0 END) AS negative_cases,
                        SUM(CASE WHEN a.total_tarif < a.tarif_rs THEN a.tarif_rs - a.total_tarif ELSE 0 END) AS negative_gap,
                        SUM(CASE WHEN a.ptd = 1 THEN a.los ELSE 0 END) AS total_los,
                        SUM(CASE WHEN a.ptd = 1 AND a.los IS NOT NULL THEN 1 ELSE 0 END) AS los_cases,
                        SUM(CASE WHEN a.ptd = 1 AND a.los < 2 THEN 1 ELSE 0 END) AS short_stay,
                        SUM(CASE WHEN a.ptd = 1 AND a.sl = 'I' AND a.los > 7 THEN 1 ELSE 0 END) AS long_sl1,
                        SUM(CASE WHEN a.ptd = 1 AND a.sl = 'III' AND a.los < 4 THEN 1 ELSE 0 END) AS short_sl3,
                        SUM(a.total_tarif) AS total_tarif,
                        SUM(a.idrg_total_tarif) AS idrg_total_tarif,
                        SUM(a.prosedur_non_bedah) AS prosedur_non_bedah,
                        SUM(a.prosedur_bedah) AS prosedur_bedah,
                        SUM(a.konsultasi) AS konsultasi,
                        SUM(a.tenaga_ahli) AS tenaga_ahli,
                        SUM(a.keperawatan) AS keperawatan,
                        SUM(a.penunjang) AS penunjang,
                        SUM(a.radiologi) AS radiologi,
                        SUM(a.laboratorium) AS laboratorium,
                        SUM(a.pelayanan_darah) AS pelayanan_darah,
                        SUM(a.rehabilitasi) AS rehabilitasi,
                        SUM(a.kamar_akomodasi) AS kamar_akomodasi,
                        SUM(a.rawat_intensif) AS rawat_intensif,
                        SUM(a.obat) AS obat,
                        SUM(a.alkes) AS alkes,
                        SUM(a.bmhp) AS bmhp,
                        SUM(a.sewa_alat) AS sewa_alat,
                        SUM(a.obat_kronis) AS obat_kronis,
                        SUM(a.obat_kemo) AS obat_kemo
                    FROM dt01_bpjs_ur_dt a
                    WHERE a.kode_rs = ?
                    AND a.discharge_date >= ?
                    AND a.discharge_date < ?
                    GROUP BY
                        MONTH(a.discharge_date),
                        a.ptd,
                        a.sl
                    ORDER BY
                        MONTH(a.discharge_date),
                        a.ptd,
                        a.sl;
                ";

        $recordset = $this->db->query($query, [
            $koders, sprintf('%04d-01-01', $periode), sprintf('%04d-01-01', $periode + 1)
        ]);
        return $recordset->getResult();
    }
    
}
