<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompletedToQiPdsaItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('dt01_qi_pdsa_items', [
            'completed' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 0,
                'after' => 'item_text',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('dt01_qi_pdsa_items', 'completed');
    }
}
