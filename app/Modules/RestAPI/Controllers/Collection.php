<?php
namespace Modules\RestAPI\Controllers;

use App\Controllers\BaseController;
use App\Libraries\UserActivity;

class Collection extends BaseController
{
    use \Modules\RestAPI\Libraries\WorkspaceStorage;

    public function send()
    {
        if (!session()->get('loggedin')) return response()->setStatusCode(401)->setJSON(['error' => 'Sesi login berakhir.']);
        if (strtolower(request()->getMethod()) !== 'post') return response()->setStatusCode(405)->setJSON(['error' => 'Gunakan POST.']);
        $started = microtime(true);
        $activity = ['Aksi' => 'SEND REST API', 'Hasil' => 'Pengiriman gagal'];
        try {
            service('security')->verify(request());
            $input = request()->getJSON(true);
            $url = $input['url'] ?? '';
            if (!is_string($url)) throw new \InvalidArgumentException('URL endpoint tidak valid.');
            $parts = parse_url($url);
            if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) ||
                empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
                throw new \InvalidArgumentException('Gunakan URL endpoint HTTP atau HTTPS yang valid.');
            }
            $method = $input['method'] ?? 'GET';
            if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], true)) throw new \InvalidArgumentException('Method tidak valid.');
            $activity['Metode API'] = $method;
            $activity['Host tujuan'] = mb_substr($parts['host'], 0, 255);
            $headers = [];
            foreach (($input['headers'] ?? []) as $name => $value) {
                if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $name) || !is_string($value) || preg_match('/[\r\n]/', $value)) throw new \InvalidArgumentException('Header tidak valid.');
                if (in_array(strtolower($name), ['host', 'cookie', 'content-length', 'connection', 'transfer-encoding'], true)) continue;
                $headers[] = $name . ': ' . $value;
            }
            $responseHeaders = [];
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
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
            if ($body === false) throw new \RuntimeException('Koneksi server ke endpoint API gagal. ' . $error);
            $activity['Status endpoint'] = (int) $status;
            $activity['Hasil'] = $status >= 400 ? 'Endpoint mengembalikan error' : 'Respons endpoint diterima';
            return response()->setHeader('Cache-Control', 'no-store')->setJSON(['status' => $status, 'headers' => $responseHeaders, 'body' => $body, 'csrfHash' => csrf_hash()]);
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) {
            $activity['Hasil'] = 'Token keamanan berakhir';
            return response()->setStatusCode(403)->setJSON(['error' => 'Token keamanan berakhir. Muat ulang halaman.']);
        } catch (\InvalidArgumentException $e) {
            $activity['Hasil'] = 'Validasi pengiriman gagal';
            return response()->setStatusCode(422)->setJSON(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return response()->setStatusCode(502)->setJSON(['error' => $e instanceof \RuntimeException ? $e->getMessage() : 'Pengiriman server gagal.']);
        } finally {
            $activity['Durasi (ms)'] = (int) round((microtime(true) - $started) * 1000);
            // The global activity filter persists this once, with the authenticated user's scope.
            UserActivity::setDetails($activity);
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
