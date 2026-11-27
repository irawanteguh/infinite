<?php
namespace Modules\Satusehat\Database;

use CodeIgniter\Database\Migration;

class CreateBridgingLog extends Migration
{
    public const TABLE = 'dt01_satusehat_bridging_log';

    public function up()
    {
        $this->forge->addField([
            'bridging_log_id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'group_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'org_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'environment_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'encounter_tx_id' => ['type' => 'CHAR', 'constraint' => 32, 'null' => true],
            'method' => ['type' => 'VARCHAR', 'constraint' => 10],
            'endpoint' => ['type' => 'TEXT'],
            'request_json' => ['type' => 'LONGTEXT', 'null' => true],
            'http_status' => ['type' => 'INT', 'null' => true],
            'response_json' => ['type' => 'LONGTEXT', 'null' => true],
            'duration_ms' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'state' => ['type' => 'VARCHAR', 'constraint' => 20],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 64],
            'created_at' => ['type' => 'DATETIME'],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('bridging_log_id', true);
        $this->forge->addKey(['group_id', 'org_id', 'encounter_tx_id']);
        $attributes = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [];
        if (!$this->forge->createTable(self::TABLE, true, $attributes)) throw new \RuntimeException('Tabel log bridging belum tersedia.');
    }

    public function down()
    {
        $this->forge->dropTable(self::TABLE, true);
    }
}
