<?php

namespace Modules\Ca\Models;

use CodeIgniter\Model;
use Modules\Ca\Config\ScreeningMasters;
use Modules\Ca\Database\CreateScreeningTransactions;

class CaskriningModel extends Model
{
    public const DOMICILES = [
        'JAKARTA' => 'Jakarta',
        'LUAR_JAKARTA' => 'Di luar Jakarta',
    ];

    public static function revision(array $instrument): string
    {
        return hash('sha256', json_encode($instrument, JSON_THROW_ON_ERROR));
    }

    public function store(array $instrument, array $answers, ?string $name, ?string $domicile, string $user, ?string $phone = null, bool $contactConsent = false): array
    {
        $questions = $instrument['questions'];
        if (count($answers) !== count($questions)) {
            throw new \InvalidArgumentException('Lengkapi pilihan pada setiap pertanyaan.');
        }

        $details = [];
        foreach ($questions as $question) {
            $choice = $answers[$question['question_id']] ?? null;
            if (!is_string($choice) || !ctype_digit($choice) || !isset($question['answer_options'][$choice])) {
                throw new \InvalidArgumentException('Pilihan jawaban tidak valid. Periksa kembali setiap pertanyaan.');
            }

            [$label, $score] = $question['answer_options'][$choice];
            $details[] = [
                'question_id' => $question['question_id'],
                'sort_order' => $question['sort_order'],
                'question_text' => $question['question'],
                'answer_label' => $label,
                'answer_score' => $score,
            ];
        }

        $definition = ScreeningMasters::INSTRUMENTS[$instrument['code']];
        $total = count($details) === count($questions) ? array_sum(array_column($details, 'answer_score')) : null;
        $risk = null;
        if ($total !== null && (!empty($instrument['risk_ranges']) || self::matchesSource($questions, $definition['questions']))) {
            foreach ($instrument['risks'] ?? $definition['risks'] as [$min, $max, $level]) {
                if (($min === null || $total >= $min) && $total <= $max) {
                    $risk = $level;
                    break;
                }
            }
        }

        $now = date('Y-m-d H:i:s');
        if (!$this->db->transBegin()) {
            throw new \RuntimeException('Gagal memulai transaksi CA.');
        }
        try {
            $scope = ['group_id' => $instrument['group_id'], 'org_id' => $instrument['org_id'], 'active' => '1'];
            $header = $this->db->table(CreateScreeningTransactions::HEADER);
            if (!$header->insert($scope + [
                'instrument_id' => $instrument['instrument_id'],
                'screened_at' => $now,
                'domicile' => $domicile,
                'respondent_name' => $name,
                'phone_number' => $phone,
                'contact_consent' => $contactConsent ? '1' : '0',
                'total_score' => $total,
                'risk_level' => $risk,
                'created_by' => $user,
                'created_at' => $now,
                'updated_by' => $user,
                'updated_at' => $now,
            ])) {
                throw new \RuntimeException('Gagal menyimpan header skrining.');
            }
            $responseId = $this->db->insertID();
            if ($details) {
                $rows = array_map(static function (array $detail) use ($responseId, $scope) {
                    return $detail + $scope + ['response_id' => $responseId];
                }, $details);
                if ($this->db->table(CreateScreeningTransactions::DETAIL)->insertBatch($rows) !== count($rows)) {
                    throw new \RuntimeException('Gagal menyimpan detail skrining.');
                }
            }
            if (!$this->db->transStatus() || !$this->db->transCommit()) {
                throw new \RuntimeException('Gagal menyelesaikan transaksi CA.');
            }
        } catch (\Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }

        return ['name' => $instrument['name'], 'total' => $total, 'risk' => $risk];
    }

    private static function matchesSource(array $questions, array $source): bool
    {
        $encode = static function (array $item): string {
            [$text, $options] = $item;
            sort($options);
            return json_encode([$text, $options], JSON_THROW_ON_ERROR);
        };
        $current = array_map(static function (array $question) {
            return [$question['question'], $question['answer_options']];
        }, $questions);
        $current = array_map($encode, $current);
        $source = array_map($encode, $source);
        sort($current);
        sort($source);
        return $current === $source;
    }
}
