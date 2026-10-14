<?php

namespace Modules\Klaim\Models;

use CodeIgniter\Model;

class PendingModel extends Model{
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
                        DATE_FORMAT(a.TGLVERIF, '%d.%m.%Y') AS verification_date
                    FROM dt01_bpjs_pending_klaim a
                    WHERE a.KODEPPK = '".$koders."'
                    AND YEAR(a.TGLPULANG) = ".$periode."
                    ORDER BY a.TGLPULANG DESC
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function dataraweklaim($kodeppk,$periode){
        $query = "
                    SELECT LPAD(MONTH(a.tglpulang), 2, '0') AS periode, a.status, a.jnspel, COUNT(*) AS jumlah_kasus, sum(pengajuan) pengajuan
                    FROM dt01_bpjs_pending_klaim a
                    WHERE  a.kodeppk = '".$kodeppk."'
                    AND    year(a.tglpulang) = ".$periode."
                    GROUP BY MONTH(a.tglpulang), a.jnspel, status
                    ORDER BY MONTH(a.tglpulang), a.jnspel;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function dataalgoritmatype($kodeppk,$periode){
        $query = "
                    SELECT a.algoritma_type, a.status, a.jnspel, COUNT(*) AS jumlah_kasus
                    FROM dt01_bpjs_pending_klaim a
                    WHERE  a.kodeppk = '".$kodeppk."'
                    AND    year(a.tglpulang) = ".$periode."
                    AND    a.algoritma_type<>''
                    GROUP by a.jnspel, status, algoritma_type
                    ORDER BY a.algoritma_type;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function dataalgoritmabreakdown($kodeppk,$periode){
        $query = "
                    WITH RECURSIVE pecah AS (
                        SELECT
                            a.algoritma AS teks
                        FROM dt01_bpjs_pending_klaim a
                        WHERE a.algoritma IS NOT NULL
                        AND   TRIM(a.algoritma) <> ''
                        AND   a.kodeppk = '".$kodeppk."'
                        AND   year(a.tglpulang) = ".$periode."
                        UNION ALL

                        SELECT
                            SUBSTRING(teks, LOCATE('|', teks) + 1)
                        FROM pecah
                        WHERE LOCATE('|', teks) > 0
                    ),
                    items AS (
                        SELECT
                            TRIM(SUBSTRING_INDEX(teks, '|', 1)) AS item_awal
                        FROM pecah
                    ),
                    normalisasi AS (
                        SELECT
                            TRIM(
                                CASE
                                    WHEN item_awal LIKE '# ALGORITMA ->%'
                                        THEN SUBSTRING(
                                            item_awal,
                                            LENGTH('# ALGORITMA ->') + 1
                                        )
                                    WHEN item_awal LIKE 'ALGORITMA ->%'
                                        THEN SUBSTRING(
                                            item_awal,
                                            LENGTH('ALGORITMA ->') + 1
                                        )
                                    WHEN item_awal LIKE 'OTOVER ->%'
                                        THEN SUBSTRING(
                                            item_awal,
                                            LENGTH('OTOVER ->') + 1
                                        )
                                    ELSE item_awal
                                END
                            ) AS algoritma
                        FROM items
                    )
                    SELECT
                        algoritma,
                        COUNT(*) AS jumlah
                    FROM normalisasi
                    WHERE algoritma <> ''
                    GROUP BY algoritma
                    ORDER BY jumlah DESC, algoritma ASC;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
}