<?php
namespace Modules\Satusehat\Libraries;

class BirthWorkspace
{
    private array $renewedTokens = [];

    public function scope(): string
    {
        if (!session()->get('loggedin') || !session()->get('orgid') || !session()->get('userid')) throw new \RuntimeException('Sesi organisasi tidak tersedia.');
        return hash('sha256', json_encode([session()->get('groupid'), session()->get('orgid')]));
    }

    public function environments(): array
    {
        $path = ROOTPATH . 'restapi-data/environment-' . $this->scope() . '.json';
        $data = is_file($path) ? json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) : [];
        return $data['environments'] ?? [];
    }

    public function actions(): array
    {
        $path = ROOTPATH . 'restapi-data/collection-' . $this->scope() . '.json';
        $collections = is_file($path) ? json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) : [];
        $actions = [];
        $walk = function ($items, $stage, $trail = '') use (&$walk, &$actions) {
            foreach ($items as $item) {
                if (isset($item['item'])) { $walk($item['item'], $stage, $trail . ' / ' . $item['name']); continue; }
                $request = $item['request'] ?? [];
                $outputs = [];
                foreach (($item['event'] ?? []) as $event) {
                    if (($event['listen'] ?? '') !== 'test') continue;
                    $script = implode("\n", $event['script']['exec'] ?? []);
                    preg_match_all('/pm\.(?:collectionVariables|environment)\.set\(["\']([^"\']+)["\'],\s*data\.id\)/', $script, $matches);
                    $outputs = array_merge($outputs, $matches[1]);
                }
                $request['__output'] = $outputs;
                $id = substr(hash('sha256', $stage . $trail . $item['name']), 0, 20);
                $actions[$id] = ['id' => $id, 'name' => $item['name'], 'stage' => $stage, 'group' => trim($trail, ' /'), 'request' => $request];
            }
        };
        foreach ($collections as $collection) {
            if (($collection['info']['name'] ?? '') !== '49. Use Case - Data Kelahiran') continue;
            foreach ($collection['item'] as $stage) {
                if (preg_match('/^0[2-7]\./', $stage['name'])) $walk($stage['item'], $stage['name']);
            }
        }
        return $actions;
    }

    public function schema(array $action): array
    {
        $request = $action['request'];
        $url = is_array($request['url'] ?? null) ? ($request['url']['raw'] ?? '') : ($request['url'] ?? '');
        $variables = [];
        preg_match_all('/{{([^{}]+)}}/', $url . ($request['body']['raw'] ?? ''), $matches);
        foreach (array_unique($matches[1]) as $variable) {
            if (!in_array($variable, ['base_url', 'Org_id', 'token'], true)) $variables[] = $variable;
        }
        $raw = $request['body']['raw'] ?? '';
        $body = $raw !== '' ? json_decode($raw, true) : null;
        $error = $raw !== '' && !is_array($body) ? 'Contoh payload pada collection belum berupa JSON valid. Pengiriman tahap ini menunggu koreksi template.' : '';
        $fields = [];
        $walk = function ($node, $path = '') use (&$walk, &$fields) {
            foreach ($node as $key => $value) {
                $pointer = $path . '/' . $key;
                if (is_array($value)) { $walk($value, $pointer); continue; }
                if (is_string($value) && strpos($value, '{{') !== false) continue;
                if (in_array((string)$key, ['resourceType', 'system', 'use', 'status', 'intent', 'priority', 'unit', 'contentType', 'profile'], true)) continue;
                // Resource definitions and relationship codes come from the supplied collection.
                if (preg_match('~/(category|code|relationship|communication|serviceType|class|meta|type|statusHistory|extension)/~', $pointer)) continue;
                if (in_array((string)$key, ['code', 'display', 'text'], true) && preg_match('~/(interpretation|valueCodeableConcept)/~', $pointer) === 0 && strpos($pointer, '/name/') === false && strpos($pointer, '/telecom/') === false) {
                    if ($key !== 'display' && $key !== 'text') continue;
                }
                $type = is_bool($value) ? 'boolean' : (is_numeric($value) && !is_string($value) ? 'number' : 'text');
                if (preg_match('/(DateTime|issued|authoredOn|lastModified|\/start|\/end|\/date)$/', $pointer)) $type = 'datetime';
                if (preg_match('/\/birthDate$/', $pointer)) $type = 'date';
                if (preg_match('/\/valueTime$/', $pointer)) $type = 'time';
                if (preg_match('~/attachment/data$~', $pointer)) $type = 'file';
                $fields[] = ['path' => $pointer, 'type' => $type, 'label' => $this->label($pointer), 'required' => preg_match('~^/(identifier/[^/]+/value|period/start|valueQuantity/value|valueInteger|valueTime|valueDateTime|valueCodeableConcept/coding/[^/]+/(code|display)|content/[^/]+/attachment/data)$~', $pointer) === 1];
            }
        };
        if ($body) $walk($body);
        if (($body['resourceType'] ?? '') === 'Encounter') {
            $fields = array_values(array_filter($fields, static fn($field) =>
                !preg_match('~^/identifier/[0-9]+/value$|^/location/[0-9]+/location/display$~', $field['path'])));
            $fields[] = ['path' => '/class/code', 'type' => 'encounter-class', 'label' => 'Jenis transaksi', 'required' => true];
        }
        return ['variables' => $variables, 'fields' => $fields, 'error' => $error, 'body' => $body];
    }

    private function label(string $path): string
    {
        $labels = [
            'identifier' => 'Identitas', 'value' => 'Nomor / nilai', 'name' => 'Nama', 'text' => 'Nama / keterangan',
            'subject' => 'Pasien', 'display' => 'Nama', 'period' => 'Waktu kunjungan', 'start' => 'Mulai', 'end' => 'Selesai',
            'effectiveDateTime' => 'Waktu pemeriksaan', 'issued' => 'Waktu pencatatan', 'valueQuantity' => 'Hasil pengukuran',
            'valueInteger' => 'Nilai bilangan', 'valueTime' => 'Jam lahir', 'valueDateTime' => 'Tanggal dan waktu hasil',
            'valueCodeableConcept' => 'Hasil berkode', 'interpretation' => 'Interpretasi hasil', 'coding' => 'Terminologi',
            'code' => 'Kode', 'gender' => 'Jenis kelamin (male/female/other/unknown)', 'birthDate' => 'Tanggal lahir',
            'telecom' => 'Kontak', 'address' => 'Alamat', 'line' => 'Jalan', 'city' => 'Kota', 'postalCode' => 'Kode pos',
            'country' => 'Negara', 'date' => 'Tanggal dokumen', 'content' => 'Dokumen', 'attachment' => 'Lampiran',
            'data' => 'Berkas PDF/JPEG/PNG', 'title' => 'Judul dokumen', 'authoredOn' => 'Waktu pengajuan',
            'lastModified' => 'Waktu pembaruan', 'active' => 'Aktif', 'reference' => 'Referensi SATUSEHAT',
        ];
        $parts = array_filter(explode('/', $path), static fn($part) => $part !== '' && !ctype_digit($part));
        return implode(' · ', array_map(static fn($part) => $labels[$part] ?? $part, $parts));
    }

    public function load(string $id): ?array
    {
        $path = $this->path($id);
        return is_file($path) ? json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) : null;
    }

    private function path(string $id): string
    {
        if (!preg_match('/^[a-f0-9]{32}$/D', $id)) throw new \InvalidArgumentException('Nomor transaksi tidak valid.');
        $directory = ROOTPATH . 'restapi-data/birth-' . $this->scope();
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) throw new \RuntimeException('Penyimpanan transaksi belum tersedia.');
        return $directory . '/' . $id . '.json';
    }

    public function listings(): array
    {
        $directory = dirname($this->path(str_repeat('0', 32)));
        $rows = [];
        foreach (glob($directory . '/*.json') ?: [] as $path) {
            $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            $rows[] = ['id' => $data['id'], 'number' => $data['number'], 'mother' => $data['mother'], 'baby' => $data['baby'], 'updated' => $data['updated'], 'sent' => count(array_filter($data['results'], static fn($result) => $result['state'] === 'success'))];
        }
        usort($rows, static fn($a, $b) => strcmp($b['updated'], $a['updated']));
        return $rows;
    }

    public function update(string $id, callable $operation): array
    {
        $path = $this->path($id);
        $lock = fopen($path . '.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX)) throw new \RuntimeException('Transaksi sedang digunakan.');
        try {
            $current = is_file($path) ? json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) : null;
            $next = $operation($current);
            $next['id'] = $id;
            $next['updated'] = date(DATE_ATOM);
            $next['revision'] = bin2hex(random_bytes(16));
            $encoded = json_encode($next, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $temporary = tempnam(dirname($path), '.draft-');
            try {
                if (file_put_contents($temporary, $encoded) !== strlen($encoded) || !rename($temporary, $path)) throw new \RuntimeException('Transaksi belum tersimpan.');
                chmod($path, 0600);
            } finally { if (is_file($temporary)) unlink($temporary); }
            return $next;
        } finally { flock($lock, LOCK_UN); fclose($lock); }
    }

    public function api(string $environment, string $method, string $path, ?array $body = null, ?string $expectedTargetHash = null, ?string $ifMatch = null): array
    {
        $env = null;
        foreach ($this->environments() as $candidate) if ($candidate['id'] === $environment) $env = $candidate;
        if (!$env) throw new \InvalidArgumentException('Pilih environment.');
        $values = [];
        foreach ($env['values'] as $value) if (($value['enabled'] ?? true) !== false) $values[$value['key']] = $value['value'];
        $base = rtrim($values['base_url'] ?? '', '/');
        if (!preg_match('~^https://(?:api-satusehat\.kemkes\.go\.id|api-satusehat-stg\.dto\.kemkes\.go\.id)/fhir-r4/v1$~D', $base)) throw new \InvalidArgumentException('Base URL FHIR tidak valid.');
        if ($expectedTargetHash !== null && !hash_equals($expectedTargetHash, hash('sha256', $base . '|' . ($values['Org_id'] ?? '')))) throw new \DomainException('Konfigurasi environment berubah. Buka ulang transaksi.');
        $masterdata = preg_match('~^masterdata/v2/(?:provinces|cities|districts|sub-districts)\?[^#]*$~D', $path) === 1;
        if (strpos($path, 'masterdata/') === 0 && (!$masterdata || $method !== 'GET' || $body !== null)) throw new \InvalidArgumentException('Endpoint master wilayah tidak valid.');
        $requestBase = $masterdata ? preg_replace('~/fhir-r4/v1$~', '', $base) : $base;
        $mediaType = $method === 'POST' && in_array($path, ['Patient', 'Organization', 'Location'], true) ? 'application/json' : 'application/fhir+json';
        $headers = ['Accept: ' . $mediaType];
        if ($body !== null) $headers[] = 'Content-Type: ' . $mediaType;
        if ($ifMatch !== null) {
            if (!preg_match('/^[A-Za-z0-9.-]+$/D', $ifMatch)) throw new \InvalidArgumentException('Versi resource tidak valid.');
            $headers[] = 'If-Match: W/"' . $ifMatch . '"';
        }
        $url = $requestBase . '/' . ltrim($path, '/');
        $payload = $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $tokenKey = hash('sha256', serialize([$this->scope(), $environment, $values]));
        $token = $this->renewedTokens[$tokenKey] ?? trim($values['token'] ?? '');
        $canRenew = ($method === 'GET' || ($method === 'POST' && in_array($path, ['Patient', 'Organization', 'Location', 'Encounter'], true)))
            && !empty($values['client_id']) && !empty($values['client_secret']);
        if ($token === '' && $canRenew) {
            $token = $this->renewedTokens[$tokenKey] = $this->renewToken($base, $values);
        }
        if ($token === '') throw new \InvalidArgumentException('Token environment belum tersedia. Lengkapi kredensial API di Environment atau buat token melalui Collections.');

        $result = $this->send($url, $method, array_merge($headers, ['Authorization: Bearer ' . $token]), $payload);
        if ($result['status'] === 401 && $canRenew && !isset($this->renewedTokens[$tokenKey])) {
            $token = $this->renewedTokens[$tokenKey] = $this->renewToken($base, $values);
            $result = $this->send($url, $method, array_merge($headers, ['Authorization: Bearer ' . $token]), $payload);
        }
        return $result;
    }

    private function renewToken(string $base, array $values): string
    {
        // Derive the OAuth host from the validated FHIR base to keep credentials in the selected environment.
        $url = substr($base, 0, -strlen('/fhir-r4/v1')) . '/oauth2/v1/accesstoken?grant_type=client_credentials';
        $payload = http_build_query(['client_id' => $values['client_id'], 'client_secret' => $values['client_secret']]);
        try {
            $result = $this->send($url, 'POST', ['Content-Type: application/x-www-form-urlencoded'], $payload, 20);
        } catch (\RuntimeException $e) {
            throw new \DomainException('Server autentikasi SATUSEHAT belum dapat dihubungi. Coba kembali.', 0, $e);
        }
        $token = $result['body']['access_token'] ?? '';
        if ($result['status'] !== 200 || !is_string($token) || !preg_match('#^[A-Za-z0-9._~+/-]+=*$#D', $token)) {
            throw new \DomainException('Token SATUSEHAT gagal diperbarui (HTTP ' . $result['status'] . '). Periksa Client ID dan Client Secret pada environment yang dipilih.');
        }
        return $token;
    }

    private function send(string $url, string $method, array $headers, ?string $payload, int $timeout = 90): array
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers, CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_TIMEOUT => $timeout, CURLOPT_FOLLOWLOCATION => false, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2]);
        if ($payload !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        $raw = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_HTTP_CODE); $error = curl_error($curl); curl_close($curl);
        if ($raw === false) throw new \RuntimeException('Koneksi belum mendapat kepastian hasil. ' . $error);
        return ['status' => $status, 'body' => json_decode($raw, true) ?? ['text' => $raw]];
    }

    public function payload(array $action, array $draft, array $transaction): ?array
    {
        $schema = $this->schema($action);
        if ($schema['error']) throw new \InvalidArgumentException($schema['error']);
        $body = $schema['body'];
        $context = $transaction['context'] ?? [];
        $context['Patient_Name_Ibu'] = $transaction['mother'];
        $context['Patient_Name_Bayi'] = $transaction['baby'];
        $env = null;
        foreach ($this->environments() as $candidate) if ($candidate['id'] === $transaction['environment']) $env = $candidate;
        foreach (($env['values'] ?? []) as $value) {
            if ($value['key'] === 'Org_id' && ($value['enabled'] ?? true) !== false) $context['Org_id'] = $value['value'];
            if ($value['key'] === 'Loc_id_INC' && ($value['enabled'] ?? true) !== false && empty($context['Loc_id_INC'])) $context['Loc_id_INC'] = $value['value'];
        }
        foreach ($schema['variables'] as $variable) {
            if (!empty($context[$variable])) continue;
            if ($variable === 'Loc_id_INC') throw new \InvalidArgumentException('Lokasi INC belum dikonfigurasi pada environment organisasi.');
            throw new \InvalidArgumentException('Lengkapi ' . $variable . ' pada identitas transaksi.');
        }
        if (empty($context['Org_id'])) throw new \InvalidArgumentException('Org_id environment belum tersedia.');
        foreach ($schema['fields'] as $field) {
            $value = $draft[$field['path']] ?? null;
            if ($value === null || $value === '') {
                if ($field['required']) throw new \InvalidArgumentException('Lengkapi ' . $field['label']);
                $parts = explode('/', ltrim($field['path'], '/'));
                $last = array_pop($parts);
                $node = &$body;
                foreach ($parts as $part) $node = &$node[$part];
                unset($node[$last]); unset($node);
                continue;
            }
            if ($field['type'] === 'encounter-class') {
                $classes = ['EMER' => 'emergency', 'AMB' => 'ambulatory', 'IMP' => 'inpatient encounter'];
                if (!isset($classes[$value])) throw new \InvalidArgumentException('Pilih jenis transaksi.');
                $body['class'] = ['system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode', 'code' => $value, 'display' => $classes[$value]];
                continue;
            }
            if ($field['type'] === 'number') {
                if (!is_numeric($value)) throw new \InvalidArgumentException('Nilai angka tidak valid.');
                $value = 0 + $value;
            } elseif ($field['type'] === 'boolean') $value = $value === true || $value === 'true';
            elseif ($field['type'] === 'datetime') {
                try { $value = (new \DateTimeImmutable($value))->format(DATE_ATOM); } catch (\Throwable $e) { throw new \InvalidArgumentException('Tanggal tidak valid.'); }
            }
            $parts = explode('/', ltrim($field['path'], '/'));
            $node = &$body;
            foreach ($parts as $part) $node = &$node[$part];
            $node = $value; unset($node);
        }
        $replace = function ($node) use (&$replace, $context) {
            if (is_array($node)) return array_map($replace, $node);
            if (!is_string($node)) return $node;
            return preg_replace_callback('/{{([^{}]+)}}/', static function ($match) use ($context) {
                if (!isset($context[$match[1]]) || $context[$match[1]] === '') throw new \InvalidArgumentException('Referensi belum tersedia: ' . $match[1]);
                return $context[$match[1]];
            }, $node);
        };
        $body = $replace($body);
        if (isset($body['telecom'])) $body['telecom'] = array_values(array_filter($body['telecom'], static fn($entry) => isset($entry['value'])));
        if (isset($body['name'])) $body['name'] = array_values(array_filter($body['name'], static fn($entry) => isset($entry['text'])));
        if (isset($body['address'])) {
            foreach ($body['address'] as &$address) if (isset($address['line'])) $address['line'] = array_values($address['line']);
            unset($address);
            $body['address'] = array_values(array_filter($body['address'], static fn($entry) => count(array_diff(array_keys($entry), ['use', 'type'])) > 0));
        }
        foreach (['telecom', 'name', 'address'] as $key) if (isset($body[$key]) && !$body[$key]) unset($body[$key]);
        if (isset($body['interpretation'])) {
            $body['interpretation'] = array_values(array_filter($body['interpretation'], static fn($entry) => !empty($entry['coding'][0]['code'])));
            if (!$body['interpretation']) unset($body['interpretation']);
        }
        if (isset($body['content'][0]['attachment']['data'])) {
            $bytes = base64_decode($body['content'][0]['attachment']['data'], true);
            if ($bytes === false || strlen($bytes) > 4 * 1024 * 1024) throw new \InvalidArgumentException('Berkas tidak valid atau lebih dari 4 MB.');
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
            if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true)) throw new \InvalidArgumentException('Gunakan berkas PDF, JPEG, atau PNG.');
            $body['content'][0]['attachment']['contentType'] = $mime;
        }
        if (($body['resourceType'] ?? '') === 'Encounter') {
            // Stable across create/update steps, distinct for mother and baby in the same transaction.
            $body['identifier'][0]['value'] = $transaction['number'] . '-' . substr(hash('sha256', $body['subject']['reference']), 0, 8);
            foreach (($body['location'] ?? []) as $index => $location) unset($body['location'][$index]['location']['display']);
        }
        if (isset($body['period']['start'])) {
            foreach (($body['statusHistory'] ?? []) as $index => $history) {
                $body['statusHistory'][$index]['period']['start'] = $body['period']['start'];
            }
        }
        // Patient display values in collection examples must be replaced with this transaction's names.
        if (isset($body['subject']['display'])) $body['subject']['display'] = strpos($body['subject']['reference'] ?? '', $context['Patient_Id_Ibu'] ?? '___') !== false ? $transaction['mother'] : $transaction['baby'];
        return $body;
    }
}
