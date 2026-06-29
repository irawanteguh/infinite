<?php
    class Modelmasterindikator extends CI_Model{
        
        function dataindikator($groupid,$orgid){
            $query =
                    "
                        select a.indikator_id, indikator, definisi, dasar_pemikiran,  tujuan, created_by, numerator, denumerator, inklusi, eksklusi, active, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                            dimensi_mutu_keselamatan, dimensi_mutu_waktu, dimensi_mutu_efektif, dimensi_mutu_efesien, dimensi_mutu_pasien, dimensi_mutu_integrasi,
                            (select jenis from dt01_qi_jenis_indikator_ms where jenis_indikator_id=a.jenis_indikator_id)jenisindikator,
                            (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh
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