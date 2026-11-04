<?php

namespace Modules\Hr\Models;

use CodeIgniter\Model;

class UsersModel extends Model{
    protected $DBGroup = 'default';

    function datamasterusers($groupid,$orgid){
        $query =
                "
                    select a.user_id, username, name, email, created_by, nik, active, suspend, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                            (select org_name from dt01_gen_organization_ms where org_id=a.org_id)orgname,
                            (select department from dt01_gen_department_ms where department_id=a.department_id)department,
                            (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh
                    from dt01_gen_user_data a
                    where a.active='1'
                    and   a.group_id='".$groupid."'
                    and   a.org_id='".$orgid."'
                    order by name asc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    public function updateuser($userid, $data){
        return $this->db->table('dt01_gen_user_data')->where('user_id', $userid)->update($data);
    }

}