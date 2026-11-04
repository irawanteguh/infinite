<?php

namespace Modules\Organization\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model{
    protected $DBGroup = 'default';

    function datamasteruser($groupid,$orgid){
        $query =
                "
                    select a.user_id, name
                    from dt01_gen_user_data a
                    where a.active='1'
                    and   a.group_id='".$groupid."'
                    and   a.org_id='".$orgid."'
                    and   a.department_id is null
                    order by name asc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
    
    function datamasterdepartment($groupid,$orgid){
        $query =
                "
                    select a.department_id, department, user_id, last_update_by, active, date_format(a.last_update_date, '%d.%m.%Y %H:%i:%s')dibuattgl, active,
                        (select name from dt01_gen_user_data where user_id=a.user_id)pic,
                        (select email from dt01_gen_user_data where user_id=a.user_id)emailpic,
                        (select name from dt01_gen_user_data where user_id=a.last_update_by)dibuatoleh
                    from dt01_gen_department_ms a
                    where a.group_id='".$groupid."'
                    and   a.org_id='".$orgid."'
                    order by last_update_date desc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    public function updatemasterdepartment($departmentid, $data){
        return $this->db->table('dt01_gen_department_ms')->where('department_id', $departmentid)->update($data);
    }

    public function addmasterdepartment($data){
        return $this->db->table('dt01_gen_department_ms')->insert($data);
    }

    public function updatemasteruser($userid, $data){
        return $this->db->table('dt01_gen_user_data')->where('user_id', $userid)->update($data);
    }

}