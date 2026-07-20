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
                            i.dimensi_mutu_keselamatan,
                            i.dimensi_mutu_waktu,
                            i.dimensi_mutu_efektif,
                            i.dimensi_mutu_efesien,
                            i.dimensi_mutu_pasien,
                            i.dimensi_mutu_integrasi,

                            s.code AS statuscode,
                            s.master_name AS status,
                            s.description AS statusdescription,
                            s.color AS statuscolor,
                            s.icon AS statusicon,

                            sat.kode,

                            /* ===========================
                            NUMERATOR
                            =========================== */
                            MAX(CASE WHEN it.bulan='01' THEN it.numerator END) AS numerator01,
                            MAX(CASE WHEN it.bulan='02' THEN it.numerator END) AS numerator02,
                            MAX(CASE WHEN it.bulan='03' THEN it.numerator END) AS numerator03,
                            MAX(CASE WHEN it.bulan='04' THEN it.numerator END) AS numerator04,
                            MAX(CASE WHEN it.bulan='05' THEN it.numerator END) AS numerator05,
                            MAX(CASE WHEN it.bulan='06' THEN it.numerator END) AS numerator06,
                            MAX(CASE WHEN it.bulan='07' THEN it.numerator END) AS numerator07,
                            MAX(CASE WHEN it.bulan='08' THEN it.numerator END) AS numerator08,
                            MAX(CASE WHEN it.bulan='09' THEN it.numerator END) AS numerator09,
                            MAX(CASE WHEN it.bulan='10' THEN it.numerator END) AS numerator10,
                            MAX(CASE WHEN it.bulan='11' THEN it.numerator END) AS numerator11,
                            MAX(CASE WHEN it.bulan='12' THEN it.numerator END) AS numerator12,

                            /* ===========================
                            DENUMERATOR
                            =========================== */
                            MAX(CASE WHEN it.bulan='01' THEN it.denumerator END) AS denumerator01,
                            MAX(CASE WHEN it.bulan='02' THEN it.denumerator END) AS denumerator02,
                            MAX(CASE WHEN it.bulan='03' THEN it.denumerator END) AS denumerator03,
                            MAX(CASE WHEN it.bulan='04' THEN it.denumerator END) AS denumerator04,
                            MAX(CASE WHEN it.bulan='05' THEN it.denumerator END) AS denumerator05,
                            MAX(CASE WHEN it.bulan='06' THEN it.denumerator END) AS denumerator06,
                            MAX(CASE WHEN it.bulan='07' THEN it.denumerator END) AS denumerator07,
                            MAX(CASE WHEN it.bulan='08' THEN it.denumerator END) AS denumerator08,
                            MAX(CASE WHEN it.bulan='09' THEN it.denumerator END) AS denumerator09,
                            MAX(CASE WHEN it.bulan='10' THEN it.denumerator END) AS denumerator10,
                            MAX(CASE WHEN it.bulan='11' THEN it.denumerator END) AS denumerator11,
                            MAX(CASE WHEN it.bulan='12' THEN it.denumerator END) AS denumerator12,

                            /* ===========================
                            REASON
                            =========================== */
                            MAX(CASE WHEN it.bulan='01' THEN it.reason END) AS reason01,
                            MAX(CASE WHEN it.bulan='02' THEN it.reason END) AS reason02,
                            MAX(CASE WHEN it.bulan='03' THEN it.reason END) AS reason03,
                            MAX(CASE WHEN it.bulan='04' THEN it.reason END) AS reason04,
                            MAX(CASE WHEN it.bulan='05' THEN it.reason END) AS reason05,
                            MAX(CASE WHEN it.bulan='06' THEN it.reason END) AS reason06,
                            MAX(CASE WHEN it.bulan='07' THEN it.reason END) AS reason07,
                            MAX(CASE WHEN it.bulan='08' THEN it.reason END) AS reason08,
                            MAX(CASE WHEN it.bulan='09' THEN it.reason END) AS reason09,
                            MAX(CASE WHEN it.bulan='10' THEN it.reason END) AS reason10,
                            MAX(CASE WHEN it.bulan='11' THEN it.reason END) AS reason11,
                            MAX(CASE WHEN it.bulan='12' THEN it.reason END) AS reason12,

                            /* ===========================
                            RTL
                            =========================== */
                            MAX(CASE WHEN it.bulan='01' THEN it.rtl END) AS rtl01,
                            MAX(CASE WHEN it.bulan='02' THEN it.rtl END) AS rtl02,
                            MAX(CASE WHEN it.bulan='03' THEN it.rtl END) AS rtl03,
                            MAX(CASE WHEN it.bulan='04' THEN it.rtl END) AS rtl04,
                            MAX(CASE WHEN it.bulan='05' THEN it.rtl END) AS rtl05,
                            MAX(CASE WHEN it.bulan='06' THEN it.rtl END) AS rtl06,
                            MAX(CASE WHEN it.bulan='07' THEN it.rtl END) AS rtl07,
                            MAX(CASE WHEN it.bulan='08' THEN it.rtl END) AS rtl08,
                            MAX(CASE WHEN it.bulan='09' THEN it.rtl END) AS rtl09,
                            MAX(CASE WHEN it.bulan='10' THEN it.rtl END) AS rtl10,
                            MAX(CASE WHEN it.bulan='11' THEN it.rtl END) AS rtl11,
                            MAX(CASE WHEN it.bulan='12' THEN it.rtl END) AS rtl12,

                            /* ===========================
                            PENCAPAIAN (%)
                            =========================== */
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
                            ON j.jenis_indikator_id = a.jenis_indikator_id

                        LEFT JOIN dt01_qi_indikator_it it
                            ON it.referensi_id = a.transaksi_id
                        
                        LEFT JOIN dt01_qi_satuan_ms sat
                            ON sat.satuan_id = i.satuan_id

                        WHERE a.active = '1'
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
                            p.status_id,
                            d.department,
                            u.name,
                            j.jenis,
                            i.indikator,
                            i.definisi,
                            i.dimensi_mutu_keselamatan,
                            i.dimensi_mutu_waktu,
                            i.dimensi_mutu_efektif,
                            i.dimensi_mutu_efesien,
                            i.dimensi_mutu_pasien,
                            i.dimensi_mutu_integrasi,
                            s.code,
                            s.master_name,
                            s.description,
                            s.color,
                            s.icon

                        ORDER BY p.tahun DESC;
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datateam($groupid,$orgid,$userid){
            $query =
                    "
                        SELECT GROUP_CONCAT(
                                    DISTINCT CONCAT(
                                        a.pic, ':',
                                        u.name
                                    )
                                    ORDER BY u.name ASC
                                    SEPARATOR ';'
                            ) AS pic_list
                        FROM dt01_qi_indikator_hd a
                        LEFT JOIN dt01_gen_user_data u
                            ON u.user_id = a.pic
                        WHERE a.active = '1'
                        and   a.group_id='".$groupid."'
                        and   a.org_id='".$orgid."'
                        AND a.department_id IN (
                            SELECT department_id
                            FROM dt01_gen_department_ms
                            WHERE active = '1'
                            and group_id='".$groupid."'
                            and org_id='".$orgid."'
                            AND user_id = '".$userid."'
                        );
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