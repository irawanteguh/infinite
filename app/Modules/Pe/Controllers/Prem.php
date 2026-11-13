<?php

namespace Modules\Pe\Controllers;

use App\Controllers\BaseController;
use Modules\Pe\Models\PremModel;

class Prem extends BaseController
{
    protected $modelClass = PremModel::class;
    protected $formTokenKey = 'prem_form_tokens';
    protected $surveyPath = 'pe/prem';
    protected $surveyType = 'PREM';
    public function index()
    {
        $instrument = null;
        $questions = [];
        $error = '';
        $token = '';
        $tenant = ['group_id' => '', 'org_id' => ''];
        try {
            $tenant = $this->publicScope();
            $model = new $this->modelClass();
            $id = request()->getGet('instrument_id');
            if ($id !== null && (!is_string($id) || !preg_match('/^[a-f0-9-]{36}$/i', $id))) {
                throw new \InvalidArgumentException('Tautan kuesioner tidak valid.');
            }
            $instrument = $model->resolveInstrument($id, $tenant['group_id'], $tenant['org_id']);
            if (!$instrument) {
                throw new \InvalidArgumentException('Kuesioner PREM belum tersedia. Silakan hubungi petugas untuk mendapatkan tautan yang sesuai.');
            }
            $questions = $model->questions($instrument['instrument_id']);
            if (!$questions) throw new \InvalidArgumentException('Belum ada pertanyaan aktif pada kuesioner ini. Silakan hubungi petugas.');
            if (!$model->responsesReady()) throw new \RuntimeException('PE response tables are not ready.');
            if (request()->getGet('form') === null && request()->getGet('start') !== '1') {
                return view('template/dashboard-light-blank', [
                    'contents' => view('Modules\Pe\Views\v_prem_welcome', compact('instrument', 'questions', 'tenant')),
                ]);
            }
            return $this->formPage($instrument, $questions);
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
            response()->setStatusCode(422);
        } catch (\Throwable $e) {
            log_message('error', 'PREM form could not be loaded: {message}', ['message' => $e->getMessage()]);
            $error = 'Kuesioner belum dapat dimuat. Silakan coba lagi atau hubungi petugas.';
        }
        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Pe\Views\v_prem', [
                'instrument' => $instrument, 'questions' => $questions, 'error' => $error,
                'submissionToken' => $token, 'revision' => PremModel::revision($questions), 'tenant' => $tenant,
            ]),
        ]);
    }

    protected function publicScope(): array
    {
        $scope = [];
        foreach (['group_id', 'org_id'] as $key) {
            $value = request()->getGet($key);
            if (!is_string($value) || !preg_match('/\A[A-Za-z0-9][A-Za-z0-9_-]{0,63}\z/', $value)) {
                throw new \InvalidArgumentException('Tautan harus menyertakan group_id dan org_id yang valid. Silakan minta tautan lengkap dari petugas rumah sakit.');
            }
            $scope[$key] = $value;
        }
        return $scope;
    }
    protected function formPage(array $instrument, array $questions, ?string $phase = null)
    {
        $tokens = session()->get($this->formTokenKey) ?? [];
        $token = request()->getGet('form');
        if ($token === null) {
            $token = bin2hex(random_bytes(32));
            $context = ['instrument_id' => $instrument['instrument_id'], 'group_id' => $instrument['group_id'], 'org_id' => $instrument['org_id']];
            $fixedService = \Modules\Pe\Config\GeneralQuestionnaires::service($instrument['code']);
            if ($fixedService !== null) $context['fixed_service_type'] = $fixedService;
            if ($phase !== null) $context['measurement_phase'] = $phase;
            $tokens[$token] = $context;
            session()->set($this->formTokenKey, array_slice($tokens, -10, null, true));
            return $this->visitPage($instrument, $token, $context);
        }
        if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token) || !isset($tokens[$token])
            || !is_array($tokens[$token]) || $tokens[$token]['instrument_id'] !== $instrument['instrument_id']
            || ($tokens[$token]['measurement_phase'] ?? null) !== $phase
            || ($tokens[$token]['group_id'] ?? '') !== $instrument['group_id']
            || ($tokens[$token]['org_id'] ?? '') !== $instrument['org_id']) {
            throw new \InvalidArgumentException('Sesi pengisian tidak valid. Silakan buka ulang tautan kuesioner.');
        }
        $context = $tokens[$token];
        if (request()->getGet('edit') === '1' || (\Modules\Pe\Config\GeneralQuestionnaires::service($instrument['code']) === 'RJ' && empty($context['clinic'])) || (\Modules\Pe\Config\GeneralQuestionnaires::service($instrument['code']) !== null && !isset($context['respondent_name'])) || !isset($context['service_type'], $context['medical_record_number'])) {
            return $this->visitPage($instrument, $token, $context);
        }
        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Pe\Views\v_prem', [
                'surveyType' => $this->surveyType, 'surveyPath' => $this->surveyPath, 'phase' => $phase,
                'instrument' => $instrument, 'questions' => $questions, 'error' => '',
                'submissionToken' => $token, 'revision' => PremModel::revision($questions), 'tenant' => ['group_id' => $instrument['group_id'], 'org_id' => $instrument['org_id']], 'visit' => $context,
            ]),
        ]);
    }

    protected function visitPage(array $instrument, string $token, array $context, string $error = '')
    {
        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Pe\Views\v_visit', compact('instrument', 'token', 'context', 'error') + [
                'surveyType' => $this->surveyType, 'surveyPath' => $this->surveyPath,
                'tenant' => ['group_id' => $instrument['group_id'] ?? '', 'org_id' => $instrument['org_id'] ?? ''],
            ]),
        ]);
    }

    public function visit()
    {
        if (request()->getMethod() !== 'post') return redirect()->to(site_url($this->surveyPath));
        $instrument = null;
        $token = request()->getPost('submission_token');
        $context = [];
        try {
            service('security')->verify(request());
            $tokens = session()->get($this->formTokenKey) ?? [];
            if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)
                || !isset($tokens[$token]) || !is_array($tokens[$token])) {
                throw new \InvalidArgumentException('Sesi pengisian berakhir. Silakan buka ulang tautan kuesioner.');
            }
            $context = $tokens[$token];
            $tenant = $this->publicScope();
            if (($context['group_id'] ?? '') !== $tenant['group_id'] || ($context['org_id'] ?? '') !== $tenant['org_id']) {
                throw new \InvalidArgumentException('Organisasi pada tautan tidak sesuai dengan formulir.');
            }
            $tenant = $this->publicScope();
            $model = new $this->modelClass();
            $instrument = $model->resolveInstrument($context['instrument_id'], $tenant['group_id'], $tenant['org_id']);
            if (!$instrument) throw new \InvalidArgumentException('Kuesioner sudah tidak aktif.');
            $visit = $model->validateVisit($context['fixed_service_type'] ?? request()->getPost('service_type'), request()->getPost('medical_record_number'));
            if (isset($context['fixed_service_type']) && $visit['service_type'] !== $context['fixed_service_type']) {
                throw new \InvalidArgumentException('Jenis pelayanan harus sesuai dengan kuesioner yang dipilih.');
            }
            if (\Modules\Pe\Config\GeneralQuestionnaires::service($instrument['code']) !== null) {
                $respondent = [];
                foreach (\Modules\Pe\Config\InpatientRespondent::FIELDS as $field) $respondent[$field] = request()->getPost($field);
                if (in_array(\Modules\Pe\Config\GeneralQuestionnaires::service($instrument['code']), ['RJ', 'IGD'], true)) $respondent['ward_class'] = 'KELAS_3';
                $visit += \Modules\Pe\Config\InpatientRespondent::validate($respondent, \Modules\Pe\Config\GeneralQuestionnaires::service($instrument['code']) === 'RJ');
            }
            $tokens[$token] = array_merge($context, $visit);
            session()->set($this->formTokenKey, $tokens);
            $query = $tenant + ['instrument_id' => $instrument['instrument_id'], 'form' => $token];
            if (isset($context['measurement_phase'])) $query['phase'] = $context['measurement_phase'];
            $path = $this->surveyPath;
            return redirect()->to(site_url($path) . '?' . http_build_query($query))->setStatusCode(303);
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
            response()->setStatusCode(422);
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) {
            $error = 'Sesi keamanan berakhir. Silakan buka ulang tautan kuesioner.';
            response()->setStatusCode(403);
        } catch (\Throwable $e) {
            log_message('error', 'PE visit: {message}', ['message' => $e->getMessage()]);
            $error = 'Data kunjungan belum dapat diproses. Silakan coba kembali.';
            response()->setStatusCode(500);
        }
        foreach (array_merge(['service_type', 'medical_record_number'], \Modules\Pe\Config\InpatientRespondent::FIELDS) as $field) {
            $value = request()->getPost($field);
            if (is_string($value)) $context[$field] = $value;
        }
        if ($instrument && is_string($token)) return $this->visitPage($instrument, $token, $context, $error);
        return view('template/dashboard-light-blank', ['contents' => view('Modules\Pe\Views\v_visit', [
            'instrument' => null, 'token' => '', 'context' => [], 'error' => $error,
            'surveyType' => $this->surveyType, 'surveyPath' => $this->surveyPath,
                'tenant' => ['group_id' => $instrument['group_id'] ?? '', 'org_id' => $instrument['org_id'] ?? ''],
        ])]);
    }
    public function submit()
    {
        if (request()->getMethod() !== 'post') {
            return response()->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Gunakan metode POST.']);
        }
        try {
            service('security')->verify(request());
            $id = request()->getPost('instrument_id');
            $token = request()->getPost('submission_token');
            $revision = request()->getPost('revision');
            $tenant = $this->publicScope();
            $tokens = session()->get($this->formTokenKey) ?? [];
            if (!is_string($id) || !is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)
                || !isset($tokens[$token]) || (is_array($tokens[$token]) ? $tokens[$token]['instrument_id'] : $tokens[$token]) !== $id) {
                throw new \InvalidArgumentException('Sesi pengisian tidak valid. Silakan muat ulang halaman.');
            }
            $model = new $this->modelClass();
            if (!$model->responsesReady()) throw new \RuntimeException('PE response tables are not ready.');
            if (($tokens[$token]['group_id'] ?? '') !== $tenant['group_id'] || ($tokens[$token]['org_id'] ?? '') !== $tenant['org_id']) {
                throw new \InvalidArgumentException('Organisasi pada tautan tidak sesuai dengan formulir.');
            }
            $key = hash('sha256', $token);
            $existing = $model->submitted($id, $key);
            if ($existing) return $this->success($existing['response_id']);
            $instrument = $model->resolveInstrument($id, $tenant['group_id'], $tenant['org_id']);
            if (!$instrument) throw new \InvalidArgumentException('Kuesioner ini sudah tidak aktif. Silakan hubungi petugas.');
            $questions = $model->questions($id);
            if (!is_string($revision) || !hash_equals(PremModel::revision($questions), $revision)) {
                throw new \InvalidArgumentException('Pertanyaan telah diperbarui. Silakan muat ulang halaman dan isi kembali.');
            }
            $answers = $model->validateAnswers($questions, request()->getPost('answers'));
            $visit = $model->validateVisit($tokens[$token]['service_type'] ?? null, $tokens[$token]['medical_record_number'] ?? null);
            $context = (is_array($tokens[$token]) ? $tokens[$token] : []) + $visit;
            $context['suggestions'] = request()->getPost('suggestions') ?? '';
            $responseId = $model->storeResponse($instrument, $questions, $answers, $key, $context);
            return $this->success($responseId);
        } catch (\InvalidArgumentException $e) {
            return response()->setStatusCode(422)->setJSON(['success' => false, 'message' => $e->getMessage(), 'csrfHash' => csrf_hash()]);
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) {
            return response()->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Sesi keamanan berakhir. Silakan muat ulang halaman.', 'csrfHash' => csrf_hash()]);
        } catch (\Throwable $e) {
            log_message('error', 'PREM response could not be saved: {message}', ['message' => $e->getMessage()]);
            return response()->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Jawaban belum berhasil dikirim. Silakan coba kembali.', 'csrfHash' => csrf_hash()]);
        }
    }

    protected function success($id)
    {
        return response()->setJSON(['success' => true, 'responseId' => $id, 'csrfHash' => csrf_hash()]);
    }
}
