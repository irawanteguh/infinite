<?php
    class Modelperiodepelaporan extends CI_Model{
        
        function dataperiodepelaporan($groupid,$orgid){
            $query =
                    "
                        select a.periode_id, created_by, active, tahun, user_id, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                            (select name from dt01_gen_user_data where user_id=a.user_id)pic,
                            (select email from dt01_gen_user_data where user_id=a.user_id)emailpic,
                            (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh,
                            (
                                SELECT GROUP_CONCAT(
                                    x.pic, ':',
                                    b.name, ':'
                                    SEPARATOR ';'
                                )
                                FROM dt01_qi_indikator_hd x
                                LEFT JOIN dt01_gen_user_data b
                                    ON b.user_id = x.pic 
                                WHERE x.active = '1'
                                and   x.periode_id=a.periode_id
                            ) AS picindikator
                        from dt01_qi_periode_mutu a
                        where a.active='1'
                        and   a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                        order by tahun desc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }


    }
?>