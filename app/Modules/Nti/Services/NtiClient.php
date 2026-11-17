<?php

namespace Modules\Nti\Services;

use RuntimeException;

/** Read-only NTI device client. Credentials and session cookies never reach the browser. */
class NtiClient
{
    private $baseUrl;
    private $username;
    private $password;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) env('nti.baseUrl', 'http://10.21.1.10'), '/');
        $this->username = (string) env('nti.username', 'root');
        $this->password = (string) env('nti.password', 'nti');
    }

    public function read(array $groups): array
    {
        $paths = ['internal' => 'appISens.json', 'external' => 'appESens.json', 'digital' => 'appDiginp.json'];
        $login = $this->request('/goform/login', null, ['username' => $this->username, 'password' => $this->password]);
        $cookie = $login['cookie'] ?? null;
        if (!is_string($cookie) || trim($cookie) === '' || preg_match('/[\r\n]/', $cookie)) {
            throw new RuntimeException('Login NTI ditolak atau cookie sesi tidak valid.');
        }
        $results = [];
        foreach ($groups as $group) {
            if (!isset($paths[$group])) {
                throw new RuntimeException('Kelompok sensor tidak dikenal.');
            }
            try {
                $results[$group] = ['ok' => true, 'data' => $this->request('/json/get/' . $paths[$group], $cookie), 'readAt' => date(DATE_ATOM)];
            } catch (RuntimeException $e) {
                $results[$group] = ['ok' => false, 'error' => $e->getMessage()];
            }
        }
        if (isset($results['external']) && $results['external']['ok']) {
            // Consult the device's own condition text; do not infer alarm polarity from voltage.
            $conditions = [];
            try {
                $html = $this->request('/monitoring_summary.asp', $cookie, null, false);
                if (class_exists('DOMDocument')) {
                    $doc = new \DOMDocument();
                    $previous = libxml_use_internal_errors(true);
                    $doc->loadHTML($html, LIBXML_NONET);
                    libxml_clear_errors();
                    libxml_use_internal_errors($previous);
                    $xpath = new \DOMXPath($doc);
                    foreach ($xpath->query('//div[@id="sensortable"]//tr[td]') as $row) {
                        $cells = $xpath->query('./td', $row);
                        if ($cells->length < 5) continue;
                        $link = $xpath->query('.//a[starts-with(@href,"sensor.asp?id=")]', $row)->item(0);
                        if ($link && preg_match('/[?&]id=(\d+)/', $link->getAttribute('href'), $match)) {
                            $conditions[(int) $match[1]] = trim($cells->item(4)->textContent);
                        }
                    }
                }
            } catch (RuntimeException $e) {
                // JSON readings remain available; unverified conditions are never promoted to Normal.
            }
            if (isset($results['external']['data']['data']['esens'])) {
                foreach ($results['external']['data']['data']['esens'] as &$sensor) {
                    $sensor['conditionText'] = $conditions[(int) ($sensor['idx'] ?? -1)] ?? null;
                }
                unset($sensor);
            }
        }
        return $results;
    }

    private function request(string $path, ?string $cookie = null, ?array $post = null, bool $json = true)
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('Ekstensi PHP cURL belum aktif.');
        }
        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL) || !in_array(parse_url($this->baseUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new RuntimeException('Konfigurasi URL perangkat NTI tidak valid.');
        }
        $ch = curl_init($this->baseUrl . $path);
        $headers = ['Accept: application/json'];
        if ($cookie !== null) $headers[] = 'Cookie: ' . $cookie;
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 6, CURLOPT_FOLLOWLOCATION => false, CURLOPT_HTTPHEADER => $headers]);
        if ($post !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false) throw new RuntimeException('Perangkat NTI tidak dapat dijangkau. Periksa jaringan dan alamat perangkat.');
        if ($status < 200 || $status >= 300) throw new RuntimeException('Perangkat NTI mengembalikan HTTP ' . $status . '.');
        if (!$json) return $body;
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) throw new RuntimeException('Respons NTI bukan objek atau array JSON yang valid.');
        if (isset($data['code']) && (int) $data['code'] !== 200) throw new RuntimeException('Perangkat NTI menolak permintaan pembacaan.');
        return $data;
    }
}
