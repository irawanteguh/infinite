<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class CreateDomainMaster extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'domain_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'group_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'org_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'active' => ['type' => 'CHAR', 'constraint' => 1, 'default' => '1'],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 64],
            'updated_by' => ['type' => 'VARCHAR', 'constraint' => 64],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('domain_id', true);
        $this->forge->addUniqueKey(['group_id', 'org_id', 'name']);
        $this->forge->createTable('dt01_pe_domain_ms', true);
        if (!$this->db->fieldExists('domain_id', 'dt01_pe_question_ms')) {
            $this->forge->addColumn('dt01_pe_question_ms', [
                'domain_id' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            ]);
        }

        // Only unlinked questions are migrated, so reruns preserve edited master names.
        $rows = $this->db->table('dt01_pe_question_ms q')->select('q.question_id, q.domain, q.created_by, i.group_id, i.org_id')
            ->join('dt01_pe_instrument_ms i', 'i.instrument_id = q.instrument_id')
            ->where('q.domain_id', null)->get()->getResultArray();
        $this->db->transBegin();
        try {
            foreach ($rows as $row) {
                $name = trim($row['domain']);
                if ($name === '') throw new \RuntimeException('Domain kosong ditemukan. Periksa pertanyaan sebelum migrasi.');
                $master = $this->db->table('dt01_pe_domain_ms')->where('group_id', $row['group_id'])
                    ->where('org_id', $row['org_id'])->where('name', $name)->get()->getRowArray();
                if (!$master) {
                    $id = $this->db->query('SELECT UUID() AS id')->getRow()->id;
                    $now = date('Y-m-d H:i:s');
                    if (!$this->db->table('dt01_pe_domain_ms')->insert([
                        'domain_id' => $id, 'group_id' => $row['group_id'], 'org_id' => $row['org_id'],
                        'name' => $name, 'active' => '1', 'created_by' => $row['created_by'],
                        'updated_by' => $row['created_by'], 'created_at' => $now, 'updated_at' => $now,
                    ])) throw new \RuntimeException('Gagal membuat master domain.');
                } else {
                    $id = $master['domain_id'];
                }
                if (!$this->db->table('dt01_pe_question_ms')->where('question_id', $row['question_id'])->where('domain_id', null)->update(['domain_id' => $id])) {
                    throw new \RuntimeException('Gagal menghubungkan pertanyaan dengan domain.');
                }
            }
            if ($this->db->transStatus() === false || !$this->db->transCommit()) throw new \RuntimeException('Migrasi master domain gagal.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function down()
    {
        $this->forge->dropColumn('dt01_pe_question_ms', 'domain_id');
        $this->forge->dropTable('dt01_pe_domain_ms', true);
    }
}
