<?php
    class Modeldepartment extends CI_Model{
        
        function datauser($groupid,$orgid){
            $query =
                    "
                        select a.user_id, name
                        from dt01_gen_user_data a
                        where a.active='1'
                        and   a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                        order by name asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datadepartment($groupid,$orgid){
            $query =
                    "
                        select a.department_id, department, user_id, created_by, active, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl, active,
                            (select name from dt01_gen_user_data where user_id=a.user_id)pic,
                            (select email from dt01_gen_user_data where user_id=a.user_id)emailpic,
                            (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh
                        from dt01_gen_department_ms a
                        where a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                        order by created_date desc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function insertdepartment($data){           
            $sql =   $this->db->insert("dt01_gen_department_ms",$data);
            return $sql;
        }

        function updatedepartment($departmentid, $data){           
            $sql =   $this->db->update("dt01_gen_department_ms",$data,array("department_id"=>$departmentid));
            return $sql;
        }

    }
?>