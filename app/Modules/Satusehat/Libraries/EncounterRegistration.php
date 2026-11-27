<?php
namespace Modules\Satusehat\Libraries;

class EncounterRegistration
{
    public const CLASSES = ['EMER' => 'emergency', 'AMB' => 'ambulatory', 'IMP' => 'inpatient encounter'];

    public function environment(string $id): array
    {
        foreach ((new BirthWorkspace())->environments() as $environment) {
            if ($environment['id'] !== $id) continue;
            $values = [];
            foreach ($environment['values'] as $variable) if (($variable['enabled'] ?? true) !== false) $values[$variable['key']] = $variable['value'];
            $base = rtrim($values['base_url'] ?? '', '/');
            if (!preg_match('~^https://(api-satusehat\.kemkes\.go\.id|api-satusehat-stg\.dto\.kemkes\.go\.id)/fhir-r4/v1$~D', $base)) throw new \InvalidArgumentException('Base URL FHIR environment tidak valid.');
            if (!preg_match('/^[A-Za-z0-9.-]{1,64}$/D', $values['Org_id'] ?? '')) throw new \InvalidArgumentException('Org_id belum tersedia pada environment.');
            return ['id' => $id, 'name' => $environment['name'], 'org_ihs' => $values['Org_id'], 'base_url' => $base,
                'sandbox' => strpos($base, 'api-satusehat-stg.') !== false, 'hash' => hash('sha256', $base . '|' . $values['Org_id'])];
        }
        throw new \InvalidArgumentException('Pilih environment yang tersedia.');
    }

    public function bridge(BirthWorkspace $workspace, string $group, string $org, string $user, array $environment, string $method, string $path, ?array $body = null, ?string $transaction = null, ?string $ifMatch = null): array
    {
        $logs = new \Modules\Satusehat\Models\BridgingLogModel();
        $base = strpos($path, 'masterdata/v2/') === 0 ? preg_replace('~/fhir-r4/v1$~', '', $environment['base_url']) : $environment['base_url'];
        $id = $logs->begin($group, $org, $user, $environment['id'], $method, $base . '/' . ltrim($path, '/'), $body, $transaction);
        $started = microtime(true);
        try {
            $result = $workspace->api($environment['id'], $method, $path, $body, $environment['hash'], $ifMatch);
        } catch (\Throwable $e) {
            $logs->complete($id, $group, $org, null, (int)round((microtime(true) - $started) * 1000), $e->getMessage());
            throw $e;
        }
        $logs->complete($id, $group, $org, $result, (int)round((microtime(true) - $started) * 1000), null);
        return $result;
    }

    public function resources(BirthWorkspace $workspace, string $group, string $org, string $user, array $environment, string $resource, array $filters, ?string $transaction = null): array
    {
        if (!in_array($resource, ['Location', 'EpisodeOfCare', 'Organization'], true)) throw new \InvalidArgumentException('Resource pencarian tidak valid.');
        $path = $resource . '?' . http_build_query($filters);
        $resources = []; $seen = [];
        do {
            if (isset($seen[$path])) throw new \RuntimeException('Pagination SATUSEHAT berulang. Silakan coba kembali.');
            $seen[$path] = true;
            $result = $this->bridge($workspace, $group, $org, $user, $environment, 'GET', $path, null, $transaction);
            if ($result['status'] < 200 || $result['status'] >= 300) throw new \RuntimeException('Data ' . $resource . ' belum dapat dimuat (HTTP ' . $result['status'] . ').');
            if (($result['body']['resourceType'] ?? '') !== 'Bundle') throw new \RuntimeException('Respons pencarian ' . $resource . ' tidak valid.');
            foreach (($result['body']['entry'] ?? []) as $entry) {
                if (($entry['resource']['resourceType'] ?? '') === $resource) $resources[] = $entry['resource'];
            }
            $next = null;
            foreach (($result['body']['link'] ?? []) as $link) if (($link['relation'] ?? '') === 'next') $next = $link['url'] ?? null;
            $path = null;
            if ($next) {
                $prefix = $environment['base_url'] . '/' . $resource . '?';
                if (strpos($next, $prefix) !== 0) throw new \RuntimeException('Tautan pagination SATUSEHAT tidak valid.');
                $parts = parse_url($next);
                parse_str($parts['query'] ?? '', $query);
                $path = $resource . '?' . http_build_query(array_merge($query, $filters));
            }
        } while ($path);
        return $resources;
    }

    public function validateEpisode(array $episode, string $patient, array $environment): void
    {
        if (($episode['resourceType'] ?? '') !== 'EpisodeOfCare' || empty($episode['id']) || ($episode['status'] ?? '') !== 'active') throw new \InvalidArgumentException('Episode ANC yang dipilih sudah tidak aktif.');
        if (!in_array($episode['patient']['reference'] ?? '', ['Patient/' . $patient, $environment['base_url'] . '/Patient/' . $patient], true)) throw new \InvalidArgumentException('Episode ANC bukan milik pasien ini.');
        foreach (($episode['type'] ?? []) as $type) {
            foreach (($type['coding'] ?? []) as $coding) if (($coding['code'] ?? '') === 'ANC') return;
        }
        throw new \InvalidArgumentException('Pilih episode dengan jenis ANC.');
    }

    private function person($value, string $label, bool $required): ?array
    {
        if ($value !== null && !is_array($value)) throw new \InvalidArgumentException('Data ' . $label . ' tidak valid.');
        if (!$value || empty($value['id'])) {
            if ($required) throw new \InvalidArgumentException('Pilih ' . $label . '.');
            return null;
        }
        if (!is_array($value) || !is_string($value['id']) || !preg_match('/^[A-Za-z0-9.-]{1,64}$/D', $value['id'])) throw new \InvalidArgumentException('ID ' . $label . ' tidak valid.');
        $name = trim((string)($value['name'] ?? ''));
        if ($name === '' || strlen($name) > 600) throw new \InvalidArgumentException('Nama ' . $label . ' belum valid.');
        return ['id' => $value['id'], 'name' => $name, 'nik' => (string)($value['nik'] ?? ''),
            'gender' => $value['gender'] ?? null, 'birthDate' => $value['birthDate'] ?? null];
    }

    public function normalize(array $input): array
    {
        $number = trim((string)($input['registration_no'] ?? ''));
        if (strlen($number) > 100) throw new \InvalidArgumentException('Isi nomor registrasi maksimal 100 karakter.');
        $class = $input['visit_class'] ?? '';
        if (!isset(self::CLASSES[$class])) throw new \InvalidArgumentException('Pilih jenis kunjungan.');
        $time = $input['arrived_at'] ?? '';
        if (!is_string($time) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{3})?(?:Z|[+-]\d{2}:\d{2})$/D', $time)) throw new \InvalidArgumentException('Isi waktu kedatangan dengan zona waktu.');
        try { $date = new \DateTimeImmutable($time); } catch (\Throwable $e) { throw new \InvalidArgumentException('Waktu kedatangan tidak valid.'); }
        $errors = \DateTimeImmutable::getLastErrors();
        if ($errors && ($errors['warning_count'] || $errors['error_count'])) throw new \InvalidArgumentException('Tanggal kedatangan tidak valid.');
        $environment = $this->environment((string)($input['environment_id'] ?? ''));
        $location = $input['location'] ?? null;
        if ($location && (!is_array($location) || !preg_match('/^[A-Za-z0-9.-]{1,64}$/D', $location['id'] ?? ''))) throw new \InvalidArgumentException('Pilih lokasi yang valid.');
        return ['registration_no' => $number, 'environment_id' => $environment['id'], 'visit_class' => $class,
            'arrived_at' => $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:sP'),
            'patient' => $this->person($input['patient'] ?? null, 'pasien', true),
            'practitioner' => $this->person($input['practitioner'] ?? null, 'Practitioner', false),
            'location' => $location ? ['id' => $location['id'], 'name' => (string)($location['name'] ?? '')] : null];
    }

    public function roomApplied(array $resource, array $pending): bool
    {
        if (($resource['status'] ?? '') !== 'in-progress') return false;
        if (!empty($pending['episode_id'])) {
            $found = false;
            foreach (($resource['episodeOfCare'] ?? []) as $reference) {
                if (($reference['reference'] ?? '') === 'EpisodeOfCare/' . $pending['episode_id']) $found = true;
            }
            if (!$found) return false;
        }
        foreach (($resource['location'] ?? []) as $entry) {
            if (($entry['location']['reference'] ?? '') === 'Location/' . $pending['location']['id']
                && ($entry['status'] ?? 'active') === 'active'
                && empty($entry['period']['end']) && !empty($entry['period']['start'])
                && (new \DateTimeImmutable($entry['period']['start'])) == new \DateTimeImmutable($pending['entered_at'])) return true;
        }
        return false;
    }

    public function roomPayload(array $resource, array $record, array $location, string $enteredAt, ?string $episodeId = null): array
    {
        if (($resource['resourceType'] ?? '') !== 'Encounter' || ($resource['id'] ?? '') !== $record['satusehat_id']
            || ($resource['subject']['reference'] ?? '') !== 'Patient/' . $record['form']['patient']['id']) throw new \InvalidArgumentException('Resource kunjungan tidak sesuai pasien.');
        if (!in_array($resource['status'] ?? '', ['arrived', 'in-progress'], true)) throw new \InvalidArgumentException('Kunjungan bukan dalam status kedatangan atau pelayanan.');
        if (!empty($resource['period']['end'])) throw new \InvalidArgumentException('Kunjungan sudah memiliki waktu selesai.');
        if ($episodeId !== null) {
            if (!preg_match('/^[A-Za-z0-9.-]{1,64}$/D', $episodeId)) throw new \InvalidArgumentException('ID episode ANC tidak valid.');
            $reference = 'EpisodeOfCare/' . $episodeId;
            $exists = false;
            foreach (($resource['episodeOfCare'] ?? []) as $episode) if (($episode['reference'] ?? '') === $reference) $exists = true;
            if (!$exists) $resource['episodeOfCare'][] = ['reference' => $reference];
        }
        if (!preg_match('/^[A-Za-z0-9.-]{1,64}$/D', $location['id'] ?? '') || empty($location['name'])) throw new \InvalidArgumentException('Pilih ruang pelayanan.');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{3})?(?:Z|[+-]\d{2}:\d{2})$/D', $enteredAt)) throw new \InvalidArgumentException('Isi waktu masuk ruang dengan zona waktu.');
        $date = new \DateTimeImmutable($enteredAt);
        $errors = \DateTimeImmutable::getLastErrors();
        if ($errors && ($errors['warning_count'] || $errors['error_count'])) throw new \InvalidArgumentException('Waktu masuk ruang tidak valid.');
        if ($date < new \DateTimeImmutable($resource['period']['start'] ?? $record['form']['arrived_at'])) throw new \InvalidArgumentException('Waktu masuk ruang mendahului waktu kedatangan.');
        $time = $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:sP');
        $hasInProgress = false;
        foreach (($resource['statusHistory'] ?? []) as $index => $history) {
            if (empty($history['period']['end'])) {
                if (!empty($history['period']['start']) && $date < new \DateTimeImmutable($history['period']['start'])) throw new \InvalidArgumentException('Waktu masuk ruang mendahului riwayat kunjungan terakhir.');
                if (($history['status'] ?? '') === 'in-progress') $hasInProgress = true;
                else $resource['statusHistory'][$index]['period']['end'] = $time;
            }
        }
        if (!$hasInProgress) $resource['statusHistory'][] = ['status' => 'in-progress', 'period' => ['start' => $time]];
        if ($this->roomApplied($resource, ['location' => $location, 'entered_at' => $time])) return $resource;
        foreach (($resource['location'] ?? []) as $index => $entry) {
            if (!empty($entry['period']['end'])) continue;
            if (!empty($entry['period']['start']) && $date < new \DateTimeImmutable($entry['period']['start'])) throw new \InvalidArgumentException('Waktu masuk mendahului riwayat ruang terakhir.');
            $resource['location'][$index]['status'] = 'completed';
            $resource['location'][$index]['period']['end'] = $time;
        }
        $resource['location'][] = ['location' => ['reference' => 'Location/' . $location['id'], 'display' => $location['name']],
            'status' => 'active', 'period' => ['start' => $time]];
        $resource['status'] = 'in-progress';
        return $resource;
    }

    public function payload(array $form, array $environment): array
    {
        if (empty($form['location'])) throw new \InvalidArgumentException('Pilih poli atau lokasi pelayanan sebelum mengirim.');
        $body = [
            'resourceType' => 'Encounter',
            'identifier' => [['system' => 'http://sys-ids.kemkes.go.id/encounter/' . $environment['org_ihs'], 'value' => $form['registration_no']]],
            'status' => 'arrived',
            'statusHistory' => [['status' => 'arrived', 'period' => ['start' => $form['arrived_at']]]],
            'class' => ['system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode', 'code' => $form['visit_class'], 'display' => self::CLASSES[$form['visit_class']]],
            'subject' => ['reference' => 'Patient/' . $form['patient']['id'], 'display' => $form['patient']['name']],
            'period' => ['start' => $form['arrived_at']],
            'serviceProvider' => ['reference' => 'Organization/' . $environment['org_ihs']],
            'location' => [['location' => ['reference' => 'Location/' . $form['location']['id'], 'display' => $form['location']['name']]]],
        ];
        if ($form['practitioner']) {
            $body['participant'] = [['type' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType', 'code' => 'ATND', 'display' => 'attender']]]],
                'individual' => ['reference' => 'Practitioner/' . $form['practitioner']['id'], 'display' => $form['practitioner']['name']]]];
        }
        return $body;
    }
}
