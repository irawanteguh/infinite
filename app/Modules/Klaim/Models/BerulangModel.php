<?php

namespace Modules\Klaim\Models;

use CodeIgniter\Model;

class BerulangModel extends Model{
    protected $DBGroup = null;

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

    function dataraweklaimdetail($koders,$periode){
        $query = "
                    SELECT
                        a.NOSEP AS sep,
                        a.NOKARTU AS nokartu,
                        a.NIK AS nik,
                        a.NMPESERTA AS nama_pasien,
                        a.JNSPEL AS jnspel,
                        a.KDINACBG AS kdinacbg,
                        a.CMG AS cmg,
                        a.CG AS cg,
                        a.CT AS ct,
                        a.SL AS sl,
                        a.PENGAJUAN AS pengajuan, u.tarif_rs, u.total_tarif AS nilai_inacbg,
                        a.ALGORITMA_TYPE AS algoritma_type,
                        a.ALGORITMA AS algoritma,
                        a.KETERANGAN_VERIFIKASI AS keterangan_verifikasi,
                        a.STATUS AS status,
                        DATE_FORMAT(a.TGLSEP, '%d.%m.%Y') AS admission_date,
                        DATE_FORMAT(a.TGLPULANG, '%d.%m.%Y') AS discharge_date,
                        DATE_FORMAT(a.TGLVERIF, '%d.%m.%Y') AS verification_date,
                        LPAD(MONTH(a.TGLPULANG), 2, '0') AS periode
                        
                    FROM dt01_bpjs_pending_klaim a
                    LEFT JOIN (
                        SELECT sep,
                            CASE WHEN COUNT(DISTINCT tarif_rs) = 1 THEN MAX(tarif_rs) END AS tarif_rs,
                            CASE WHEN COUNT(DISTINCT total_tarif) = 1 THEN MAX(total_tarif) END AS total_tarif
                        FROM dt01_bpjs_ur_dt
                        WHERE kode_rs = ? AND discharge_date >= ? AND discharge_date < ?
                        GROUP BY sep
                    ) u ON u.sep = a.NOSEP
                    WHERE a.KODEPPK = ? AND a.TGLPULANG >= ? AND a.TGLPULANG < ?
                    and   a.algoritma='Prosedur berulang'
                    ORDER BY a.TGLPULANG DESC
                ";

        $recordset = $this->db->query($query, [$koders, sprintf('%04d-01-01', $periode), sprintf('%04d-01-01', $periode+1), $koders, sprintf('%04d-01-01', $periode), sprintf('%04d-01-01', $periode+1)]);
        return $recordset->getResult();
    }
}