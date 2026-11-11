<?php

namespace Modules\Risk\Models;

use CodeIgniter\Model;

class RegisterModel extends Model
{
    public function ready(): bool
    {
        return $this->db->tableExists('dt01_risk_register') && $this->db->tableExists('dt01_risk_monitor');
    }

    public function years($group, $org): array
    {
        if (!$this->db->tableExists('dt01_qi_periode_mutu')) { return [(int) date('Y')]; }
        $rows = $this->db->table('dt01_qi_periode_mutu')->distinct()->select('tahun')
            ->where('group_id', $group)->where('org_id', $org)->where('active', '1')
            ->orderBy('tahun', 'DESC')->get()->getResultArray();
        $years = array_values(array_filter(array_map(static function ($row) {
            return preg_match('/^20\d{2}$/', (string) ($row['tahun'] ?? '')) ? (int) $row['tahun'] : null;
        }, $rows), static function ($year) { return $year !== null; }));
        return $years ?: [(int) date('Y')];
    }

    public function departments($group, $org, string $userId = ''): array
    {
        $query = $this->db->table('dt01_gen_department_ms')->select('department_id, department')
            ->where('group_id', $group)->where('org_id', $org)->where('active', '1');
        if ($userId !== '') { $query->where('user_id', $userId); }
        return $query
            ->orderBy('department')->get()->getResultArray();
    }

    public function usedCodes($group, $org): array
    {
        return $this->db->table('dt01_risk_register r')
            ->distinct()->select('r.code, r.department_id, d.department')
            ->join('dt01_gen_department_ms d', 'd.department_id = r.department_id AND d.group_id = r.group_id AND d.org_id = r.org_id', 'left')
            ->where('r.group_id', $group)->where('r.org_id', $org)->where('r.active', '1')
            ->orderBy('r.code')->orderBy('d.department')->get()->getResultArray();
    }

    private function scoped($group, $org)
    {
        return $this->db->table('dt01_risk_register r')->select('r.*, d.department')
            ->join('dt01_gen_department_ms d', 'd.department_id = r.department_id AND d.group_id = r.group_id AND d.org_id = r.org_id', 'left')
            ->where('r.group_id', $group)->where('r.org_id', $org)->where('r.active', '1');
    }

    public function risks($group, $org, int $year, string $department, string $userId = ''): array
    {
        $builder = $this->scoped($group, $org)->where('r.year', $year);
        if ($department !== '') {
            $builder->where('r.department_id', $department);
        }
        if ($userId !== '') { $builder->where('d.user_id', $userId); }
        return $builder->orderBy('d.department')->orderBy('r.code')->get()->getResultArray();
    }

    public function risk($group, $org, string $id): ?array
    {
        return $this->scoped($group, $org)->where('r.risk_id', $id)->get()->getRowArray();
    }

    public function saveRisk($group, $org, string $id, array $data): bool
    {
        if ($id === '') {
            return $this->db->table('dt01_risk_register')->insert($data);
        }
        return $this->db->table('dt01_risk_register')->where('group_id', $group)->where('org_id', $org)
            ->where('risk_id', $id)->where('active', '1')->update($data);
    }

    public function monitoring(string $riskId): array
    {
        return $this->db->table('dt01_risk_monitor')->where('risk_id', $riskId)->orderBy('quarter')->get()->getResultArray();
    }

    public function saveMonitoring(string $riskId, int $quarter, array $data): bool
    {
        $existing = $this->db->table('dt01_risk_monitor')->where('risk_id', $riskId)->where('quarter', $quarter)->get()->getRowArray();
        if ($existing) {
            unset($data['monitor_id'], $data['created_by'], $data['created_date']);
            // Baseline disimpan saat pemantauan pertama agar perubahan register tidak mengubah riwayat.
            unset($data['baseline_score']);
            return $this->db->table('dt01_risk_monitor')->where('monitor_id', $existing['monitor_id'])->update($data);
        }
        return $this->db->table('dt01_risk_monitor')->insert($data);
    }
}
