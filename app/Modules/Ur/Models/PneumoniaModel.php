<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class PneumoniaModel extends Model{
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
                        DATE_FORMAT(a.admission_date, '%d.%m.%Y') AS admission_date,
                        DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date
                    FROM dt01_bpjs_ur_dt a
                    WHERE a.ptd = '1'
                    AND a.diaglist REGEXP '(^|;)[[:space:]]*(J1[2-8]|J22)([.]|;|$)'
                    and   a.kode_rs='".$koders."'
                    and   year(a.discharge_date) = ".$periode."
                    ORDER BY a.admission_date ASC;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
    
}