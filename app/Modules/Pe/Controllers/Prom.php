<?php

namespace Modules\Pe\Controllers;

use Modules\Pe\Models\PromModel;

class Prom extends Prem
{
    protected $modelClass = PromModel::class;
    protected $formTokenKey = 'prom_form_tokens';
    protected $surveyPath = 'pe/prom';
    protected $surveyType = 'PROM';

    public function index()
    {
        $instrument = null;
        $instruments = [];
        $questions = [];
        $error = '';
        $token = '';
        $tenant = ['group_id' => '', 'org_id' => ''];
        $phase = request()->getGet('phase');
        $id = request()->getGet('instrument_id');
        try {
            $tenant = $this->publicScope();
            $model = new $this->modelClass();
            if ($id !== null && (!is_string($id) || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $id))) {
                throw new \InvalidArgumentException('Tautan kuesioner tidak valid.');
            }
            if ($phase !== null && !in_array($phase, ['T0', 'T1'], true)) {
                throw new \InvalidArgumentException('Tahap pengukuran tidak valid.');
            }
            $instruments = $model->availableInstruments($tenant['group_id'], $tenant['org_id']);
            if ($id !== null) {
                $allowed = array_column($instruments, 'instrument_id');
                if (!in_array($id, $allowed, true)) throw new \InvalidArgumentException('Instrumen PROM tidak tersedia. Silakan pilih kuesioner yang aktif.');
                $instrument = $model->resolveInstrument($id, $tenant['group_id'], $tenant['org_id']);
                if ($phase !== null) {
                    $questions = $model->questions($id);
                    if (!$questions) throw new \InvalidArgumentException('Belum ada pertanyaan aktif pada kuesioner ini.');
                    if (!$model->responsesReady()) throw new \RuntimeException('PROM response tables are not ready.');
                    return $this->formPage($instrument, $questions, $phase);
                }
            }
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
            response()->setStatusCode(422);
        } catch (\Throwable $e) {
            log_message('error', 'PROM form could not be loaded: {message}', ['message' => $e->getMessage()]);
            $error = 'Kuesioner belum dapat dimuat. Silakan coba lagi atau hubungi petugas.';
        }
        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Pe\Views\v_prom', [
                'instrument' => $instrument, 'instruments' => $instruments, 'questions' => $questions,
                'error' => $error, 'phase' => $phase, 'submissionToken' => $token,
                'revision' => PromModel::revision($questions), 'tenant' => $tenant,
            ]),
        ]);
    }
}
