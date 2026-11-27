<?php
namespace Modules\Satusehat\Database;

use CodeIgniter\Database\Migration;

class CreateLocationMaster extends Migration
{
    public const TABLE = 'dt01_satusehat_location_ms';

    public function up()
    {
        $fields = [
            'location_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
        ];
        foreach (['group_id', 'org_id', 'target_hash', 'ihs_number'] as $key) {
            $fields[$key] = ['type' => 'VARCHAR', 'constraint' => 64];
        }
        $fields['resource_json'] = ['type' => 'LONGTEXT'];
        $fields['synced_at'] = ['type' => 'DATETIME'];
        $this->forge->addField($fields);
        $this->forge->addKey('location_id', true);
        $this->forge->addUniqueKey(['group_id', 'org_id', 'target_hash', 'ihs_number'], 'location_scope_ihs');
        $attributes = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [];
        if (!$this->forge->createTable(self::TABLE, true, $attributes)) throw new \RuntimeException('Tabel Location gagal dibuat.');
    }

    public function down()
    {
        $this->forge->dropTable(self::TABLE, true);
    }
}
