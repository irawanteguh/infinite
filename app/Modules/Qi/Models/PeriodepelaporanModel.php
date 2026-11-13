<?php

namespace Modules\Qi\Models;

use CodeIgniter\Model;

class PeriodepelaporanModel extends Model{
    protected $DBGroup = null;

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
        return $recordset->getResult();
    }

    function datamasterperiodepelaporan($groupid,$orgid){
        $query =
                "
                    select
                        a.periode_id,
                        a.last_update_by,
                        a.active,
                        a.tahun,
                        a.user_id,
                        a.status_id,
                        date_format(a.last_update_date, '%d.%m.%Y %H:%i:%s') as dibuattgl,

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
                        ) as picindikator,
                        
                        (
                            select group_concat(
                                x.user_id, ':',
                                b.name
                                separator ';'
                            )
                            from dt01_qi_komite_mutu_hd x
                            left join dt01_gen_user_data b
                                on b.user_id = x.user_id
                            where x.active = '1'
                            and x.periode_id = a.periode_id
                        ) as komite

                    from dt01_qi_periode_mutu a

                    left join dt01_gen_user_data u1
                        on u1.user_id = a.user_id

                    left join dt01_gen_user_data u2
                        on u2.user_id = a.last_update_by

                    left join dt01_gen_master_ms s
                        on s.code = a.status_id
                        and s.active = '1'

                    where a.active = '1'
                    and a.group_id = '".$groupid."'
                    and a.org_id = '".$orgid."'

                    order by a.tahun desc;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function insertmasterperiode($data){
        return $this->db->table('dt01_qi_periode_mutu')->insert($data);
    }

    function updatemasterperiode($periodeid, $data){
        return $this->db->table('dt01_qi_periode_mutu')->where('periode_id', $periodeid)->update($data);
    }

    function periodeExists($periodeid, $groupid, $orgid){
        return $this->db->table('dt01_qi_periode_mutu')
            ->where('periode_id', $periodeid)
            ->where('group_id', $groupid)
            ->where('org_id', $orgid)
            ->where('active', '1')
            ->countAllResults() > 0;
    }

    function teamMembers($periodeid, $groupid, $orgid){
        return $this->db->table('dt01_qi_komite_mutu_hd x')
            ->select('x.user_id, u.name')
            ->join('dt01_qi_periode_mutu p', 'p.periode_id = x.periode_id', 'inner')
            ->join('dt01_gen_user_data u', 'u.user_id = x.user_id', 'left')
            ->where('x.periode_id', $periodeid)
            ->where('x.active', '1')
            ->where('p.group_id', $groupid)
            ->where('p.org_id', $orgid)
            ->orderBy('u.name', 'ASC')
            ->get()->getResult();
    }

    function replaceTeamMembers($periodeid, array $memberids, $userid, $groupid, $orgid){
        $table = $this->db->table('dt01_qi_komite_mutu_hd');
        $current = $table->select('user_id')->where('periode_id', $periodeid)->get()->getResultArray();
        $currentIds = array_map(static function ($row) { return (string) $row['user_id']; }, $current);

        $this->db->transStart();

        $removed = array_values(array_diff($currentIds, $memberids));
        if ($removed) {
            $this->db->table('dt01_qi_komite_mutu_hd')
                ->where('periode_id', $periodeid)
                ->whereIn('user_id', $removed)
                ->update(['active' => '0']);
        }

        foreach ($memberids as $memberid) {
            if (in_array($memberid, $currentIds, true)) {
                $this->db->table('dt01_qi_komite_mutu_hd')
                    ->where('periode_id', $periodeid)
                    ->where('user_id', $memberid)
                    ->update(['active' => '1']);
                continue;
            }

            $this->db->table('dt01_qi_komite_mutu_hd')->insert([
                'periode_id' => $periodeid,
                'user_id' => $memberid,
                'group_id' => $groupid,
                'org_id' => $orgid,
                'active' => '1',
                'created_by' => $userid
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

}
