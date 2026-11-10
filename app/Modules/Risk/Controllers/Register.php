<?php

namespace Modules\Risk\Controllers;

use App\Controllers\BaseController;
use Modules\Risk\Libraries\RiskMatrix;
use Modules\Risk\Libraries\RiskCatalog;
use Modules\Risk\Models\RegisterModel;

class Register extends BaseController
{

    private function model(): RegisterModel
    {
        if (!session()->get('loggedin') || !session()->get('groupid') || !session()->get('orgid') || !session()->get('userid')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Sesi organisasi tidak tersedia.');
        }
        return new RegisterModel();
    }

    private function text(string $name): string
    {
        $value = request()->getPost($name);
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function query(string $name): string
    {
        $value = request()->getGet($name);
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function mutation(): void
    {
        // Dynamic HMVC routes also resolve GET methods; enforce verb and CSRF here.
        if (strtolower(request()->getMethod()) !== 'post') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        service('security')->verify(request());
    }

    private function back(string $message, bool $error = false, string $id = '')
    {
        session()->setFlashdata($error ? 'risk_error' : 'risk_success', $message);
        $year = $this->text('year');
        $params = ['year' => preg_match('/^20\d{2}$/', $year) ? $year : date('Y')];
        if ($id !== '') {
            $params['id'] = $id;
        }
        if (preg_match('/^[1-4]$/', $this->text('quarter'))) {
            $params['quarter'] = $this->text('quarter');
        }
        $redirect = redirect()->to(site_url('risk/register') . '?' . http_build_query($params))->withCookies();
        if ($error) {
            session()->setFlashdata('risk_form', $this->text('quarter') !== '' ? 'monitor' : 'profile');
            $redirect->withInput();
        }
        return $redirect;
    }

    private function enrich(array $risk, RegisterModel $model): array
    {
        $risk['assessment'] = RiskMatrix::assess((int) $risk['likelihood'], (int) $risk['impact_level']);
        $risk['monitoring'] = $model->monitoring($risk['risk_id']);
        foreach ($risk['monitoring'] as &$monitor) {
            $monitor['assessment'] = RiskMatrix::assess((int) $monitor['likelihood'], (int) $monitor['impact_level']);
            $monitor['trend'] = RiskMatrix::trend((int) $monitor['baseline_score'], $monitor['assessment']['score']);
        }
        unset($monitor);
        return $risk;
    }

    public function index()
    {
        $model = $this->model();
        $year = $this->query('year');
        $year = preg_match('/^20\d{2}$/', $year) ? (int) $year : (int) date('Y');
        $department = $this->query('department');
        $group = session()->get('groupid');
        $org = session()->get('orgid');
        $ready = $model->ready();
        $risks = [];
        $selected = null;
        if ($ready) {
            foreach ($model->risks($group, $org, $year, $department) as $risk) {
                $risks[] = $this->enrich($risk, $model);
            }
            usort($risks, static function ($a, $b) { return $b['assessment']['score'] <=> $a['assessment']['score']; });
            $id = $this->query('id');
            if ($id !== '') {
                $selected = $model->risk($group, $org, $id);
                if (!$selected) {
                    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
                }
                $selected = $this->enrich($selected, $model);
            }
        }
        $quarter = $this->query('quarter');
        $quarter = in_array($quarter, ['1', '2', '3', '4'], true) ? (int) $quarter : (int) ceil(date('n') / 3);
        return view('template/dashboard-light-aside', ['contents' => view('Modules\\Risk\\Views\\register', [
            'ready' => $ready, 'risks' => $risks, 'selected' => $selected, 'year' => $year, 'department' => $department,
            'quarter' => $quarter, 'departments' => $model->departments($group, $org), 'categories' => RiskCatalog::CATEGORIES,
        ])]);
    }

    public function peta()
    {
        $model = $this->model();
        $year = $this->query('year');
        $year = preg_match('/^20\d{2}$/', $year) ? (int) $year : (int) date('Y');
        $department = $this->query('department');
        $group = session()->get('groupid');
        $org = session()->get('orgid');
        $departments = $model->departments($group, $org);
        $unitName = 'Semua unit';
        if ($department !== '') {
            $found = false;
            foreach ($departments as $unit) {
                if ($unit['department_id'] === $department) {
                    $unitName = $unit['department'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
        }
        $ready = $model->ready();
        $risks = $ready ? $model->risks($group, $org, $year, $department) : [];
        foreach ($risks as &$risk) {
            $risk['assessment'] = RiskMatrix::assess((int) $risk['likelihood'], (int) $risk['impact_level']);
        }
        unset($risk);
        usort($risks, static function ($a, $b) { return $b['assessment']['score'] <=> $a['assessment']['score']; });
        $data = compact('ready', 'year', 'department', 'departments', 'unitName', 'risks');
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\\Risk\\Views\\v_peta', $data),
            'riskPetaContext' => ['year' => $year, 'unitName' => $unitName],
        ]);
    }

    public function save()
    {
        $this->mutation();
        $model = $this->model();
        if (!$model->ready()) {
            return $this->back('Tabel manajemen risiko belum tersedia.', true);
        }
        $id = $this->text('risk_id');
        $group = session()->get('groupid');
        $org = session()->get('orgid');
        $existing = $id !== '' ? $model->risk($group, $org, $id) : null;
        if ($id !== '' && !$existing) {
            return $this->back('Risiko tidak ditemukan.', true);
        }
        $data = [];
        foreach (['department_id', 'year', 'code', 'category', 'objective', 'indicator', 'target', 'business_process', 'event', 'cause', 'consequence', 'control', 'likelihood', 'impact_level', 'likelihood_reason', 'impact_reason', 'action_plan', 'output', 'action_target', 'implementation', 'pic'] as $key) {
            $data[$key] = $this->text($key);
            if (strlen($data[$key]) > 12000) {
                return $this->back('Isi kolom terlalu panjang.', true, $id);
            }
        }
        foreach (['code' => 60, 'category' => 100, 'pic' => 200] as $key => $max) {
            if (mb_strlen($data[$key]) > $max) {
                return $this->back('Kode, kategori atau PIC terlalu panjang.', true, $id);
            }
        }
        foreach (['objective', 'indicator', 'target', 'business_process', 'code', 'category', 'event', 'cause', 'consequence', 'control', 'likelihood_reason', 'impact_reason', 'pic'] as $key) {
            if ($data[$key] === '') {
                return $this->back('Lengkapi semua kolom wajib pada profil risiko.', true, $id);
            }
        }
        $departmentIds = array_column($model->departments($group, $org), 'department_id');
        if (!in_array($data['department_id'], $departmentIds, true) || !preg_match('/^20\d{2}$/', $data['year'])
            || !in_array($data['category'], RiskCatalog::CATEGORIES, true)
            || !preg_match('/^[1-5]$/', $data['likelihood']) || !preg_match('/^[1-5]$/', $data['impact_level'])) {
            return $this->back('Unit, tahun, kategori atau level penilaian tidak valid.', true, $id);
        }
        if ($existing && $model->monitoring($id) && ($existing['year'] != $data['year'] || $existing['department_id'] !== $data['department_id'])) {
            return $this->back('Tahun dan unit tidak dapat diubah setelah memiliki pemantauan.', true, $id);
        }
        $assessment = RiskMatrix::assess((int) $data['likelihood'], (int) $data['impact_level']);
        if ($assessment['requires_action'] && ($data['action_plan'] === '' || $data['output'] === '' || $data['action_target'] === '' || $data['implementation'] === '')) {
            return $this->back('Risiko dengan skor ≥12 wajib memiliki rencana aksi, output, target dan waktu implementasi.', true, $id);
        }
        $data += ['last_update_by' => session()->get('userid'), 'last_update_date' => date('Y-m-d H:i:s')];
        if ($id === '') {
            $data += ['risk_id' => bin2hex(random_bytes(16)), 'group_id' => $group, 'org_id' => $org,
                'created_by' => session()->get('userid'), 'created_date' => date('Y-m-d H:i:s'), 'active' => '1'];
        }
        $ok = $model->saveRisk($group, $org, $id, $data);
        return $this->back($ok ? 'Profil risiko berhasil disimpan.' : 'Profil risiko gagal disimpan.', !$ok, $id ?: $data['risk_id']);
    }

    public function monitor()
    {
        $this->mutation();
        $model = $this->model();
        $id = $this->text('risk_id');
        $risk = $model->risk(session()->get('groupid'), session()->get('orgid'), $id);
        if (!$risk) {
            return $this->back('Risiko tidak ditemukan.', true);
        }
        $data = [];
        foreach (['action', 'output', 'target', 'realization', 'implementation', 'pic', 'notes', 'evidence_url', 'likelihood', 'impact_level'] as $key) {
            $data[$key] = $this->text($key);
            if (strlen($data[$key]) > 12000) {
                return $this->back('Isi kolom terlalu panjang.', true, $id);
            }
        }
        foreach (['action', 'output', 'target', 'realization', 'implementation', 'pic'] as $key) {
            if ($data[$key] === '') {
                return $this->back('Lengkapi aksi, output, target, realisasi, waktu dan PIC pemantauan.', true, $id);
            }
        }
        $quarter = $this->text('quarter');
        if (!preg_match('/^[1-4]$/', $quarter) || !preg_match('/^[1-5]$/', $data['likelihood']) || !preg_match('/^[1-5]$/', $data['impact_level'])
            || mb_strlen($data['pic']) > 200 || strlen($data['evidence_url']) > 2000) {
            return $this->back('Triwulan, level penilaian, PIC atau tautan tidak valid.', true, $id);
        }
        if ($data['evidence_url'] !== '' && (!filter_var($data['evidence_url'], FILTER_VALIDATE_URL)
            || !in_array(strtolower((string) parse_url($data['evidence_url'], PHP_URL_SCHEME)), ['http', 'https'], true))) {
            return $this->back('Bukti harus berupa tautan http atau https.', true, $id);
        }
        $data += ['monitor_id' => bin2hex(random_bytes(16)), 'risk_id' => $id, 'quarter' => (int) $quarter,
            'baseline_score' => RiskMatrix::assess((int) $risk['likelihood'], (int) $risk['impact_level'])['score'],
            'created_by' => session()->get('userid'), 'created_date' => date('Y-m-d H:i:s'),
            'last_update_by' => session()->get('userid'), 'last_update_date' => date('Y-m-d H:i:s')];
        $ok = $model->saveMonitoring($id, (int) $quarter, $data);
        return $this->back($ok ? 'Pemantauan triwulan berhasil disimpan.' : 'Pemantauan gagal disimpan.', !$ok, $id);
    }

    public function export()
    {
        $model = $this->model();
        if (!$model->ready()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $year = $this->query('year');
        $year = preg_match('/^20\d{2}$/', $year) ? (int) $year : (int) date('Y');
        $quarter = $this->query('quarter');
        $quarter = in_array($quarter, ['1', '2', '3', '4'], true) ? (int) $quarter : 1;
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['Tahun', 'Unit', 'Kode', 'Kejadian Risiko', 'Skor Register', 'Level', 'Perlu Penanganan', 'Triwulan', 'Aksi', 'Output', 'Target', 'Realisasi', 'Waktu Implementasi', 'PIC', 'Skor Awal Pemantauan', 'Skor Outlook', 'Tren', 'Bukti', 'Catatan']);
        foreach ($model->risks(session()->get('groupid'), session()->get('orgid'), $year, $this->query('department')) as $risk) {
            $risk = $this->enrich($risk, $model);
            $m = null;
            foreach ($risk['monitoring'] as $item) {
                if ((int) $item['quarter'] === $quarter) { $m = $item; }
            }
            $row = [$year, $risk['department'], $risk['code'], $risk['event'], $risk['assessment']['score'], $risk['assessment']['level'], $risk['assessment']['requires_action'] ? 'Ya' : 'Tidak', $quarter,
                $m['action'] ?? $risk['action_plan'], $m['output'] ?? $risk['output'], $m['target'] ?? $risk['action_target'], $m['realization'] ?? '', $m['implementation'] ?? $risk['implementation'], $m['pic'] ?? $risk['pic'],
                $m['baseline_score'] ?? '', $m['assessment']['score'] ?? '', $m['trend'] ?? 'Belum dipantau', $m['evidence_url'] ?? '', $m['notes'] ?? ''];
            // Prevent spreadsheet formula injection in exported user-controlled text.
            $row = array_map(static function ($value) {
                return is_string($value) && preg_match('/^[\s]*[=+@-]/u', $value) ? "'" . $value : $value;
            }, $row);
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        return response()->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="pemantauan-risiko-' . $year . '-tw' . $quarter . '.csv"')->setBody($csv);
    }
}
