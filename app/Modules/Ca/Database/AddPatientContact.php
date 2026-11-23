<?php

namespace Modules\Ca\Database;

use CodeIgniter\Database\Migration;

class AddPatientContact extends Migration
{
    public function up()
    {
        $table = CreateScreeningTransactions::HEADER;
        $columns = [
            'phone_number' => ['type' => 'VARCHAR', 'constraint' => 15, 'null' => true],
            'contact_consent' => ['type' => 'CHAR', 'constraint' => 1, 'null' => true],
        ];
        foreach ($columns as $column => $definition) {
            $this->db->resetDataCache();
            if (!$this->db->fieldExists($column, $table) && !$this->forge->addColumn($table, [$column => $definition])) {
                throw new \RuntimeException('Gagal menambah kolom kontak skrining ' . $column);
            }
        }
        $this->db->resetDataCache();
    }

    public function down()
    {
        $this->forge->dropColumn(CreateScreeningTransactions::HEADER, ['phone_number', 'contact_consent']);
    }
}
