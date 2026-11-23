<?php

namespace Modules\Ca\Database;

use CodeIgniter\Database\Migration;

class AddPatientDomicile extends Migration
{
    public function up()
    {
        $table = CreateScreeningTransactions::HEADER;
        $this->db->resetDataCache();
        if (!$this->db->fieldExists('domicile', $table) && !$this->forge->addColumn($table, [
            'domicile' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
        ])) {
            throw new \RuntimeException('Gagal menambah kolom domisili skrining.');
        }
        $this->db->resetDataCache();
    }

    public function down()
    {
        $this->forge->dropColumn(CreateScreeningTransactions::HEADER, 'domicile');
    }
}
