<?php
namespace Modules\RestAPI\Libraries;

trait WorkspaceStorage
{
    public function storage()
    {
        if (!session()->get('loggedin') || !session()->get('userid') || !session()->get('orgid')) {
            return response()->setStatusCode(401)->setJSON(['error' => 'Sesi login berakhir.']);
        }
        $kind = strtolower((new \ReflectionClass($this))->getShortName());
        $scope = hash('sha256', json_encode([session()->get('groupid'), session()->get('orgid')]));
        $legacyScope = hash('sha256', json_encode([session()->get('groupid'), session()->get('orgid'), session()->get('userid')]));
        $directory = ROOTPATH . 'restapi-data';
        $lock = null;
        try {
            if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) throw new \RuntimeException();
            $path = $directory . '/' . $kind . '-' . $scope . '.json';
            $lock = fopen($path . '.lock', 'c');
            if (!$lock || !flock($lock, LOCK_EX)) throw new \RuntimeException();
            $legacyPath = $directory . '/' . $kind . '-' . $legacyScope . '.json';
            if (!is_file($path) && is_file($legacyPath)) {
                $legacy = file_get_contents($legacyPath);
                json_decode($legacy, true, 512, JSON_THROW_ON_ERROR);
                $temporary = tempnam($directory, '.migrate-');
                try {
                    if (file_put_contents($temporary, $legacy) !== strlen($legacy) || !rename($temporary, $path)) throw new \RuntimeException();
                    chmod($path, 0600);
                } finally {
                    if (is_file($temporary)) unlink($temporary);
                }
            }
            $raw = is_file($path) ? file_get_contents($path) : null;
            $revision = hash('sha256', $raw ?? '');
            $data = $raw === null ? null : json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            if (strtolower(request()->getMethod()) === 'post') {
                $compressed = request()->getHeaderLine('X-Workspace-Encoding') === 'gzip';
                $requestBody = $compressed ? request()->getBody() : null;
                service('security')->verify(request());
                if ($compressed) {
                    $decoded = gzdecode($requestBody, 64 * 1024 * 1024);
                    if ($decoded === false) throw new \RuntimeException('Payload terkompresi tidak valid.');
                    $input = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
                } else {
                    $input = request()->getJSON(true);
                }
                if (!is_array($input) || !isset($input['revision']) || !array_key_exists('data', $input)) {
                    return response()->setStatusCode(422)->setJSON(['error' => 'Payload penyimpanan tidak valid.']);
                }
                if ($input['revision'] !== $revision) return response()->setStatusCode(409)->setJSON(['error' => 'Data berubah di tab lain. Muat ulang sebelum menyimpan.']);
                $data = $input['data'];
                if (!is_array($data) || ($kind === 'environment' && (!isset($data['environments']) || !is_array($data['environments'])))) {
                    return response()->setStatusCode(422)->setJSON(['error' => 'Format data tidak valid.']);
                }
                $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                $temporary = tempnam($directory, '.save-');
                try {
                    if (file_put_contents($temporary, $encoded) !== strlen($encoded) || !rename($temporary, $path)) throw new \RuntimeException();
                    chmod($path, 0600);
                } finally {
                    if (is_file($temporary)) unlink($temporary);
                }
                $revision = hash('sha256', $encoded);
            }
            return response()->setHeader('Cache-Control', 'no-store')->setJSON(['data' => $data, 'revision' => $revision, 'csrfHash' => csrf_hash()]);
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) {
            return response()->setStatusCode(403)->setJSON(['error' => 'Token keamanan berakhir. Muat ulang halaman.']);
        } catch (\Throwable $e) {
            log_message('error', 'REST workspace storage: {message}', ['message' => $e->getMessage()]);
            return response()->setStatusCode(500)->setJSON(['error' => 'Data belum dapat dibaca atau disimpan di server.']);
        } finally {
            if (is_resource($lock)) { flock($lock, LOCK_UN); fclose($lock); }
        }
    }
}
