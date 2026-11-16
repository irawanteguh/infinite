<?php
namespace Modules\Hr\Models;

use App\Libraries\ModuleAccess;
use CodeIgniter\Model;

class AccessModel extends Model
{
    public function ready(): bool
    {
        foreach (['dt01_gen_access_manager','dt01_gen_user_module_access','dt01_gen_user_module_access_log'] as $table) {
            if (!$this->db->tableExists($table)) return false;
        }
        return true;
    }

    private function scoped(string $table, string $group, string $org)
    {
        return $this->db->table($table)->where('group_id',$group)->where('org_id',$org);
    }

    public function isManager(string $group, string $org, string $user): bool
    {
        return (bool) $this->scoped('dt01_gen_access_manager',$group,$org)->where('user_id',$user)->countAllResults();
    }

    public function state(string $group, string $org, string $user): array
    {
        if (!$this->ready()) return ['manager'=>false,'moduleIds'=>[]];
        // Read the current account rather than trusting a stale session after deactivation/move.
        $account = $this->scoped('dt01_gen_user_data',$group,$org)->select('user_id,suspend')->where('user_id',$user)->where('active','1')->get()->getRowArray();
        if (!$account || in_array(strtoupper((string) $account['suspend']), ['Y','1'], true)) return ['manager'=>false,'moduleIds'=>[]];
        return ['manager'=>$this->isManager($group,$org,$user), 'moduleIds'=>$this->grants($group,$org,$user)];
    }

    public function users(string $group, string $org): array
    {
        return $this->db->table('dt01_gen_user_data u')->select('u.user_id,u.username,u.name,u.suspend,d.department')
            ->join('dt01_gen_department_ms d','d.department_id = u.department_id AND d.group_id = u.group_id AND d.org_id = u.org_id','left')
            ->where('u.group_id',$group)->where('u.org_id',$org)->where('u.active','1')
            ->orderBy('u.name')->orderBy('u.username')->get()->getResultArray();
    }

    public function modules(): array
    {
        $rows = $this->db->table('dt01_gen_modules_ms')->select('modules_id,modules_name,modules_header_id,package,def_controller,parent,urut')
            ->where('active','1')->orderBy('urut')->orderBy('modules_name')->get()->getResultArray();
        $byId = array_column($rows, null, 'modules_id');
        $result = [];
        foreach ($rows as $row) {
            if (!ModuleAccess::hasRoute($row) || ModuleAccess::isWelcome($row['package'],$row['def_controller']) || ModuleAccess::isManagement($row)) continue;
            $path = []; $parent = (string) $row['modules_header_id']; $seen = [];
            while (isset($byId[$parent]) && !isset($seen[$parent])) {
                $seen[$parent] = true;
                array_unshift($path, $byId[$parent]['modules_name']);
                $parent = (string) $byId[$parent]['modules_header_id'];
            }
            $row['category'] = $path ? implode(' / ', $path) : $row['package'];
            $result[] = $row;
        }
        return $result;
    }

    public function grants(string $group, string $org, string $user): array
    {
        return array_column($this->scoped('dt01_gen_user_module_access',$group,$org)->select('modules_id')->where('user_id',$user)->orderBy('modules_id')->get()->getResultArray(),'modules_id');
    }

    public function detail(string $group, string $org, string $user): array
    {
        $account = $this->scoped('dt01_gen_user_data',$group,$org)->select('user_id,username,name,suspend')->where('user_id',$user)->where('active','1')->get()->getRowArray();
        if (!$account) throw new \InvalidArgumentException('Pengguna tidak ditemukan pada organisasi ini.');
        $ids = $this->grants($group,$org,$user);
        $latest = $this->db->table('dt01_gen_user_module_access_log l')->select('l.created_at,u.name AS updated_by')
            ->join('dt01_gen_user_data u','u.user_id = l.created_by AND u.group_id = l.group_id AND u.org_id = l.org_id','left')
            ->where('l.group_id',$group)->where('l.org_id',$org)->where('l.user_id',$user)->orderBy('l.created_at','DESC')->orderBy('l.audit_id','DESC')->get(1)->getRowArray();
        return ['user'=>$account,'moduleIds'=>$ids,'revision'=>ModuleAccess::revision($ids),'manager'=>$this->isManager($group,$org,$user),'updated'=>$latest];
    }

    public function saveAccess(string $group, string $org, string $actor, string $user, array $ids, string $revision): array
    {
        if (count($ids) > 1000) throw new \InvalidArgumentException('Daftar modul tidak valid.');
        foreach ($ids as $id) if (!is_string($id) || strlen($id)>64) throw new \InvalidArgumentException('ID modul tidak valid.');
        if (count($ids) !== count(array_unique($ids))) throw new \InvalidArgumentException('Daftar modul tidak valid.');
        $valid = array_column($this->modules(),'modules_id');
        foreach ($ids as $id) if (!in_array($id,$valid,true)) throw new \InvalidArgumentException('Modul tidak tersedia atau tidak dapat diberikan melalui halaman ini.');
        $this->db->transBegin();
        try {
            // Serialize saves on the target user; compare the last revision to prevent lost updates.
            $sql = 'SELECT user_id FROM dt01_gen_user_data WHERE group_id = ? AND org_id = ? AND user_id = ? AND active = ?';
            if ($this->db->DBDriver === 'MySQLi') $sql .= ' FOR UPDATE';
            if (!$this->db->query($sql,[$group,$org,$user,'1'])->getRowArray()) throw new \InvalidArgumentException('Pengguna tidak ditemukan pada organisasi ini.');
            if (empty($this->state($group,$org,$actor)['manager'])) throw new \RuntimeException('Pengelola akses tidak tersedia.');
            $before = $this->grants($group,$org,$user);
            if (!hash_equals(ModuleAccess::revision($before),$revision)) throw new \DomainException('Akses telah diubah oleh pengelola lain. Muat ulang sebelum menyimpan.');
            // Preserve inactive module grants that are not part of the editable catalog.
            $after = array_values(array_unique(array_merge(array_diff($before,$valid),$ids)));
            sort($after, SORT_STRING); sort($before, SORT_STRING);
            if ($before !== $after) {
                if (!$this->scoped('dt01_gen_user_module_access',$group,$org)->where('user_id',$user)->delete()) throw new \RuntimeException('Failed deleting grants.');
                $now = date('Y-m-d H:i:s');
                foreach ($after as $id) if (!$this->db->table('dt01_gen_user_module_access')->insert([
                    'group_id'=>$group,'org_id'=>$org,'user_id'=>$user,'modules_id'=>$id,'created_by'=>$actor,'created_at'=>$now,
                ])) throw new \RuntimeException('Failed saving grant.');
                if (!$this->db->table('dt01_gen_user_module_access_log')->insert([
                    'audit_id'=>bin2hex(random_bytes(16)),'group_id'=>$group,'org_id'=>$org,'user_id'=>$user,
                    'before_modules'=>json_encode($before),'after_modules'=>json_encode($after),'created_by'=>$actor,'created_at'=>$now,
                ])) throw new \RuntimeException('Failed saving audit.');
            }
            if ($this->db->transStatus() === false || !$this->db->transCommit()) throw new \RuntimeException('Failed committing access.');
        } catch (\Throwable $e) { $this->db->transRollback(); throw $e; }
        return $this->detail($group,$org,$user);
    }
}
