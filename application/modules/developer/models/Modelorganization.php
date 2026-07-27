<?php
    class Modelorganization extends CI_Model{

        function dataorganization(){
            $query =
                    "
                        select a.org_id, header_id, org_name, website, email, address, holding, user_id, created_by, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                            (select name from dt01_gen_user_data where user_id=a.user_id)pic,
                            (select email from dt01_gen_user_data where user_id=a.user_id)emailpic,
                            (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh
                        from dt01_gen_organization_ms a
                        where a.active='1'
                        order by header_id asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function insertorganization($data){           
            $sql =   $this->db->insert("dt01_gen_organization_ms",$data);
            return $sql;
        }

    }
?>