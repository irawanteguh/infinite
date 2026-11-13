<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;
use Modules\Pe\Config\GeneralQuestionnaires;

class SeparateSkmTransactions extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('dt01_pe_skm_response_hd')) {
            $this->db->query('CREATE TABLE dt01_pe_skm_response_hd LIKE dt01_pe_prem_response_hd');
            $this->db->query('ALTER TABLE dt01_pe_skm_response_hd ADD CONSTRAINT fk_pe_skm_instrument
                FOREIGN KEY (instrument_id) REFERENCES dt01_pe_instrument_ms (instrument_id) ON DELETE RESTRICT ON UPDATE RESTRICT');
        }
        if (!$this->db->tableExists('dt01_pe_skm_response_dt')) {
            $this->db->query('CREATE TABLE dt01_pe_skm_response_dt LIKE dt01_pe_prem_response_dt');
            $this->db->query('ALTER TABLE dt01_pe_skm_response_dt ADD CONSTRAINT fk_pe_skm_response
                FOREIGN KEY (response_id) REFERENCES dt01_pe_skm_response_hd (response_id) ON DELETE RESTRICT ON UPDATE RESTRICT');
        }
        $this->db->resetDataCache();
        $this->move('prem', 'skm');
    }

    private function move(string $source, string $target): void
    {
        $sourceHeader = 'dt01_pe_' . $source . '_response_hd';
        $sourceDetail = 'dt01_pe_' . $source . '_response_dt';
        $targetHeader = 'dt01_pe_' . $target . '_response_hd';
        $targetDetail = 'dt01_pe_' . $target . '_response_dt';
        $codes = array_column(GeneralQuestionnaires::SERVICES, 'code');
        $this->db->transBegin();
        try {
            $sql = $this->db->table($sourceHeader . ' h')->select('h.*')
                ->join('dt01_pe_instrument_ms i', 'i.instrument_id = h.instrument_id')
                ->whereIn('i.code', $codes)->getCompiledSelect();
            $headers = $this->db->query($sql . ' FOR UPDATE')->getResultArray();
            foreach ($headers as $header) {
                $id = $header['response_id'];
                if (!$this->db->table('dt01_pe_instrument_ms')->where('instrument_id', $header['instrument_id'])
                    ->where('group_id', $header['group_id'])->where('org_id', $header['org_id'])->countAllResults()) {
                    throw new \RuntimeException('Organisasi header SKM tidak cocok dengan instrumen.');
                }
                $existing = $this->db->table($targetHeader)->where('response_id', $id)->get()->getRowArray();
                if (!$existing) {
                    if (!$this->db->table($targetHeader)->insert($header)) throw new \RuntimeException('Gagal menyalin header SKM.');
                } elseif (!$this->sameRow($header, $existing)) {
                    throw new \RuntimeException('Konflik header SKM; sumber tetap dipertahankan.');
                }
                $details = $this->db->table($sourceDetail)->where('response_id', $id)->get()->getResultArray();
                foreach ($details as $detail) {
                    if ($detail['group_id'] !== $header['group_id'] || $detail['org_id'] !== $header['org_id']) {
                        throw new \RuntimeException('Organisasi detail SKM tidak sesuai header.');
                    }
                    $existing = $this->db->table($targetDetail)->where('response_id', $id)->where('question_id', $detail['question_id'])->get()->getRowArray();
                    if (!$existing) {
                        if (!$this->db->table($targetDetail)->insert($detail)) throw new \RuntimeException('Gagal menyalin detail SKM.');
                    } elseif (!$this->sameRow($detail, $existing)) {
                        throw new \RuntimeException('Konflik detail SKM; sumber tetap dipertahankan.');
                    }
                }
                // Verify exact values and cardinality before removing this response from the source tables.
                $copy = $this->db->table($targetHeader)->where('response_id', $id)->get()->getRowArray();
                if (!$this->sameRow($header, $copy) || $this->db->table($targetDetail)->where('response_id', $id)->countAllResults() !== count($details)) {
                    throw new \RuntimeException('Verifikasi salinan SKM gagal.');
                }
                if (!$this->db->table($sourceDetail)->where('response_id', $id)->delete()
                    || !$this->db->table($sourceHeader)->where('response_id', $id)->delete()) {
                    throw new \RuntimeException('Gagal memindahkan sumber SKM.');
                }
            }
            if ($this->db->transStatus() === false || !$this->db->transCommit()) throw new \RuntimeException('Transaksi pemindahan SKM gagal.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    private function sameRow(array $source, array $target): bool
    {
        ksort($source);
        ksort($target);
        return $source === $target;
    }

    public function down()
    {
        $this->move('skm', 'prem');
        $this->forge->dropTable('dt01_pe_skm_response_dt', true);
        $this->forge->dropTable('dt01_pe_skm_response_hd', true);
        $this->db->resetDataCache();
    }
}