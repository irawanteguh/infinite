<?php
    class Modelindikatorunit extends CI_Model{
        
        function dataindikatorunit($groupid,$orgid,$userid){
            $query =
                    "
                        select a.transaksi_id, periode_id, target, pic, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                            (select tahun from dt01_qi_periode_mutu where periode_id=a.periode_id)tahun,
                            (select department from dt01_gen_department_ms where department_id=a.department_id)department,
                            (select name from dt01_gen_user_data where user_id=a.pic)picname,
                            (select jenis from dt01_qi_jenis_indikator_ms where jenis_indikator_id=(select jenis_indikator_id from dt01_qi_indikator_ms where indikator_id=a.indikator_id))jenis,
                            (select indikator from dt01_qi_indikator_ms where indikator_id=a.indikator_id)indikator,
                            (select definisi from dt01_qi_indikator_ms where indikator_id=a.indikator_id)definisi,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='01' and referensi_id=a.transaksi_id)nilai01,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='02' and referensi_id=a.transaksi_id)nilai02,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='03' and referensi_id=a.transaksi_id)nilai03,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='04' and referensi_id=a.transaksi_id)nilai04,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='05' and referensi_id=a.transaksi_id)nilai05,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='06' and referensi_id=a.transaksi_id)nilai06,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='07' and referensi_id=a.transaksi_id)nilai07,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='08' and referensi_id=a.transaksi_id)nilai08,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='09' and referensi_id=a.transaksi_id)nilai09,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='10' and referensi_id=a.transaksi_id)nilai10,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='11' and referensi_id=a.transaksi_id)nilai11,
                            (select round((numerator/denumerator)*100,2) from dt01_qi_indikator_it where bulan='12' and referensi_id=a.transaksi_id)nilai12

                        from dt01_qi_indikator_hd a
                        where a.active='1'
                        and   a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                        and   a.department_id in (select department_id from dt01_gen_department_ms where active='1' and user_id='".$userid."')
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datateam($groupid,$orgid,$userid){
            $query =
                    "
                        select a.pic,
                            (select name from dt01_gen_user_data where user_id=a.pic)picname
                        from dt01_qi_indikator_hd a
                        where a.active='1'
                        and   a.department_id in (select department_id from dt01_gen_department_ms where active='1' and user_id='".$userid."')
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }


    }
?>