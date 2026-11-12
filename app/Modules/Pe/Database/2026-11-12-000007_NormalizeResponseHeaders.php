<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class NormalizeResponseHeaders extends Migration
{
    private const TABLES = ['dt01_pe_prem_response_hd', 'dt01_pe_prom_response_hd'];

    public function up()
    {
        foreach (self::TABLES as $table) {
            if (!$this->db->tableExists($table)) throw new \RuntimeException('Jalankan pe:setup terlebih dahulu.');
            $total = $this->db->table($table)->countAllResults();
            $linked = $this->db->table($table . ' h')
                ->join('dt01_pe_instrument_ms i', 'i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id')
                ->countAllResults();
            if ($total !== $linked) throw new \RuntimeException('Relasi instrumen jawaban PE tidak lengkap. Normalisasi dibatalkan.');
        }
        foreach (self::TABLES as $table) {
            if ($this->db->fieldExists('instrument_name', $table)) {
                $this->forge->dropColumn($table, 'instrument_name');
            }
        }
    }

    public function down()
    {
        foreach (self::TABLES as $table) {
            if (!$this->db->fieldExists('instrument_name', $table)) {
                $this->forge->addColumn($table, [
                    'instrument_name' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
                ]);
            }
            $this->db->query("UPDATE {$table} h
                JOIN dt01_pe_instrument_ms i ON i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id
                SET h.instrument_name = i.name");
        }
    }
}