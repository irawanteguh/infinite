<?php

namespace Modules\Qi\Models;

use CodeIgniter\Model;

class FishboneModel extends Model
{
    protected $DBGroup = 'default';

    public function departments($groupid, $orgid)
    {
        return $this->db->table('dt01_gen_department_ms')
            ->select('department_id, department')->where('active', '1')
            ->where('group_id', $groupid)->where('org_id', $orgid)
            ->orderBy('department')->get()->getResult();
    }

    public function periods($groupid, $orgid)
    {
        return $this->db->table('dt01_qi_periode_mutu')->distinct()
            ->select('periode_id, tahun')->where('active', '1')
            ->where('group_id', $groupid)->where('org_id', $orgid)
            ->orderBy('tahun', 'DESC')->get()->getResult();
    }

    public function departmentExists($departmentId, $groupid, $orgid)
    {
        return $this->db->table('dt01_gen_department_ms')->where('department_id', $departmentId)
            ->where('group_id', $groupid)->where('org_id', $orgid)->where('active', '1')->countAllResults() > 0;
    }

    public function periodExists($periodId, $groupid, $orgid)
    {
        return $this->db->table('dt01_qi_periode_mutu')->where('periode_id', $periodId)
            ->where('group_id', $groupid)->where('org_id', $orgid)->where('active', '1')->countAllResults() > 0;
    }

    public function analyses($groupid, $orgid, $referenceId = '')
    {
        $builder = $this->db->table('dt01_qi_fishbone_hd f')
            ->select('f.fishbone_id, f.problem, f.department_id, f.periode_id, f.indikator_ref_id, d.department, p.tahun, f.created_date, i.indikator')
            ->join('dt01_gen_department_ms d', 'd.department_id = f.department_id', 'left')
            ->join('dt01_qi_periode_mutu p', 'p.periode_id = f.periode_id', 'left')
            ->join('dt01_qi_indikator_hd ih', 'ih.transaksi_id = f.indikator_ref_id AND ih.group_id = f.group_id AND ih.org_id = f.org_id', 'left')
            ->join('dt01_qi_indikator_ms i', 'i.indikator_id = ih.indikator_id', 'left')
            ->where('f.active', '1')->where('f.group_id', $groupid)->where('f.org_id', $orgid);
        if ($referenceId !== '') {
            $builder->where('f.indikator_ref_id', $referenceId);
        }
        return $builder->orderBy('f.created_date', 'DESC')->get()->getResult();
    }

    public function indicatorReference($referenceId, $groupid, $orgid)
    {
        return $this->db->table('dt01_qi_indikator_hd h')
            ->select('h.transaksi_id, h.indikator_id, h.department_id, h.periode_id, i.indikator, d.department, p.tahun')
            ->join('dt01_qi_indikator_ms i', 'i.indikator_id = h.indikator_id', 'left')
            ->join('dt01_gen_department_ms d', 'd.department_id = h.department_id', 'left')
            ->join('dt01_qi_periode_mutu p', 'p.periode_id = h.periode_id', 'left')
            ->where('h.transaksi_id', $referenceId)->where('h.group_id', $groupid)->where('h.org_id', $orgid)
            ->where('h.active', '1')->get()->getRow();
    }

    public function analysis($id, $groupid, $orgid)
    {
        return $this->db->table('dt01_qi_fishbone_hd f')
            ->select('f.*, d.department')->join('dt01_gen_department_ms d', 'd.department_id = f.department_id', 'left')
            ->where('f.fishbone_id', $id)->where('f.group_id', $groupid)->where('f.org_id', $orgid)
            ->where('f.active', '1')->get()->getRow();
    }

    public function causes($id)
    {
        return $this->db->table('dt01_qi_fishbone_dt')->where('fishbone_id', $id)
            ->where('active', '1')->orderBy('created_date')->get()->getResult();
    }

    public function saveAnalysis($id, array $data, $groupid, $orgid)
    {
        if ($id !== '') {
            $this->db->table('dt01_qi_fishbone_hd')->where('fishbone_id', $id)
                ->where('group_id', $groupid)->where('org_id', $orgid)->where('active', '1')->update($data);
            return $this->db->affectedRows() >= 0;
        }
        return $this->db->table('dt01_qi_fishbone_hd')->insert($data);
    }

    public function saveCause($id, array $data, $fishboneId)
    {
        if ($id !== '') {
            return $this->db->table('dt01_qi_fishbone_dt')->where('cause_id', $id)
                ->where('fishbone_id', $fishboneId)->where('active', '1')->update($data);
        }
        return $this->db->table('dt01_qi_fishbone_dt')->insert($data);
    }

    public function deleteCause($causeId, $fishboneId, array $data)
    {
        return $this->db->table('dt01_qi_fishbone_dt')->where('cause_id', $causeId)
            ->where('fishbone_id', $fishboneId)->where('active', '1')->update($data);
    }
}
