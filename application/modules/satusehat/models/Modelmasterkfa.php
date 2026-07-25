<?php
    class Modelmasterkfa extends CI_Model{
        
        function checkdatakfa($groupid,$orgid,$kfaid){
            $query =
                    "
                        select a.obat_id, kfa_id, hrg_total
                        from dt01_frm_obat_ms a
                        where a.active='1'
                        and   a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                        and   a.kfa_id='".$kfaid."'
                        order by name asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }


        function insertkfa($data){           
            $sql =   $this->db->insert("dt01_frm_obat_ms",$data);
            return $sql;
        }

        function updatekfa($kfaid, $data){           
            $sql =   $this->db->update("dt01_frm_obat_ms",$data,array("kfa_id"=>$kfaid));
            return $sql;
        }

    }
?>