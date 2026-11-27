<?php
namespace Modules\Satusehat\Controllers;

use App\Controllers\BaseController;
use Modules\Satusehat\Libraries\BirthWorkspace;
use Modules\Satusehat\Libraries\EncounterRegistration;
use Modules\Satusehat\Models\LocationModel;

class Location extends BaseController
{
    public function index()
    {
        if (!session()->get('loggedin')) return redirect()->to(site_url('auth/login'));
        $environments = [];
        $loadError = '';
        try {
            $workspace = new BirthWorkspace();
            $workspace->scope();
            $environments = $workspace->environments();
        } catch (\Throwable $e) {
            $loadError = 'Environment belum dapat dimuat.';
        }
        return view('template/dashboard-light-aside', ['contents' => view('Modules\Satusehat\Views\v_location', compact('environments', 'loadError'))]);
    }

    private function execute(string $action)
    {
        try {
            $workspace = new BirthWorkspace();
            $scope = $workspace->scope();
            $write = $action !== 'data';
            if ($write) {
                if (strtolower(request()->getMethod()) !== 'post') return response()->setStatusCode(405)->setJSON(['error' => 'Gunakan POST.']);
                service('security')->verify(request());
            }
            $input = $write ? request()->getJSON(true) : request()->getGet();
            if (!is_array($input)) throw new \InvalidArgumentException('Permintaan tidak valid.');
            $service = new EncounterRegistration();
            $env = $service->environment((string)($input['environment_id'] ?? ''));
            $group = (string)session()->get('groupid');
            $org = (string)session()->get('orgid');
            $model = new LocationModel();
            // Check storage before making any remote request.
            $records = $model->records($group, $org, $env['hash']);
            $organizations = (new \Modules\Satusehat\Models\OrganizationModel())->records($group, $org, $env['hash']);
            if (!$write) return response()->setHeader('Cache-Control', 'no-store')->setJSON(['data' => ['records' => $records, 'rootId' => $env['org_ihs'], 'organizations' => $organizations], 'csrfHash' => csrf_hash()]);
            $lock = fopen(WRITEPATH . 'location-' . $scope . '.lock', 'c');
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) throw new \DomainException('Location sedang diproses. Tunggu hingga selesai.');
            try {
                if ($action === 'sync') {
                    $records = $service->resources($workspace, $group, $org, (string)session()->get('userid'), $env, 'Location', ['organization' => $env['org_ihs']]);
                } else {
                    $name = trim((string)($input['name'] ?? ''));
                    $code = trim((string)($input['code'] ?? ''));
                    if ($name === '' || $code === '' || strlen($name) > 200 || strlen($code) > 100) throw new \InvalidArgumentException('Isi kode dan nama Location dengan benar.');
                    foreach ($records as $record) {
                        foreach ($record['identifier'] ?? [] as $identifier) {
                            if (($identifier['system'] ?? '') === 'http://sys-ids.kemkes.go.id/location/' . $env['org_ihs'] && ($identifier['value'] ?? '') === $code) throw new \DomainException('Kode Location sudah tersimpan. Gunakan kode berbeda.');
                        }
                    }
                    $type = trim((string)($input['type'] ?? ''));
                    if (!in_array($type, ['si', 'bu', 'lvl', 'ro', 'bd', 'wa', 'wi', 'co', 've', 'ho', 'ca', 'rd', 'area', 'jdn'], true)) throw new \InvalidArgumentException('Pilih jenis Location.');
                    $parent = $input['part_of'] ?? $env['org_ihs'];
                    if (!is_string($parent) || !in_array($parent, array_merge([$env['org_ihs']], array_column($organizations, 'id')), true)) {
                        throw new \InvalidArgumentException('Pilih induk dari master Location environment aktif.');
                    }
                    $body = [
                        'resourceType' => 'Location', 'status' => 'active', 'mode' => 'instance',
                        'identifier' => [['use' => 'official', 'system' => 'http://sys-ids.kemkes.go.id/location/' . $env['org_ihs'], 'value' => $code]],
                        'physicalType' => ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/location-physical-type', 'code' => $type]]],
                        'name' => $name, 'managingOrganization' => ['reference' => 'Organization/' . $parent],
                    ];
                    $description = trim((string)($input['description'] ?? ''));
                    if (strlen($description) > 1000) throw new \InvalidArgumentException('Deskripsi maksimal 1000 karakter.');
                    if ($description !== '') $body['description'] = $description;
                    $result = $service->bridge($workspace, $group, $org, (string)session()->get('userid'), $env, 'POST', 'Location', $body);
                    if ($result['status'] < 200 || $result['status'] >= 300) {
                        $issues = array_map(static fn($issue) => $issue['details']['text'] ?? $issue['diagnostics'] ?? $issue['code'] ?? '', $result['body']['issue'] ?? []);
                        throw new \DomainException('SATUSEHAT menolak Location (HTTP ' . $result['status'] . '). ' . implode(' ', $issues));
                    }
                    $records = [$result['body']];
                }
                $model->store($group, $org, $env['hash'], $records);
                return response()->setHeader('Cache-Control', 'no-store')->setJSON(['data' => ['count' => count($records)], 'csrfHash' => csrf_hash()]);
            } finally {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Location: {message}', ['message' => $e->getMessage()]);
            $message = $e instanceof \DomainException || $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Location gagal diproses. Periksa log bridging lalu lakukan Sync sebelum mengulangi penambahan.';
            return response()->setStatusCode(422)->setHeader('Cache-Control', 'no-store')->setJSON(['error' => $message, 'csrfHash' => csrf_hash()]);
        }
    }

    public function data() { return $this->execute('data'); }
    public function sync() { return $this->execute('sync'); }
    public function create() { return $this->execute('create'); }
}
