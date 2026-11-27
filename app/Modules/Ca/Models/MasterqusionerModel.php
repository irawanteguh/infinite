<?php

namespace Modules\Ca\Models;

use CodeIgniter\Model;
use Modules\Ca\Config\ScreeningMasters;

class MasterqusionerModel extends Model
{
    private const INSTRUMENTS = 'dt01_ca_instrument_ms';
    private const QUESTIONS = 'dt01_ca_question_ms';

    public function ready(): bool
    {
        return $this->db->tableExists(self::INSTRUMENTS) && $this->db->tableExists(self::QUESTIONS);
    }

    private function instruments(string $group, string $org)
    {
        return $this->db->table(self::INSTRUMENTS)->where('group_id', $group)->where('org_id', $org);
    }

    public function masters(string $group, string $org, bool $ready, bool $includeCustom = false): array
    {
        $saved = $ready ? array_column($this->instruments($group, $org)->get()->getResultArray(), null, 'code') : [];
        $result = [];

        $definitions = ScreeningMasters::INSTRUMENTS;
        if ($includeCustom) {
            $definitions += array_fill_keys(array_keys($saved), null);
        }
        foreach ($definitions as $code => $definition) {
            $instrument = $saved[$code] ?? ['name' => $definition['name'], 'active' => '1'];
            $questions = [];
            if (isset($instrument['instrument_id'])) {
                $questions = $this->questions($group, $org, $instrument['instrument_id']);
            } else {
                foreach ($definition['questions'] as $index => [$question, $options]) {
                    $questions[] = ['question' => $question, 'answer_options' => $options, 'sort_order' => $index + 1, 'active' => '1'];
                }
            }

            $result[] = [
                'code' => $code,
                'name' => $instrument['name'],
                'active' => $instrument['active'],
                'initialized' => isset($instrument['instrument_id']),
                'writable' => $ready,
                'risks' => $this->riskRanges($instrument, $code),
                'custom' => $definition === null,
                'questions' => $questions,
            ];
        }
        return $result;
    }

    public function activeInstrument(string $group, string $org, string $code): ?array
    {
        $instrument = $this->instruments($group, $org)->where('active', '1')->where('code', $code)->get()->getRowArray();
        if ($instrument) {
            $instrument['risks'] = $this->riskRanges($instrument, $code);
            $instrument['questions'] = $this->questions($group, $org, $instrument['instrument_id'], true);
        }
        return $instrument;
    }

    private function riskRanges(array $instrument, string $code): array
    {
        return empty($instrument['risk_ranges'])
            ? (ScreeningMasters::INSTRUMENTS[$code]['risks'] ?? [])
            : json_decode($instrument['risk_ranges'], true, 512, JSON_THROW_ON_ERROR);
    }

    private function scopedQuestions(string $group, string $org, string $instrumentId)
    {
        return $this->db->table(self::QUESTIONS)
            ->where('group_id', $group)->where('org_id', $org)->where('instrument_id', $instrumentId);
    }

    private function questions(string $group, string $org, string $instrumentId, bool $activeOnly = false): array
    {
        $builder = $this->scopedQuestions($group, $org, $instrumentId);
        if ($activeOnly) {
            $builder->where('active', '1');
        }
        $questions = $builder->orderBy('sort_order')->get()->getResultArray();
        foreach ($questions as &$question) {
            $question['answer_options'] = json_decode($question['answer_options'], true, 512, JSON_THROW_ON_ERROR);
        }
        return $questions;
    }

    public function installDefaults(string $group, string $org, string $code, array $audit): void
    {
        $definition = ScreeningMasters::INSTRUMENTS[$code];
        $this->db->transBegin();
        try {
            if (!$this->instruments($group, $org)->where('code', $code)->countAllResults()) {
                $this->checkWrite($this->db->table(self::INSTRUMENTS)->insert($audit + [
                    'group_id' => $group, 'org_id' => $org, 'code' => $code, 'name' => $definition['name'],
                ]));
                $instrumentId = $this->db->insertID();
                $rows = [];
                foreach ($definition['questions'] as $index => [$question, $options]) {
                    $rows[] = $audit + [
                        'group_id' => $group, 'org_id' => $org,
                        'instrument_id' => $instrumentId, 'question' => $question, 'sort_order' => $index + 1,
                        'answer_options' => json_encode($options, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    ];
                }
                $this->checkWrite($this->db->table(self::QUESTIONS)->insertBatch($rows) === count($rows));
            }

            $this->checkWrite($this->db->transStatus() && $this->db->transCommit());
        } catch (\Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }

    public function saveInstrument(string $group, string $org, string $code, array $data): void
    {
        $instrumentId = $this->instrumentId($group, $org, $code);
        if (isset($data['risk_ranges'])) {
            $data['risk_ranges'] = json_encode($data['risk_ranges'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }
        $this->checkWrite($this->instruments($group, $org)->where('instrument_id', $instrumentId)->update($data));
    }

    public function createInstrument(string $group, string $org, string $code, array $data): void
    {
        if (isset(ScreeningMasters::INSTRUMENTS[$code]) || $this->instruments($group, $org)->where('code', $code)->countAllResults()) {
            throw new \InvalidArgumentException('Kode instrumen sudah digunakan.');
        }
        $this->checkWrite($this->db->table(self::INSTRUMENTS)->insert($data + [
            'group_id' => $group, 'org_id' => $org, 'code' => $code, 'active' => '0',
        ]));
    }

    public function saveQuestion(string $group, string $org, string $code, string $id, array $data): void
    {
        $instrumentId = $this->instrumentId($group, $org, $code);
        $questions = function () use ($group, $org, $instrumentId) {
            return $this->scopedQuestions($group, $org, $instrumentId);
        };

        if ($id !== '' && !$questions()->where('question_id', $id)->countAllResults()) {
            throw new \InvalidArgumentException('Pertanyaan tidak ditemukan pada instrumen ini.');
        }
        $duplicate = $questions()->where('sort_order', $data['sort_order']);
        if ($id !== '') {
            $duplicate->where('question_id !=', $id);
        }
        if ($duplicate->countAllResults()) {
            throw new \InvalidArgumentException('Nomor urut sudah digunakan.');
        }

        $data['answer_options'] = json_encode($data['answer_options'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $this->checkWrite($id !== ''
            ? $questions()->where('question_id', $id)->update($data)
            : $this->db->table(self::QUESTIONS)->insert($data + [
                'group_id' => $group, 'org_id' => $org, 'instrument_id' => $instrumentId,
            ]));
    }

    private function instrumentId(string $group, string $org, string $code): string
    {
        $instrument = $this->instruments($group, $org)->where('code', $code)->get()->getRowArray();
        if (!$instrument) {
            throw new \InvalidArgumentException('Simpan master awal untuk organisasi ini terlebih dahulu.');
        }
        return (string) $instrument['instrument_id'];
    }

    private function checkWrite(bool $success): void
    {
        if (!$success) {
            throw new \RuntimeException('Penyimpanan master CA gagal.');
        }
    }
}
