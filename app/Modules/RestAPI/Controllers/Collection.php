<?php
namespace Modules\RestAPI\Controllers;

use App\Controllers\BaseController;

class Collection extends BaseController
{
    use \Modules\RestAPI\Libraries\WorkspaceStorage;

    public function send()
    {
        if (!session()->get('loggedin')) return response()->setStatusCode(401)->setJSON(['error' => 'Sesi login berakhir.']);
        if (strtolower(request()->getMethod()) !== 'post') return response()->setStatusCode(405)->setJSON(['error' => 'Gunakan POST.']);
        try {
            service('security')->verify(request());
            $input = request()->getJSON(true);
            $url = $input['url'] ?? '';
            $parts = parse_url($url);
            $allowed = ['api-satusehat.kemkes.go.id', 'api-satusehat-stg.dto.kemkes.go.id'];
            if (!$parts || ($parts['scheme'] ?? '') !== 'https' || !in_array($parts['host'] ?? '', $allowed, true) ||
                isset($parts['user']) || isset($parts['pass']) || (isset($parts['port']) && $parts['port'] !== 443)) {
                throw new \InvalidArgumentException('Pengiriman server tersedia untuk endpoint HTTPS SATUSEHAT.');
            }
            $method = $input['method'] ?? 'GET';
            if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], true)) throw new \InvalidArgumentException('Method tidak valid.');
            $headers = [];
            foreach (($input['headers'] ?? []) as $name => $value) {
                if (!preg_match('/^[a-zA-Z0-9-]+$/D', $name) || !is_string($value) || preg_match('/[\r\n]/', $value)) throw new \InvalidArgumentException('Header tidak valid.');
                if (in_array(strtolower($name), ['host', 'cookie', 'content-length', 'connection', 'transfer-encoding'], true)) continue;
                $headers[] = $name . ': ' . $value;
            }
            $responseHeaders = [];
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_TIMEOUT => 90,
                CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HEADERFUNCTION => static function ($handle, $line) use (&$responseHeaders) {
                    $pair = explode(':', $line, 2);
                    if (count($pair) === 2) $responseHeaders[strtolower(trim($pair[0]))] = trim($pair[1]);
                    return strlen($line);
                },
            ]);
            if (!in_array($method, ['GET', 'HEAD'], true)) curl_setopt($curl, CURLOPT_POSTFIELDS, (string)($input['body'] ?? ''));
            if ($method === 'HEAD') curl_setopt($curl, CURLOPT_NOBODY, true);
            session()->close();
            $body = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);
            if ($body === false) throw new \RuntimeException('Koneksi server ke SATUSEHAT gagal. ' . $error);
            return response()->setHeader('Cache-Control', 'no-store')->setJSON(['status' => $status, 'headers' => $responseHeaders, 'body' => $body, 'csrfHash' => csrf_hash()]);
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) {
            return response()->setStatusCode(403)->setJSON(['error' => 'Token keamanan berakhir. Muat ulang halaman.']);
        } catch (\InvalidArgumentException $e) {
            return response()->setStatusCode(422)->setJSON(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return response()->setStatusCode(502)->setJSON(['error' => $e instanceof \RuntimeException ? $e->getMessage() : 'Pengiriman server gagal.']);
        }
    }

    public function index()
    {
        if (!session()->get('loggedin')) return redirect()->to(site_url('auth/login'));
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\RestAPI\Views\v_collection'),
        ]);
    }
}
