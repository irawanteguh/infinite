<?php

namespace Modules\Qi\Models;

use CodeIgniter\Model;

class IndikatorunitModel extends Model{
    protected $DBGroup = 'default';

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
        return $recordset->getResult();
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
        return $recordset->getResult();
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
        return $recordset->getResult();
    }

    function datateam($groupid,$orgid,$userid){
        $query =
                "
                    select
                        group_concat(
                            distinct concat(
                                a.pic, ':',
                                u.name
                            )
                            order by u.name asc
                            separator ';'
                        ) as pic_list
                    from dt01_qi_indikator_hd a
                    left join dt01_gen_user_data u
                        on u.user_id = a.pic
                    where a.active = '1'
                        and a.group_id = '".$groupid."'
                        and a.org_id = '".$orgid."'
                        and a.department_id in (
                            select department_id
                            from dt01_gen_department_ms
                            where active = '1'
                                and group_id = '".$groupid."'
                                and org_id = '".$orgid."'
                                and user_id = '".$userid."'
                        );
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function datauser($groupid,$orgid,$departmentid){
        $query =
                "
                    select a.user_id, name
                    from dt01_gen_user_data a
                    where a.active='1'
                    and   a.group_id='".$groupid."'
                    and   a.org_id='".$orgid."'
                    and   a.department_id='".$departmentid."'
                    order by name asc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function dataindikatorunit($groupid,$orgid,$userid){
        $query =
                "
                    SELECT
                        a.transaksi_id,
                        a.periode_id,
                        a.target,
                        a.pic,
                        a.status_id statusidindikatorunit,
                        a.department_id,
                        a.indikator_id,
                        DATE_FORMAT(a.created_date,'%d.%m.%Y %H:%i:%s') AS dibuattgl,

                        p.tahun,
                        p.status_id,
                        d.department,
                        u.name AS picname,

                        j.jenis,
                        i.indikator,
                        i.target_capaian,
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
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '01' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai01,
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '02' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai02,
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '03' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai03,
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '04' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai04,
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '05' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai05,
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '06' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai06,
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '07' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai07,
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '08' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai08,
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '09' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai09,
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '10' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai10,
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '11' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai11,
                        ROUND(LEAST(100, MAX(CASE WHEN it.bulan = '12' THEN CASE WHEN COALESCE(it.numerator, 0) = 0 AND COALESCE(it.denumerator, 0) = 0 THEN 100 WHEN COALESCE(it.denumerator, 0) = 0 THEN 0 ELSE (it.numerator / it.denumerator) * 100 END END)), 2) AS nilai12

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
                        AND it.active = '1'
                    
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
                        i.target_capaian,
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

                    order by p.tahun desc;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function updateindikator($transaksiid, $data){
        return $this->db->table('dt01_qi_indikator_hd')->where('transaksi_id', $transaksiid)->update($data);
    }

    public function indicatorForPicChange($transaksiId, $groupId, $orgId, $userId)
    {
        $indicator = $this->db->table('dt01_qi_indikator_hd a')
            ->select('a.transaksi_id, a.department_id, a.pic')
            ->join('dt01_qi_periode_mutu p', 'p.periode_id = a.periode_id', 'inner')
            ->where('a.transaksi_id', $transaksiId)
            ->where('a.group_id', $groupId)
            ->where('a.org_id', $orgId)
            ->where('a.active', '1')
            ->where('a.status_id', '1')
            ->where('p.status_id', '3')
            ->get()->getRow();

        if (!$indicator || !$this->departmentManagedBy($indicator->department_id, $groupId, $orgId, $userId)) {
            return null;
        }

        return $indicator;
    }

    public function departmentManagedBy($departmentId, $groupId, $orgId, $userId)
    {
        return $this->db->table('dt01_gen_department_ms')
            ->where('department_id', $departmentId)
            ->where('group_id', $groupId)
            ->where('org_id', $orgId)
            ->where('user_id', $userId)
            ->where('active', '1')
            ->countAllResults() > 0;
    }

    public function usersForDepartment($groupId, $orgId, $departmentId)
    {
        return $this->db->table('dt01_gen_user_data')
            ->select('user_id, name')
            ->where('group_id', $groupId)
            ->where('org_id', $orgId)
            ->where('department_id', $departmentId)
            ->where('active', '1')
            ->orderBy('name', 'ASC')
            ->get()->getResult();
    }

    public function userBelongsToDepartment($userId, $groupId, $orgId, $departmentId)
    {
        return $this->db->table('dt01_gen_user_data')
            ->where('user_id', $userId)
            ->where('group_id', $groupId)
            ->where('org_id', $orgId)
            ->where('department_id', $departmentId)
            ->where('active', '1')
            ->countAllResults() > 0;
    }

    public function changeIndicatorPic($transaksiId, $userId, $groupId, $orgId, $actorId)
    {
        $indicator = $this->indicatorForPicChange($transaksiId, $groupId, $orgId, $actorId);
        if (!$indicator || !$this->userBelongsToDepartment($userId, $groupId, $orgId, $indicator->department_id)) {
            return false;
        }

        $updated = $this->db->table('dt01_qi_indikator_hd')
            ->where('transaksi_id', $transaksiId)
            ->where('group_id', $groupId)
            ->where('org_id', $orgId)
            ->where('department_id', $indicator->department_id)
            ->where('active', '1')
            ->where('status_id', '1')
            ->update([
                'pic' => $userId,
                'last_update_by' => $actorId,
                'last_update_date' => date('Y-m-d H:i:s'),
            ]);

        return $updated && $this->db->affectedRows() >= 0;
    }

    function insertindikatorunit($data){
        return $this->db->table('dt01_qi_indikator_hd')->insert($data);
    }

    function insertnilaiindikator($data){
        return $this->db->table('dt01_qi_indikator_it')->insert($data);
    }

    function indikatorBelongsTo($transaksiid, $indikatorid, $groupid, $orgid, $userid){
        $departments = $this->db->table('dt01_gen_department_ms')
            ->select('department_id')
            ->where('active', '1')
            ->where('group_id', $groupid)
            ->where('org_id', $orgid)
            ->where('user_id', $userid)
            ->get()->getResultArray();
        $departmentIds = array_column($departments, 'department_id');
        if (empty($departmentIds)) {
            return false;
        }

        return $this->db->table('dt01_qi_indikator_hd')
            ->where('transaksi_id', $transaksiid)
            ->where('indikator_id', $indikatorid)
            ->where('group_id', $groupid)
            ->where('org_id', $orgid)
            ->where('active', '1')
            ->whereIn('department_id', $departmentIds)
            ->countAllResults() > 0;
    }

    function indikatorReferenceBelongsTo($transaksiid, $groupid, $orgid, $userid){
        $departments = $this->db->table('dt01_gen_department_ms')
            ->select('department_id')
            ->where('active', '1')
            ->where('group_id', $groupid)
            ->where('org_id', $orgid)
            ->where('user_id', $userid)
            ->get()->getResultArray();
        $departmentIds = array_column($departments, 'department_id');
        if (empty($departmentIds)) {
            return false;
        }

        return $this->db->table('dt01_qi_indikator_hd')
            ->where('transaksi_id', $transaksiid)
            ->where('group_id', $groupid)
            ->where('org_id', $orgid)
            ->where('active', '1')
            ->whereIn('department_id', $departmentIds)
            ->countAllResults() > 0;
    }

    function saveNilaiindikator($data){
        $builder = $this->db->table('dt01_qi_indikator_it');
        $exists = $builder->where('referensi_id', $data['referensi_id'])
            ->where('active', '1')
            ->where('bulan', $data['bulan'])
            ->where('group_id', $data['group_id'])
            ->where('org_id', $data['org_id'])
            ->countAllResults() > 0;

        if ($exists) {
            unset($data['created_by']);
            return $this->db->table('dt01_qi_indikator_it')
                ->where('active', '1')
                ->where('referensi_id', $data['referensi_id'])
                ->where('bulan', $data['bulan'])
                ->where('group_id', $data['group_id'])
                ->where('org_id', $data['org_id'])
                ->update($data);
        }

        return $this->insertnilaiindikator($data);
    }

}
