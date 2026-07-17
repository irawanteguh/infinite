<?php
    class Modelsign extends CI_Model{

        function login($username,$password){
            $query =
                    "
                        select a.user_id, active
                        from dt01_gen_user_data a
                        where a.username='".$username."'
                        and   a.password='".$password."'
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->row();
            return $recordset;
        }

        function datasession($userid){
            $query =
                    "
                        SELECT 
                            a.group_id,
                            a.org_id,
                            a.user_id,
                            a.name,
                            a.email,
                            b.org_name AS organizationname,
                            b.website,
                            b.address,
                            b.email AS organizationemail,
                            c.name AS pimpinan
                        FROM dt01_gen_user_data a
                        LEFT JOIN dt01_gen_organization_ms b
                            ON b.org_id = a.org_id
                        LEFT JOIN dt01_gen_user_data c
                            ON c.user_id = b.user_id
                        WHERE a.active = '1'
                        AND a.user_id = '".$userid."';
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->row();
            return $recordset;
        }

    }
?>