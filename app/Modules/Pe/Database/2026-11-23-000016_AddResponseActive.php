<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class AddResponseActive extends Migration
{
    private const TABLES = [
        'dt01_pe_skm_response_hd',
        'dt01_pe_prem_response_hd',
        'dt01_pe_prom_response_hd',
    ];

    public function up()
    {
        $this->db->resetDataCache();
        foreach (self::TABLES as $table) {
            if (!$this->db->fieldExists('active', $table)) {
                $this->forge->addColumn($table, [
                    'active' => ['type' => 'CHAR', 'constraint' => 1, 'null' => false, 'default' => '1'],
                ]);
            }
        }
        $this->db->resetDataCache();
    }

    public function down()
    {
        foreach (self::TABLES as $table) {
            if ($this->db->fieldExists('active', $table)) {
                $this->forge->dropColumn($table, 'active');
            }
        }
        $this->db->resetDataCache();
    }
}
