<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class AddInpatientRespondentFields extends Migration
{
    private const FIELDS = [
        'respondent_name' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
        'respondent_gender' => ['type' => 'CHAR', 'constraint' => 1, 'null' => true],
        'respondent_age' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
        'ward_class' => ['type' => 'VARCHAR', 'constraint' => 15, 'null' => true],
        'occupation' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        'occupation_other' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
        'education' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        'insurance' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
    ];

    public function up()
    {
        $table = 'dt01_pe_prem_response_hd';
        $this->db->resetDataCache();
        $fields = [];
        foreach (self::FIELDS as $name => $definition) {
            if (!$this->db->fieldExists($name, $table)) $fields[$name] = $definition;
        }
        if ($fields) $this->forge->addColumn($table, $fields);
        $this->db->resetDataCache();
    }

    public function down()
    {
        $table = 'dt01_pe_prem_response_hd';
        foreach (self::FIELDS as $name => $definition) {
            if ($this->db->fieldExists($name, $table)) $this->forge->dropColumn($table, $name);
        }
        $this->db->resetDataCache();
    }
}