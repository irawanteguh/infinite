<?php

namespace Modules\Pe\Models;

use CodeIgniter\Model;

class PremModel extends Model
{
    protected $DBGroup = 'default';
    protected $skmTransactions = false;
    protected $instrumentType = 'PREM';
    protected $responseHeader = 'dt01_pe_prem_response_hd';
    protected $responseDetail = 'dt01_pe_prem_response_dt';

    public function resolveInstrument($id = null, $group = null, $org = null): ?array
    {
        $builder = $this->db->table('dt01_pe_instrument_ms')->where('type', $this->instrumentType)->where('active', '1');
        if ($group && $org) $builder->where('group_id', $group)->where('org_id', $org);
        $codes = array_column(\Modules\Pe\Config\GeneralQuestionnaires::SERVICES, 'code');
        if ($this->skmTransactions) $builder->whereIn('code', $codes);
        else $builder->whereNotIn('code', $codes);
        if ($id !== null && $id !== '') {
            return $builder->where('instrument_id', $id)->get()->getRowArray();
        }

        // Never choose an arbitrary organization when several questionnaires exist.
        $rows = $builder->limit(2)->get()->getResultArray();
        return count($rows) === 1 ? $rows[0] : null;
    }

    public function questions($instrumentId): array
    {
        return $this->db->table('dt01_pe_question_ms q')
            ->select('q.question_id, q.domain_id, q.question, q.sort_order, q.answer_options, d.name AS domain')
            ->join('dt01_pe_instrument_ms i', 'i.instrument_id = q.instrument_id AND i.group_id = q.group_id AND i.org_id = q.org_id')
            ->join('dt01_pe_domain_ms d', 'd.domain_id = q.domain_id AND d.group_id = i.group_id AND d.org_id = i.org_id')
            ->where('i.instrument_id', $instrumentId)->where('i.type', $this->instrumentType)->where('i.active', '1')
            ->where('q.active', '1')->where('d.active', '1')->orderBy('q.sort_order')->get()->getResultArray();
    }

    public function responsesReady(): bool
    {
        return $this->db->tableExists($this->responseHeader) && $this->db->tableExists($this->responseDetail)
            && $this->db->fieldExists('service_type', $this->responseHeader)
            && $this->db->fieldExists('medical_record_number', $this->responseHeader);
    }

    public static function revision(array $questions): string
    {
        return hash('sha256', json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function validateAnswers(array $questions, $answers): array
    {
        if (!$questions || !is_array($answers) || count($answers) !== count($questions)) {
            throw new \InvalidArgumentException('Mohon jawab seluruh pertanyaan sebelum mengirim.');
        }
        $validated = [];
        foreach ($questions as $question) {
            $id = $question['question_id'];
            $value = $answers[$id] ?? null;
            if ((!is_string($value) && !is_int($value)) || !in_array((string) $value, array_map('strval', array_keys(\Modules\Pe\Config\SkmQuestionnaire::options($question))), true)) {
                throw new \InvalidArgumentException('Pilih satu jawaban yang tersedia untuk setiap pertanyaan.');
            }
            $validated[$id] = (int) $value;
        }
        return $validated;
    }

    public function validateVisit($serviceType, $medicalRecordNumber): array
    {
        if (!is_string($serviceType) || !in_array($serviceType, ['RJ', 'RI', 'IGD'], true)) {
            throw new \InvalidArgumentException('Pilih jenis pelayanan Rawat Jalan, Rawat Inap, atau IGD.');
        }
        if (!is_string($medicalRecordNumber)) {
            throw new \InvalidArgumentException('Masukkan nomor rekam medis pasien.');
        }
        $medicalRecordNumber = trim($medicalRecordNumber);
        if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9.\/-]{0,49}\z/', $medicalRecordNumber)) {
            throw new \InvalidArgumentException('Nomor rekam medis wajib diisi, maksimal 50 karakter berupa huruf, angka, titik, garis miring, atau tanda hubung.');
        }
        return ['service_type' => $serviceType, 'medical_record_number' => $medicalRecordNumber];
    }

    protected function responseMetadata(array $context): array
    {
        return $this->validateVisit($context['service_type'] ?? null, $context['medical_record_number'] ?? null);
    }

    public function submitted($instrumentId, $key): ?array
    {
        return $this->db->table($this->responseHeader)->where('instrument_id', $instrumentId)
            ->where('submission_key', $key)->get()->getRowArray();
    }

    public function responseHeader(string $responseId, string $group, string $org): ?array
    {
        return $this->db->table($this->responseHeader . ' h')
            ->select('h.*, i.name AS instrument_name')
            ->join('dt01_pe_instrument_ms i', 'i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id')
            ->where('h.response_id', $responseId)->where('h.group_id', $group)->where('h.org_id', $org)
            ->where('i.type', $this->instrumentType)->get()->getRowArray();
    }
    public function responseDetails(string $responseId, string $group, string $org): array
    {
        return $this->db->table($this->responseDetail . ' r')
            ->select('r.response_id, r.question_id, r.domain_id, d.name AS domain_name, q.question AS question_text, r.sort_order, r.score')
            ->join($this->responseHeader . ' h', 'h.response_id = r.response_id AND h.group_id = r.group_id AND h.org_id = r.org_id')
            ->join('dt01_pe_instrument_ms i', 'i.instrument_id = h.instrument_id AND i.group_id = h.group_id AND i.org_id = h.org_id')
            ->join('dt01_pe_question_ms q', 'q.question_id = r.question_id AND q.instrument_id = h.instrument_id AND q.group_id = h.group_id AND q.org_id = h.org_id')
            ->join('dt01_pe_domain_ms d', 'd.domain_id = r.domain_id AND d.group_id = h.group_id AND d.org_id = h.org_id')
            ->where('r.response_id', $responseId)->where('h.group_id', $group)->where('h.org_id', $org)
            ->where('i.type', $this->instrumentType)->orderBy('r.sort_order')->get()->getResultArray();
    }
    public function storeResponse(array $instrument, array $questions, array $answers, string $key, array $context = []): string
    {
        $existing = $this->submitted($instrument['instrument_id'], $key);
        if ($existing) return $existing['response_id'];
        $fixedService = \Modules\Pe\Config\GeneralQuestionnaires::service($instrument['code']);
        $isSkmInstrument = ($instrument['type'] ?? '') === 'SKM' || $fixedService !== null;
        if ($isSkmInstrument !== $this->skmTransactions) {
            throw new \InvalidArgumentException('Gunakan formulir transaksi yang sesuai: SKM atau PREM/PROM.');
        }
        if ($fixedService !== null && ($context['service_type'] ?? null) !== $fixedService) {
            throw new \InvalidArgumentException('Jenis pelayanan harus sesuai dengan kuesioner yang dipilih.');
        }
        $linkedSkm = $this->instrumentType === 'PREM' && !$this->skmTransactions && !empty($context['skm_response_id']);
        if ($linkedSkm) {
            $source = $this->db->table('dt01_pe_skm_response_hd')->where('response_id', $context['skm_response_id'])
                ->where('group_id', $instrument['group_id'])->where('org_id', $instrument['org_id'])->get()->getRowArray();
            if (!$source || $source['medical_record_number'] !== ($context['medical_record_number'] ?? null) || $source['service_type'] !== ($context['service_type'] ?? null)) {
                throw new \InvalidArgumentException('Data lanjutan PREM tidak sesuai dengan respons SKM.');
            }
            $previous = $this->db->table($this->responseHeader)->where('skm_response_id', $context['skm_response_id'])->where('group_id', $instrument['group_id'])->where('org_id', $instrument['org_id'])->get()->getRowArray();
            if ($previous) return $previous['response_id'];
        }
        $respondent = ($isSkmInstrument || ($linkedSkm && isset($context['respondent_name'])))
            ? \Modules\Pe\Config\InpatientRespondent::validate($context, ($context['service_type'] ?? '') === 'RJ') : [];
        $answers = $this->validateAnswers($questions, $answers);
        $survey = [];
        if (in_array($this->instrumentType, ['PREM', 'SKM'], true)) {
            $scales = array_unique(array_map(static fn($question) => count(\Modules\Pe\Config\SkmQuestionnaire::options($question)), $questions));
            if (count($scales) !== 1) throw new \InvalidArgumentException('Skala jawaban dalam satu kuesioner harus sama.');
            $suggestions = $context['suggestions'] ?? '';
            if (!is_string($suggestions) || mb_strlen($suggestions) > 4000) throw new \InvalidArgumentException('Saran dan masukan maksimal 4000 karakter.');
            $survey = ['score_scale' => (int) reset($scales), 'suggestions' => trim($suggestions) ?: null];
        }
        if ($linkedSkm) $survey['skm_response_id'] = $context['skm_response_id'];
        $responseId = $this->db->query('SELECT UUID() AS id')->getRow()->id;
        $this->db->transBegin();
        try {
            if (!$this->db->table($this->responseHeader)->insert([
                'response_id' => $responseId, 'instrument_id' => $instrument['instrument_id'],
                'group_id' => $instrument['group_id'], 'org_id' => $instrument['org_id'],
                'submission_key' => $key,
                'submitted_at' => date('Y-m-d H:i:s'),
            ] + $this->responseMetadata($context) + $respondent + $survey)) throw new \RuntimeException('Failed to insert PREM response.');
            $rows = [];
            foreach ($questions as $question) {
                $rows[] = [
                    'response_id' => $responseId, 'question_id' => $question['question_id'],
                    'group_id' => $instrument['group_id'], 'org_id' => $instrument['org_id'],
                    'domain_id' => $question['domain_id'],
                    'sort_order' => $question['sort_order'],
                    'score' => $answers[$question['question_id']],
                ];
            }
            if (!$rows || !$this->db->table($this->responseDetail)->insertBatch($rows)
                || $this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new \RuntimeException('Failed to save all PREM answers.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            // A retry with the same form key returns the committed response instead of duplicating it.
            $existing = $this->submitted($instrument['instrument_id'], $key);
            if ($existing) return $existing['response_id'];
            throw $e;
        }
        return $responseId;
    }
}
