<?php

namespace Modules\Pe\Controllers;

use App\Controllers\BaseController;
use Modules\Pe\Models\PremModel;

class Prem extends BaseController
{
    protected $modelClass = PremModel::class;
    protected $formTokenKey = 'prem_form_tokens';
    public function index()
    {
        $instrument = null;
        $questions = [];
        $error = '';
        $token = '';
        try {
            $model = new $this->modelClass();
            $id = request()->getGet('instrument_id');
            if ($id !== null && (!is_string($id) || !preg_match('/^[a-f0-9-]{36}$/i', $id))) {
                throw new \InvalidArgumentException('Tautan kuesioner tidak valid.');
            }
            $instrument = $model->resolveInstrument($id, session()->get('groupid'), session()->get('orgid'));
            if (!$instrument) {
                throw new \InvalidArgumentException('Kuesioner PREM belum tersedia. Silakan hubungi petugas untuk mendapatkan tautan yang sesuai.');
            }
            $questions = $model->questions($instrument['instrument_id']);
            if (!$questions) throw new \InvalidArgumentException('Belum ada pertanyaan aktif pada kuesioner ini. Silakan hubungi petugas.');
            if (!$model->responsesReady()) throw new \RuntimeException('PREM response tables are not ready.');
            $token = bin2hex(random_bytes(32));
            $tokens = session()->get($this->formTokenKey) ?? [];
            $tokens[$token] = $instrument['instrument_id'];
            session()->set($this->formTokenKey, array_slice($tokens, -10, null, true));
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (\Throwable $e) {
            log_message('error', 'PREM form could not be loaded: {message}', ['message' => $e->getMessage()]);
            $error = 'Kuesioner belum dapat dimuat. Silakan coba lagi atau hubungi petugas.';
        }
        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Pe\Views\v_prem', [
                'instrument' => $instrument, 'questions' => $questions, 'error' => $error,
                'submissionToken' => $token, 'revision' => PremModel::revision($questions),
            ]),
        ]);
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
            $tokens = session()->get($this->formTokenKey) ?? [];
            if (!is_string($id) || !is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)
                || !isset($tokens[$token]) || (is_array($tokens[$token]) ? $tokens[$token]['instrument_id'] : $tokens[$token]) !== $id) {
                throw new \InvalidArgumentException('Sesi pengisian tidak valid. Silakan muat ulang halaman.');
            }
            $model = new $this->modelClass();
            if (!$model->responsesReady()) throw new \RuntimeException('PREM response tables are not ready.');
            $key = hash('sha256', $token);
            $existing = $model->submitted($id, $key);
            if ($existing) return $this->success($existing['response_id']);
            $instrument = $model->resolveInstrument($id);
            if (!$instrument) throw new \InvalidArgumentException('Kuesioner ini sudah tidak aktif. Silakan hubungi petugas.');
            $questions = $model->questions($id);
            if (!is_string($revision) || !hash_equals(PremModel::revision($questions), $revision)) {
                throw new \InvalidArgumentException('Pertanyaan telah diperbarui. Silakan muat ulang halaman dan isi kembali.');
            }
            $answers = $model->validateAnswers($questions, request()->getPost('answers'));
            $responseId = $model->storeResponse($instrument, $questions, $answers, $key, is_array($tokens[$token]) ? $tokens[$token] : []);
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

    private function success($id)
    {
        return response()->setJSON(['success' => true, 'responseId' => $id, 'csrfHash' => csrf_hash()]);
    }
}
