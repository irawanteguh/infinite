<?php

namespace Modules\Qi\Models;

use CodeIgniter\Model;

class MasterindikatorModel extends Model{
    protected $DBGroup = null;

    function datamasterindikator($groupid,$orgid){
        $query =
                "
                    select a.indikator_id, indikator_code, indikator, a.jenis_indikator_id, definisi, dasar_pemikiran, formula, populasi, instrument, tujuan, last_update_by, numerator, denumerator, inklusi, eksklusi, a.active, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                        dimensi_mutu_keselamatan, dimensi_mutu_waktu, dimensi_mutu_efektif, dimensi_mutu_efesien, dimensi_mutu_pasien, dimensi_mutu_integrasi,
                        satuan_id, frekuensi_id, sumber_id, donabedian_id, target_capaian, benchmark_id,
                        b.jenis,
                        (select name from dt01_gen_user_data where user_id=a.last_update_by)dibuatoleh,
                        (select concat(satuan,' /',keterangan) from dt01_qi_satuan_ms where satuan_id=a.satuan_id)satuan

                    from dt01_qi_indikator_ms a
                    left join dt01_qi_jenis_indikator_ms b
                    on b.jenis_indikator_id=a.jenis_indikator_id
                    where a.group_id='".$groupid."'
                    and   a.org_id='".$orgid."'
                    order by last_update_date desc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
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
        return $recordset->getResult();
    }

    function datamasterjenisindikator(){
        $query =
                "
                    select a.jenis_indikator_id, jenis
                    from dt01_qi_jenis_indikator_ms a
                    where a.active='1'
                    order by jenis asc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
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
        return $recordset->getResult();
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
        return $recordset->getResult();
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
        return $recordset->getResult();
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
        return $recordset->getResult();
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
        return $recordset->getResult();
    }

    function updatemasterindikator($indikatorid, $data){
        return $this->db->table('dt01_qi_indikator_ms')->where('indikator_id', $indikatorid)->update($data);
    }

    function insertmasterindikator($data){
        return $this->db->table('dt01_qi_indikator_ms')->insert($data);
    }
}