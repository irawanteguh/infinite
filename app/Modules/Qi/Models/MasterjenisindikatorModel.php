<?php

namespace Modules\Qi\Models;

use CodeIgniter\Model;

class MasterjenisindikatorModel extends Model{
    protected $DBGroup = 'default';

    function datamasterjenisindikator($groupid,$orgid){
        $query =
                "
                    select a.jenis_indikator_id, jenis, active, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                        (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh
                    from dt01_qi_jenis_indikator_ms a
                    where a.group_id='".$groupid."'
                    and   a.org_id='".$orgid."'
                    order by created_date desc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    public function updatemasterjenisindikator($jenisindikatorid, $data){
        return $this->db->table('dt01_qi_jenis_indikator_ms')->where('jenis_indikator_id', $jenisindikatorid)->update($data);
    }
}