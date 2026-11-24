<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;
use App\Libraries\UserActivity;
use CodeIgniter\Security\Exceptions\SecurityException;
use Modules\Developer\Models\MastermodulesModel;

class Mastermodules extends BaseController
{
    public function index()
    {
        $modules = (new MastermodulesModel())->catalog();
        $modulesById = array_column($modules, null, 'modules_id');
        $groups = [];
        foreach ($modules as $module) {
            $groups[(string) $module['modules_header_id']][] = $module;
        }

        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Developer\Views\v_mastermodules', compact('groups', 'modulesById')),
        ]);
    }

    public function reorder()
    {
        return $this->save(static function ($request, MastermodulesModel $model): array {
            $group = $request->getPost('group');
            $revision = $request->getPost('revision');
            $ids = $request->getPost('module_ids');
            if (!is_string($group) || strlen($group) > 36
                || !is_string($revision) || !preg_match('/\A[a-f0-9]{64}\z/D', $revision)
                || !is_array($ids) || !$ids || array_values($ids) !== $ids || count($ids) > 1000) {
                throw new \DomainException('Daftar urutan modul tidak valid.');
            }
            foreach ($ids as $id) {
                if (!is_string($id) || $id === '' || strlen($id) > 36) {
                    throw new \DomainException('Daftar urutan modul tidak valid.');
                }
            }
            $result = $model->reorder($group, $ids, $revision);
            UserActivity::setReorderDetails($result['details']);
            return ['revision' => $result['revision']];
        }, 'Urutan modul berhasil disimpan.');
    }

    public function update()
    {
        return $this->save(function ($request, MastermodulesModel $model): void {
            $id = $request->getPost('module_id');
            $revision = $request->getPost('revision');
            if (!is_string($id) || $id === '' || strlen($id) > 36
                || !is_string($revision) || !preg_match('/\A[a-f0-9]{64}\z/D', $revision)) {
                throw new \DomainException('Modul yang dipilih tidak valid.');
            }
            $model->updateDetails($id, $this->details($request, true), $revision);
        }, 'Modul berhasil diperbarui.');
    }

    public function addchild()
    {
        return $this->save(function ($request, MastermodulesModel $model): void {
            $parent = $request->getPost('module_id');
            if (!is_string($parent) || $parent === '' || strlen($parent) > 36) {
                throw new \DomainException('Modul induk yang dipilih tidak valid.');
            }
            $model->addChild($parent, $this->details($request), (string) session('userid'));
        }, 'Submodul berhasil ditambahkan.');
    }

    private function details(\CodeIgniter\HTTP\IncomingRequest $request, bool $editing = false): array
    {
        $limits = ['module_name' => 100, 'icon' => 500, 'package' => 100, 'def_controller' => 100];
        if ($editing) {
            $limits += ['status' => 1, 'version' => 100];
        }
        $data = [];
        foreach ($limits as $field => $max) {
            $value = $request->getPost($field);
            if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || mb_strlen(trim($value), 'UTF-8') > $max) {
                throw new \DomainException('Ada nilai yang tidak valid atau melebihi batas karakter.');
            }
            $data[$field] = trim($value);
        }
        if ($data['module_name'] === '') {
            throw new \DomainException('Nama modul wajib diisi.');
        }
        if ($editing) {
            if ($data['status'] === '') {
                throw new \DomainException('Kode status wajib diisi.');
            }
            $data['version'] = $data['version'] === '' ? null : $data['version'];
        }
        $data['modules_name'] = $data['module_name'];
        unset($data['module_name']);
        return $data;
    }

    private function save(callable $change, string $success)
    {
        $request = request();
        if (strtoupper($request->getMethod()) !== 'POST') {
            return response()->setStatusCode(405)->setHeader('Allow', 'POST');
        }

        $url = site_url('developer/mastermodules');
        try {
            service('security')->verify($request);
            $result = $change($request, new MastermodulesModel());
            if ($request->isAJAX()) {
                return $this->saveResponse(200, $success, $result ?? []);
            }
            return redirect()->to($url, 303)->withCookies()->with('mastermodules_success', $success);
        } catch (SecurityException $e) {
            $message = 'Sesi formulir berakhir. Silakan coba lagi.';
            $status = 403;
        } catch (\DomainException $e) {
            $message = $e->getMessage();
            $status = 422;
        } catch (\Throwable $e) {
            log_message('error', 'Master Modules: gagal menyimpan perubahan ({type}).', ['type' => get_class($e)]);
            $message = 'Perubahan modul belum dapat disimpan. Silakan coba lagi.';
            $status = 503;
        }

        if ($request->isAJAX()) {
            return $this->saveResponse($status, $message);
        }
        return redirect()->to($url, 303)->withCookies()->with('mastermodules_error', $message);
    }

    private function saveResponse(int $status, string $message, array $result = [])
    {
        return response()->setStatusCode($status)->setHeader('Cache-Control', 'no-store')->setJSON($result + [
            'message' => $message,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }
}
