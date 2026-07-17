<?php
    class Modelmasterindikator extends CI_Model{
        
        function dataindikator($groupid,$orgid){
            $query =
                    "
                        select a.indikator_id, indikator, definisi, dasar_pemikiran, formula, populasi, metode, instrument, tujuan, last_update_by, numerator, denumerator, inklusi, eksklusi, active, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                            dimensi_mutu_keselamatan, dimensi_mutu_waktu, dimensi_mutu_efektif, dimensi_mutu_efesien, dimensi_mutu_pasien, dimensi_mutu_integrasi,
                            satuan_id, frekuensi_id, sumber_id, donabedian_id, target_capaian, benchmark_id,
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

        function datamastersatuan(){
            $query =
                    "
                        select a.satuan_id, concat(satuan,' / ',keterangan)keterangan
                        from dt01_qi_satuan_ms a
                        where a.active='1'
                        order by sort asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datamasterfrekuensi(){
            $query =
                    "
                        select a.frekuensi_id, frekuensi
                        from dt01_qi_frekuensi_ms a
                        where a.active='1'
                        order by sort asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datamastersumber(){
            $query =
                    "
                        select a.sumber_id, sumber
                        from dt01_qi_sumber_ms a
                        where a.active='1'
                        order by sort asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datamasterdonabedian(){
            $query =
                    "
                        select a.donabedian_id, donabedian
                        from dt01_qi_donabedian_ms a
                        where a.active='1'
                        order by sort asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datamastertarget(){
            $query =
                    "
                        SELECT 'H' AS value, 'Higher is Better' AS label
                        UNION ALL
                        SELECT 'L', 'Lower is Better'
                        UNION ALL
                        SELECT 'R', 'Range';
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function datamasterbenchmark(){
            $query =
                    "
                        select a.benchmark_id, benchmark
                        from dt01_qi_benchmark_ms a
                        where a.active='1'
                        order by sort asc
                    ";

            $recordset = $this->db->query($query);
            $recordset = $recordset->result();
            return $recordset;
        }

        function updatemasterindikator($indikatorid, $data){           
            $sql =   $this->db->update("dt01_qi_indikator_ms",$data,array("indikator_id"=>$indikatorid));
            return $sql;
        }



    }
?>