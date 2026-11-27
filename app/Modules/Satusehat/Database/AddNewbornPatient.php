<?php
namespace Modules\Satusehat\Database;
use CodeIgniter\Database\Migration;
class AddNewbornPatient extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('mother_nik', CreatePatientMaster::TABLE)) {
            $this->forge->addColumn(CreatePatientMaster::TABLE, ['mother_nik' => ['type' => 'VARCHAR', 'constraint' => 16, 'null' => true]]);
        }
        $this->forge->modifyColumn(CreatePatientMaster::TABLE, ['nik' => ['type' => 'VARCHAR', 'constraint' => 16, 'null' => true]]);
    }
    public function down() {}
}
