<?php
    class Modelmasterindikator extends CI_Model{
        
        function dataindikator($groupid,$orgid){
            $query =
                    "
                        select a.indikator_id, indikator, definisi, dasar_pemikiran, formula, populasi, metode, instrument, tujuan, last_update_by, numerator, denumerator, inklusi, eksklusi, active, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                            dimensi_mutu_keselamatan, dimensi_mutu_waktu, dimensi_mutu_efektif, dimensi_mutu_efesien, dimensi_mutu_pasien, dimensi_mutu_integrasi,
                            (select name from dt01_gen_user_data where user_id=a.last_update_by)dibuatoleh
                        from dt01_qi_indikator_ms a
                        where a.active='1'
                        and   a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }


    }
?>