<?php

namespace Modules\Ca\Database;

use CodeIgniter\Database\Migration;

class AddScreeningAnswerType extends Migration
{
    public function up()
    {
        $table = CreateScreeningTransactions::DETAIL;
        $this->db->resetDataCache();
        if (!$this->db->fieldExists('answer_type', $table) && !$this->forge->addColumn($table, [
            'answer_type' => ['type' => 'VARCHAR', 'constraint' => 8, 'default' => 'score'],
        ])) {
            throw new \RuntimeException('Gagal menambah jenis jawaban pada detail skrining.');
        }
        $this->db->resetDataCache();
    }

    public function down()
    {
        $this->forge->dropColumn(CreateScreeningTransactions::DETAIL, 'answer_type');
    }
}
