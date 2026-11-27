<?php
namespace Modules\Satusehat\Database;
use CodeIgniter\Database\Migration;
class CreateRegionMaster extends Migration
{
    public const TABLE = 'dt01_satusehat_region_ms';
    public function up()
    {
        $this->forge->addField([
            'code' => ['type' => 'VARCHAR', 'constraint' => 10],
            'parent_code' => ['type' => 'VARCHAR', 'constraint' => 6, 'null' => true],
            'level' => ['type' => 'VARCHAR', 'constraint' => 20],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'code_system' => ['type' => 'VARCHAR', 'constraint' => 255],
            'source_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'source_sha256' => ['type' => 'CHAR', 'constraint' => 64],
        ]);
        $this->forge->addKey('code', true);
        $this->forge->addKey(['level', 'parent_code']);
        if (!$this->forge->createTable(self::TABLE, true, $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [])) throw new \RuntimeException('Master wilayah belum dapat dibuat.');
    }
    public function down() { $this->forge->dropTable(self::TABLE, true); }
}
