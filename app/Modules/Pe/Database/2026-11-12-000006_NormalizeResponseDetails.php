<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class NormalizeResponseDetails extends Migration
{
    private const TABLES = [
        'dt01_pe_prem_response_dt' => 'dt01_pe_prem_response_hd',
        'dt01_pe_prom_response_dt' => 'dt01_pe_prom_response_hd',
    ];

    public function up()
    {
        // Verify every existing answer can resolve its master text before removing snapshots.
        foreach (self::TABLES as $detail => $header) {
            if (!$this->db->tableExists($detail)) throw new \RuntimeException('Jalankan pe:setup terlebih dahulu.');
            $total = $this->db->table($detail)->countAllResults();
            $linked = $this->db->table($detail . ' r')
                ->join($header . ' h', 'h.response_id = r.response_id')
                ->join('dt01_pe_instrument_ms i', 'i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id')
                ->join('dt01_pe_question_ms q', 'q.question_id = r.question_id AND q.instrument_id = h.instrument_id')
                ->join('dt01_pe_domain_ms d', 'd.domain_id = r.domain_id AND d.group_id = h.group_id AND d.org_id = h.org_id')
                ->countAllResults();
            if ($linked !== $total) throw new \RuntimeException('Relasi master jawaban PE tidak lengkap. Normalisasi dibatalkan.');
        }
        foreach (self::TABLES as $detail => $header) {
            $fields = [];
            foreach (['domain_name', 'question_text'] as $field) {
                if ($this->db->fieldExists($field, $detail)) $fields[] = $field;
            }
            if ($fields) $this->forge->dropColumn($detail, $fields);
        }
    }

    public function down()
    {
        foreach (self::TABLES as $detail => $header) {
            $fields = [];
            if (!$this->db->fieldExists('domain_name', $detail)) {
                $fields['domain_name'] = ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true];
            }
            if (!$this->db->fieldExists('question_text', $detail)) {
                $fields['question_text'] = ['type' => 'TEXT', 'null' => true];
            }
            if ($fields) $this->forge->addColumn($detail, $fields);
            // Restored values reflect the current masters.
            $this->db->query("UPDATE {$detail} r
                JOIN {$header} h ON h.response_id = r.response_id
                JOIN dt01_pe_question_ms q ON q.question_id = r.question_id AND q.instrument_id = h.instrument_id
                JOIN dt01_pe_domain_ms d ON d.domain_id = r.domain_id AND d.group_id = h.group_id AND d.org_id = h.org_id
                SET r.domain_name = d.name, r.question_text = q.question");
        }
    }
}