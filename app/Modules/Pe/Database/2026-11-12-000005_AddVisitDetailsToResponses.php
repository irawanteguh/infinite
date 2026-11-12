<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class AddVisitDetailsToResponses extends Migration
{
    public function up()
    {
        foreach (['dt01_pe_prem_response_hd', 'dt01_pe_prom_response_hd'] as $table) {
            if (!$this->db->tableExists($table)) {
                throw new \RuntimeException('Tabel jawaban PE belum tersedia. Jalankan pe:setup terlebih dahulu.');
            }
            $fields = [];
            if (!$this->db->fieldExists('service_type', $table)) {
                $fields['service_type'] = ['type' => 'VARCHAR', 'constraint' => 3, 'null' => true];
            }
            if (!$this->db->fieldExists('medical_record_number', $table)) {
                $fields['medical_record_number'] = ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true];
            }
            if ($fields) $this->forge->addColumn($table, $fields);
        }
    }

    public function down()
    {
        foreach (['dt01_pe_prem_response_hd', 'dt01_pe_prom_response_hd'] as $table) {
            foreach (['service_type', 'medical_record_number'] as $field) {
                if ($this->db->tableExists($table) && $this->db->fieldExists($field, $table)) {
                    $this->forge->dropColumn($table, $field);
                }
            }
        }
    }
}