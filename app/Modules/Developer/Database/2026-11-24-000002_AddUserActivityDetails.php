<?php

namespace Modules\Developer\Database;

use CodeIgniter\Database\Migration;

class AddUserActivityDetails extends Migration
{
    private const TABLE = 'dt01_gen_user_activity';

    public function up()
    {
        if (!$this->db->fieldExists('details', self::TABLE)) {
            $this->forge->addColumn(self::TABLE, [
                'details' => ['type' => 'MEDIUMTEXT', 'null' => true],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('details', self::TABLE)) {
            $this->forge->dropColumn(self::TABLE, 'details');
        }
    }
}
