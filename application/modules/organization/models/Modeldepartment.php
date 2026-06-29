<?php
    class Modeldepartment extends CI_Model{
        
        function datadepartment($groupid,$orgid){
            $query =
                    "
                        select a.department_id, department, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl, active,
                            (select name from dt01_gen_user_data where user_id=a.user_id)pic,
                            (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh
                        from dt01_gen_department_ms a
                        where a.active='1'
                        and   a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                        order by department asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function insertdepartment($data){           
            $sql =   $this->db->insert("dt01_gen_department_ms",$data);
            return $sql;
        }

    }
?>