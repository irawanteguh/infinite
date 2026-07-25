<?php
    class Modelmasterobat extends CI_Model{
        
        function datamasterobat($groupid,$orgid){
            $query =
                    "
                        SELECT
                            a.obat_id,
                            a.kfa_id,
                            a.name,
                            a.produsen,
                            a.het,
                            a.hrg_distributor,
                            a.disc,
                            a.ppn,
                            a.hrg_total,
                            a.active,
                            a.created_by,
                            DATE_FORMAT(a.created_date, '%d.%m.%Y %H:%i:%s') AS dibuattgl,
                            (
                                SELECT b.name
                                FROM dt01_gen_user_data b
                                WHERE b.user_id = a.created_by
                            ) AS dibuatoleh
                        FROM dt01_frm_obat_ms a
                        WHERE a.active = '1'
                        AND a.group_id = '".$groupid."'
                        AND a.org_id = '".$orgid."'
                        ORDER BY a.created_date DESC
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }



    }
?>