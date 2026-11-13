<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class AddRespondentClinic extends Migration
{
    public function up()
    {
        foreach (['dt01_pe_skm_response_hd', 'dt01_pe_prem_response_hd'] as $table) {
            $this->db->resetDataCache();
            if (!$this->db->fieldExists('clinic', $table)) {
                $this->forge->addColumn($table, ['clinic' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true]]);
            }
        }
        $this->db->resetDataCache();
    }

    public function down()
    {
        foreach (['dt01_pe_skm_response_hd', 'dt01_pe_prem_response_hd'] as $table) {
            if ($this->db->fieldExists('clinic', $table)) $this->forge->dropColumn($table, 'clinic');
        }
        $this->db->resetDataCache();
    }
}
