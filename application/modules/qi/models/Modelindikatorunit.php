<?php
    class Modelindikatorunit extends CI_Model{
        
        function datamasterindikator($groupid,$orgid){
            $query =
                    "
                        select a.indikator_id, indikator
                        from dt01_qi_indikator_ms a
                        where a.active='1'
                        and   a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                        order by indikator asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datamasterperiodepelaporan($groupid,$orgid){
            $query =
                    "
                        select a.periode_id, tahun
                        from dt01_qi_periode_mutu a
                        where a.active='1'
                        and   a.status_id='1'
                        and   a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                        order by tahun desc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datamasterdepartment($groupid,$orgid,$userid){
            $query =
                    "
                        select a.department_id, department
                        from dt01_gen_department_ms a
                        where a.active='1'
                        and   a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                        and   a.user_id='".$userid."'
                        order by department asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datauser($groupid,$orgid){
            $query =
                    "
                        select a.user_id, name
                        from dt01_gen_user_data a
                        where a.active='1'
                        and   a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                        order by name asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function dataindikatorunit($groupid,$orgid,$userid){
            $query =
                    "
                        SELECT
                            a.transaksi_id,
                            a.periode_id,
                            a.target,
                            a.pic,
                            DATE_FORMAT(a.created_date,'%d.%m.%Y %H:%i:%s') AS dibuattgl,

                            p.tahun,
                            p.status_id,
                            d.department,
                            u.name AS picname,

                            j.jenis,
                            i.indikator,
                            i.definisi,
                            i.dimensi_mutu_keselamatan, i.dimensi_mutu_waktu, i.dimensi_mutu_efektif, i.dimensi_mutu_efesien, i.dimensi_mutu_pasien, i.dimensi_mutu_integrasi,

                            s.code AS statuscode,
                            s.master_name AS status,
                            s.description AS statusdescription,
                            s.color AS statuscolor,
                            s.icon AS statusicon,

                            ROUND(MAX(CASE WHEN it.bulan='01' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai01,
                            ROUND(MAX(CASE WHEN it.bulan='02' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai02,
                            ROUND(MAX(CASE WHEN it.bulan='03' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai03,
                            ROUND(MAX(CASE WHEN it.bulan='04' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai04,
                            ROUND(MAX(CASE WHEN it.bulan='05' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai05,
                            ROUND(MAX(CASE WHEN it.bulan='06' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai06,
                            ROUND(MAX(CASE WHEN it.bulan='07' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai07,
                            ROUND(MAX(CASE WHEN it.bulan='08' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai08,
                            ROUND(MAX(CASE WHEN it.bulan='09' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai09,
                            ROUND(MAX(CASE WHEN it.bulan='10' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai10,
                            ROUND(MAX(CASE WHEN it.bulan='11' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai11,
                            ROUND(MAX(CASE WHEN it.bulan='12' THEN (it.numerator/NULLIF(it.denumerator,0))*100 END),2) AS nilai12

                        FROM dt01_qi_indikator_hd a

                        LEFT JOIN dt01_qi_periode_mutu p
                            ON p.periode_id = a.periode_id

                        LEFT JOIN dt01_gen_master_ms s
                            ON s.code = p.status_id
                            AND s.active = '1'

                        LEFT JOIN dt01_gen_department_ms d
                            ON d.department_id = a.department_id

                        LEFT JOIN dt01_gen_user_data u
                            ON u.user_id = a.pic

                        LEFT JOIN dt01_qi_indikator_ms i
                            ON i.indikator_id = a.indikator_id

                        LEFT JOIN dt01_qi_jenis_indikator_ms j
                            ON j.jenis_indikator_id = i.jenis_indikator_id

                        LEFT JOIN dt01_qi_indikator_it it
                            ON it.referensi_id = a.transaksi_id

                        WHERE a.active='1'
                        AND a.group_id='".$groupid."'
                        AND a.org_id='".$orgid."'
                        AND EXISTS (
                                SELECT 1
                                FROM dt01_gen_department_ms dm
                                WHERE dm.department_id = a.department_id
                                AND dm.active='1'
                                AND dm.user_id='".$userid."'
                        )

                        GROUP BY
                            a.transaksi_id,
                            a.periode_id,
                            a.target,
                            a.pic,
                            a.created_date,
                            p.tahun,
                            d.department,
                            u.name,
                            j.jenis,
                            i.indikator,
                            i.definisi,
                            s.code,
                            s.master_name,
                            s.description,
                            s.color,
                            s.icon
                        order by p.tahun desc
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
                        order by picname asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function insertindikatorunit($data){           
            $sql =   $this->db->insert("dt01_qi_indikator_hd",$data);
            return $sql;
        }

        function insertnilaiindikator($data){           
            $sql =   $this->db->insert("dt01_qi_indikator_it",$data);
            return $sql;
        }

        function updateindikatorunit($transaksiid, $data){           
            $sql =   $this->db->update("dt01_qi_indikator_hd",$data,array("transaksi_id"=>$transaksiid));
            return $sql;
        }

    }
?>