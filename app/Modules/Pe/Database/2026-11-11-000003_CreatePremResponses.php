<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class CreatePremResponses extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'response_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'instrument_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'group_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'org_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'instrument_name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'submission_key' => ['type' => 'VARCHAR', 'constraint' => 64],
            'submitted_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('response_id', true);
        $this->forge->addUniqueKey('submission_key');
        $this->forge->addKey(['group_id', 'org_id', 'submitted_at']);
        $this->forge->addForeignKey('instrument_id', 'dt01_pe_instrument_ms', 'instrument_id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('dt01_pe_prem_response_hd', true);
        $this->forge->addField([
            'response_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'question_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'domain_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'domain_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'question_text' => ['type' => 'TEXT'],
            'sort_order' => ['type' => 'INT', 'unsigned' => true],
            'score' => ['type' => 'TINYINT', 'unsigned' => true],
        ]);
        $this->forge->addKey(['response_id', 'question_id'], true);
        $this->forge->addForeignKey('response_id', 'dt01_pe_prem_response_hd', 'response_id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('dt01_pe_prem_response_dt', true);
    }

    public function down()
    {
        $this->forge->dropTable('dt01_pe_prem_response_dt', true);
        $this->forge->dropTable('dt01_pe_prem_response_hd', true);
    }
}
