<?php

namespace Modules\Risk\Models;

use CodeIgniter\Model;

class MasterriskModel extends Model
{
    public function ready(): bool
    {
        return $this->db->tableExists('dt01_risk_code_ms');
    }

    public function codes($group, $org): array
    {
        $rows = $this->db->table('dt01_risk_code_ms c')->select('c.code_id, c.code, c.department_id, d.department')
            ->join('dt01_gen_department_ms d', 'd.department_id = c.department_id AND d.group_id = c.group_id AND d.org_id = c.org_id', 'left')
            ->where('c.group_id', $group)->where('c.org_id', $org)->orderBy('c.code')->get()->getResultArray();
        usort($rows, static function ($a, $b) { return strnatcasecmp($a['code'], $b['code']); });
        return $rows;
    }

    public function exists($group, $org, string $code, string $exceptId = ''): bool
    {
        $query = $this->db->table('dt01_risk_code_ms')->where('group_id', $group)->where('org_id', $org)->where('code', $code);
        if ($exceptId !== '') { $query->where('code_id !=', $exceptId); }
        return $query->countAllResults() > 0;
    }

    public function code($group, $org, string $id): ?array
    {
        return $this->db->table('dt01_risk_code_ms')->where('group_id', $group)->where('org_id', $org)
            ->where('code_id', $id)->get()->getRowArray();
    }

    public function updateCode($group, $org, string $id, array $data): bool
    {
        if (!$this->code($group, $org, $id)) { return false; }
        return $this->db->table('dt01_risk_code_ms')->where('group_id', $group)->where('org_id', $org)
            ->where('code_id', $id)->update(['code' => $data['code'], 'department_id' => $data['department_id']]);
    }

    public function add(array $data): bool
    {
        return $this->db->table('dt01_risk_code_ms')->insert($data);
    }

    public function categoriesReady(): bool
    {
        return $this->db->tableExists('dt01_risk_category_ms');
    }

    public function categories($group, $org): array
    {
        return $this->db->table('dt01_risk_category_ms')->select('category_id, name, description')
            ->where('group_id', $group)->where('org_id', $org)->orderBy('name')->get()->getResultArray();
    }

    public function categoryExists($group, $org, string $name): bool
    {
        return $this->db->table('dt01_risk_category_ms')->where('group_id', $group)->where('org_id', $org)
            ->where('name', $name)->countAllResults() > 0;
    }

    public function addCategory(array $data): bool
    {
        return $this->db->table('dt01_risk_category_ms')->insert($data);
    }
}
