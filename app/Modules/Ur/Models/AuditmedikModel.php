<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class AuditmedikModel extends Model{
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
                    FROM(
                        SELECT
                            a.ptd,
                            a.los,
                            a.mrn,
                            a.nama_pasien,
                            a.sep,
                            a.nokartu,
                            a.dpjp,
                            a.diaglist,
                            a.proclist,
                            a.tarif_rs,
                            a.total_tarif,
                            a.idrg_total_tarif,
                            a.sl,
                            CASE
                                WHEN a.los > 5 AND sl='I' AND a.diaglist REGEXP '(^|;)[[:space:]]*J22([.]|;|$)' THEN
                                'Evaluasi diagnosis J22: tinjau perkembangan klinis, hasil pemeriksaan penunjang, dan dokumentasi medis untuk memastikan spesifisitas diagnosis akhir.'
                                WHEN a.los <= 5 AND sl='I' AND a.diaglist REGEXP '(^|;)[[:space:]]*J22([.]|;|$)' then
                                'Evaluasi kesesuaian diagnosis J22 dengan kondisi klinis dan lama rawat kurang dari 5 hari. Tinjau dokumentasi medis, hasil pemeriksaan penunjang, serta kemungkinan diagnosis yang lebih spesifik sesuai temuan klinis.'
                                else NULL
                            END AS case_notes,
                            DATE_FORMAT(a.admission_date, '%d.%m.%Y') AS admission_date,
                            DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date
                        FROM dt01_bpjs_ur_dt a
                        WHERE a.ptd = '1'
                        and   a.kode_rs='".$koders."'
                        and   year(a.discharge_date) = ".$periode."
                        ORDER BY a.admission_date asc
                    )x
                    where x.case_notes is not null
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
    
}