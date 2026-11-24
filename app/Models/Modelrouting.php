<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelrouting extends Model{

    protected $DBGroup = null;


    public function menu(): array
    {
        $menus = $this->allMenus();
        $session = session();
        if (!$session->get('loggedin')) return [];
        $state = (new \Modules\Hr\Models\AccessModel())->state((string)$session->get('groupid'),(string)$session->get('orgid'),(string)$session->get('userid'));
        return \App\Libraries\ModuleAccess::navigation($menus,$state);
    }

    public function allMenus(): array
    {
        return $this->menuRows(true);
    }

    protected function menuRows(bool $activeOnly, bool $lock = false): array
    {
        $details = $activeOnly ? '' : ', a.status AS status, a.version AS version';
        $query = "
            select a.modules_id, a.modules_name, a.modules_header_id, a.package, a.def_controller, a.parent, a.icon, a.urut, a.active{$details}
            from dt01_gen_modules_ms a
        ";
        if ($activeOnly) {
            $query .= " where a.active = '1'";
        }
        $query .= " order by coalesce(a.modules_header_id, '') asc, a.urut asc, a.modules_id asc";
        if ($lock && $this->db->DBDriver === 'MySQLi') {
            $query .= ' FOR UPDATE';
        }

        return $this->db->query($query)->getResultArray();
    }

    /**
     * Get environment based on group and organization
     */
    public function environment($groupid, $orgid): array
    {
        $query = "
            SELECT
                a.*
            FROM dt01_gen_enviroment_ms a
            WHERE a.active = '1'
            AND a.group_id = ?
            AND a.org_id = ?
        ";

        return $this->db
            ->query($query, [
                $groupid,
                $orgid
            ])
            ->getResultArray();
    }
}
