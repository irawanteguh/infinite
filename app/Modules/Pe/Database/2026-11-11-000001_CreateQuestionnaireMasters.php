<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class CreateQuestionnaireMasters extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'instrument_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'group_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'org_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'code' => ['type' => 'VARCHAR', 'constraint' => 50],
            'name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'type' => ['type' => 'VARCHAR', 'constraint' => 4],
            'respondent' => ['type' => 'VARCHAR', 'constraint' => 200],
            'measurement' => ['type' => 'VARCHAR', 'constraint' => 255],
            'active' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '1'],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 64],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('instrument_id', true);
        $this->forge->addUniqueKey(['group_id', 'org_id', 'code']);
        $this->forge->createTable('dt01_pe_instrument_ms', true);

        $this->forge->addField([
            'question_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'group_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'org_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'instrument_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'domain' => ['type' => 'VARCHAR', 'constraint' => 150],
            'question' => ['type' => 'TEXT'],
            'sort_order' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true],
            'active' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '1'],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 64],
            'updated_by' => ['type' => 'VARCHAR', 'constraint' => 64],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('question_id', true);
        $this->forge->addUniqueKey(['instrument_id', 'sort_order']);
        $this->forge->addForeignKey('instrument_id', 'dt01_pe_instrument_ms', 'instrument_id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('dt01_pe_question_ms', true);
    }

    public function down()
    {
        $this->forge->dropTable('dt01_pe_question_ms', true);
        $this->forge->dropTable('dt01_pe_instrument_ms', true);
    }
}
