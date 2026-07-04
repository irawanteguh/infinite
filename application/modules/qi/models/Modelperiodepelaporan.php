<?php
    class Modelperiodepelaporan extends CI_Model{
        
        function dataperiodepelaporan($groupid,$orgid){
            $query =
                    "
                        select
                            a.periode_id,
                            a.created_by,
                            a.active,
                            a.tahun,
                            a.user_id,
                            a.status_id,
                            date_format(a.created_date, '%d.%m.%Y %H:%i:%s') as dibuattgl,

                            u1.name as pic,
                            u1.email as emailpic,

                            u2.name as dibuatoleh,

                            s.code as statuscode,
                            s.master_name as status,
                            s.description as statusdescription,
                            s.color as statuscolor,
                            s.icon as statusicon,

                            (
                                select group_concat(
                                    x.pic, ':',
                                    b.name
                                    separator ';'
                                )
                                from dt01_qi_indikator_hd x
                                left join dt01_gen_user_data b
                                    on b.user_id = x.pic
                                where x.active = '1'
                                and x.periode_id = a.periode_id
                            ) as picindikator

                        from dt01_qi_periode_mutu a

                        left join dt01_gen_user_data u1
                            on u1.user_id = a.user_id

                        left join dt01_gen_user_data u2
                            on u2.user_id = a.created_by

                        left join dt01_gen_master_ms s
                            on s.code = a.status_id
                            and s.active = '1'

                        where a.active = '1'
                        and a.group_id = '".$groupid."'
                        and a.org_id = '".$orgid."'

                        order by a.tahun desc;
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }


    }
?>