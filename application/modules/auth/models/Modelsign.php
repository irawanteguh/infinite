<?php
    class Modelsign extends CI_Model{

        function login($username,$password){
            $query =
                    "
                        select a.user_id
                        from dt01_gen_user_data a
                        where a.active='1'
                        and   a.username='".$username."'
                        and   a.password='".$password."'
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->row();
            return $recordset;
        }

        function datasession($userid){
            $query =
                    "
                        select a.group_id, org_id, user_id, name, email                        

                        from dt01_gen_user_data a
                        where a.active='1'
                        and   a.user_id='".$userid."'
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->row();
            return $recordset;
        }

    }
?>