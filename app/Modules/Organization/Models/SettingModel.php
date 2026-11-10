<?php

namespace Modules\Organization\Models;

use CodeIgniter\Model;
use Modules\Organization\Services\IndicatorDeadline;

class SettingModel extends Model
{
    public function ready()
    {
        return $this->db->tableExists('dt01_qi_input_setting') && $this->db->tableExists('dt01_qi_input_grant');
    }

    private function scoped($table)
    {
        return $this->db->table($table)->where('group_id', session()->get('groupid'))->where('org_id', session()->get('orgid'));
    }

    public function setting()
    {
        return $this->scoped('dt01_qi_input_setting')->select('deadline_day AS deadline_day')->get()->getRow();
    }

    public function departments()
    {
        return $this->scoped('dt01_gen_department_ms')->select('department_id AS department_id, department AS department')->where('active', '1')->orderBy('department')->get()->getResult();
    }

    public function grants()
    {
        $builder = $this->scoped('dt01_qi_input_grant')->select('grant_id AS grant_id, department_id AS department_id, granted_at AS granted_at, expires_at AS expires_at');
        $builder->select($this->supportsDeactivation() ? 'active AS active' : "'1' AS active", false);
        return $builder->orderBy('granted_at', 'DESC')->limit(100)->get()->getResult();
    }

    public function supportsDeactivation()
    {
        return $this->db->fieldExists('active', 'dt01_qi_input_grant');
    }

    private function activeGrants()
    {
        $builder = $this->scoped('dt01_qi_input_grant');
        return $this->supportsDeactivation() ? $builder->where('active', '1') : $builder;
    }

    public function deactivateGrant($grantId)
    {
        if (!$this->supportsDeactivation() || $grantId === '') {
            return false;
        }
        $updated = $this->activeGrants()->where('grant_id', $grantId)->update([
            'active' => '0',
            'deactivated_by' => session()->get('userid'),
            'deactivated_at' => date('Y-m-d H:i:s'),
        ]);
        return $updated && $this->db->affectedRows() > 0;
    }

    public function activeGrantExpiries()
    {
        return $this->activeGrants()->select('department_id AS department_id, MAX(expires_at) AS expires_at')
            ->groupBy('department_id')->get()->getResult();
    }

    public function saveSetting($day)
    {
        $data = ['deadline_day' => $day, 'updated_by' => session()->get('userid'), 'updated_at' => date('Y-m-d H:i:s')];
        if ($this->setting()) {
            return $this->scoped('dt01_qi_input_setting')->update($data);
        }
        return $this->db->table('dt01_qi_input_setting')->insert($data + ['group_id' => session()->get('groupid'), 'org_id' => session()->get('orgid')]);
    }

    public function grant($departmentId)
    {
        if (!$this->scoped('dt01_gen_department_ms')->where('department_id', $departmentId)->where('active', '1')->countAllResults()) {
            return false;
        }
        $now = new \DateTimeImmutable();
        // An active grant cannot be extended by repeatedly clicking the button.
        if ($this->activeGrants()->where('department_id', $departmentId)->where('expires_at >', $now->format('Y-m-d H:i:s'))->countAllResults()) {
            return false;
        }
        return $this->db->table('dt01_qi_input_grant')->insert([
            'grant_id' => generateuuid(), 'group_id' => session()->get('groupid'), 'org_id' => session()->get('orgid'),
            'department_id' => $departmentId, 'granted_by' => session()->get('userid'),
            'granted_at' => $now->format('Y-m-d H:i:s'), 'expires_at' => $now->modify('+24 hours')->format('Y-m-d H:i:s'),
        ]);
    }

    public function allowsInput($referenceId, $month)
    {
        $statuses = $this->inputStatuses($referenceId);
        return isset($statuses[$month]) && $statuses[$month]['allowed'];
    }

    public function inputStatuses($referenceId)
    {
        $statuses = [];
        for ($month = 1; $month <= 12; $month++) {
            $statuses[sprintf('%02d', $month)] = ['allowed' => true, 'status' => 'unlimited'];
        }
        $indicator = $this->db->table('dt01_qi_indikator_hd a')->select('a.department_id AS department_id, p.tahun AS tahun')
            ->join('dt01_qi_periode_mutu p', 'p.periode_id = a.periode_id')
            ->where('a.transaksi_id', $referenceId)->where('a.group_id', session()->get('groupid'))->where('a.org_id', session()->get('orgid'))->get()->getRow();
        if (!$indicator) {
            return array_map(static function () { return ['allowed' => false, 'status' => 'closed']; }, $statuses);
        }
        $now = new \DateTimeImmutable();
        foreach ($statuses as $month => &$status) {
            if (!IndicatorDeadline::hasStarted($indicator->tahun, $month, $now)) {
                $status = ['allowed' => false, 'status' => 'upcoming'];
            }
        }
        unset($status);
        if (!$this->ready()) {
            return $statuses;
        }
        $setting = $this->setting();
        if (!$setting || $setting->deadline_day === null) {
            return $statuses;
        }
        $grant = $this->activeGrants()->select('expires_at AS expires_at')->where('department_id', $indicator->department_id)->orderBy('expires_at', 'DESC')->get(1)->getRow();
        foreach ($statuses as $month => &$status) {
            if ($status['status'] === 'upcoming') {
                continue;
            }
            $withinDeadline = IndicatorDeadline::allows($indicator->tahun, $month, $setting->deadline_day, $now);
            $allowed = $withinDeadline || IndicatorDeadline::allows($indicator->tahun, $month, $setting->deadline_day, $now, $grant ? $grant->expires_at : null);
            $status = ['allowed' => $allowed, 'status' => $withinDeadline ? 'open' : ($allowed ? 'granted' : 'closed')];
        }
        unset($status);
        return $statuses;
    }
}
