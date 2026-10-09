<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class AnalystslModel extends Model{
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

    function jmlkasus($koders, $periode){
        $query = "
            SELECT
                MONTH(a.discharge_date) AS bulan,

                -- SL I
                SUM(CASE WHEN a.sl = 'I' THEN 1 ELSE 0 END) AS kasus_i_real,
                SUM(CASE WHEN a.sl = 'I' THEN a.tarif_inacbg ELSE 0 END) AS pendapatan_i_real,
                ROUND(SUM(CASE WHEN a.sl = 'I' THEN 1 ELSE 0 END) * 0.90) AS kasus_i_simulasi,
                CASE
                    WHEN SUM(CASE WHEN a.sl = 'I' THEN 1 ELSE 0 END) = 0 THEN 0
                    ELSE
                        (
                            SUM(CASE WHEN a.sl = 'I' THEN a.tarif_inacbg ELSE 0 END)
                            / SUM(CASE WHEN a.sl = 'I' THEN 1 ELSE 0 END)
                        )
                        * ROUND(SUM(CASE WHEN a.sl = 'I' THEN 1 ELSE 0 END) * 0.90)
                END AS pendapatan_i_simulasi,

                -- SL II
                SUM(CASE WHEN a.sl = 'II' THEN 1 ELSE 0 END) AS kasus_ii_real,
                SUM(CASE WHEN a.sl = 'II' THEN a.tarif_inacbg ELSE 0 END) AS pendapatan_ii_real,
                ROUND(SUM(CASE WHEN a.sl = 'II' THEN 1 ELSE 0 END) * 1.10) AS kasus_ii_simulasi,
                CASE
                    WHEN SUM(CASE WHEN a.sl = 'II' THEN 1 ELSE 0 END) = 0 THEN 0
                    ELSE
                        (
                            SUM(CASE WHEN a.sl = 'II' THEN a.tarif_inacbg ELSE 0 END)
                            / SUM(CASE WHEN a.sl = 'II' THEN 1 ELSE 0 END)
                        )
                        * ROUND(SUM(CASE WHEN a.sl = 'II' THEN 1 ELSE 0 END) * 1.10)
                END AS pendapatan_ii_simulasi,

                -- SL III
                SUM(CASE WHEN a.sl = 'III' THEN 1 ELSE 0 END) AS kasus_iii_real,
                SUM(CASE WHEN a.sl = 'III' THEN a.tarif_inacbg ELSE 0 END) AS pendapatan_iii_real,
                ROUND(
                    SUM(CASE WHEN a.sl = 'III' THEN 1 ELSE 0 END)
                    + SUM(CASE WHEN a.sl = 'II' THEN 1 ELSE 0 END) * 0.10
                ) AS kasus_iii_simulasi,
                CASE
                    WHEN SUM(CASE WHEN a.sl = 'III' THEN 1 ELSE 0 END) = 0 THEN 0
                    ELSE
                        (
                            SUM(CASE WHEN a.sl = 'III' THEN a.tarif_inacbg ELSE 0 END)
                            / SUM(CASE WHEN a.sl = 'III' THEN 1 ELSE 0 END)
                        )
                        * ROUND(
                            SUM(CASE WHEN a.sl = 'III' THEN 1 ELSE 0 END)
                            + SUM(CASE WHEN a.sl = 'II' THEN 1 ELSE 0 END) * 0.10
                        )
                END AS pendapatan_iii_simulasi

            FROM dt01_bpjs_ur_dt AS a
            WHERE a.ptd = '1'
            AND a.discharge_date IS NOT NULL
            AND a.kode_rs = ?
            AND YEAR(a.discharge_date) = ?
            AND a.sl IN ('I', 'II', 'III')
            GROUP BY MONTH(a.discharge_date)
            ORDER BY MONTH(a.discharge_date)
        ";

        return $this->db->query($query, [$koders, $periode])->getResult();
    }
}