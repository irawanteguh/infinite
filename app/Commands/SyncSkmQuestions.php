<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Pe\Config\GeneralQuestionnaires;
use Modules\Pe\Config\SkmQuestionnaire;

class SyncSkmQuestions extends BaseCommand
{
    protected $group = 'Patient Experience';
    protected $name = 'pe:sync-skm';
    protected $description = 'Match IGD/RJ SKM questions to RI and delete unreferenced drafts for <group_id> <org_id>.';

    public function run(array $params)
    {
        if (count($params) !== 2) throw new \InvalidArgumentException('Gunakan: pe:sync-skm <group_id> <org_id>');
        [$group, $org] = $params;
        foreach ([$group, $org] as $value) if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9_-]{0,63}\z/', $value)) throw new \InvalidArgumentException('Identitas organisasi tidak valid.');
        $db = db_connect();
        $scoped = static function ($table) use ($db, $group, $org) {
            return $db->table($table)->where('group_id', $group)->where('org_id', $org);
        };
        $instruments = [];
        foreach (GeneralQuestionnaires::SERVICES as $service => $definition) {
            $row = $scoped('dt01_pe_instrument_ms')->where('code', $definition['code'])->get()->getRowArray();
            if (!$row) throw new \RuntimeException('Instrumen ' . $definition['name'] . ' belum tersedia.');
            $instruments[$service] = $row;
        }
        $reference = $scoped('dt01_pe_question_ms')->where('instrument_id', $instruments['RI']['instrument_id'])
            ->where('active', '1')->orderBy('sort_order')->get()->getResultArray();
        if (count($reference) !== 9) throw new \RuntimeException('SKM Rawat Inap harus memiliki 9 pertanyaan aktif.');
        foreach ($reference as $row) if (count(SkmQuestionnaire::options($row)) !== 4) throw new \RuntimeException('Pilihan referensi SKM harus berjumlah empat.');
        $referenced = static function ($id) use ($scoped) {
            foreach (['dt01_pe_skm_response_dt', 'dt01_pe_prem_response_dt', 'dt01_pe_prom_response_dt'] as $table) {
                if ($scoped($table)->where('question_id', $id)->countAllResults()) return true;
            }
            return false;
        };
        $deleted = 0;
        $archived = 0;
        $db->transBegin();
        try {
            foreach ($instruments as $service => $instrument) {
                $rows = $scoped('dt01_pe_question_ms')->where('instrument_id', $instrument['instrument_id'])
                    ->orderBy('active', 'DESC')->orderBy('sort_order')->get()->getResultArray();
                if ($service === 'RI') {
                    $keep = array_column($reference, 'question_id');
                } else {
                    $keep = [];
                    foreach ($reference as $source) {
                        foreach ($rows as $row) {
                            if (!in_array($row['question_id'], $keep, true) && $row['question'] === $source['question']
                                && $row['domain_id'] === $source['domain_id']
                                && SkmQuestionnaire::options($row) === SkmQuestionnaire::options($source)) {
                                $keep[] = $row['question_id'];
                                break;
                            }
                        }
                    }
                    // Free the display positions without colliding with archived/retained questions.
                    $max = $rows ? max(array_column($rows, 'sort_order')) : 0;
                    $db->query('UPDATE dt01_pe_question_ms SET sort_order = sort_order + ?
                        WHERE instrument_id = ? AND group_id = ? AND org_id = ? ORDER BY sort_order DESC',
                        [(int) $max + 1000, $instrument['instrument_id'], $group, $org]);
                }
                foreach ($rows as $row) {
                    if (in_array($row['question_id'], $keep, true)) continue;
                    $query = $scoped('dt01_pe_question_ms')->where('instrument_id', $instrument['instrument_id'])->where('question_id', $row['question_id']);
                    if ($referenced($row['question_id'])) {
                        if (!$query->update(['active' => '0'])) throw new \RuntimeException('Gagal mengarsipkan pertanyaan historis.');
                        $archived++;
                    } else {
                        if (!$query->delete()) throw new \RuntimeException('Gagal menghapus draf tidak terpakai.');
                        $deleted++;
                    }
                }
                if ($service !== 'RI') {
                    $used = [];
                    foreach ($reference as $index => $source) {
                        $existingId = null;
                        foreach ($rows as $row) {
                            if (in_array($row['question_id'], $keep, true) && !in_array($row['question_id'], $used, true)
                                && $row['question'] === $source['question']
                                && SkmQuestionnaire::options($row) === SkmQuestionnaire::options($source)) {
                                $existingId = $row['question_id'];
                                $used[] = $existingId;
                                break;
                            }
                        }
                        $now = date('Y-m-d H:i:s');
                        $data = [
                            'domain_id' => $source['domain_id'], 'domain' => $source['domain'], 'question' => $source['question'],
                            'sort_order' => $index + 1, 'answer_options' => $source['answer_options'],
                            'active' => '1', 'updated_by' => 'skm-sync', 'updated_at' => $now,
                        ];
                        if ($existingId) {
                            $ok = $scoped('dt01_pe_question_ms')->where('instrument_id', $instrument['instrument_id'])->where('question_id', $existingId)->update($data);
                        } else {
                            $data += [
                                'question_id' => $db->query('SELECT UUID() AS id')->getRow()->id,
                                'instrument_id' => $instrument['instrument_id'], 'group_id' => $group, 'org_id' => $org,
                                'created_by' => 'skm-sync', 'created_at' => $now,
                            ];
                            $ok = $db->table('dt01_pe_question_ms')->insert($data);
                        }
                        if (!$ok) throw new \RuntimeException('Gagal menyamakan pertanyaan SKM.');
                    }
                }
                $active = $scoped('dt01_pe_question_ms')->where('instrument_id', $instrument['instrument_id'])->where('active', '1')
                    ->orderBy('sort_order')->get()->getResultArray();
                if (count($active) !== 9) throw new \RuntimeException('Jumlah pertanyaan SKM tidak sesuai.');
                foreach ($active as $index => $row) {
                    if ($row['question'] !== $reference[$index]['question'] || SkmQuestionnaire::options($row) !== SkmQuestionnaire::options($reference[$index])) {
                        throw new \RuntimeException('Verifikasi pertanyaan SKM gagal.');
                    }
                }
                CLI::write($instrument['name'] . ': 9 pertanyaan, 4 pilihan, sesuai SKM Rawat Inap.');
            }
            if ($db->transStatus() === false || !$db->transCommit()) throw new \RuntimeException('Sinkronisasi SKM gagal.');
            CLI::write('Draf tidak terpakai dihapus: ' . $deleted . '. Pertanyaan berjawaban yang dipertahankan sebagai nonaktif: ' . $archived . '.', 'green');
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }
}