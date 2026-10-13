<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class DataeklaimModel extends Model{
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

    function dataraweklaim($koders,$periode){
        $query = "
                    select a.sep, nokartu, mrn, los, nama_pasien, ptd, dpjp, a.diaglist, a.proclist, kelas_rawat, sl, tarif_rs, total_tarif, idrg_total_tarif,
                    DATE_FORMAT(a.admission_date, '%d.%m.%Y') AS admission_date,
                    DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date
                    from dt01_bpjs_ur_dt a
                    where a.kode_rs='".$koders."'
                    and   year(a.discharge_date) = ".$periode."
                    limit 100;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
    
}