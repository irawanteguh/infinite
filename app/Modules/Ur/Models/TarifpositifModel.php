<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class TarifpositifModel extends Model{
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
                    select a.ptd, mrn, nama_pasien, sep, nokartu, dpjp, diaglist, proclist, tarif_rs, total_tarif, idrg_total_tarif,
                           DATE_FORMAT(admission_date, '%d.%m.%Y') AS admission_date,
                           DATE_FORMAT(discharge_date, '%d.%m.%Y') AS discharge_date
                    from dt01_bpjs_ur_dt a
                    where a.tarif_rs < a.total_tarif
                    and   a.kode_rs='".$koders."'
                    and   year(a.discharge_date) = ".$periode."
                    order by admission_date asc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
    
}