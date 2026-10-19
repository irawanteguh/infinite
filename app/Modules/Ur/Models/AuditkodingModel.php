<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class AuditkodingModel extends Model{
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

    function rawdata($koders,$periode){
        $query = "
                    SELECT x.*
                    FROM (
                        SELECT
                            a.ptd,
                            a.los,
                            a.mrn,
                            a.nama_pasien,
                            a.sep,
                            a.umur_tahun,
                            a.nokartu,
                            a.dpjp,
                            a.diaglist,
                            a.proclist,
                            a.tarif_rs,
                            a.total_tarif,
                            a.idrg_total_tarif,
                            a.sl,
                            CASE
                                WHEN a.diaglist REGEXP '^[[:space:]]*I10([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*I67[.]8([[:space:]]*;|[[:space:]]*$)' THEN 'Jadikan I67.8 sebagai diagnosis utama, sedangkan I10, I15.0, I15.1, I15.2, I15.8, atau I15.9 yang sesuai sebagai diagnosis sekunder. Pastikan penetapan diagnosis utama berdasarkan evaluasi klinis dan dokumentasi medis.'
                                WHEN a.diaglist REGEXP '^[[:space:]]*I10([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*I69[.]3([[:space:]]*;|[[:space:]]*$)' THEN 'Jadikan I69.3 sebagai diagnosis utama, sedangkan I10, I15.0, I15.1, I15.2, I15.8, atau I15.9 yang sesuai sebagai diagnosis sekunder. Pastikan penetapan diagnosis utama berdasarkan evaluasi klinis dan dokumentasi medis.'
                                -- WHEN a.diaglist REGEXP '^[[:space:]]*J40([[:space:]]*;|[[:space:]]*$)' AND a.umur_tahun < 15 THEN 'J40 untuk pasien > 15 tahun'
                                -- WHEN a.diaglist REGEXP '(^|;)[[:space:]]*J20[.]9([[:space:]]*;|[[:space:]]*$)' AND a.umur_tahun < 15 THEN 'J20 untuk pasien < 15 tahun, dan lihat diagnosa spesifiknya'
                                -- WHEN a.diaglist REGEXP '^[[:space:]]*N20[.]0([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*N3[0-9]([.][0-9A-Z]+)?([[:space:]]*;|[[:space:]]*$)' THEN 'Terdapat N20.0 dan diagnosis N30*–N39*. Evaluasi aturan omit code: diagnosis N30*–N39* mungkin tidak perlu dikoding bersamaan dengan N20*, sesuai pedoman koding yang berlaku.'
                                ELSE NULL
                            END AS case_notes,
                            CASE
                                WHEN a.diaglist REGEXP '^[[:space:]]*I10([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*I67[.]8([[:space:]]*;|[[:space:]]*$)' AND a.ptd='1' THEN 3666200
                                WHEN a.diaglist REGEXP '^[[:space:]]*I10([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*I69[.]3([[:space:]]*;|[[:space:]]*$)' AND a.ptd='1' THEN 3666200
                                -- WHEN a.diaglist REGEXP '^[[:space:]]*J40([[:space:]]*;|[[:space:]]*$)' AND a.umur_tahun < 15 THEN 'J40 untuk pasien > 15 tahun'
                                -- WHEN a.diaglist REGEXP '(^|;)[[:space:]]*J20[.]9([[:space:]]*;|[[:space:]]*$)' AND a.umur_tahun < 15 THEN 'J20 untuk pasien < 15 tahun, dan lihat diagnosa spesifiknya'
                                -- WHEN a.diaglist REGEXP '^[[:space:]]*N20[.]0([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*N3[0-9]([.][0-9A-Z]+)?([[:space:]]*;|[[:space:]]*$)' THEN 'Terdapat N20.0 dan diagnosis N30*–N39*. Evaluasi aturan omit code: diagnosis N30*–N39* mungkin tidak perlu dikoding bersamaan dengan N20*, sesuai pedoman koding yang berlaku.'
                                ELSE NULL
                            END AS potensibiaya,
                            DATE_FORMAT(a.admission_date, '%d.%m.%Y') AS admission_date,
                            DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date
                        FROM dt01_bpjs_ur_dt a
                        where a.kode_rs='".$koders."'
                        and   year(a.discharge_date) = ".$periode."
                        ORDER BY a.admission_date ASC
                    ) x
                    WHERE x.case_notes IS NOT NULL;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    // function rawdata($koders,$periode){
    //     $query = "
    //                 SELECT x.*
    //                 FROM (
    //                     SELECT
    //                         a.ptd,
    //                         a.los,
    //                         a.mrn,
    //                         a.nama_pasien,
    //                         a.sep,
    //                         a.umur_tahun,
    //                         a.nokartu,
    //                         a.dpjp,
    //                         a.diaglist,
    //                         a.proclist,
    //                         a.tarif_rs,
    //                         a.total_tarif,
    //                         a.idrg_total_tarif,
    //                         a.sl,
    //                         CASE
    //                             WHEN a.diaglist REGEXP '(^|;)[[:space:]]*I67[.]8([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*(I10|I15[.]0|I15[.]1|I15[.]2|I15[.]8|I15[.]9)([[:space:]]*;|[[:space:]]*$)' THEN 'Jadikan I67.8 sebagai diagnosis utama, sedangkan I10, I15.0, I15.1, I15.2, I15.8, atau I15.9 yang sesuai sebagai diagnosis sekunder. Pastikan penetapan diagnosis utama berdasarkan evaluasi klinis dan dokumentasi medis.'
    //                             WHEN a.diaglist REGEXP '(^|;)[[:space:]]*I69[.]3([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*(I10|I15[.]0|I15[.]1|I15[.]2|I15[.]8|I15[.]9)([[:space:]]*;|[[:space:]]*$)' THEN 'Jadikan I69.3 sebagai diagnosis utama, sedangkan I10, I15.0, I15.1, I15.2, I15.8, atau I15.9 yang sesuai sebagai diagnosis sekunder. Pastikan penetapan diagnosis utama berdasarkan evaluasi klinis dan dokumentasi medis.'
    //                             WHEN a.diaglist REGEXP '(^|;)[[:space:]]*N20[.]0([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*N30[.]0([[:space:]]*;|[[:space:]]*$)' AND a.proclist REGEXP '(^|;)[[:space:]]*(57[.]94|96[.]48|88[.]75)([[:space:]]*;|[[:space:]]*$)' THEN 'Tinjau pengodean N20.0 dan prosedur 57.94, 96.48, atau 88.75. Jika aturan omit code berlaku untuk N20*, evaluasi apakah diagnosis N30*–N39* perlu dihilangkan sesuai pedoman koding yang berlaku.'
    //                             WHEN a.diaglist REGEXP '(^|;)[[:space:]]*J40([[:space:]]*;|[[:space:]]*$)' AND a.umur_tahun > 15 THEN 'Tinjau kembali pengodean J40 pada pasien berusia lebih dari 15 tahun berdasarkan dokumentasi medis dan pedoman koding yang berlaku.'
    //                             WHEN a.diaglist REGEXP '(^|;)[[:space:]]*J20[.]9([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*J06[.]9([[:space:]]*;|[[:space:]]*$)' AND a.proclist REGEXP '(^|;)[[:space:]]*(93[.]94|90[.]59)([[:space:]]*;|[[:space:]]*$)' AND a.umur_tahun < 15 THEN 'Rekomendasi: Tinjau kembali pengodean J20.9 pada pasien berusia kurang dari 15 tahun dan evaluasi diagnosis yang lebih spesifik berdasarkan dokumentasi medis serta pedoman koding yang berlaku.'
    //                             ELSE NULL
    //                         END AS case_notes,
    //                         CASE
    //                             WHEN a.diaglist REGEXP '(^|;)[[:space:]]*I67[.]8([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*(I10|I15[.]0|I15[.]1|I15[.]2|I15[.]8|I15[.]9)([[:space:]]*;|[[:space:]]*$)' THEN 0
    //                             WHEN a.diaglist REGEXP '(^|;)[[:space:]]*I69[.]3([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*(I10|I15[.]0|I15[.]1|I15[.]2|I15[.]8|I15[.]9)([[:space:]]*;|[[:space:]]*$)' THEN 0
    //                             WHEN a.diaglist REGEXP '(^|;)[[:space:]]*N20[.]0([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*N30[.]0([[:space:]]*;|[[:space:]]*$)' AND a.proclist REGEXP '(^|;)[[:space:]]*(57[.]94|96[.]48|88[.]75)([[:space:]]*;|[[:space:]]*$)' THEN 0
    //                             WHEN a.diaglist REGEXP '(^|;)[[:space:]]*J40([[:space:]]*;|[[:space:]]*$)' AND a.umur_tahun > 15 THEN 0
    //                             WHEN a.diaglist REGEXP '(^|;)[[:space:]]*J20[.]9([[:space:]]*;|[[:space:]]*$)' AND a.diaglist REGEXP '(^|;)[[:space:]]*J06[.]9([[:space:]]*;|[[:space:]]*$)' AND a.proclist REGEXP '(^|;)[[:space:]]*(93[.]94|90[.]59)([[:space:]]*;|[[:space:]]*$)' AND a.umur_tahun < 15 THEN 0
    //                             ELSE NULL
    //                         END AS potensibiaya,
    //                         DATE_FORMAT(a.admission_date, '%d.%m.%Y') AS admission_date,
    //                         DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date
    //                     FROM dt01_bpjs_ur_dt a
    //                     where a.kode_rs='".$koders."'
    //                     and   year(a.discharge_date) = ".$periode."
    //                     ORDER BY a.admission_date ASC
    //                 ) x
    //                 WHERE x.case_notes IS NOT NULL;
    //             ";

    //     $recordset = $this->db->query($query);
    //     return $recordset->getResult();
    // }
    
}