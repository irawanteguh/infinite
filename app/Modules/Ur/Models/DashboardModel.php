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
                    select a.sep, kelas_rawat, sl, ptd, tarif_rs, total_tarif, idrg_total_tarif, lpad(month(a.discharge_date), 2, '0') as periode
                    from dt01_bpjs_ur_dt a
                    where a.kode_rs='".$koders."'
                    and   year(a.discharge_date) = " . $periode;

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function detailtarif($koders,$periode){
        $query = "
                        SELECT
                            LPAD(MONTH(a.discharge_date), 2, '0') AS periode,
                            SUM(a.PROSEDUR_NON_BEDAH)   AS prosedurnonbedah,
                            SUM(a.PROSEDUR_BEDAH)       AS prosedurbedah,
                            SUM(a.KONSULTASI)           AS konsultasi,
                            SUM(a.TENAGA_AHLI)          AS tenagaahli,
                            SUM(a.KEPERAWATAN)          AS keperawatan,
                            SUM(a.PENUNJANG)            AS penunjang,
                            SUM(a.RADIOLOGI)            AS radiologi,
                            SUM(a.LABORATORIUM)         AS laboratorium,
                            SUM(a.PELAYANAN_DARAH)      AS pelayanandarah,
                            SUM(a.REHABILITASI)         AS rehabilitasi,
                            SUM(a.KAMAR_AKOMODASI)      AS kamarakomodasi,
                            SUM(a.RAWAT_INTENSIF)       AS rawatintensif,
                            SUM(a.OBAT)                 AS obat,
                            SUM(a.ALKES)                AS alkes,
                            SUM(a.BMHP)                 AS bmhp,
                            SUM(a.SEWA_ALAT)            AS sewaalat,
                            SUM(a.OBAT_KRONIS)          AS obatkronis,
                            SUM(a.OBAT_KEMO)            AS obatkemo
                        FROM dt01_bpjs_ur_dt a
                        WHERE a.discharge_date IS NOT null
                        and   a.ptd='1'
                        and   a.kode_rs='".$koders."'
                        and   year(a.discharge_date) = ".$periode."
                        GROUP BY MONTH(a.discharge_date)
                        ORDER BY MONTH(a.discharge_date);
                    ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
    
}