<?php

namespace Modules\Qi\Models;

use CodeIgniter\Model;

class PdsaModel extends Model
{
    protected $DBGroup = 'default';

    public function departments($groupId, $orgId, $userId)
    {
        return $this->db->table('dt01_gen_department_ms')
            ->select('department_id, department')->where('active', '1')
            ->where('group_id', $groupId)->where('org_id', $orgId)
            ->where('user_id', $userId)->orderBy('department')->get()->getResult();
    }

    public function periods($groupId, $orgId)
    {
        return $this->db->table('dt01_qi_periode_mutu')->select('periode_id, tahun')
            ->where('active', '1')->where('group_id', $groupId)->where('org_id', $orgId)
            ->orderBy('tahun', 'DESC')->get()->getResult();
    }

    public function indicators($groupId, $orgId, $userId)
    {
        $departmentIds = array_map(static function ($row) { return (string) $row->department_id; }, $this->departments($groupId, $orgId, $userId));
        if (!$departmentIds) return [];
        return $this->db->table('dt01_qi_indikator_hd h')
            ->select('h.transaksi_id, h.department_id, h.periode_id, i.indikator, d.department, p.tahun')
            ->join('dt01_qi_indikator_ms i', 'i.indikator_id = h.indikator_id', 'left')
            ->join('dt01_gen_department_ms d', 'd.department_id = h.department_id', 'left')
            ->join('dt01_qi_periode_mutu p', 'p.periode_id = h.periode_id', 'left')
            ->where('h.active', '1')->where('h.group_id', $groupId)->where('h.org_id', $orgId)
            ->whereIn('h.department_id', $departmentIds)
            ->orderBy('p.tahun', 'DESC')->get()->getResult();
    }

    public function indicatorReference($referenceId, $groupId, $orgId, $userId)
    {
        $departmentIds = array_map(static function ($row) { return (string) $row->department_id; }, $this->departments($groupId, $orgId, $userId));
        if (!$departmentIds) return null;
        return $this->db->table('dt01_qi_indikator_hd h')
            ->select('h.transaksi_id, h.department_id, h.periode_id, i.indikator, d.department, p.tahun')
            ->join('dt01_qi_indikator_ms i', 'i.indikator_id = h.indikator_id', 'left')
            ->join('dt01_gen_department_ms d', 'd.department_id = h.department_id', 'left')
            ->join('dt01_qi_periode_mutu p', 'p.periode_id = h.periode_id', 'left')
            ->where('h.transaksi_id', $referenceId)->where('h.group_id', $groupId)->where('h.org_id', $orgId)
            ->where('h.active', '1')->whereIn('h.department_id', $departmentIds)->get()->getRow();
    }

    public function periodExists($periodId, $groupId, $orgId)
    {
        return $this->db->table('dt01_qi_periode_mutu')->where('periode_id', $periodId)
            ->where('group_id', $groupId)->where('org_id', $orgId)->where('active', '1')->countAllResults() > 0;
    }

    public function indicatorExists($referenceId, $groupId, $orgId, $departmentId)
    {
        return $this->db->table('dt01_qi_indikator_hd')->where('transaksi_id', $referenceId)
            ->where('group_id', $groupId)->where('org_id', $orgId)->where('department_id', $departmentId)
            ->where('active', '1')->countAllResults() > 0;
    }

    public function listCycles($groupId, $orgId, $userId, $referenceId = '')
    {
        $departmentIds = array_column($this->departments($groupId, $orgId, $userId), 'department_id');
        $builder = $this->db->table('dt01_qi_pdsa x')
            ->select('x.*, d.department, p.tahun, i.indikator')
            ->join('dt01_gen_department_ms d', 'd.department_id = x.department_id', 'left')
            ->join('dt01_qi_periode_mutu p', 'p.periode_id = x.periode_id', 'left')
            ->join('dt01_qi_indikator_hd ih', 'ih.transaksi_id = x.indikator_ref_id', 'left')
            ->join('dt01_qi_indikator_ms i', 'i.indikator_id = ih.indikator_id', 'left')
            ->where('x.active', '1')->where('x.group_id', $groupId)->where('x.org_id', $orgId);
        if ($referenceId !== '') $builder->where('x.indikator_ref_id', $referenceId);
        $rows = $builder->orderBy('x.last_update_date', 'DESC')->get()->getResult();
        if (!$rows) return [];

        $ids = array_map(static function ($row) { return (string) $row->pdsa_id; }, $rows);
        $counts = $this->db->table('dt01_qi_pdsa_items')->select('pdsa_id, COUNT(*) AS item_count')
            ->where('active', '1')->whereIn('pdsa_id', $ids)->groupBy('pdsa_id')->get()->getResultArray();
        $countMap = [];
        foreach ($counts as $count) $countMap[(string) $count['pdsa_id']] = (int) $count['item_count'];
        foreach ($rows as $row) {
            $row->item_count = $countMap[(string) $row->pdsa_id] ?? 0;
            $row->can_manage = in_array((string) $row->department_id, array_map('strval', $departmentIds), true);
        }
        return $rows;
    }

    public function getCycle($id, $groupId, $orgId, $userId)
    {
        foreach ($this->listCycles($groupId, $orgId, $userId) as $cycle) {
            if ((string) $cycle->pdsa_id === (string) $id && $cycle->can_manage) {
                return $cycle;
            }
        }
        return null;
    }

    public function getCycleForOrganization($id, $groupId, $orgId)
    {
        return $this->db->table('dt01_qi_pdsa x')
            ->select('x.*, d.department, p.tahun, i.indikator')
            ->join('dt01_gen_department_ms d', 'd.department_id = x.department_id', 'left')
            ->join('dt01_qi_periode_mutu p', 'p.periode_id = x.periode_id', 'left')
            ->join('dt01_qi_indikator_hd ih', 'ih.transaksi_id = x.indikator_ref_id', 'left')
            ->join('dt01_qi_indikator_ms i', 'i.indikator_id = ih.indikator_id', 'left')
            ->where('x.pdsa_id', $id)->where('x.group_id', $groupId)->where('x.org_id', $orgId)
            ->where('x.active', '1')->get()->getRow();
    }

    public function saveCycle($id, array $data, $groupId, $orgId)
    {
        if ($id === '') {
            return $this->db->table('dt01_qi_pdsa')->insert($data);
        }
        return $this->db->table('dt01_qi_pdsa')->where('pdsa_id', $id)
            ->where('group_id', $groupId)->where('org_id', $orgId)->where('active', '1')->update($data);
    }

    public function items($pdsaId)
    {
        return $this->db->table('dt01_qi_pdsa_items')->where('pdsa_id', $pdsaId)->where('active', '1')
            ->orderBy('phase')->orderBy('sort_order')->orderBy('created_date')->get()->getResult();
    }

    public function replaceItems($pdsaId, array $items, $userId)
    {
        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();
        $this->db->table('dt01_qi_pdsa_items')->where('pdsa_id', $pdsaId)->where('active', '1')->update([
            'active' => '0', 'last_update_by' => $userId, 'last_update_date' => $now,
        ]);
        foreach ($items as $index => $item) {
            if (trim((string) ($item['item_text'] ?? '')) === '') continue;
            $id = function_exists('generateuuid') ? generateuuid() : bin2hex(random_bytes(16));
            $this->db->table('dt01_qi_pdsa_items')->insert([
                'pdsa_item_id' => $id, 'pdsa_id' => $pdsaId,
                'phase' => $item['phase'], 'item_text' => trim((string) $item['item_text']),
                'sort_order' => (int) $index + 1, 'active' => '1', 'created_by' => $userId,
                'created_date' => $now, 'last_update_by' => $userId, 'last_update_date' => $now,
            ]);
        }
        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return false;
        }
        return $this->db->transCommit();
    }

    public function archiveCycle($id, $groupId, $orgId, $departmentIds, array $data)
    {
        if (!$departmentIds) return false;
        $this->db->transBegin();
        $this->db->table('dt01_qi_pdsa_items')->where('pdsa_id', $id)->where('active', '1')->update([
            'active' => '0', 'last_update_by' => $data['last_update_by'], 'last_update_date' => $data['last_update_date'],
        ]);
        $this->db->table('dt01_qi_pdsa')->where('pdsa_id', $id)->where('group_id', $groupId)
            ->where('org_id', $orgId)->whereIn('department_id', $departmentIds)->where('active', '1')->update($data);
        if ($this->db->transStatus() === false || $this->db->affectedRows() === 0) {
            $this->db->transRollback();
            return false;
        }
        return $this->db->transCommit();
    }
}
