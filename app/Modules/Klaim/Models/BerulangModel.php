<?php

namespace Modules\Klaim\Models;

use CodeIgniter\Model;

class BerulangModel extends Model{
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
                        a.PENGAJUAN AS pengajuan,
                        a.ALGORITMA_TYPE AS algoritma_type,
                        a.ALGORITMA AS algoritma,
                        a.KETERANGAN_VERIFIKASI AS keterangan_verifikasi,
                        a.STATUS AS status,
                        DATE_FORMAT(a.TGLSEP, '%d.%m.%Y') AS admission_date,
                        DATE_FORMAT(a.TGLPULANG, '%d.%m.%Y') AS discharge_date,
                        DATE_FORMAT(a.TGLVERIF, '%d.%m.%Y') AS verification_date,
                        LPAD(MONTH(a.TGLPULANG), 2, '0') AS periode
                        
                    FROM dt01_bpjs_pending_klaim a
                    WHERE a.KODEPPK = '".$koders."'
                    AND   YEAR(a.TGLPULANG) = ".$periode."
                    and   a.algoritma='Prosedur berulang'
                    ORDER BY a.TGLPULANG DESC
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
}