<?php

namespace Modules\Ca\Database;

use CodeIgniter\Database\Migration;

class AddScreeningScopes extends Migration
{
    private const PARENTS = [
        'dt01_ca_question_ms' => ['dt01_ca_instrument_ms', 'instrument_id'],
        CreateScreeningTransactions::HEADER => ['dt01_ca_instrument_ms', 'instrument_id'],
        CreateScreeningTransactions::DETAIL => [CreateScreeningTransactions::HEADER, 'response_id'],
    ];

    public function up()
    {
        foreach (self::PARENTS as $table => [$parent, $key]) {
            $this->db->resetDataCache();
            foreach (['group_id', 'org_id'] as $column) {
                $definition = ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true];
                if (!$this->db->fieldExists($column, $table) && !$this->forge->addColumn($table, [$column => $definition])) {
                    throw new \RuntimeException("Gagal menambah kolom CA {$table}.{$column}.");
                }

                $target = $this->db->protectIdentifiers($table, true);
                $source = $this->db->protectIdentifiers($parent, true);
                $updated = $this->db->table($table)->where($column, null)
                    ->set($column, "(SELECT {$source}.{$column} FROM {$source} WHERE {$source}.{$key} = {$target}.{$key})", false)
                    ->update();
                if (!$updated || $this->db->table($table)->where($column, null)->countAllResults() > 0) {
                    throw new \RuntimeException("Gagal mengisi kolom CA {$table}.{$column} dari tabel induk.");
                }
            }
        }

        $table = CreateScreeningTransactions::DETAIL;
        $this->db->resetDataCache();
        if (!$this->db->fieldExists('active', $table) && !$this->forge->addColumn($table, [
            'active' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '1'],
        ])) {
            throw new \RuntimeException('Gagal menambah kolom active pada detail skrining.');
        }
        $this->db->resetDataCache();
    }

    public function down()
    {
        $this->forge->dropColumn(CreateScreeningTransactions::DETAIL, 'active');
        foreach (array_reverse(array_keys(self::PARENTS)) as $table) {
            $this->forge->dropColumn($table, ['group_id', 'org_id']);
        }
    }
}
