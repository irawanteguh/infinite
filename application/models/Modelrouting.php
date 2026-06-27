<?php
    class Modelrouting extends CI_Model{

        function menu(){
            $query =
                    "
                        select a.modules_id, modules_name, modules_header_id, package, def_controller, parent, icon
                        from dt01_gen_modules_ms a
                        where a.active='1'
                        order by urut asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result_array();
            return $recordset;
        }


    }
?>