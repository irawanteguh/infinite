<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class LinkPremToSkm extends Migration
{
    public function up()
    {
        $table = 'dt01_pe_prem_response_hd';
        $this->db->resetDataCache();
        if (!$this->db->fieldExists('skm_response_id', $table)) {
            $this->forge->addColumn($table, ['skm_response_id' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true]]);
            $this->db->query('ALTER TABLE dt01_pe_prem_response_hd
                ADD UNIQUE KEY pe_prem_skm_response (skm_response_id),
                ADD CONSTRAINT fk_pe_prem_skm_response FOREIGN KEY (skm_response_id)
                REFERENCES dt01_pe_skm_response_hd (response_id) ON DELETE RESTRICT ON UPDATE RESTRICT');
        }
        $this->db->resetDataCache();
    }

    public function down()
    {
        if ($this->db->fieldExists('skm_response_id', 'dt01_pe_prem_response_hd')) {
            $this->db->query('ALTER TABLE dt01_pe_prem_response_hd DROP FOREIGN KEY fk_pe_prem_skm_response, DROP INDEX pe_prem_skm_response');
            $this->forge->dropColumn('dt01_pe_prem_response_hd', 'skm_response_id');
        }
        $this->db->resetDataCache();
    }
}