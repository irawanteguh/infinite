<?php
namespace Modules\Satusehat\Controllers;

use App\Controllers\BaseController;
use Modules\Satusehat\Libraries\BirthWorkspace;
use Modules\Satusehat\Libraries\EncounterRegistration;
use Modules\Satusehat\Models\OrganizationModel;

class Organization extends BaseController
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
        return view('template/dashboard-light-aside', ['contents' => view('Modules\Satusehat\Views\v_organization', compact('environments', 'loadError'))]);
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
            $model = new OrganizationModel();
            // Check storage before making any remote request.
            $records = $model->records($group, $org, $env['hash']);
            if (!$write) return response()->setHeader('Cache-Control', 'no-store')->setJSON(['data' => ['records' => $records, 'rootId' => $env['org_ihs']], 'csrfHash' => csrf_hash()]);
            $lock = fopen(WRITEPATH . 'organization-' . $scope . '.lock', 'c');
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) throw new \DomainException('Organization sedang diproses. Tunggu hingga selesai.');
            try {
                if ($action === 'sync') {
                    $records = $service->resources($workspace, $group, $org, (string)session()->get('userid'), $env, 'Organization', ['partof' => $env['org_ihs']]);
                } else {
                    $name = trim((string)($input['name'] ?? ''));
                    $code = trim((string)($input['code'] ?? ''));
                    if ($name === '' || $code === '' || strlen($name) > 200 || strlen($code) > 100) throw new \InvalidArgumentException('Isi kode dan nama Organization dengan benar.');
                    foreach ($records as $record) {
                        foreach ($record['identifier'] ?? [] as $identifier) {
                            if (($identifier['system'] ?? '') === 'http://sys-ids.kemkes.go.id/organization/' . $env['org_ihs'] && ($identifier['value'] ?? '') === $code) throw new \DomainException('Kode Organization sudah tersimpan. Gunakan kode berbeda.');
                        }
                    }
                    $type = trim((string)($input['type'] ?? ''));
                    if (!in_array($type, ['dept', 'team', 'other'], true)) throw new \InvalidArgumentException('Pilih jenis Organization.');
                    $parent = $input['part_of'] ?? $env['org_ihs'];
                    if (!is_string($parent) || !in_array($parent, array_merge([$env['org_ihs']], array_column($records, 'id')), true)) {
                        throw new \InvalidArgumentException('Pilih induk dari master Organization environment aktif.');
                    }
                    $body = [
                        'resourceType' => 'Organization', 'active' => true,
                        'identifier' => [['use' => 'official', 'system' => 'http://sys-ids.kemkes.go.id/organization/' . $env['org_ihs'], 'value' => $code]],
                        'type' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/organization-type', 'code' => $type]]]],
                        'name' => $name, 'partOf' => ['reference' => 'Organization/' . $parent],
                    ];
                    $result = $service->bridge($workspace, $group, $org, (string)session()->get('userid'), $env, 'POST', 'Organization', $body);
                    if ($result['status'] < 200 || $result['status'] >= 300) {
                        $issues = array_map(static fn($issue) => $issue['details']['text'] ?? $issue['diagnostics'] ?? $issue['code'] ?? '', $result['body']['issue'] ?? []);
                        throw new \DomainException('SATUSEHAT menolak Organization (HTTP ' . $result['status'] . '). ' . implode(' ', $issues));
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
            log_message('error', 'Organization: {message}', ['message' => $e->getMessage()]);
            $message = $e instanceof \DomainException || $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Organization gagal diproses. Periksa log bridging lalu lakukan Sync sebelum mengulangi penambahan.';
            return response()->setStatusCode(422)->setHeader('Cache-Control', 'no-store')->setJSON(['error' => $message, 'csrfHash' => csrf_hash()]);
        }
    }

    public function data() { return $this->execute('data'); }
    public function sync() { return $this->execute('sync'); }
    public function create() { return $this->execute('create'); }
}
