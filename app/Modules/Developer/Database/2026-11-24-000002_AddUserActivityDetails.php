<?php

namespace Modules\Developer\Database;

use CodeIgniter\Database\Migration;

class AddUserActivityDetails extends Migration
{
    private const TABLE = 'dt01_gen_user_activity';

    public function up()
    {
        $this->db->resetDataCache();
        if (!$this->db->fieldExists('details', self::TABLE)) {
            if (!$this->forge->addColumn(self::TABLE, [
                'details' => ['type' => 'MEDIUMTEXT', 'null' => true],
            ])) {
                throw new \RuntimeException('Gagal menambahkan kolom detail aktivitas pengguna.');
            }
            $this->db->resetDataCache();
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('details', self::TABLE)) {
            $this->forge->dropColumn(self::TABLE, 'details');
        }
    }
}
