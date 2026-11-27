<?php
namespace Modules\Satusehat\Controllers;

use App\Controllers\BaseController;
use Modules\Satusehat\Libraries\BirthWorkspace;
use Modules\Satusehat\Libraries\EncounterRegistration;
use Modules\Satusehat\Models\EncounterModel;
use Modules\Satusehat\Models\PatientModel;
use Modules\Satusehat\Models\PractitionerModel;

class Encounter extends BaseController
{
    public function index()
    {
        if (!session()->get('loggedin')) return redirect()->to(site_url('auth/login'));
        return view('template/dashboard-light-aside', ['contents' => view('Modules\Satusehat\Views\v_encounter')]);
    }

    private function execute(callable $action, bool $write = false)
    {
        try {
            $workspace = new BirthWorkspace();
            $workspace->scope();
            $group = (string)session()->get('groupid');
            $org = (string)session()->get('orgid');
            $user = (string)session()->get('userid');
            if ($write) {
                if (strtolower(request()->getMethod()) !== 'post') return response()->setStatusCode(405)->setJSON(['error' => 'Gunakan POST.']);
                service('security')->verify(request());
            }
            $model = new EncounterModel();
            if (!$model->ready()) return response()->setStatusCode(503)->setJSON(['error' => 'Tabel pendaftaran Encounter belum tersedia.']);
            $result = $action($model, new EncounterRegistration(), $workspace, $group, $org, $user);
            return response()->setHeader('Cache-Control', 'no-store')->setJSON(['data' => $result, 'csrfHash' => csrf_hash()]);
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) {
            return $this->failure(403, 'Token keamanan berakhir. Muat ulang halaman.');
        } catch (\DomainException $e) {
            return $this->failure(409, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return $this->failure(422, $e->getMessage());
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Encounter database: {message}', ['message' => $e->getMessage()]);
            return $this->failure(409, 'Data belum tersimpan. Pastikan nomor registrasi belum digunakan pada environment organisasi ini.');
        } catch (\Throwable $e) {
            log_message('error', 'Encounter registration: {message}', ['message' => $e->getMessage()]);
            return $this->failure(502, $e instanceof \RuntimeException ? $e->getMessage() : 'Pendaftaran belum dapat diproses. Silakan coba kembali.');
        }
    }

    private function failure(int $status, string $message)
    {
        return response()->setStatusCode($status)->setHeader('Cache-Control', 'no-store')->setJSON(['error' => $message, 'csrfHash' => csrf_hash()]);
    }

    public function data()
    {
        return $this->execute(static function ($model, $service, $workspace, $group, $org) {
            return ['records' => $model->records($group, $org), 'environments' => array_map(static fn($env) => ['id' => $env['id'], 'name' => $env['name']], $workspace->environments())];
        });
    }

    public function detail()
    {
        return $this->execute(static fn($model, $service, $workspace, $group, $org) => $model->record($group, $org, (string)request()->getGet('id')));
    }

    public function save()
    {
        return $this->execute(static function ($model, $service, $workspace, $group, $org, $user) {
            $input = request()->getJSON(true);
            if (!is_array($input) || !is_array($input['form'] ?? null)) throw new \InvalidArgumentException('Formulir pendaftaran tidak valid.');
            $form = $service->normalize(self::masterPeople($input['form'], $service, $group, $org));
            $id = $input['id'] ?? null;
            if ($id !== null && !is_string($id)) throw new \InvalidArgumentException('Transaksi tidak valid.');
            return $model->saveDraft($group, $org, $user, $form, $id, (int)($input['revision'] ?? 0));
        }, true);
    }

    private static function masterPeople(array $form, EncounterRegistration $service, string $group, string $org): array
    {
        $environment = $service->environment((string)($form['environment_id'] ?? ''));
        foreach (['patient' => new PatientModel(), 'practitioner' => new PractitionerModel()] as $key => $master) {
            if (empty($form[$key]['id'])) continue;
            $row = $master->findForOrganization($group, $org, 'ihs', (string)$form[$key]['id'], $environment['sandbox']);
            if (!$row) throw new \InvalidArgumentException(($key === 'patient' ? 'Pasien' : 'Practitioner') . ' tidak ditemukan pada master organisasi untuk environment ini.');
            $form[$key] = ['id' => $row['ihs_number'], 'name' => $row['name'], 'nik' => $row['nik'], 'gender' => $row['gender'], 'birthDate' => $row['birth_date']];
        }
        return $form;
    }

    public function exchange()
    {
        return $this->execute(static function ($model, $service, $workspace, $group, $org) {
            $record = $model->record($group, $org, (string)request()->getGet('id'));
            $row = \Config\Database::connect()->table(\Modules\Satusehat\Database\CreateBridgingLog::TABLE)
                ->where(['group_id' => $group, 'org_id' => $org, 'encounter_tx_id' => $record['encounter_tx_id'], 'method' => 'POST'])
                ->orderBy('bridging_log_id', 'DESC')->get(1)->getRowArray();
            if (!$row) throw new \DomainException('Log pengiriman Encounter belum tersedia.');
            return [
                'requestBody' => json_decode($row['request_json'] ?? 'null', true),
                'responseBody' => json_decode($row['response_json'] ?? 'null', true),
                'status' => $row['http_status'],
            ];
        });
    }

    public function search()
    {
        return $this->execute(static function ($model, $service, $workspace, $group, $org) {
            $input = request()->getJSON(true);
            $resource = $input['resource'] ?? '';
            $environment = $service->environment((string)($input['environment_id'] ?? ''));
            if ($resource === 'Location') {
                $resources = (new \Modules\Satusehat\Models\LocationModel())->records($group, $org, $environment['hash']);
                return ['resources' => array_values(array_filter($resources, static fn($location) => ($location['status'] ?? '') === 'active')),
                    'source' => 'Master Location organisasi sesuai environment. Gunakan Sync pada modul Location untuk memperbarui data.'];
            }
            if (!in_array($resource, ['Patient', 'Practitioner'], true)) throw new \InvalidArgumentException('Jenis pencarian tidak valid.');
            $master = $resource === 'Patient' ? new PatientModel() : new PractitionerModel();
            $query = trim((string)($input['identifier'] ?? ''));
            $rows = $master->searchForOrganization($group, $org, $query, $environment['sandbox']);
            return ['resources' => array_map([$master, 'toResource'], $rows), 'source' => 'Master ' . ($resource === 'Patient' ? 'pasien' : 'Practitioner') . ' organisasi'];

        }, true);
    }

    private static function searchResult(array $result): array
    {
        if ($result['status'] === 404) return ['resources' => [], 'source' => 'SATUSEHAT'];
        if ($result['status'] < 200 || $result['status'] >= 300) {
            $messages = [];
            foreach (($result['body']['issue'] ?? []) as $issue) $messages[] = $issue['details']['text'] ?? $issue['diagnostics'] ?? $issue['code'];
            throw new \RuntimeException('Pencarian SATUSEHAT gagal (HTTP ' . $result['status'] . '). ' . implode(' ', $messages));
        }
        $body = $result['body'];
        $resources = ($body['resourceType'] ?? '') === 'Bundle' ? array_column($body['entry'] ?? [], 'resource') : [$body];
        return ['resources' => $resources, 'source' => 'SATUSEHAT'];
    }

    public function send()
    {
        return $this->execute(static function ($model, $service, $workspace, $group, $org) {
            $input = request()->getJSON(true);
            $record = $model->record($group, $org, (string)($input['id'] ?? ''));
            $environment = $service->environment($record['environment_id']);
            $form = $service->normalize(self::masterPeople($record['form'], $service, $group, $org));
            $payload = $service->payload($form, $environment);
            $claimed = $model->claim($group, $org, $record['encounter_tx_id'], (int)($input['revision'] ?? 0), $environment['hash']);
            try {
                $result = $service->bridge($workspace, $group, $org, (string)session()->get('userid'), $environment, 'POST', 'Encounter', $payload, $record['encounter_tx_id']);
            } catch (\InvalidArgumentException | \DomainException $e) {
                return $model->finish($group, $org, $record['encounter_tx_id'], (int)$claimed['revision'], 'failed', null,
                    ['message' => $e->getMessage()], null);
            } catch (\Throwable $e) {
                return $model->finish($group, $org, $record['encounter_tx_id'], (int)$claimed['revision'], 'unknown', null,
                    ['message' => 'Koneksi terputus. Periksa status sebelum pengiriman berikutnya.'], null);
            }
            $body = $result['body'];
            $success = $result['status'] >= 200 && $result['status'] < 300 && ($body['resourceType'] ?? '') === 'Encounter' && !empty($body['id']);
            $state = $success ? 'sent' : ($result['status'] >= 400 && $result['status'] < 500 && $result['status'] !== 409 ? 'failed' : 'unknown');
            return $model->finish($group, $org, $record['encounter_tx_id'], (int)$claimed['revision'], $state, $result['status'], $body, $success ? $body['id'] : null);
        }, true);
    }

    public function episodes()
    {
        return $this->execute(static function ($model, $service, $workspace, $group, $org, $user) {
            $input = request()->getJSON(true);
            $record = $model->record($group, $org, (string)($input['id'] ?? ''));
            $environment = $service->environment($record['environment_id']);
            if ($record['target_hash'] !== $environment['hash']) throw new \DomainException('Konfigurasi environment kunjungan telah berubah.');
            $resources = $service->resources($workspace, $group, $org, $user, $environment, 'EpisodeOfCare',
                ['patient' => $record['form']['patient']['id'], 'type' => 'ANC', 'status' => 'active'], $record['encounter_tx_id']);
            return ['resources' => $resources];
        }, true);
    }

    public function room()
    {
        return $this->execute(static function ($model, $service, $workspace, $group, $org, $user) {
            $input = request()->getJSON(true);
            $record = $model->record($group, $org, (string)($input['id'] ?? ''));
            if (!in_array($record['state'], ['sent', 'unknown'], true) || !$record['satusehat_id']) throw new \InvalidArgumentException('Encounter harus sudah terkirim sebelum masuk ruang.');
            if ((int)$record['revision'] !== (int)($input['revision'] ?? 0)) throw new \DomainException('Transaksi berubah. Buka ulang aksi masuk ruang.');
            $environment = $service->environment($record['environment_id']);
            if ($record['target_hash'] !== $environment['hash']) throw new \DomainException('Konfigurasi environment kunjungan telah berubah.');
            $episodeId = $input['episode_id'] ?? '';
            if (!is_string($episodeId) || !preg_match('/^[A-Za-z0-9.-]{1,64}$/D', $episodeId)) throw new \InvalidArgumentException('Pilih episode ANC aktif.');
            $episode = $service->bridge($workspace, $group, $org, $user, $environment, 'GET', 'EpisodeOfCare/' . rawurlencode($episodeId), null, $record['encounter_tx_id']);
            if ($episode['status'] !== 200 || ($episode['body']['id'] ?? '') !== $episodeId) throw new \InvalidArgumentException('Episode ANC belum dapat diverifikasi.');
            $service->validateEpisode($episode['body'], $record['form']['patient']['id'], $environment);
            $location = $input['location'] ?? null;
            if (!is_array($location) || !preg_match('/^[A-Za-z0-9.-]{1,64}$/D', $location['id'] ?? '')) throw new \InvalidArgumentException('Pilih ruang pelayanan.');
            $room = $service->bridge($workspace, $group, $org, $user, $environment, 'GET', 'Location/' . rawurlencode($location['id']), null, $record['encounter_tx_id']);
            if ($room['status'] !== 200 || ($room['body']['resourceType'] ?? '') !== 'Location' || ($room['body']['id'] ?? '') !== $location['id']) throw new \InvalidArgumentException('Ruang pelayanan belum dapat diverifikasi.');
            $organization = $room['body']['managingOrganization']['reference'] ?? '';
            if (!in_array($organization, ['Organization/' . $environment['org_ihs'], $environment['base_url'] . '/Organization/' . $environment['org_ihs']], true)) throw new \InvalidArgumentException('Ruang pelayanan tidak termasuk organisasi kunjungan.');
            $location = ['id' => $room['body']['id'], 'name' => $room['body']['name'] ?? $location['id']];
            $path = 'Encounter/' . rawurlencode($record['satusehat_id']);
            $current = $service->bridge($workspace, $group, $org, $user, $environment, 'GET', $path, null, $record['encounter_tx_id']);
            if ($current['status'] !== 200) throw new \RuntimeException('Data Encounter terbaru belum dapat dibaca.');
            if (!in_array($current['body']['serviceProvider']['reference'] ?? '', ['Organization/' . $environment['org_ihs'], $environment['base_url'] . '/Organization/' . $environment['org_ihs']], true)) throw new \InvalidArgumentException('Organisasi Encounter tidak sesuai environment.');
            $payload = $service->roomPayload($current['body'], $record, $location, (string)($input['entered_at'] ?? ''), $episodeId);
            $pending = ['episode_id' => $episodeId, 'location' => $location, 'entered_at' => (new \DateTimeImmutable($input['entered_at']))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:sP')];
            $claimed = $model->claimRoom($group, $org, $record['encounter_tx_id'], (int)($input['revision'] ?? 0), $environment['hash'], $pending, $user);
            $form = $claimed['form'];
            try {
                $result = $service->bridge($workspace, $group, $org, $user, $environment, 'PUT', $path, $payload, $record['encounter_tx_id'], $current['body']['meta']['versionId'] ?? null);
            } catch (\InvalidArgumentException | \DomainException $e) {
                unset($form['pending_room']);
                $form['room_state'] = 'failed'; $form['room_error'] = $e->getMessage();
                return $model->finish($group, $org, $record['encounter_tx_id'], (int)$claimed['revision'], 'sent', null, ['message' => $e->getMessage()], $record['satusehat_id'], $form);
            } catch (\Throwable $e) {
                $form['room_state'] = 'unknown';
                return $model->finish($group, $org, $record['encounter_tx_id'], (int)$claimed['revision'], 'unknown', null, ['message' => 'Hasil masuk ruang belum pasti. Periksa status kunjungan.'], $record['satusehat_id'], $form);
            }
            $success = $result['status'] >= 200 && $result['status'] < 300
                && ($result['body']['resourceType'] ?? '') === 'Encounter' && ($result['body']['id'] ?? '') === $record['satusehat_id'] && $service->roomApplied($result['body'], $pending);
            $rejected = $result['status'] >= 400 && $result['status'] < 500;
            if ($success) {
                $form['location'] = $location; $form['room_entered_at'] = $pending['entered_at']; $form['room_state'] = 'success'; $form['episode_id'] = $pending['episode_id'];
                unset($form['pending_room'], $form['room_error']);
            } elseif ($rejected) {
                unset($form['pending_room']);
                $form['room_state'] = 'failed'; $form['room_error'] = 'Pembaruan masuk ruang ditolak SATUSEHAT. Periksa hasil pengiriman.';
            } else $form['room_state'] = 'unknown';
            return $model->finish($group, $org, $record['encounter_tx_id'], (int)$claimed['revision'], $success || $rejected ? 'sent' : 'unknown',
                $result['status'], $result['body'], $record['satusehat_id'], $form);
        }, true);
    }

    public function reconcile()
    {
        return $this->execute(static function ($model, $service, $workspace, $group, $org) {
            $input = request()->getJSON(true);
            $record = $model->record($group, $org, (string)($input['id'] ?? ''));
            if ($record['state'] === 'sent') return $record;
            if (!in_array($record['state'], ['sending', 'unknown', 'failed'], true)) throw new \InvalidArgumentException('Transaksi ini belum dikirim.');
            if ($record['state'] === 'sending' && time() - strtotime($record['updated_at'] . ' UTC') < 120) throw new \DomainException('Pengiriman masih berlangsung. Tunggu hingga proses selesai.');
            $environment = $service->environment($record['environment_id']);
            if ($record['target_hash'] !== $environment['hash']) throw new \DomainException('Tujuan environment berubah. Kembalikan konfigurasi sebelum pemeriksaan status.');
            $identifier = 'http://sys-ids.kemkes.go.id/encounter/' . $environment['org_ihs'] . '|' . $record['registration_no'];
            $result = $service->bridge($workspace, $group, $org, (string)session()->get('userid'), $environment, 'GET', 'Encounter?' . http_build_query(['identifier' => $identifier]), null, $record['encounter_tx_id']);
            if ($result['status'] !== 200) throw new \RuntimeException('Status belum dapat diperiksa. Silakan coba kembali.');
            $entries = $result['body']['entry'] ?? [];
            $matches = array_values(array_filter(array_column($entries, 'resource'), static function ($resource) use ($record, $environment) {
                if (($resource['resourceType'] ?? '') !== 'Encounter' || empty($resource['id'])) return false;
                if (($resource['subject']['reference'] ?? '') !== 'Patient/' . $record['form']['patient']['id']) return false;
                foreach (($resource['identifier'] ?? []) as $identifier) {
                    if (($identifier['value'] ?? '') === $record['registration_no'] && ($identifier['system'] ?? '') === 'http://sys-ids.kemkes.go.id/encounter/' . $environment['org_ihs']) return true;
                }
                return false;
            }));
            if (count($matches) !== 1 || (int)($result['body']['total'] ?? count($entries)) > 1) throw new \DomainException(count($matches) ? 'Ditemukan lebih dari satu Encounter. Periksa kunjungan di SATUSEHAT.' : 'Encounter belum ditemukan. Status tetap dipertahankan agar tidak terjadi pengiriman ganda.');
            $form = null;
            if (!empty($record['form']['pending_room'])) {
                $pending = $record['form']['pending_room'];
                if (!$service->roomApplied($matches[0], $pending)) throw new \DomainException('Pembaruan masuk ruang belum terkonfirmasi. Buka aksi masuk ruang untuk memeriksa atau mengirim pembaruan yang sama.');
                $form = $record['form'];
                $form['location'] = $pending['location']; $form['room_entered_at'] = $pending['entered_at']; $form['room_state'] = 'success'; $form['episode_id'] = $pending['episode_id'] ?? ($form['episode_id'] ?? null);
                unset($form['pending_room'], $form['room_error']);
            }
            return $model->finish($group, $org, $record['encounter_tx_id'], (int)$record['revision'], 'sent', 200, $matches[0], $matches[0]['id'], $form);
        }, true);
    }
}
