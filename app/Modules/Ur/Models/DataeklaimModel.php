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
                    select a.sep, kelas_rawat, sl, tarif_rs, total_tarif, idrg_total_tarif, lpad(month(a.admission_date), 2, '0') as periode
                    from dt01_bpjs_ur_dt a
                    where a.kode_rs='".$koders."'
                    and   year(a.admission_date) = " . $periode;

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }


    function cekdatastatusur($nomorsep){
        $query =
                "
                    select a.sep
                    from dt01_bpjs_ur_dt a
                    where a.sep='".$nomorsep."'
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function insertstatusur($data){
        return $this->db->table('dt01_bpjs_ur_dt')->insert($data);
    }

    function updatestatusur($nomorsep, $data){
        return $this->db->table('dt01_bpjs_ur_dt')->where('sep', $nomorsep)->update($data);
    }
    
}