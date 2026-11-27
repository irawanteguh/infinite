<?php

namespace Modules\Ca\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Security\Exceptions\SecurityException;
use Modules\Ca\Config\ScreeningMasters;
use Modules\Ca\Models\MasterqusionerModel;

class Masterqusioner extends BaseController
{
    private const CODE_RULE = 'required|regex_match[/\A[A-Za-z][A-Za-z0-9_-]{0,9}\z/]';

    public function index()
    {
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Ca\Views\v_masterqusioner'),
        ]);
    }

    public function data()
    {
        return $this->execute(function ($model, $group, $org) {
            $ready = $model->ready();
            return [
                'responseResult' => $model->masters($group, $org, $ready, true),
                'responseDesc' => $ready ? '' : 'Tabel master belum tersedia. Templat masih dapat dilihat dan dipratinjau.',
            ];
        });
    }

    public function createinstrument()
    {
        return $this->execute(function ($model, $group, $org, $audit) {
            $data = $this->validated(['code' => self::CODE_RULE, 'name' => 'required|max_length[200]']);
            $code = strtoupper($data['code']);
            unset($data['code']);
            $model->createInstrument($group, $org, $code, $data + $audit);
            return ['responseDesc' => 'Instrumen berhasil ditambahkan.', 'instrumentCode' => $code];
        }, true);
    }

    public function initialize()
    {
        return $this->execute(function ($model, $group, $org, $audit) {
            $data = $this->validated(['code' => 'required|in_list[IASTO,APCS]']);
            $model->installDefaults($group, $org, $data['code'], $audit);
            return ['responseDesc' => 'Master awal berhasil disimpan.'];
        }, true);
    }

    public function saveinstrument()
    {
        return $this->execute(function ($model, $group, $org, $audit) {
            $data = $this->validated([
                'code' => self::CODE_RULE,
                'name' => 'required|max_length[200]',
                'active' => 'required|in_list[0,1]',
            ]);
            $code = strtoupper($data['code']);
            unset($data['code']);
            $data['risk_ranges'] = $this->riskRanges($code);
            $model->saveInstrument($group, $org, $code, $data + $audit);
            return ['responseDesc' => 'Instrumen berhasil disimpan.'];
        }, true);
    }

    public function savequestion()
    {
        return $this->execute(function ($model, $group, $org, $audit) {
            $data = $this->validated([
                'code' => self::CODE_RULE,
                'question_id' => 'permit_empty|is_natural_no_zero|max_length[10]',
                'question' => 'required|max_length[4000]',
                'sort_order' => 'required|is_natural_no_zero|less_than_equal_to[9999]',
                'active' => 'required|in_list[0,1]',
            ]);
            $code = strtoupper($data['code']);
            $id = $data['question_id'];
            unset($data['code'], $data['question_id']);
            $data['answer_options'] = $this->answerOptions($code);
            $model->saveQuestion($group, $org, $code, $id, $data + $audit);
            return ['responseDesc' => 'Pertanyaan berhasil disimpan.'];
        }, true);
    }

    private function riskRanges(string $code): array
    {
        $limits = [];
        foreach (ScreeningMasters::RISK_LABELS as $index => $label) {
            $limits[] = [
                request()->getPost('risk_min_' . $index),
                request()->getPost('risk_max_' . $index),
            ];
        }
        if (!isset(ScreeningMasters::INSTRUMENTS[$code])
            && $limits === array_fill(0, count(ScreeningMasters::RISK_LABELS), ['', ''])) {
            return [];
        }

        $ranges = [];
        $previousMax = null;
        foreach ($limits as $index => [$min, $max]) {
            if (!is_string($min) || !is_string($max)
                || ($min !== '' && !preg_match('/^[0-9]{1,6}$/', $min))
                || !preg_match('/^[0-9]{1,6}$/', $max)
                || ($index > 0 && $min === '')) {
                throw new \InvalidArgumentException('Isi batas skor kategori dengan bilangan bulat. Batas bawah kategori pertama boleh kosong.');
            }
            $min = $min === '' ? null : (int) $min;
            $max = (int) $max;
            if (($min !== null && $min > $max) || ($index > 0 && $min !== $previousMax + 1)) {
                throw new \InvalidArgumentException('Rentang skor harus berurutan, tanpa tumpang tindih atau celah.');
            }
            $ranges[] = [$min, $max, ScreeningMasters::RISK_LABELS[$index]];
            $previousMax = $max;
        }
        return $ranges;
    }

    private function answerOptions(string $code): array
    {
        $labels = request()->getPost('label');
        $scores = request()->getPost('score');
        $types = request()->getPost('answer_type');
        if (!is_array($labels) || !is_array($scores) || array_keys($labels) !== array_keys($scores)
            || ($types !== null && (!is_array($types) || array_keys($labels) !== array_keys($types)))
            || count($labels) < 2 || count($labels) > 20) {
            throw new \InvalidArgumentException('Isi 2 hingga 20 pilihan jawaban.');
        }

        $options = [];
        $range = [
            'IASTO' => '/\A[1-4]\z/',
            'APCS' => '/\A[0-3]\z/',
        ][$code] ?? '/\A[0-9]{1,6}\z/';
        foreach ($labels as $index => $label) {
            $score = $scores[$index];
            $type = $types === null ? 'score' : $types[$index];
            if (!is_string($label) || trim($label) === '' || mb_strlen(trim($label)) > 500) {
                throw new \InvalidArgumentException('Isi teks setiap pilihan jawaban.');
            }
            if ($type !== 'score') {
                if (isset(ScreeningMasters::INSTRUMENTS[$code])
                    || !in_array($type, ['unscored', 'warning'], true) || $score !== '') {
                    throw new \InvalidArgumentException('Jenis penilaian pilihan jawaban tidak valid.');
                }
                $options[] = $type === 'warning' ? [trim($label), null, true] : [trim($label), null];
                continue;
            }
            if (!is_string($score) || !preg_match($range, $score)) {
                throw new \InvalidArgumentException('Isi teks pilihan dan skor dalam rentang instrumen.');
            }
            $options[] = [trim($label), (int) $score];
        }
        return $options;
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
        $validation->reset()->setRules($rules);
        if (!$validation->run($data)) {
            throw new \InvalidArgumentException(implode(' ', $validation->getErrors()));
        }
        return $data;
    }

    private function execute(callable $action, bool $write = false)
    {
        $session = session();
        if (!$session->get('loggedin') || !$session->get('groupid') || !$session->get('orgid') || !$session->get('userid')) {
            return response()->setStatusCode(401)->setJSON(['responseCode' => '01', 'responseDesc' => 'Sesi organisasi tidak tersedia. Silakan login kembali.']);
        }
        if ($write && strtolower(request()->getMethod()) !== 'post') {
            return response()->setStatusCode(405)->setJSON(['responseCode' => '01', 'responseDesc' => 'Gunakan metode POST.']);
        }

        try {
            if ($write) {
                service('security')->verify(request());
            }
            $model = new MasterqusionerModel();
            if ($write && !$model->ready()) {
                return response()->setStatusCode(503)->setJSON(['responseCode' => '01', 'responseDesc' => 'Tabel master skrining kanker belum tersedia. Hubungi administrator.', 'csrfHash' => csrf_hash()]);
            }
            $result = $action($model, (string) $session->get('groupid'), (string) $session->get('orgid'), [
                'updated_by' => (string) $session->get('userid'), 'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return response()->setJSON(['responseCode' => '00', 'csrfHash' => csrf_hash()] + $result);
        } catch (\InvalidArgumentException $error) {
            $status = 422;
            $message = $error->getMessage();
        } catch (SecurityException $error) {
            $status = 403;
            $message = 'Token keamanan tidak valid. Muat ulang halaman.';
        } catch (\Throwable $error) {
            log_message('error', 'CA master kuesioner: {message}', ['message' => $error->getMessage()]);
            $status = 500;
            $message = 'Data gagal diproses. Silakan coba kembali.';
        }
        return response()->setStatusCode($status)->setJSON(['responseCode' => '01', 'responseDesc' => $message, 'csrfHash' => csrf_hash()]);
    }
}
