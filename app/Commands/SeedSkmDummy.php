<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Pe\Config\InpatientRespondent;
use Modules\Pe\Models\SkmModel;

class SeedSkmDummy extends BaseCommand
{
    protected $group = 'Patient Experience';
    protected $name = 'pe:seed-skm-dummy';
    protected $description = 'Create 100 dummy SKM responses per service for January-November 2026 in one tenant.';

    public function run(array $params)
    {
        [$group, $org] = array_pad($params, 2, null);
        if (!$group || !$org) throw new \InvalidArgumentException('Gunakan pe:seed-skm-dummy group_id org_id.');
        $db = db_connect();
        $model = new SkmModel();
        if (!$model->responsesReady() || !$db->fieldExists('clinic', 'dt01_pe_skm_response_hd')) {
            throw new \RuntimeException('Jalankan pe:setup terlebih dahulu.');
        }
        $instruments = $model->availableInstruments($group, $org);
        foreach (['IGD', 'RJ', 'RI'] as $service) {
            if (!isset($instruments[$service])) throw new \RuntimeException('Instrumen SKM ' . $service . ' belum tersedia pada organisasi ini.');
        }
        $created = 0;
        $summary = [];
        $db->transBegin();
        try {
            foreach (['IGD', 'RJ', 'RI'] as $service) {
                $instrument = $instruments[$service];
                $questions = $model->questions($instrument['instrument_id']);
                if (count($questions) !== 9) throw new \RuntimeException('SKM harus memiliki 9 pertanyaan aktif.');
                for ($i = 0; $i < 100; $i++) {
                    $seed = $service . '/' . $i;
                    $month = $i % 11 + 1;
                    $day = 1 + $this->number($seed . '/day', 28);
                    $date = sprintf('2026-%02d-%02d %02d:%02d:00', $month, $day, 7 + $this->number($seed . '/hour', 12), $this->number($seed . '/minute', 60));
                    $mr = sprintf('DUMMY-SKM26-%s-%04d', $service, $i + 1);
                    $key = hash('sha256', 'skm-dummy-jan-nov-2026-v1/' . $group . '/' . $org . '/' . $seed);
                    $context = ['service_type' => $service, 'medical_record_number' => $mr,
                        'respondent_name' => sprintf('DUMMY SKM %s %03d', $service, $i + 1),
                        'respondent_age' => 1 + $this->number($seed . '/age', 85),
                        'occupation_other' => 'Pekerjaan dummy lainnya',
                        'clinic' => $service === 'RJ' ? InpatientRespondent::CLINICS[$i % count(InpatientRespondent::CLINICS)] : null,
                        'suggestions' => [null, 'DUMMY: Pertahankan keramahan petugas.', 'DUMMY: Mohon waktu tunggu dipersingkat.', 'DUMMY: Informasi alur pelayanan agar lebih jelas.'][$this->number($seed . '/suggestion', 4)],
                    ];
                    foreach (InpatientRespondent::OPTIONS as $field => $definition) {
                        $keys = array_keys($definition['choices']);
                        $context[$field] = $keys[$this->number($seed . '/' . $field, count($keys))];
                    }
                    if ($service !== 'RI') $context['ward_class'] = 'KELAS_3';
                    $answers = [];
                    foreach ($questions as $question) {
                        $answers[$question['question_id']] = [1, 2, 2, 3, 3, 3, 3, 4, 4, 4][$this->number($seed . '/' . $question['question_id'], 10)];
                    }
                    $existing = $model->submitted($instrument['instrument_id'], $key);
                    if (!$existing) {
                        $id = $model->storeResponse($instrument, $questions, $answers, $key, $context);
                        if (!$db->table('dt01_pe_skm_response_hd')->where('response_id', $id)->where('group_id', $group)->where('org_id', $org)->update(['submitted_at' => $date])) {
                            throw new \RuntimeException('Gagal menetapkan tanggal dummy.');
                        }
                        $created++;
                    } else {
                        $id = $existing['response_id'];
                    }
                    $row = $model->responseHeader($id, $group, $org);
                    if (!$row || $row['submitted_at'] !== $date || $row['medical_record_number'] !== $mr
                        || $row['service_type'] !== $service || (int) $row['score_scale'] !== 4
                        || ($row['clinic'] ?? null) !== $context['clinic'] || $row['ward_class'] !== $context['ward_class']) {
                        throw new \RuntimeException('Verifikasi header dummy SKM gagal.');
                    }
                    $details = $model->responseDetails($id, $group, $org);
                    if (count($details) !== count($questions)) throw new \RuntimeException('Jawaban dummy SKM tidak lengkap.');
                    foreach ($details as $detail) {
                        if ((int) $detail['score'] !== $answers[$detail['question_id']]) throw new \RuntimeException('Verifikasi skor dummy SKM gagal.');
                    }
                    $summary[substr($date, 0, 7)][$service] = ($summary[substr($date, 0, 7)][$service] ?? 0) + 1;
                }
            }
            if (!$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Transaksi dummy SKM gagal.');
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
        CLI::write('Data baru: ' . $created . '. Terverifikasi: SKM IGD 100, Rawat Jalan 100, Rawat Inap 100; total 300 respons dan 2700 jawaban.', 'green');
        ksort($summary);
        foreach ($summary as $month => $counts) CLI::write($month . ': IGD ' . $counts['IGD'] . ', Rawat Jalan ' . $counts['RJ'] . ', Rawat Inap ' . $counts['RI']);
    }

    private function number(string $seed, int $limit): int
    {
        return (int) hexdec(substr(hash('sha256', $seed), 0, 6)) % $limit;
    }
}
