<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class AuditkodingModel extends Model{
    protected $DBGroup = null;

    public function sequencingDetails($koders, $year, $care)
    {
        $sql = "SELECT sep, mrn, nama_pasien, dpjp, ptd, admission_date, discharge_date,
            diaglist, proclist, los, tarif_rs, total_tarif
            FROM dt01_bpjs_ur_dt WHERE kode_rs = ? AND discharge_date >= ? AND discharge_date < ?
            AND diaglist REGEXP '^[[:space:]]*I10[[:space:]]*;'
            AND diaglist REGEXP '(^|;)[[:space:]]*I(67[.]8|69[.]3)([[:space:]]*;|[[:space:]]*$)'";
        $params = [$koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1)];
        if ($care !== '') { $sql .= ' AND ptd = ?'; $params[] = $care; }
        return $this->db->query($sql . ' ORDER BY discharge_date DESC, sep', $params)->getResult();
    }

    public function comparison($koders, $year)
    {
        $bindings = [$koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1)];
        $coverage = $this->db->query("SELECT COUNT(*) pending_records,
            COUNT(DISTINCT p.NOSEP) pending_episodes,
            COUNT(DISTINCT CASE WHEN EXISTS (SELECT 1 FROM dt01_bpjs_ur_dt u
                WHERE u.kode_rs = p.KODEPPK AND u.sep = p.NOSEP) THEN p.NOSEP END) matched_episodes
            FROM dt01_bpjs_pending_klaim p
            WHERE p.KODEPPK = ? AND p.TGLPULANG >= ? AND p.TGLPULANG < ?", $bindings)->getRow();
        $patterns = $this->db->query("SELECT TRIM(p.ALGORITMA_TYPE) category,
            TRIM(p.ALGORITMA) rule_text, u.ptd care,
            UPPER(TRIM(COALESCE(u.diaglist,''))) diagnoses,
            UPPER(TRIM(COALESCE(u.proclist,''))) procedures,
            COUNT(DISTINCT p.NOSEP) episodes
            FROM dt01_bpjs_pending_klaim p
            JOIN dt01_bpjs_ur_dt u ON u.kode_rs = p.KODEPPK AND u.sep = p.NOSEP
            WHERE p.KODEPPK = ? AND p.TGLPULANG >= ? AND p.TGLPULANG < ?
            GROUP BY TRIM(p.ALGORITMA_TYPE), TRIM(p.ALGORITMA), u.ptd,
                UPPER(TRIM(COALESCE(u.diaglist,''))), UPPER(TRIM(COALESCE(u.proclist,'')))
            ORDER BY episodes DESC", $bindings)->getResult();
        return ['coverage' => $coverage, 'patterns' => $patterns];
    }

    public function pendingRules($koders, $year)
    {
        return $this->db->query("SELECT TRIM(ALGORITMA_TYPE) category, TRIM(ALGORITMA) rule_text,
            JNSPEL care, COUNT(*) records, COUNT(DISTINCT NOSEP) episodes,
            MIN(TGLPULANG) first_date, MAX(TGLPULANG) last_date
            FROM dt01_bpjs_pending_klaim
            WHERE KODEPPK = ? AND TGLPULANG >= ? AND TGLPULANG < ?
              AND TRIM(COALESCE(ALGORITMA, '')) <> ''
            GROUP BY TRIM(ALGORITMA_TYPE), TRIM(ALGORITMA), JNSPEL
            ORDER BY episodes DESC", [$koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1)])->getResult();
    }

    public function summary($koders, $year)
    {
        $sql = "SELECT MONTH(discharge_date) month, ptd,
                COUNT(*) all_episodes,
                SUM(TRIM(COALESCE(diaglist,'')) = '') missing_diagnosis,
                SUM(TRIM(COALESCE(proclist,'')) = '') missing_procedure,
                SUM(diaglist REGEXP '^[[:space:]]*I10[[:space:]]*;' AND diaglist REGEXP '(^|;)[[:space:]]*I(67[.]8|69[.]3)([[:space:]]*;|[[:space:]]*$)') sequencing,
                SUM(diaglist REGEXP '^[[:space:]]*R[0-9]{2}([.][0-9A-Z]+)?([[:space:]]*;|[[:space:]]*$)') symptom_primary,
                SUM(proclist REGEXP '(^|;)[[:space:]]*96[.]7[012]([[:space:]]*;|[[:space:]]*$)' AND COALESCE(diaglist,'') NOT REGEXP '(^|;)[[:space:]]*(J96([.][0-9A-Z]+)?|Z99[.]1)([[:space:]]*;|[[:space:]]*$)') ventilation_review,
                SUM(total_tarif < tarif_rs) negative_cases,
                SUM(tarif_rs) tarif_rs, SUM(total_tarif) total_tarif
                FROM dt01_bpjs_ur_dt
                WHERE kode_rs = ? AND discharge_date >= ? AND discharge_date < ?
                GROUP BY MONTH(discharge_date), ptd";
        return $this->db->query($sql, [$koders, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1)])->getResult();
    }

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
