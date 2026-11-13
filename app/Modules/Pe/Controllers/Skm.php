<?php

namespace Modules\Pe\Controllers;

use Modules\Pe\Models\SkmModel;

class Skm extends Prem
{
    protected $modelClass = SkmModel::class;
    protected $formTokenKey = 'premgeneral_form_tokens';
    protected $surveyPath = 'pe/skm';
    protected $surveyType = 'SKM';

    public function index()
    {
        $tenant = ['group_id' => '', 'org_id' => ''];
        $instruments = [];
        $error = '';
        try {
            $tenant = $this->publicScope();
            $model = new SkmModel();
            $instruments = $model->availableInstruments($tenant['group_id'], $tenant['org_id']);
            $id = request()->getGet('instrument_id');
            if ($id !== null) {
                if (!is_string($id) || !preg_match('/^[a-f0-9-]{36}$/i', $id)) throw new \InvalidArgumentException('Tautan kuesioner tidak valid.');
                $instrument = $model->resolveInstrument($id, $tenant['group_id'], $tenant['org_id']);
                if (!$instrument || !in_array($id, array_column($instruments, 'instrument_id'), true)) {
                    throw new \InvalidArgumentException('Kuesioner tidak tersedia pada rumah sakit ini.');
                }
                if (!$model->responsesReady()) throw new \RuntimeException('PE response tables are not ready.');
                return $this->formPage($instrument, $model->questions($id));
            }
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
            response()->setStatusCode(422);
        } catch (\Throwable $e) {
            log_message('error', 'SKM: {message}', ['message' => $e->getMessage()]);
            $error = 'Kuesioner belum dapat dimuat. Silakan hubungi petugas.';
            response()->setStatusCode(503);
        }
        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Pe\Views\v_skm', compact('tenant', 'instruments', 'error')),
        ]);
    }

    protected function formPage(array $instrument, array $questions, ?string $phase = null)
    {
        $token = request()->getGet('form');
        $tokens = session()->get($this->formTokenKey) ?? [];
        if (is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token) && isset($tokens[$token])
            && ($tokens[$token]['instrument_id'] ?? '') === $instrument['instrument_id']
            && ($tokens[$token]['group_id'] ?? '') === $instrument['group_id']
            && ($tokens[$token]['org_id'] ?? '') === $instrument['org_id']) {
            $source = (new SkmModel())->submitted($instrument['instrument_id'], hash('sha256', $token));
            if ($source) {
                if (request()->getGet('edit') === '1') return $this->visitPage($instrument, $token, $tokens[$token]);
                $tenant = ['group_id' => $instrument['group_id'], 'org_id' => $instrument['org_id']];
                return view('template/dashboard-light-blank', [
                    'contents' => $this->continuationHtml($source['response_id'], $token, $tenant),
                ]);
            }
        }
        return parent::formPage($instrument, $questions, $phase);
    }

    public function visit()
    {
        $result = parent::visit();
        if (!($result instanceof \CodeIgniter\HTTP\ResponseInterface) || $result->getStatusCode() !== 303) return $result;
        $token = request()->getPost('submission_token');
        $context = (session()->get($this->formTokenKey) ?? [])[$token] ?? null;
        if (!$context) return $result;
        $source = (new SkmModel())->submitted($context['instrument_id'], hash('sha256', $token));
        if (!$source) return $result;
        $updates = array_intersect_key($context, array_flip(array_merge(
            ['medical_record_number'], \Modules\Pe\Config\InpatientRespondent::FIELDS
        )));
        db_connect()->table('dt01_pe_skm_response_hd')->where('response_id', $source['response_id'])
            ->where('group_id', $context['group_id'])->where('org_id', $context['org_id'])->update($updates);
        db_connect()->table('dt01_pe_prem_response_hd')->where('skm_response_id', $source['response_id'])
            ->where('group_id', $context['group_id'])->where('org_id', $context['org_id'])->update($updates);
        return $result;
    }

    public function submitprem()
    {
        // The second stage stays on the SKM page, but uses PREM validation and transaction tables.
        return (new Prem())->submit();
    }

    protected function success($id)
    {
        $payload = ['success' => true, 'responseId' => $id, 'csrfHash' => csrf_hash()];
        try {
            $token = request()->getPost('submission_token');
            if (!is_string($token)) throw new \InvalidArgumentException('Sesi pengisian tidak tersedia.');
            $payload['nextHtml'] = $this->continuationHtml($id, $token, $this->publicScope());
        } catch (\Throwable $e) {
            // The SKM response is committed; preserve success even if the next stage cannot be loaded.
            log_message('error', 'SKM inline PREM: {message}', ['message' => $e->getMessage()]);
            $payload['continuationMessage'] = 'Jawaban SKM sudah tersimpan. Tahap PREM belum dapat dimuat. Muat ulang halaman ini atau hubungi petugas.';
        }
        return response()->setJSON($payload);
    }

    protected function continuationHtml(string $id, string $sourceToken, array $tenant): string
    {
        $source = (new SkmModel())->responseHeader($id, $tenant['group_id'], $tenant['org_id']);
        $sourceTokens = session()->get($this->formTokenKey) ?? [];
        if (!$source || !isset($sourceTokens[$sourceToken])
            || ($sourceTokens[$sourceToken]['instrument_id'] ?? '') !== $source['instrument_id']
            || ($sourceTokens[$sourceToken]['group_id'] ?? '') !== $tenant['group_id']
            || ($sourceTokens[$sourceToken]['org_id'] ?? '') !== $tenant['org_id']) {
            throw new \InvalidArgumentException('Sesi lanjutan tidak sesuai dengan respons SKM.');
        }
        $prem = new \Modules\Pe\Models\PremModel();
        $instrument = db_connect()->table('dt01_pe_instrument_ms')->where('group_id', $tenant['group_id'])->where('org_id', $tenant['org_id'])
            ->where('code', 'PREM_RS')->where('type', 'PREM')->where('active', '1')->get()->getRowArray();
        $questions = $instrument ? $prem->questions($instrument['instrument_id']) : [];
        if (!$instrument || !$prem->responsesReady() || !$questions) throw new \InvalidArgumentException('PREM rumah sakit belum tersedia.');
        $premTokens = session()->get('prem_form_tokens') ?? [];
        $nextToken = $sourceTokens[$sourceToken]['prem_continuation_token'] ?? null;
        if (!is_string($nextToken) || !isset($premTokens[$nextToken])) {
            $nextToken = bin2hex(random_bytes(32));
            $context = $tenant + [
                'instrument_id' => $instrument['instrument_id'], 'service_type' => $source['service_type'],
                'medical_record_number' => $source['medical_record_number'], 'skm_response_id' => $id,
            ];
            foreach (\Modules\Pe\Config\InpatientRespondent::FIELDS as $field) {
                if (isset($source[$field])) $context[$field] = $source[$field];
            }
            $premTokens[$nextToken] = $context;
            session()->set('prem_form_tokens', array_slice($premTokens, -10, null, true));
            $sourceTokens[$sourceToken]['prem_continuation_token'] = $nextToken;
            session()->set($this->formTokenKey, $sourceTokens);
        }
        // Refresh the continuation context after editing the shared respondent data.
        foreach (array_merge(['medical_record_number'], \Modules\Pe\Config\InpatientRespondent::FIELDS) as $field) {
            if (array_key_exists($field, $source)) $premTokens[$nextToken][$field] = $source[$field];
        }
        session()->set('prem_form_tokens', $premTokens);
        $completed = db_connect()->table('dt01_pe_prem_response_hd')->where('skm_response_id', $id)
            ->where('group_id', $tenant['group_id'])->where('org_id', $tenant['org_id'])->get()->getRowArray();
        if ($completed) return view('Modules\Pe\Views\v_skm_complete', ['tenant' => $tenant]);
        return view('Modules\Pe\Views\v_prem', [
            'surveyType' => 'PREM', 'surveyPath' => 'pe/skm', 'phase' => null,
            'instrument' => $instrument, 'questions' => $questions, 'error' => '', 'tenant' => $tenant,
            'submissionToken' => $nextToken, 'revision' => \Modules\Pe\Models\PremModel::revision($questions),
            'visit' => $premTokens[$nextToken],
            'respondentEditUrl' => site_url('pe/skm') . '?' . http_build_query($tenant + [
                'instrument_id' => $source['instrument_id'], 'form' => $sourceToken, 'edit' => '1',
            ]),
            'submissionUrl' => site_url('pe/skm/submitprem') . '?' . http_build_query($tenant),
        ]);
    }
}