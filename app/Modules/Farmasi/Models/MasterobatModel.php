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
                j.hrg_umum,

                -- Pemakaian Bulanan
                COALESCE(s.bulan_01, 0) AS pemakaian_bulan_01,
                COALESCE(s.bulan_02, 0) AS pemakaian_bulan_02,
                COALESCE(s.bulan_03, 0) AS pemakaian_bulan_03,
                COALESCE(s.bulan_04, 0) AS pemakaian_bulan_04,
                COALESCE(s.bulan_05, 0) AS pemakaian_bulan_05,
                COALESCE(s.bulan_06, 0) AS pemakaian_bulan_06,
                COALESCE(s.bulan_07, 0) AS pemakaian_bulan_07,
                COALESCE(s.bulan_08, 0) AS pemakaian_bulan_08,
                COALESCE(s.bulan_09, 0) AS pemakaian_bulan_09,
                COALESCE(s.bulan_10, 0) AS pemakaian_bulan_10,
                COALESCE(s.bulan_11, 0) AS pemakaian_bulan_11,
                COALESCE(s.bulan_12, 0) AS pemakaian_bulan_12,

                -- Total Pemakaian Tahun Berjalan
                (
                    COALESCE(s.bulan_01, 0) +
                    COALESCE(s.bulan_02, 0) +
                    COALESCE(s.bulan_03, 0) +
                    COALESCE(s.bulan_04, 0) +
                    COALESCE(s.bulan_05, 0) +
                    COALESCE(s.bulan_06, 0) +
                    COALESCE(s.bulan_07, 0) +
                    COALESCE(s.bulan_08, 0) +
                    COALESCE(s.bulan_09, 0) +
                    COALESCE(s.bulan_10, 0) +
                    COALESCE(s.bulan_11, 0) +
                    COALESCE(s.bulan_12, 0)
                ) AS total_pemakaian

            FROM dt01_frm_obat_ms a

            LEFT JOIN dt01_frm_harga_modal_obat_hd h
                ON h.obat_id = a.obat_id

            LEFT JOIN dt01_frm_harga_jual_obat_hd j
                ON j.obat_id = a.obat_id

            LEFT JOIN (
                SELECT
                    obat_id,

                    SUM(bulan_01) AS bulan_01,
                    SUM(bulan_02) AS bulan_02,
                    SUM(bulan_03) AS bulan_03,
                    SUM(bulan_04) AS bulan_04,
                    SUM(bulan_05) AS bulan_05,
                    SUM(bulan_06) AS bulan_06,
                    SUM(bulan_07) AS bulan_07,
                    SUM(bulan_08) AS bulan_08,
                    SUM(bulan_09) AS bulan_09,
                    SUM(bulan_10) AS bulan_10,
                    SUM(bulan_11) AS bulan_11,
                    SUM(bulan_12) AS bulan_12

                FROM dt01_frm_stock_out_dt

                WHERE active = '1'
                AND year = YEAR(CURDATE())

                GROUP BY obat_id

            ) s
                ON s.obat_id = a.obat_id

            WHERE a.active = '1'

            ORDER BY a.name ASC;

        ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
}