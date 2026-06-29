<?php
    class Modelusers extends CI_Model{
        
        function datausers(){
            $query =
                    "
                        select a.user_id, username, name, email, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                               (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh
                        from dt01_gen_user_data a
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

    }
?>