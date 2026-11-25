<?php

namespace Modules\Developer\Database;

use CodeIgniter\Database\Migration;

class CreateUserActivity extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'activity_id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'group_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'org_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'user_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'user_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'event' => ['type' => 'VARCHAR', 'constraint' => 20],
            'route' => ['type' => 'VARCHAR', 'constraint' => 200],
            'http_method' => ['type' => 'VARCHAR', 'constraint' => 16],
            'http_status' => ['type' => 'SMALLINT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('activity_id', true);
        $this->forge->addKey(['group_id', 'org_id', 'activity_id']);
        $this->forge->addKey(['group_id', 'org_id', 'created_at']);
        if (!$this->forge->createTable('dt01_gen_user_activity', true)) {
            throw new \RuntimeException('Gagal membuat tabel aktivitas pengguna.');
        }
    }

    public function down()
    {
        $this->forge->dropTable('dt01_gen_user_activity', true);
    }
}
