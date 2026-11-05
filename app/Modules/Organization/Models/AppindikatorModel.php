<?php

namespace Modules\Organization\Models;

use CodeIgniter\Model;

class AppindikatorModel extends Model{
    protected $DBGroup = 'default';

    function datamasterindikator($groupid,$orgid){
        $query =
                "
                    select a.transaksi_id, approval_by, a.created_by, target, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl, date_format(a.approval_date, '%d.%m.%Y %H:%i:%s')approvaldate,  status_id as statusidindikatorunit,
                        b.indikator_code, indikator, definisi, dasar_pemikiran, formula, populasi, instrument, tujuan, numerator, denumerator, inklusi, eksklusi,
                        b.dimensi_mutu_keselamatan, dimensi_mutu_waktu, dimensi_mutu_efektif, dimensi_mutu_efesien, dimensi_mutu_pasien, dimensi_mutu_integrasi,
                        b.satuan_id, frekuensi_id, sumber_id, donabedian_id, target_capaian, benchmark_id,
                        c.department,
                        d.name as dibuatoleh,
                        concat(e.satuan,' /',e.keterangan) as satuan,
                        f.name as divalidasioleh,
                        g.jenis
                        
                    from dt01_qi_indikator_hd a
                    join dt01_qi_indikator_ms b
                    on b.indikator_id=a.indikator_id
                    join dt01_gen_department_ms c
                    on c.department_id=a.department_id
                    join dt01_gen_user_data d
                    on d.user_id=a.created_by
                    left join dt01_qi_satuan_ms e
                    on e.satuan_id=b.satuan_id
                    left join dt01_gen_user_data f
                    on f.user_id=a.approval_by
                    left join dt01_qi_jenis_indikator_ms g
                    on g.jenis_indikator_id=b.jenis_indikator_id
                    where a.active='1'
                    and   a.group_id='".$groupid."'
                    and   a.org_id='".$orgid."'
                    order by a.created_date asc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    public function updateindikator($transaksiid, $data){
        return $this->db->table('dt01_qi_indikator_hd')->where('transaksi_id', $transaksiid)->update($data);
    }

}