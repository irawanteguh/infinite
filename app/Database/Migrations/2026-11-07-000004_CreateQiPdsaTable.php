<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQiPdsaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'pdsa_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'group_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'org_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'department_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'periode_id' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'indikator_ref_id' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'problem' => ['type' => 'TEXT'],
            'aim' => ['type' => 'TEXT', 'null' => true],
            'plan' => ['type' => 'TEXT', 'null' => true],
            'do_result' => ['type' => 'TEXT', 'null' => true],
            'study' => ['type' => 'TEXT', 'null' => true],
            'act' => ['type' => 'TEXT', 'null' => true],
            'cycle_no' => ['type' => 'INT', 'constraint' => 4, 'default' => 1],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Plan'],
            'active' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '1'],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'created_date' => ['type' => 'DATETIME', 'null' => true],
            'last_update_by' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'last_update_date' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('pdsa_id', true);
        $this->forge->addKey(['group_id', 'org_id', 'active']);
        $this->forge->addKey(['department_id', 'periode_id']);
        $this->forge->createTable('dt01_qi_pdsa', true);
    }

    public function down()
    {
        $this->forge->dropTable('dt01_qi_pdsa', true);
    }
}
