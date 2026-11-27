<?php
namespace Modules\Satusehat\Controllers;

use App\Controllers\BaseController;
use Modules\Satusehat\Libraries\BirthWorkspace;

class Datakelahiran extends BaseController
{
    public function index()
    {
        if (!session()->get('loggedin')) return redirect()->to(site_url('auth/login'));
        return view('template/dashboard-light-aside', ['contents' => view('Modules\Satusehat\Views\v_datakelahiran')]);
    }

    private function execute(callable $action, bool $write = false)
    {
        try {
            $workspace = new BirthWorkspace();
            $workspace->scope();
            if ($write) {
                if (strtolower(request()->getMethod()) !== 'post') return response()->setStatusCode(405)->setJSON(['error' => 'Gunakan POST.']);
                service('security')->verify(request());
            }
            $data = $action($workspace);
            return response()->setHeader('Cache-Control', 'no-store')->setJSON(['data' => $data, 'csrfHash' => csrf_hash()]);
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) {
            return response()->setStatusCode(403)->setJSON(['error' => 'Sesi keamanan berakhir. Muat ulang halaman.']);
        } catch (\Throwable $e) {
            log_message('error', 'Birth transaction: {message}', ['message' => $e->getMessage()]);
            return response()->setStatusCode(422)->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function data()
    {
        return $this->execute(static function ($workspace) {
            $actions = [];
            foreach ($workspace->actions() as $action) {
                $schema = $workspace->schema($action);
                $schema['method'] = $action['request']['method'] ?? 'POST';
                unset($schema['body']);
                $actions[] = array_merge(array_intersect_key($action, array_flip(['id', 'name', 'stage', 'group'])), $schema);
            }
            return ['transactions' => $workspace->listings(), 'actions' => $actions, 'environments' => array_map(static fn($env) => ['id' => $env['id'], 'name' => $env['name']], $workspace->environments())];
        });
    }

    public function detail()
    {
        return $this->execute(static function ($workspace) {
            $record = $workspace->load((string)request()->getGet('id'));
            if (!$record) throw new \InvalidArgumentException('Transaksi tidak ditemukan.');
            return $record;
        });
    }

    public function save()
    {
        return $this->execute(static function ($workspace) {
            $input = request()->getJSON(true);
            if (!is_array($input) || empty($input['number']) || empty($input['mother']) || empty($input['environment'])) throw new \InvalidArgumentException('Isi nomor registrasi, nama ibu, dan environment.');
            $id = $input['id'] ?? bin2hex(random_bytes(16));
            return $workspace->update($id, static function ($current) use ($input) {
                if ($current && ($input['revision'] ?? '') !== $current['revision']) throw new \DomainException('Transaksi telah diperbarui. Buka ulang transaksi.');
                foreach (($current['results'] ?? []) as $result) if ($result['state'] === 'pending') throw new \DomainException('Ada pengiriman yang belum selesai.');
                if ($current && $current['environment'] !== $input['environment'] && !empty($current['results'])) throw new \DomainException('Environment transaksi yang sudah dikirim tidak dapat diganti.');
                $record = $current ?? ['results' => [], 'context' => [], 'drafts' => []];
                foreach (['number', 'mother', 'baby', 'environment'] as $key) $record[$key] = (string)($input[$key] ?? '');
                if ($current && !empty($current['results'])) {
                    foreach (['Patient_Id_Ibu', 'Patient_Id_Bayi'] as $key) {
                        if (!empty($current['context'][$key]) && ($input['context'][$key] ?? '') !== $current['context'][$key]) throw new \DomainException('Identitas pasien pada transaksi yang sudah dikirim tidak dapat diganti.');
                    }
                }
                foreach (['context', 'drafts', 'patients'] as $key) {
                    if (isset($input[$key]) && !is_array($input[$key])) throw new \InvalidArgumentException('Format formulir tidak valid.');
                    $record[$key] = $input[$key] ?? ($record[$key] ?? []);
                }
                return $record;
            });
        }, true);
    }

    public function search()
    {
        return $this->execute(static function ($workspace) {
            $input = request()->getJSON(true);
            $resource = $input['resource'] ?? '';
            if (!in_array($resource, ['Patient', 'Practitioner', 'Location', 'EpisodeOfCare'], true)) throw new \InvalidArgumentException('Jenis pencarian tidak valid.');
            $query = [];
            if ($resource === 'Practitioner' && isset($input['mode'])) {
                $mode = $input['mode'];
                $identifier = (string)($input['identifier'] ?? '');
                if (!in_array($mode, ['ihs', 'nik'], true) || !preg_match($mode === 'nik' ? '/^[0-9]{16}$/D' : '/^[A-Za-z0-9-]{1,64}$/D', $identifier)) {
                    throw new \InvalidArgumentException('Masukkan IHS atau NIK Practitioner yang valid.');
                }
                $environment = null;
                foreach ($workspace->environments() as $candidate) if ($candidate['id'] === ($input['environment'] ?? '')) $environment = $candidate;
                if (!$environment) throw new \InvalidArgumentException('Pilih environment.');
                $sandbox = false;
                foreach ($environment['values'] as $value) {
                    if ($value['key'] === 'base_url' && ($value['enabled'] ?? true) !== false) {
                        $sandbox = parse_url($value['value'], PHP_URL_HOST) === 'api-satusehat-stg.dto.kemkes.go.id';
                    }
                }
                $model = new \Modules\Satusehat\Models\PractitionerModel();
                $practitioner = $model->findForOrganization((string)session()->get('groupid'), (string)session()->get('orgid'), $mode, $identifier, $sandbox);
                if ($practitioner) {
                    return [
                        'resourceType' => 'Bundle',
                        'entry' => [['resource' => $model->toResource($practitioner)]],
                        'source' => $practitioner['is_dummy'] === '1' ? 'Master Practitioner organisasi · data dummy Sandbox' : 'Master Practitioner organisasi',
                    ];
                }
            }
            if (in_array($resource, ['Patient', 'Practitioner'], true) && isset($input['mode'])) {
                $identifier = (string)($input['identifier'] ?? '');
                if ($input['mode'] === 'ihs') {
                    if (!preg_match('/^[A-Za-z0-9-]{1,64}$/D', $identifier)) throw new \InvalidArgumentException('Masukkan nomor IHS yang valid.');
                    $result = $workspace->api($input['environment'] ?? '', 'GET', $resource . '/' . rawurlencode($identifier));
                } elseif ($input['mode'] === 'nik') {
                    if (!preg_match('/^[0-9]{16}$/D', $identifier)) throw new \InvalidArgumentException('Masukkan NIK 16 digit.');
                    $result = $workspace->api($input['environment'] ?? '', 'GET', $resource . '?' . http_build_query(['identifier' => 'https://fhir.kemkes.go.id/id/nik|' . $identifier]));
                } else throw new \InvalidArgumentException('Pilih IHS atau NIK.');
                if ($result['status'] < 200 || $result['status'] >= 300) throw new \RuntimeException('Data belum dapat ditemukan. Periksa nomor dan environment.');
                return $result['body'];
            }
            if ($resource === 'Patient' || $resource === 'Practitioner') {
                $nik = $input['nik'] ?? '';
                if (!preg_match('/^[0-9]{16}$/D', $nik)) throw new \InvalidArgumentException('Masukkan NIK 16 digit.');
                $query['identifier'] = 'https://fhir.kemkes.go.id/id/' . (($input['motherNik'] ?? false) && $resource === 'Patient' ? 'nik-ibu' : 'nik') . '|' . $nik;
                if (($input['motherNik'] ?? false) && $resource === 'Patient') {
                    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $input['birthdate'] ?? '')) throw new \InvalidArgumentException('Isi tanggal lahir bayi.');
                    $query['birthdate'] = $input['birthdate'];
                }
            } elseif ($resource === 'EpisodeOfCare') {
                if (empty($input['patient'])) throw new \InvalidArgumentException('Isi ID pasien ibu.');
                $query = ['patient' => $input['patient'], 'type' => 'ANC', 'status' => 'active'];
            } else {
                if (empty($input['name'])) throw new \InvalidArgumentException('Isi nama lokasi.');
                $query['name'] = $input['name'];
            }
            $result = $workspace->api($input['environment'] ?? '', 'GET', $resource . '?' . http_build_query($query));
            if ($result['status'] >= 400) throw new \RuntimeException(json_encode($result['body'], JSON_UNESCAPED_UNICODE));
            return $result['body'];
        }, true);
    }

    public function send()
    {
        return $this->execute(static function ($workspace) {
            $input = request()->getJSON(true);
            $id = $input['id'] ?? '';
            $action = $workspace->actions()[$input['action'] ?? ''] ?? null;
            if (!$action) throw new \InvalidArgumentException('Tahap transaksi tidak tersedia.');
            $actionId = $action['id'];
            $method = strtoupper($action['request']['method'] ?? 'POST');
            $record = $workspace->load($id);
            if (!$record || $record['revision'] !== ($input['revision'] ?? '')) throw new \DomainException('Buka ulang transaksi sebelum mengirim.');
            if (in_array('EpisodeOfCare_id_Ibu', $workspace->schema($action)['variables'], true) && empty($record['context']['EpisodeOfCare_id_Ibu'])) {
                if (empty($record['context']['Patient_Id_Ibu'])) throw new \InvalidArgumentException('Pilih pasien ibu terlebih dahulu.');
                $lookup = $workspace->api($record['environment'], 'GET', 'EpisodeOfCare?' . http_build_query([
                    'patient' => $record['context']['Patient_Id_Ibu'], 'type' => 'ANC', 'status' => 'active',
                ]));
                if ($lookup['status'] < 200 || $lookup['status'] >= 300) throw new \RuntimeException('Riwayat pelayanan ANC ibu belum dapat diperiksa. Coba kembali.');
                $episodes = array_values(array_filter($lookup['body']['entry'] ?? [], static fn($entry) => ($entry['resource']['resourceType'] ?? '') === 'EpisodeOfCare'));
                if (count($episodes) !== 1 || empty($episodes[0]['resource']['id'])) throw new \InvalidArgumentException(count($episodes) ? 'Terdapat lebih dari satu pelayanan ANC aktif. Referensi kunjungan perlu ditentukan sebelum pengiriman.' : 'Pelayanan ANC aktif untuk ibu belum ditemukan di SATUSEHAT.');
                $record['context']['EpisodeOfCare_id_Ibu'] = $episodes[0]['resource']['id'];
            }
            $payload = $workspace->payload($action, $record['drafts'][$actionId] ?? [], $record);
            $url = is_array($action['request']['url'] ?? null) ? $action['request']['url']['raw'] : $action['request']['url'];
            $context = $record['context'];
            foreach ($workspace->environments() as $env) {
                if ($env['id'] === $record['environment']) foreach ($env['values'] as $value) if ($value['key'] === 'Org_id') $context['Org_id'] = $value['value'];
            }
            $url = preg_replace_callback('/{{([^{}]+)}}/', static function ($match) use ($context) {
                if ($match[1] === 'base_url') return '';
                if (!isset($context[$match[1]]) || $context[$match[1]] === '') throw new \InvalidArgumentException('Lengkapi referensi ' . $match[1]);
                return rawurlencode($context[$match[1]]);
            }, $url);
            if (strpos($url, 'http') === 0 || strpos($url, ':') !== false) throw new \InvalidArgumentException('Endpoint tahap ini perlu dikonfigurasi sebelum pengiriman.');
            $record = $workspace->update($id, static function ($current) use ($input, $actionId, $method, $record) {
                if ($current['revision'] !== $input['revision']) throw new \DomainException('Transaksi berubah. Buka ulang transaksi.');
                $state = $current['results'][$actionId]['state'] ?? '';
                if (in_array($state, ['pending', 'unknown', 'success'], true) && $method !== 'GET') throw new \DomainException('Tahap sudah dikirim atau hasilnya belum pasti. Periksa SATUSEHAT sebelum pengiriman ulang.');
                $current['context'] = $record['context'];
                $current['results'][$actionId] = ['state' => 'pending', 'time' => date(DATE_ATOM)];
                return $current;
            });
            try {
                $result = $workspace->api($record['environment'], $method, $url, $method === 'GET' ? null : $payload);
                return $workspace->update($id, static function ($current) use ($result, $action, $actionId, $payload) {
                    $success = $result['status'] >= 200 && $result['status'] < 300;
                    $current['results'][$actionId] = ['state' => $success ? 'success' : 'failed', 'status' => $result['status'], 'body' => $result['body'], 'time' => date(DATE_ATOM)];
                    if ($success && isset($result['body']['id'])) {
                        foreach (($action['request']['__output'] ?? []) as $key) $current['context'][$key] = $result['body']['id'];
                        // Output names are extracted from the collection's literal data.id setters.
                    }
                    return $current;
                });
            } catch (\Throwable $e) {
                $workspace->update($id, static function ($current) use ($actionId) {
                    $current['results'][$actionId] = ['state' => 'unknown', 'time' => date(DATE_ATOM)];
                    return $current;
                });
                throw $e;
            }
        }, true);
    }
}
