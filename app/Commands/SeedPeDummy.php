<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Pe\Models\PremModel;
use Modules\Pe\Models\PromModel;

class SeedPeDummy extends BaseCommand
{
    protected $group = 'Patient Experience';
    protected $name = 'pe:seed-dummy';
    protected $description = 'Create 100 PREM and 100 paired PROM T0/T1 dummy responses for January-November 2026.';

    public function run(array $params)
    {
        $db = db_connect();
        $premModel = new PremModel();
        $promModel = new PromModel();
        if (!$premModel->responsesReady() || !$promModel->responsesReady()) {
            throw new \RuntimeException('Jalankan pe:setup terlebih dahulu.');
        }
        $instruments = $db->table('dt01_pe_instrument_ms')->where('active', '1')->whereIn('type', ['PREM', 'PROM'])->whereNotIn('code', array_column(\Modules\Pe\Config\GeneralQuestionnaires::SERVICES, 'code'))
            ->orderBy('type')->orderBy('code')->get()->getResultArray();
        $prem = [];
        $prom = [];
        $scopes = [];
        $questions = [];
        foreach ($instruments as $instrument) {
            $model = $instrument['type'] === 'PREM' ? $premModel : $promModel;
            $items = $model->questions($instrument['instrument_id']);
            if (!$items) continue;
            $questions[$instrument['instrument_id']] = $items;
            $scopes[$instrument['group_id'] . '/' . $instrument['org_id']] = true;
            if ($instrument['type'] === 'PREM') $prem[] = $instrument;
            if ($instrument['type'] === 'PROM') $prom[] = $instrument;
        }
        if (!$prem || !$prom || count($scopes) !== 1) {
            throw new \RuntimeException('Diperlukan instrumen PREM dan PROM aktif dalam satu organisasi yang sama.');
        }

        $created = 0;
        $expected = [];
        $pairs = [];
        $db->transBegin();
        try {
            for ($i = 0; $i < 100; $i++) {
                $month = ($i % 11) + 1;
                $day = intdiv($i, 11) * 2 + 1;
                $date = new \DateTimeImmutable(sprintf('2026-%02d-%02d 08:%02d:00', $month, $day, ($i * 7) % 60));
                $service = ['RJ', 'RI', 'IGD'][$i % 3];
                $mr = sprintf('DUMMY-PE2026-%04d', $i + 1);
                $premInstrument = $prem[$i % count($prem)];
                $promInstrument = $prom[$i % count($prom)];
                $preAnswers = [];
                foreach ($questions[$promInstrument['instrument_id']] as $question) {
                    $preAnswers[$question['question_id']] = 1 + $this->number($mr . $question['question_id'], 4);
                }
                foreach (['PREM', 'T0', 'T1'] as $phase) {
                    $isPrem = $phase === 'PREM';
                    $model = $isPrem ? $premModel : $promModel;
                    $instrument = $isPrem ? $premInstrument : $promInstrument;
                    $items = $questions[$instrument['instrument_id']];
                    $table = $isPrem ? 'dt01_pe_prem_response_hd' : 'dt01_pe_prom_response_hd';
                    $key = hash('sha256', 'pe-dummy-jan-nov-2026-v1/' . $instrument['instrument_id'] . '/' . $mr . '/' . $phase);
                    $submittedAt = ($phase === 'T1' ? $date->modify('+' . (3 + $i % 5) . ' days') : $date)->format('Y-m-d H:i:s');
                    $answers = [];
                    foreach ($items as $question) {
                        $id = $question['question_id'];
                        if ($isPrem) {
                            $answers[$id] = [2, 3, 3, 4, 4, 4, 5, 5][$this->number($mr . $id . '/prem', 8)];
                        } elseif ($phase === 'T0') {
                            $answers[$id] = $preAnswers[$id];
                        } else {
                            $delta = [-1, 0, 1, 1, 1, 2][$this->number($mr . $id . '/post', 6)];
                            $answers[$id] = max(1, min(5, $preAnswers[$id] + $delta));
                        }
                    }
                    $existing = $model->submitted($instrument['instrument_id'], $key);
                    if (!$existing) {
                        $context = ['service_type' => $service, 'medical_record_number' => $mr];
                        if (!$isPrem) $context['measurement_phase'] = $phase;
                        $id = $model->storeResponse($instrument, $items, $answers, $key, $context);
                        if (!$db->table($table)->where('response_id', $id)->update(['submitted_at' => $submittedAt])) {
                            throw new \RuntimeException('Gagal menyimpan tanggal dummy.');
                        }
                        $created++;
                    }
                    $expected[] = [$table, $key, $instrument, $items, $answers, $service, $mr, $submittedAt, $phase];
                    if (!$isPrem) $pairs[$mr][$phase] = $submittedAt;
                }
            }
            $summary = [];
            foreach ($expected as [$table, $key, $instrument, $items, $answers, $service, $mr, $submittedAt, $phase]) {
                $row = $db->table($table)->where('submission_key', $key)->get()->getRowArray();
                if (!$row || $row['submitted_at'] !== $submittedAt || $row['medical_record_number'] !== $mr
                    || $row['service_type'] !== $service || $row['instrument_id'] !== $instrument['instrument_id']
                    || ($phase !== 'PREM' && $row['measurement_phase'] !== $phase)) {
                    throw new \RuntimeException('Verifikasi header dummy gagal.');
                }
                $detailTable = str_replace('_hd', '_dt', $table);
                $details = $db->table($detailTable)->where('response_id', $row['response_id'])->get()->getResultArray();
                if (count($details) !== count($items)) throw new \RuntimeException('Jawaban dummy tidak lengkap.');
                foreach ($details as $detail) {
                    if ((int) $detail['score'] !== $answers[$detail['question_id']]) {
                        throw new \RuntimeException('Verifikasi skor dummy gagal.');
                    }
                }
                $month = substr($submittedAt, 0, 7);
                $summary[$month][$phase] = ($summary[$month][$phase] ?? 0) + 1;
            }
            foreach ($pairs as $pair) {
                if ($pair['T1'] <= $pair['T0']) throw new \RuntimeException('Tanggal POST harus sesudah PRE.');
            }
            if ($db->transStatus() === false || !$db->transCommit()) throw new \RuntimeException('Transaksi dummy gagal.');
            ksort($summary);
            CLI::write('Data baru: ' . $created . '. Terverifikasi: PREM 100, PROM PRE/T0 100, PROM POST/T1 100.', 'green');
            CLI::write('PROM: 100 pasangan rekam medis dummy; semua jawaban lengkap dan skor 1-5.');
            foreach ($summary as $month => $counts) {
                CLI::write($month . ': PREM ' . $counts['PREM'] . ', PRE ' . $counts['T0'] . ', POST ' . $counts['T1']);
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    private function number(string $seed, int $limit): int
    {
        return (int) hexdec(substr(hash('sha256', $seed), 0, 6)) % $limit;
    }
}