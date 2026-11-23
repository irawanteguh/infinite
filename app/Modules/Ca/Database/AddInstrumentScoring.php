<?php

namespace Modules\Ca\Database;

use CodeIgniter\Database\Migration;

class AddInstrumentScoring extends Migration
{
    public function up()
    {
        $table = 'dt01_ca_instrument_ms';
        $this->db->resetDataCache();
        if (!$this->db->fieldExists('risk_ranges', $table) && !$this->forge->addColumn($table, [
            'risk_ranges' => ['type' => 'TEXT', 'null' => true],
        ])) {
            throw new \RuntimeException('Gagal menambah pengaturan skor instrumen.');
        }
        $this->db->resetDataCache();
    }

    public function down()
    {
        $this->forge->dropColumn('dt01_ca_instrument_ms', 'risk_ranges');
    }
}
