<?php

namespace Modules\Ca\Database;

use CodeIgniter\Database\Migration;

class CreateScreeningMasters extends Migration
{
    public function up()
    {
        $attributes = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [];
        $tenant = [
            'group_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'org_id' => ['type' => 'VARCHAR', 'constraint' => 64],
        ];
        $audit = [
            'active' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '1'],
            'updated_by' => ['type' => 'VARCHAR', 'constraint' => 64],
            'updated_at' => ['type' => 'DATETIME'],
        ];
        $this->forge->addField($tenant + $audit + [
            'instrument_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 10],
            'name' => ['type' => 'VARCHAR', 'constraint' => 200],
        ]);
        $this->forge->addKey('instrument_id', true);
        $this->forge->addUniqueKey(['group_id', 'org_id', 'code']);
        if (!$this->forge->createTable('dt01_ca_instrument_ms', true, $attributes)) {
            throw new \RuntimeException('Gagal membuat tabel dt01_ca_instrument_ms.');
        }

        $this->forge->addField($audit + [
            'question_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'instrument_id' => ['type' => 'INT', 'unsigned' => true],
            'question' => ['type' => 'TEXT'],
            'sort_order' => ['type' => 'INT', 'unsigned' => true],
            'answer_options' => ['type' => 'TEXT'],
        ]);
        $this->forge->addKey('question_id', true);
        $this->forge->addUniqueKey(['instrument_id', 'sort_order']);
        $this->forge->addForeignKey('instrument_id', 'dt01_ca_instrument_ms', 'instrument_id', 'RESTRICT', 'RESTRICT');
        if (!$this->forge->createTable('dt01_ca_question_ms', true, $attributes)) {
            throw new \RuntimeException('Gagal membuat tabel dt01_ca_question_ms.');
        }
    }

    public function down()
    {
        $this->forge->dropTable('dt01_ca_question_ms', true);
        $this->forge->dropTable('dt01_ca_instrument_ms', true);
    }
}
