<?php

namespace Modules\Ca\Database;

use CodeIgniter\Database\Migration;

class CreateScreeningTransactions extends Migration
{
    public const HEADER = 'dt01_ca_response_hd';
    public const DETAIL = 'dt01_ca_response_dt';

    public function up()
    {
        $attributes = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [];
        $this->forge->addField([
            'response_id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'instrument_id' => ['type' => 'INT', 'unsigned' => true],
            'screened_at' => ['type' => 'DATETIME'],
            'medical_record_number' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'respondent_name' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'total_score' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'risk_level' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'active' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '1'],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 64],
            'created_at' => ['type' => 'DATETIME'],
            'updated_by' => ['type' => 'VARCHAR', 'constraint' => 64],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('response_id', true);
        $this->forge->addKey(['instrument_id', 'screened_at']);
        $this->forge->addForeignKey('instrument_id', 'dt01_ca_instrument_ms', 'instrument_id', 'RESTRICT', 'RESTRICT');
        $this->create(self::HEADER, $attributes);

        $this->forge->addField([
            'response_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'question_id' => ['type' => 'INT', 'unsigned' => true],
            'sort_order' => ['type' => 'INT', 'unsigned' => true],
            'question_text' => ['type' => 'TEXT'],
            'answer_label' => ['type' => 'VARCHAR', 'constraint' => 500],
            'answer_score' => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey(['response_id', 'question_id'], true);
        $this->forge->addForeignKey('response_id', self::HEADER, 'response_id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('question_id', 'dt01_ca_question_ms', 'question_id', 'RESTRICT', 'RESTRICT');
        $this->create(self::DETAIL, $attributes);
    }

    private function create(string $table, array $attributes): void
    {
        if (!$this->forge->createTable($table, true, $attributes)) {
            throw new \RuntimeException('Gagal membuat tabel ' . $table . '.');
        }
    }

    public function down()
    {
        $this->forge->dropTable(self::DETAIL, true);
        $this->forge->dropTable(self::HEADER, true);
    }
}
