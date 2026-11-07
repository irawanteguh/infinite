<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQiFishboneTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'fishbone_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'group_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'org_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'department_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'periode_id' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'problem' => ['type' => 'VARCHAR', 'constraint' => 500],
            'active' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '1'],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'created_date' => ['type' => 'DATETIME', 'null' => true],
            'last_update_by' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'last_update_date' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('fishbone_id', true);
        $this->forge->addKey(['group_id', 'org_id', 'active']);
        $this->forge->createTable('dt01_qi_fishbone_hd', true);

        $this->forge->addField([
            'cause_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'fishbone_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'category' => ['type' => 'VARCHAR', 'constraint' => 60],
            'cause' => ['type' => 'VARCHAR', 'constraint' => 500],
            'active' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '1'],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'created_date' => ['type' => 'DATETIME', 'null' => true],
            'last_update_by' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'last_update_date' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('cause_id', true);
        $this->forge->addKey(['fishbone_id', 'active']);
        $this->forge->createTable('dt01_qi_fishbone_dt', true);
    }

    public function down()
    {
        $this->forge->dropTable('dt01_qi_fishbone_dt', true);
        $this->forge->dropTable('dt01_qi_fishbone_hd', true);
    }
}
