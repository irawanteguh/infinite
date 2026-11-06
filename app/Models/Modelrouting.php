<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelrouting extends Model{

    protected $DBGroup = 'default';


    public function menu(): array{
        $query = "
            select a.modules_id, a.modules_name, a.modules_header_id, a.package, a.def_controller, a.parent, a.icon, a.urut
            from dt01_gen_modules_ms a
            where a.active = '1'
            order by modules_header_id asc, a.urut asc
        ";

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