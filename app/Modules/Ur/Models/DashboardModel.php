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

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function dataraweklaim($koders,$periode){
        $query = "
                    SELECT
                        LPAD(MONTH(a.discharge_date), 2, '0') AS periode,
                        a.ptd,
                        a.sl,
                        COUNT(*) AS jumlah_kasus,
                        SUM(a.tarif_rs) AS tarif_rs,
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
                    WHERE  a.kode_rs = '".$koders."'
                    AND year(a.discharge_date) = ".$periode."
                    GROUP BY
                        MONTH(a.discharge_date),
                        a.ptd,
                        a.sl
                    ORDER BY
                        MONTH(a.discharge_date),
                        a.ptd,
                        a.sl;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
    
}