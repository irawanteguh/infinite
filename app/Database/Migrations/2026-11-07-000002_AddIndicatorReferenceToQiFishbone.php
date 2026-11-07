<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndicatorReferenceToQiFishbone extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('indikator_ref_id', 'dt01_qi_fishbone_hd')) {
            return;
        }

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
        if ($this->db->fieldExists('indikator_ref_id', 'dt01_qi_fishbone_hd')) {
            $this->forge->dropColumn('dt01_qi_fishbone_hd', 'indikator_ref_id');
        }
    }
}
