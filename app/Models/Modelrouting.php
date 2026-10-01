<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelrouting extends Model
{
    protected $DBGroup = 'default';

    /**
     * Get active application menu
     */
    public function menu(): array
    {
        $query = "
            SELECT
                a.modules_id,
                a.modules_name,
                a.modules_header_id,
                a.package,
                a.def_controller,
                a.parent,
                a.icon
            FROM dt01_gen_modules_ms a
            WHERE a.active = '1'
            ORDER BY a.urut ASC
        ";

        return $this->db
            ->query($query)
            ->getResultArray();
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