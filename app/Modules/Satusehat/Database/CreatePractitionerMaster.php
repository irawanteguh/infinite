<?php
namespace Modules\Satusehat\Database;

use CodeIgniter\Database\Migration;

class CreatePractitionerMaster extends Migration
{
    public const TABLE = 'dt01_satusehat_practitioner_ms';

    public function up()
    {
        $this->forge->addField([
            'practitioner_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'group_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'org_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'nik' => ['type' => 'VARCHAR', 'constraint' => 16],
            'name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'gender' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'birth_date' => ['type' => 'DATE', 'null' => true],
            'ihs_number' => ['type' => 'VARCHAR', 'constraint' => 64],
            'is_dummy' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '0'],
            'active' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '1'],
        ]);
        $this->forge->addKey('practitioner_id', true);
        $this->forge->addUniqueKey(['group_id', 'org_id', 'ihs_number']);
        $this->forge->addUniqueKey(['group_id', 'org_id', 'nik']);
        $attributes = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [];
        if (!$this->forge->createTable(self::TABLE, true, $attributes)) throw new \RuntimeException('Master Practitioner belum dapat dibuat.');
    }

    public function down()
    {
        $this->forge->dropTable(self::TABLE, true);
    }
}
