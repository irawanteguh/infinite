<?php
namespace Modules\Satusehat\Database;

use CodeIgniter\Database\Migration;

class CreateEncounterTransactions extends Migration
{
    public const TABLE = 'dt01_satusehat_encounter_tx';

    public function up()
    {
        $this->forge->addField([
            'encounter_tx_id' => ['type' => 'CHAR', 'constraint' => 32],
            'group_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'org_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'environment_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'registration_no' => ['type' => 'VARCHAR', 'constraint' => 100],
            'patient_name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'visit_class' => ['type' => 'VARCHAR', 'constraint' => 10],
            'registration_data' => ['type' => 'LONGTEXT'],
            'state' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'satusehat_id' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'http_status' => ['type' => 'INT', 'null' => true],
            'response_json' => ['type' => 'LONGTEXT', 'null' => true],
            'target_hash' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'revision' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 64],
            'updated_by' => ['type' => 'VARCHAR', 'constraint' => 64],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('encounter_tx_id', true);
        $this->forge->addUniqueKey(['group_id', 'org_id', 'environment_id', 'registration_no'], 'encounter_registration_scope');
        $attributes = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [];
        if (!$this->forge->createTable(self::TABLE, true, $attributes)) throw new \RuntimeException('Tabel transaksi Encounter belum tersedia.');
    }

    public function down()
    {
        $this->forge->dropTable(self::TABLE, true);
    }
}
