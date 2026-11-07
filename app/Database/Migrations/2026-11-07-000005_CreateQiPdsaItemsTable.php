<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQiPdsaItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'pdsa_item_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'pdsa_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'phase' => ['type' => 'VARCHAR', 'constraint' => 10],
            'item_text' => ['type' => 'TEXT'],
            'sort_order' => ['type' => 'INT', 'constraint' => 5, 'default' => 1],
            'active' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '1'],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'created_date' => ['type' => 'DATETIME', 'null' => true],
            'last_update_by' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'last_update_date' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('pdsa_item_id', true);
        $this->forge->addKey(['pdsa_id', 'phase', 'active']);
        $this->forge->createTable('dt01_qi_pdsa_items', true);

        // Preserve any phase text created before PDSA items became multi-entry.
        $legacyRows = $this->db->table('dt01_qi_pdsa')->where('active', '1')->get()->getResultArray();
        foreach ($legacyRows as $row) {
            foreach (['Plan' => 'plan', 'Do' => 'do_result', 'Study' => 'study', 'Act' => 'act'] as $phase => $column) {
                $text = trim((string) ($row[$column] ?? ''));
                if ($text === '') continue;
                $this->db->table('dt01_qi_pdsa_items')->insert([
                    'pdsa_item_id' => bin2hex(random_bytes(16)),
                    'pdsa_id' => $row['pdsa_id'], 'phase' => $phase, 'item_text' => $text,
                    'sort_order' => 1, 'active' => '1', 'created_by' => $row['last_update_by'] ?? $row['created_by'] ?? null,
                    'created_date' => $row['last_update_date'] ?? $row['created_date'] ?? date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('dt01_qi_pdsa_items', true);
    }
}
