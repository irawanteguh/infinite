<?php

namespace Modules\Pe\Controllers;

use App\Controllers\BaseController;
use Modules\Pe\Models\MasterqusionerModel;

class Masterqusioner extends BaseController
{
    public function index()
    {
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Pe\Views\v_masterqusioner'),
        ]);
    }

    public function data()
    {
        return $this->execute(function ($model, $group, $org) {
            return ['responseResult' => $model->masters($group, $org)];
        });
    }

    public function saveinstrument()
    {
        return $this->execute(function ($model, $group, $org) {
            $rules = [
                'instrument_id' => 'permit_empty|regex_match[/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i]',
                'code' => 'required|max_length[50]|regex_match[/^[A-Z0-9_-]+$/]',
                'name' => 'required|max_length[200]',
                'type' => 'required|in_list[PROM,PREM]',
                'respondent' => 'required|max_length[200]',
                'measurement' => 'required|max_length[255]',
                'active' => 'required|in_list[0,1]',
            ];
            $data = $this->validated($rules);
            $model->saveInstrument($group, $org, $data, (string) session()->get('userid'));
            return ['responseDesc' => 'Instrumen berhasil disimpan.'];
        }, true);
    }

    public function savequestion()
    {
        return $this->execute(function ($model, $group, $org) {
            $data = $this->validated([
                'question_id' => 'permit_empty|regex_match[/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i]',
                'instrument_id' => 'required|regex_match[/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i]',
                'domain_id' => 'required|regex_match[/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i]',
                'question' => 'required|max_length[4000]',
                'sort_order' => 'required|is_natural_no_zero|less_than_equal_to[9999]',
                'active' => 'required|in_list[0,1]',
            ]);
            $model->saveQuestion($group, $org, $data, (string) session()->get('userid'));
            return ['responseDesc' => 'Pertanyaan berhasil disimpan.'];
        }, true);
    }

    public function savedomain()
    {
        return $this->execute(function ($model, $group, $org) {
            $data = $this->validated([
                'domain_id' => 'permit_empty|regex_match[/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i]',
                'name' => 'required|max_length[150]',
                'active' => 'required|in_list[0,1]',
            ]);
            $model->saveDomain($group, $org, $data, (string) session()->get('userid'));
            return ['responseDesc' => 'Domain berhasil disimpan.'];
        }, true);
    }

    private function validated(array $rules): array
    {
        $data = [];
        foreach ($rules as $key => $rule) {
            $value = request()->getPost($key);
            if ($value !== null && !is_string($value)) {
                throw new \InvalidArgumentException('Format isian tidak valid.');
            }
            $data[$key] = trim((string) $value);
        }
        $validation = service('validation');
        $validation->reset();
        $validation->setRules($rules);
        if (!$validation->run($data)) {
            throw new \InvalidArgumentException(implode(' ', $validation->getErrors()));
        }
        return $data;
    }

    private function execute(callable $action, bool $write = false)
    {
        if (!session()->get('loggedin') || !session()->get('groupid') || !session()->get('orgid') || !session()->get('userid')) {
            return response()->setStatusCode(401)->setJSON(['responseCode' => '01', 'responseDesc' => 'Sesi organisasi tidak tersedia. Silakan login kembali.']);
        }
        if ($write && request()->getMethod() !== 'post') {
            return response()->setStatusCode(405)->setJSON(['responseCode' => '01', 'responseDesc' => 'Gunakan metode POST.']);
        }
        try {
            if ($write) {
                // Scoped CSRF verification; existing application endpoints are unaffected.
                service('security')->verify(request());
            }
            $model = new MasterqusionerModel();
            if (!$model->ready()) {
                return response()->setStatusCode(503)->setJSON(['responseCode' => '01', 'responseDesc' => 'Tabel master kuesioner belum tersedia. Hubungi administrator.']);
            }
            $result = $action($model, (string) session()->get('groupid'), (string) session()->get('orgid'));
            return response()->setJSON(array_merge(['responseCode' => '00', 'csrfHash' => csrf_hash()], $result));
        } catch (\InvalidArgumentException $e) {
            return response()->setStatusCode(422)->setJSON(['responseCode' => '01', 'responseDesc' => $e->getMessage(), 'csrfHash' => csrf_hash()]);
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) {
            return response()->setStatusCode(403)->setJSON(['responseCode' => '01', 'responseDesc' => 'Token keamanan tidak valid. Muat ulang halaman.', 'csrfHash' => csrf_hash()]);
        } catch (\Throwable $e) {
            log_message('error', 'PE master kuesioner: {message}', ['message' => $e->getMessage()]);
            return response()->setStatusCode(500)->setJSON(['responseCode' => '01', 'responseDesc' => 'Data gagal diproses. Silakan coba kembali.', 'csrfHash' => csrf_hash()]);
        }
    }
}
