<?php

namespace Modules\Farmasi\Models;

use CodeIgniter\Model;

class MasterobatModel extends Model{
    protected $DBGroup = 'default';

    public function masterobat(){
        $query = "
            SELECT
                a.obat_id,
                a.name,
                a.distributor,
                a.kategori_id,

                -- Harga Modal
                h.hrg_distributor,
                h.disc,
                h.ppn,
                h.hrg_total,

                -- Harga Jual
                j.hrg_asuransi,
                j.hrg_umum

            FROM dt01_frm_obat_ms a

            LEFT JOIN dt01_frm_harga_modal_obat_hd h
                ON h.obat_id = a.obat_id

            LEFT JOIN dt01_frm_harga_jual_obat_hd j
                ON j.obat_id = a.obat_id

            WHERE a.active = '1'

            ORDER BY a.name ASC

        ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
}