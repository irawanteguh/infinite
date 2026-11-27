<?php
namespace Modules\Satusehat\Controllers;
use App\Controllers\BaseController;
use Modules\Satusehat\Libraries\BirthWorkspace;
use Modules\Satusehat\Libraries\EncounterRegistration;
use Modules\Satusehat\Libraries\NewbornPatient;
use Modules\Satusehat\Models\PatientModel;
class Patient extends BaseController
{
    public function index()
    {
        if (!session()->get('loggedin')) return redirect()->to(site_url('auth/login'));
        $data = ['records' => [], 'environments' => [], 'provinces' => [], 'loadError' => ''];
        try {
            $data['records'] = (new PatientModel())->searchForOrganization((string)session()->get('groupid'), (string)session()->get('orgid'), '', true);
        } catch (\Throwable $e) {
            log_message('error', 'Patient list: {message}', ['message' => $e->getMessage()]);
            $data['loadError'] = 'Master pasien gagal dimuat karena koneksi database. Klik Perbarui untuk mencoba kembali.';
        }
        try {
            $data['environments'] = $this->environmentOptions(new BirthWorkspace());
        } catch (\Throwable $e) {
            log_message('error', 'Patient environments: {message}', ['message' => $e->getMessage()]);
            $data['loadError'] .= ' Environment belum dapat dibaca. Periksa konfigurasi organisasi.';
        }
        try {
            $data['provinces'] = (new \Modules\Satusehat\Models\RegionModel())->options('provinces', '')['results'];
        } catch (\Throwable $e) {
            log_message('error', 'Patient provinces: {message}', ['message' => $e->getMessage()]);
        }
        return view('template/dashboard-light-aside', ['contents' => view('Modules\Satusehat\Views\v_patient', $data)]);
    }
    private function execute(callable $action, bool $write = false)
    {
        try {
            $workspace = new BirthWorkspace(); $workspace->scope();
            if ($write) {
                if (strtolower(request()->getMethod()) !== 'post') return response()->setStatusCode(405)->setJSON(['error' => 'Gunakan POST.']);
                service('security')->verify(request());
            }
            $result = $action($workspace, (string)session()->get('groupid'), (string)session()->get('orgid'));
            if ($result instanceof \CodeIgniter\HTTP\ResponseInterface) return $result->setHeader('Cache-Control', 'no-store');
            return response()->setHeader('Cache-Control', 'no-store')->setJSON(['data' => $result, 'csrfHash' => csrf_hash()]);
        } catch (\Throwable $e) {
            log_message('error', 'Patient: {message}', ['message' => $e->getMessage()]);
            $message = $e instanceof \CodeIgniter\Database\Exceptions\DatabaseException ? 'Koneksi database master pasien gagal. Klik Perbarui untuk mencoba kembali.' : ($e instanceof \InvalidArgumentException || $e instanceof \DomainException ? $e->getMessage() : 'Pasien belum dapat diproses. Periksa log bridging dan muat ulang daftar sebelum mencoba kembali.');
            return response()->setStatusCode(422)->setHeader('Cache-Control', 'no-store')->setJSON(['error' => $message, 'csrfHash' => csrf_hash()]);
        }
    }
    private function environmentOptions(BirthWorkspace $workspace): array
    {
        $service = new EncounterRegistration();
        return array_map(static function ($env) use ($service) {
            $sandbox = false;
            try {
                $sandbox = $service->environment($env['id'])['sandbox'];
            } catch (\InvalidArgumentException $e) {
                // Keep incomplete environments available for manual selection.
            }
            return ['id' => $env['id'], 'name' => $env['name'], 'sandbox' => $sandbox];
        }, $workspace->environments());
    }
    public function data()
    {
        return $this->execute(function ($workspace, $group, $org) {
            return ['records' => (new PatientModel())->searchForOrganization($group, $org, '', true),
                'environments' => $this->environmentOptions($workspace)];
        });
    }
    public function mothers()
    {
        return $this->execute(static function ($workspace, $group, $org) {
            $environmentId = (string)request()->getGet('environment_id');
            $sandbox = (new EncounterRegistration())->environment($environmentId)['sandbox'];
            $dummyOnly = request()->getGet('dummy_only') === '1';
            if ($dummyOnly && !$sandbox) throw new \InvalidArgumentException('Isian dummy hanya tersedia di Sandbox.');
            return ['records' => (new PatientModel())->motherOptions($group, $org, $sandbox, $dummyOnly)];
        });
    }
    public function regions()
    {
        $post = strtolower(request()->getMethod()) === 'post';
        return $this->execute(static function () use ($post) {
            $input = $post ? request()->getJSON(true) : request()->getGet();
            if (!is_array($input)) throw new \InvalidArgumentException('Permintaan wilayah tidak valid.');
            return (new \Modules\Satusehat\Models\RegionModel())->options((string)($input['level'] ?? ''), (string)($input['parent'] ?? ''));
        }, $post);
    }
    private function patientPayload(array $input, string $group, string $org, array $environment): array
    {
        $mother = (new PatientModel())->findForOrganization($group, $org, 'nik', (string)($input['mother_nik'] ?? ''), $environment['sandbox']);
        if (!$mother) throw new \InvalidArgumentException('Pilih NIK ibu dari master pasien organisasi sesuai environment.');
        $input = (new \Modules\Satusehat\Models\RegionModel())->validateAddress($input);
        $input['mother_name'] = $mother['name'];
        $body = (new NewbornPatient())->payload($input);
        return $body;
    }

    public function preview()
    {
        return $this->execute(function ($workspace, $group, $org) {
            $input = request()->getJSON(true);
            if (!is_array($input)) throw new \InvalidArgumentException('Formulir pasien tidak valid.');
            $environment = (new EncounterRegistration())->environment((string)($input['environment_id'] ?? ''));
            return ['requestBody' => $this->patientPayload($input, $group, $org, $environment)];
        }, true);
    }

    public function create()
    {
        return $this->execute(function ($workspace, $group, $org) {
            $input = request()->getJSON(true);
            if (!is_array($input)) throw new \InvalidArgumentException('Formulir pasien tidak valid.');
            $service = new EncounterRegistration();
            $environment = $service->environment((string)($input['environment_id'] ?? ''));
            $body = $this->patientPayload($input, $group, $org, $environment);
            $db = \Config\Database::connect();
            if (!$db->fieldExists('mother_nik', 'dt01_satusehat_patient_ms')) throw new \DomainException('Jalankan php spark satusehat:setup-newborn terlebih dahulu.');
            // Serialize creates per organization to prevent simultaneous duplicate POSTs.
            $lock = fopen(WRITEPATH . 'patient-create-' . $workspace->scope() . '.lock', 'c');
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) throw new \DomainException('Penambahan pasien sedang diproses. Tunggu lalu perbarui daftar.');
            try {
                $result = $service->bridge($workspace, $group, $org, (string)session()->get('userid'), $environment, 'POST', 'Patient', $body);
                $patient = $result['body'];
                $error = null;
                if ($result['status'] < 200 || $result['status'] >= 300) {
                    $error = 'SATUSEHAT menolak penambahan (HTTP ' . $result['status'] . '). Lihat request dan response untuk rincian.';
                } elseif (($patient['resourceType'] ?? '') !== 'Patient' || !preg_match('/^[A-Za-z0-9.-]{1,64}$/D', $patient['id'] ?? '')) {
                    $error = 'Hasil penambahan belum pasti. Periksa log bridging sebelum mencoba kembali.';
                }
                if ($error !== null) {
                    return response()->setStatusCode(422)->setJSON([
                        'error' => $error,
                        'exchange' => ['requestBody' => $body, 'responseBody' => $patient],
                        'csrfHash' => csrf_hash(),
                    ]);
                }
                (new PatientModel())->storeNewborn($group, $org, $patient, $body, $environment['sandbox']);
                return ['ihs' => $patient['id']];
            } finally { flock($lock, LOCK_UN); fclose($lock); }
        }, true);
    }
}
