<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndicatorReferenceToQiFishbone extends Migration
{
    public function up()
    {
        $this->forge->addColumn('dt01_qi_fishbone_hd', [
            'indikator_ref_id' => [
                'type' => 'VARCHAR',
                'constraint' => 36,
                'null' => true,
                'after' => 'periode_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('dt01_qi_fishbone_hd', 'indikator_ref_id');
    }
}
