<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class TopupModel extends Model{
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
                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*I21[.]?[0-9]*([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*89[.]52([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='1'
                                THEN 'Periksa Top Up Untuk Streptokinase'

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*I63[.]9([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*87[.]03([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                THEN 'Periksa Tarif Non INA-CBG Untuk Alteplase, apabila memang ada tata laksana obat Alteplase sesuai dengan PMK 3 Tahun 2023'

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*(M84[.]15|M80[.]05|S72[.]00|M84[.]45|S72[.]0|M17[.]1)([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*81[.]5[124]([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='1'
                                THEN 'Periksa Top Up Untuk Hip Implant/Knee Implant. Kriteria: penjaminan tidak dapat dilakukan di RS kelas D; layanan dilakukan oleh operator sesuai kewenangan klinis dan kompetensi; serta tersedia alat untuk pelaksanaan operasi Hip/Knee Implant.'

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*(I20[.]9|I21[.][0149]|I25[.]0)([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*00[.]4[012]([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='1'
                                THEN 'Periksa Top Up Untuk Percutaneous Coronary Intervention (PCI)'

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*Z09[.]8([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*31[.]42([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='2'
                                THEN 'Periksa Top Up Untuk Microlaringoscopy'

                                WHEN a.proclist REGEXP '(^|;)[[:space:]]*88[.]9[37]([[:space:]]*;|[[:space:]]*$)'
                                    AND a.si is null
                                    AND a.ptd='1'
                                THEN 'Periksa Top Up Untuk Magnetic Resonance Imaging (MRI)'

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*Z49[.]1([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*39[.]95([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='2'
                                    AND a.total_tarif < 896700
                                THEN 'Periksa Tarif Non INA-CBG Untuk Kantong Darah Hemodialisis maksimal 4 kantong @Rp360.000. Alat dan bahan medis habis pakai tindakan hemodialisis digunakan single use; jika re-use, tarif yang dibayarkan 85% dari tarif yang berlaku.'

                                ELSE NULL
                            END AS case_notes,
                            CASE
                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*I21[.]?[0-9]*([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*89[.]52([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='1'
                                THEN 'Streptokinase'

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*I63[.]9([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*87[.]03([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                THEN 'Alteplase'

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*(M84[.]15|M80[.]05|S72[.]00|M84[.]45|S72[.]0|M17[.]1)([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*81[.]5[124]([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='1'
                                THEN 'Hip Replacement/Knee Replacement'

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*(I20[.]9|I21[.][0149]|I25[.]0)([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*00[.]4[012]([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='1'
                                THEN 'Percutaneous Coronary Intervention (PCI)'

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*Z51[.]0([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                THEN 'Stereotactic Surgery & Radiotheraphy'

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*Z09[.]8([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*31[.]42([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='2'
                                THEN 'Microlaringoscopy'

                                WHEN a.proclist REGEXP '(^|;)[[:space:]]*88[.]9[37]([[:space:]]*;|[[:space:]]*$)'
                                AND a.si is null
                                AND a.ptd='1'
                                THEN 'Magnetic Resonance Imaging (MRI)'

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*Z49[.]1([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*39[.]95([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='2'
                                    AND a.total_tarif < 896700
                                THEN 'Kantong Darah Hemodialisis'

                                ELSE NULL
                            END AS topup,
                            CASE
                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*I21[.]?[0-9]*([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*89[.]52([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='1'
                                THEN 4850700

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*I63[.]9([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*87[.]03([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                THEN 0

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*(M84[.]15|M80[.]05|S72[.]00|M84[.]45|S72[.]0|M17[.]1)([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*81[.]5[124]([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='1'
                                THEN 0

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*(I20[.]9|I21[.][0149]|I25[.]0)([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*00[.]4[012]([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='1'
                                THEN 0

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*Z51[.]0([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                THEN 0

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*Z09[.]8([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*31[.]42([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='2'
                                THEN 0

                                WHEN a.proclist REGEXP '(^|;)[[:space:]]*88[.]9[37]([[:space:]]*;|[[:space:]]*$)'
                                AND a.si is null
                                AND a.ptd='1'
                                THEN 1679300

                                WHEN a.diaglist REGEXP '(^|;)[[:space:]]*Z49[.]1([[:space:]]*;|[[:space:]]*$)'
                                    AND a.proclist REGEXP '(^|;)[[:space:]]*39[.]95([[:space:]]*;|[[:space:]]*$)'
                                    AND a.sd is null
                                    AND a.ptd='2'
                                    AND a.total_tarif < 896700
                                THEN 134505

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