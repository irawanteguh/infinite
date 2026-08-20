<?php
    class Modelusers extends CI_Model{
        
        function dataorganization(){
            $query =
                    "
                        select a.org_id, header_id, org_name
                        from dt01_gen_organization_ms a
                        where a.active='1'
                        order by header_id asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datausers(){
            $query =
                    "
                        select a.user_id, username, name, email, created_by, nik, active, suspend, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                                (select org_name from dt01_gen_organization_ms where org_id=a.org_id)orgname,
                                (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh
                        from dt01_gen_user_data a
                        where a.active='1'
                        order by name asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function checkemail($userid,$email){
            $query =
                    "
                        select a.*
                        from dt01_gen_user_data a
                        where a.active='1'
                        and   a.user_id<>'".$userid."'
                        and   lower(a.email)=lower('".$email."')
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->row();
            return $recordset;
        }

        function insertuser($data){           
            $sql =   $this->db->insert("dt01_gen_user_data",$data);
            return $sql;
        }

        function updateuser($userid, $data){           
            $sql =   $this->db->update("dt01_gen_user_data",$data,array("user_id"=>$userid));
            return $sql;
        }

    }
?>