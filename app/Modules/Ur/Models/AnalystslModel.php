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
            
                    WITH base AS (
                        SELECT
                            MONTH(a.discharge_date) AS bulan,

                            /* SL I - Aktual */
                            SUM(CASE WHEN a.sl = 'I' THEN 1 ELSE 0 END) AS kasus_i_real,
                            SUM(CASE WHEN a.sl = 'I' THEN a.tarif_inacbg ELSE 0 END) AS pendapatan_i_real,

                            /* SL II - Aktual */
                            SUM(CASE WHEN a.sl = 'II' THEN 1 ELSE 0 END) AS kasus_ii_real,
                            SUM(CASE WHEN a.sl = 'II' THEN a.tarif_inacbg ELSE 0 END) AS pendapatan_ii_real,

                            /* SL III - Aktual */
                            SUM(CASE WHEN a.sl = 'III' THEN 1 ELSE 0 END) AS kasus_iii_real,
                            SUM(CASE WHEN a.sl = 'III' THEN a.tarif_inacbg ELSE 0 END) AS pendapatan_iii_real

                        FROM dt01_bpjs_ur_dt AS a
                        WHERE a.ptd = '1'
                        AND a.discharge_date IS NOT NULL
                        AND a.kode_rs = ?
                        AND YEAR(a.discharge_date) = ?
                        AND a.sl IN ('I', 'II', 'III')
                        GROUP BY MONTH(a.discharge_date)
                    ),

                    rata_tarif AS (
                        SELECT
                            b.*,

                            /* Rata-rata INA-CBG aktual sebelum simulasi */
                            COALESCE(
                                b.pendapatan_i_real / NULLIF(b.kasus_i_real, 0),
                                0
                            ) AS rata_i,

                            COALESCE(
                                b.pendapatan_ii_real / NULLIF(b.kasus_ii_real, 0),
                                0
                            ) AS rata_ii,

                            COALESCE(
                                b.pendapatan_iii_real / NULLIF(b.kasus_iii_real, 0),
                                0
                            ) AS rata_iii

                        FROM base AS b
                    ),

                    perpindahan AS (
                        SELECT
                            r.*,

                            /* 10% kasus aktual SL I pindah ke SL II */
                            ROUND(r.kasus_i_real * 0.10) AS pindah_i_ii,

                            /* 10% kasus aktual SL II pindah ke SL III */
                            ROUND(r.kasus_ii_real * 0.10) AS pindah_ii_iii

                        FROM rata_tarif AS r
                    ),

                    simulasi AS (
                        SELECT
                            p.*,

                            /* Kasus simulasi */
                            p.kasus_i_real - p.pindah_i_ii
                                AS kasus_i_simulasi,

                            p.kasus_ii_real + p.pindah_i_ii - p.pindah_ii_iii
                                AS kasus_ii_simulasi,

                            p.kasus_iii_real + p.pindah_ii_iii
                                AS kasus_iii_simulasi

                        FROM perpindahan AS p
                    )

                    SELECT
                        bulan,

                        /* =========================
                        DATA AKTUAL SL I
                        ========================= */
                        kasus_i_real,
                        pendapatan_i_real,
                        rata_i AS rata_tarif_i,
                        pindah_i_ii,

                        /* =========================
                        DATA AKTUAL SL II
                        ========================= */
                        kasus_ii_real,
                        pendapatan_ii_real,
                        rata_ii AS rata_tarif_ii,
                        pindah_ii_iii,

                        /* =========================
                        DATA AKTUAL SL III
                        ========================= */
                        kasus_iii_real,
                        pendapatan_iii_real,
                        rata_iii AS rata_tarif_iii,

                        /* =========================
                        SIMULASI SL I
                        ========================= */
                        kasus_i_simulasi,

                        kasus_i_simulasi * rata_i
                            AS pendapatan_i_simulasi,

                        /* =========================
                        SIMULASI SL II
                        ========================= */
                        kasus_ii_simulasi,

                        kasus_ii_simulasi * rata_ii
                            AS pendapatan_ii_simulasi,

                        /* =========================
                        SIMULASI SL III
                        ========================= */
                        kasus_iii_simulasi,

                        kasus_iii_simulasi * rata_iii
                            AS pendapatan_iii_simulasi,

                        /* =========================
                        TOTAL KASUS
                        ========================= */
                        kasus_i_real + kasus_ii_real + kasus_iii_real
                            AS total_kasus_real,

                        kasus_i_simulasi + kasus_ii_simulasi + kasus_iii_simulasi
                            AS total_kasus_simulasi,

                        /* =========================
                        TOTAL PENDAPATAN AKTUAL
                        ========================= */
                        pendapatan_i_real
                        + pendapatan_ii_real
                        + pendapatan_iii_real
                            AS total_pendapatan_real,

                        /* =========================
                        TOTAL PENDAPATAN SIMULASI
                        ========================= */
                        (kasus_i_simulasi * rata_i)
                        + (kasus_ii_simulasi * rata_ii)
                        + (kasus_iii_simulasi * rata_iii)
                            AS total_pendapatan_simulasi,

                        /* =========================
                        SELISIH PENDAPATAN
                        ========================= */
                        (
                            (kasus_i_simulasi * rata_i)
                            + (kasus_ii_simulasi * rata_ii)
                            + (kasus_iii_simulasi * rata_iii)
                        ) - (
                            pendapatan_i_real
                            + pendapatan_ii_real
                            + pendapatan_iii_real
                        ) AS selisih_pendapatan_simulasi

                    FROM simulasi
                    ORDER BY bulan;

        ";

        return $this->db->query($query, [$koders, $periode])->getResult();
    }
}