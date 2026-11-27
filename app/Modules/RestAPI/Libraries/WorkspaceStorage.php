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
        $stage = 'akses-folder';
        try {
            if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
                throw new \DomainException('Folder restapi-data belum dapat dibuat. Berikan izin tulis pada folder aplikasi kepada user PHP/web server.');
            }
            if (!is_readable($directory) || !is_writable($directory)) {
                throw new \DomainException('Folder restapi-data tidak dapat diakses. Periksa pemilik dan izin baca/tulis folder untuk user PHP/web server.');
            }
            $path = $directory . '/' . $kind . '-' . $scope . '.json';
            $lock = @fopen($path . '.lock', 'c');
            if (!$lock || !flock($lock, LOCK_EX)) {
                throw new \DomainException('File pengunci di restapi-data tidak dapat diakses. Periksa pemilik dan izin tulis file .lock untuk user PHP/web server.');
            }
            $legacyPath = $directory . '/' . $kind . '-' . $legacyScope . '.json';
            $stage = 'migrasi-data';
            if (!is_file($path) && is_file($legacyPath)) {
                $legacy = @file_get_contents($legacyPath);
                if ($legacy === false) throw new \DomainException('File workspace lama tidak dapat dibaca. Periksa izin baca file di restapi-data.');
                json_decode($legacy, true, 512, JSON_THROW_ON_ERROR);
                $temporary = @tempnam($directory, '.migrate-');
                if ($temporary === false || realpath(dirname($temporary)) !== realpath($directory)) {
                    if ($temporary !== false && is_file($temporary)) @unlink($temporary);
                    throw new \DomainException('Migrasi Collection/Environment gagal membuat file sementara di restapi-data. Periksa izin folder dan kapasitas disk.');
                }
                try {
                    if (@file_put_contents($temporary, $legacy) !== strlen($legacy)) {
                        throw new \DomainException('Migrasi data lama gagal menulis file. Periksa kapasitas disk dan izin restapi-data.');
                    }
                    if (!@rename($temporary, $path)) {
                        throw new \DomainException('Migrasi data lama gagal mengganti file tujuan. Periksa izin folder dan volume Docker.');
                    }
                    if (!@chmod($path, 0600)) {
                        throw new \DomainException('File migrasi berhasil dibuat, tetapi izin file gagal diatur. Periksa pemilik file untuk user PHP/web server.');
                    }
                } finally {
                    if (is_file($temporary)) unlink($temporary);
                }
            }
            $stage = 'baca-data';
            $raw = is_file($path) ? @file_get_contents($path) : null;
            if ($raw === false) throw new \DomainException('File workspace tidak dapat dibaca. Periksa izin baca file di restapi-data.');
            $revision = hash('sha256', $raw ?? '');
            $data = $raw === null ? null : json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            if (strtolower(request()->getMethod()) === 'post') {
                $stage = 'validasi-request';
                $compressed = request()->getHeaderLine('X-Workspace-Encoding') === 'gzip';
                $requestBody = $compressed ? request()->getBody() : null;
                service('security')->verify(request());
                if ($compressed) {
                    $stage = 'dekompresi-request';
                    if (!function_exists('gzdecode')) throw new \DomainException('Ekstensi zlib PHP belum tersedia untuk membaca unggahan Collection terkompresi. Aktifkan zlib pada container PHP.');
                    $decoded = gzdecode($requestBody, 64 * 1024 * 1024);
                    if ($decoded === false) throw new \DomainException('Unggahan Collection terkompresi tidak valid atau melebihi batas 64 MB. Periksa batas unggahan PHP/proxy.');
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
                $stage = 'tulis-file';
                $temporary = @tempnam($directory, '.save-');
                if ($temporary === false || realpath(dirname($temporary)) !== realpath($directory)) {
                    if ($temporary !== false && is_file($temporary)) @unlink($temporary);
                    throw new \DomainException('File sementara tidak dapat dibuat di restapi-data. Periksa izin folder, kapasitas disk, dan volume Docker read-only.');
                }
                try {
                    if (@file_put_contents($temporary, $encoded) !== strlen($encoded)) {
                        throw new \DomainException('Data Collection/Environment gagal ditulis penuh. Periksa kapasitas disk dan kuota volume Docker.');
                    }
                    if (!@rename($temporary, $path)) {
                        throw new \DomainException('File data gagal diganti. Periksa izin folder dan file JSON, volume Docker, atau atribut immutable pada file.');
                    }
                    chmod($path, 0600);
                } finally {
                    if (is_file($temporary)) unlink($temporary);
                }
                $revision = hash('sha256', $encoded);
            }
            $stage = 'respons-data';
            return response()->setHeader('Cache-Control', 'no-store')->setJSON(['data' => $data, 'revision' => $revision, 'csrfHash' => csrf_hash()]);
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) {
            return response()->setStatusCode(403)->setJSON(['error' => 'Token keamanan berakhir. Muat ulang halaman.']);
        } catch (\JsonException $e) {
            log_message('error', 'REST workspace storage: invalid JSON ({kind}).', ['kind' => $kind]);
            return response()->setStatusCode(422)->setJSON(['error' => 'Data JSON workspace atau permintaan tidak valid. Periksa file data dan format JSON sebelum mencoba kembali.']);
        } catch (\DomainException $e) {
            log_message('error', 'REST workspace storage: {message}', ['message' => $e->getMessage()]);
            return response()->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            log_message('error', 'REST workspace storage ({stage}, {kind}, {type}): {message}', ['stage' => $stage, 'kind' => $kind, 'type' => get_class($e), 'message' => $e->getMessage()]);
            return response()->setStatusCode(500)->setJSON(['error' => 'Penyimpanan ' . $kind . ' gagal pada tahap ' . $stage . '. Periksa log PHP/server untuk rincian.']);
        } finally {
            if (is_resource($lock)) { flock($lock, LOCK_UN); fclose($lock); }
        }
    }
}
