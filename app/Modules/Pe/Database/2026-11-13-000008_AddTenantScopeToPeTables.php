<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class AddTenantScopeToPeTables extends Migration
{
    private const CHILDREN = [
        'dt01_pe_question_ms' => ['dt01_pe_instrument_ms', 'instrument_id'],
        'dt01_pe_prem_response_dt' => ['dt01_pe_prem_response_hd', 'response_id'],
        'dt01_pe_prom_response_dt' => ['dt01_pe_prom_response_hd', 'response_id'],
    ];

    public function up()
    {
        foreach (self::CHILDREN as $child => [$parent, $key]) {
            $orphans = $this->db->query("SELECT COUNT(*) AS n FROM {$child} c
                LEFT JOIN {$parent} p ON p.{$key} = c.{$key}
                WHERE p.{$key} IS NULL OR p.group_id IS NULL OR p.org_id IS NULL
                OR p.group_id = '' OR p.org_id = ''")->getRow()->n;
            if ((int) $orphans !== 0) throw new \RuntimeException('Relasi organisasi tidak lengkap pada ' . $child);
        }
        foreach (self::CHILDREN as $child => [$parent, $key]) {
            foreach (['group_id', 'org_id'] as $field) {
                if (!$this->db->fieldExists($field, $child)) {
                    $this->forge->addColumn($child, [$field => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true]]);
                }
                $this->db->query("UPDATE {$child} c JOIN {$parent} p ON p.{$key} = c.{$key}
                    SET c.{$field} = p.{$field} WHERE c.{$field} IS NULL OR c.{$field} = ''");
            }
            $mismatch = $this->db->query("SELECT COUNT(*) AS n FROM {$child} c
                JOIN {$parent} p ON p.{$key} = c.{$key}
                WHERE c.group_id <> p.group_id OR c.org_id <> p.org_id")->getRow()->n;
            if ((int) $mismatch !== 0) throw new \RuntimeException('Organisasi tabel anak berbeda dari induk: ' . $child);
            $this->forge->modifyColumn($child, [
                'group_id' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => false],
                'org_id' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => false],
            ]);
            $indexes = $this->db->getIndexData($child);
            if (!isset($indexes['pe_tenant_scope'])) {
                $this->db->query("ALTER TABLE {$child} ADD INDEX pe_tenant_scope (group_id, org_id)");
            }
        }
        $this->db->resetDataCache();
        foreach ($this->db->listTables() as $table) {
            if (strpos($table, 'dt01_pe_') !== 0) continue;
            foreach (['group_id', 'org_id'] as $field) {
                if (!$this->db->fieldExists($field, $table)) throw new \RuntimeException('Kolom ' . $field . ' belum tersedia pada ' . $table);
                if ($this->db->table($table)->groupStart()->where($field, null)->orWhere($field, '')->groupEnd()->countAllResults()) {
                    throw new \RuntimeException('Identitas organisasi kosong pada ' . $table);
                }
            }
        }
    }

    public function down()
    {
        foreach (self::CHILDREN as $child => $parent) {
            if (isset($this->db->getIndexData($child)['pe_tenant_scope'])) {
                $this->db->query("ALTER TABLE {$child} DROP INDEX pe_tenant_scope");
            }
            foreach (['group_id', 'org_id'] as $field) {
                if ($this->db->fieldExists($field, $child)) $this->forge->dropColumn($child, $field);
            }
        }
    }
}