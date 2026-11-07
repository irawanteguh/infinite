<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPriorityToQiFishboneCause extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('priority', 'dt01_qi_fishbone_dt')) {
            return;
        }

        $this->forge->addColumn('dt01_qi_fishbone_dt', [
            'priority' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'medium',
                'after' => 'cause',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('priority', 'dt01_qi_fishbone_dt')) {
            $this->forge->dropColumn('dt01_qi_fishbone_dt', 'priority');
        }
    }
}
