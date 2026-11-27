<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Security\Exceptions\SecurityException;
use Modules\Developer\Libraries\UserActivityMaintenance;
use Modules\Developer\Models\UserActivityModel;
use Modules\Developer\Models\MastermodulesModel;
use Modules\Developer\Libraries\ModuleCatalogSync;

class Os extends BaseController
{
    private const PREVIEW_KEY = 'os_userlog_preview';
    private const MODULE_PREVIEW_KEY = 'os_module_sync_preview';

    public function index()
    {
        $tableReady = null;
        try {
            $tableReady = UserActivityMaintenance::isReady();
        } catch (\Throwable $e) {
            log_message('error', 'Operasional Sistem: pemeriksaan tabel gagal ({type}).', ['type' => get_class($e)]);
        }
        $preview = session()->getTempdata(self::PREVIEW_KEY);
        if (($preview['scope'] ?? null) !== $this->scope()) {
            $preview = null;
        }
        $logs = [];
        $logsError = '';
        $canSyncModules = false;
        try {
            $canSyncModules = $this->canSyncModules();
        } catch (\Throwable $e) {
            log_message('error', 'Module sync permission check failed ({type}).', ['type' => get_class($e)]);
        }
        $modulePreview = session()->getTempdata(self::MODULE_PREVIEW_KEY);
        if (!$canSyncModules || ($modulePreview['scope'] ?? null) !== $this->scope()) $modulePreview = null;
        if ($tableReady === true) {
            try {
                $scope = $this->scope();
                $logs = (new UserActivityModel())->maintenanceLogs($scope['group_id'], $scope['org_id']);
            } catch (\Throwable $e) {
                $logsError = 'Log belum dapat dimuat. Silakan muat ulang halaman.';
                log_message('error', 'Operasional Sistem: pembacaan log gagal ({type}).', ['type' => get_class($e)]);
            }
        }

        return response()->setHeader('Cache-Control', 'private, no-store')
            ->setBody(view('template/dashboard-light-aside', [
                'documentTitle' => 'Operasional Sistem | Infinite',
                'contents' => view('Modules\Developer\Views\v_os', [
                    'preview' => $preview,
                    'tableReady' => $tableReady,
                    'logs' => $logs,
                    'logsError' => $logsError,
                    'retentionDays' => UserActivityModel::RETENTION_DAYS,
                    'canSyncModules' => $canSyncModules,
                    'modulePreview' => $modulePreview,
                ]),
            ]));
    }

    public function setup()
    {
        return $this->runAction(static function (): string {
            if (UserActivityMaintenance::isReady()) {
                \App\Libraries\UserActivity::setDetails(['Tabel' => 'dt01_gen_user_activity', 'Hasil' => 'Sudah siap']);
                return 'Tabel aktivitas pengguna sudah siap.';
            }
            UserActivityMaintenance::setup();
            \App\Libraries\UserActivity::setDetails(['Tabel' => 'dt01_gen_user_activity', 'Hasil' => 'Penyiapan selesai']);
            return 'Tabel aktivitas pengguna siap.';
        });
    }

    public function exportmodules()
    {
        if (strtoupper(request()->getMethod()) !== 'GET') return response()->setStatusCode(405)->setHeader('Allow', 'GET');
        try {
            if (!$this->canSyncModules()) return response()->setStatusCode(403)->setBody('Akses Operasional Sistem diperlukan.');
            $data = ModuleCatalogSync::export((new MastermodulesModel())->catalog());
            \App\Libraries\UserActivity::setDetails(['Operasi' => 'Export Master Modules', 'Jumlah modul' => count($data['modules'])]);
            return response()->download('infinite-modules-' . gmdate('Ymd-His') . '.json',
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))
                ->setHeader('Cache-Control', 'private, no-store');
        } catch (\Throwable $e) {
            log_message('error', 'Module catalog export failed ({type}).', ['type' => get_class($e)]);
            return redirect()->to(site_url('developer/os'), 303)->with('os_error', 'Export modul gagal. Periksa kesiapan katalog dan coba kembali.');
        }
    }

    public function previewmodules()
    {
        return $this->runAction(function (array $scope): string {
            if (!$this->canSyncModules()) throw new \DomainException('Akses Operasional Sistem diperlukan untuk sinkronisasi.');
            session()->removeTempdata(self::MODULE_PREVIEW_KEY);
            $file = request()->getFile('module_file');
            if (!$file || !$file->isValid() || strtolower($file->getClientExtension()) !== 'json'
                || $file->getSize() > 2 * 1024 * 1024) {
                throw new \DomainException('Pilih file JSON Export Master Modules, maksimal 2 MB.');
            }
            $source = ModuleCatalogSync::import((string) file_get_contents($file->getTempName()));
            $plan = (new MastermodulesModel())->previewSync($source);
            session()->setTempdata(self::MODULE_PREVIEW_KEY, [
                'scope' => $scope, 'source' => $source, 'plan' => $plan,
                'token' => bin2hex(random_bytes(24)), 'expires' => time() + 900,
            ], 900);
            \App\Libraries\UserActivity::setDetails(['Operasi' => 'Preview sinkronisasi Master Modules', 'Ringkasan' => $plan['summary']]);
            return 'Preview sinkronisasi siap. Periksa perubahan sebelum diterapkan.';
        });
    }

    public function applymodules()
    {
        return $this->runAction(function (array $scope): string {
            if (!$this->canSyncModules()) throw new \DomainException('Akses Operasional Sistem diperlukan untuk sinkronisasi.');
            $preview = session()->getTempdata(self::MODULE_PREVIEW_KEY);
            $token = request()->getPost('preview_token');
            if (!$preview || ($preview['scope'] ?? null) !== $scope || !is_string($token)
                || !hash_equals($preview['token'], $token) || $preview['expires'] < time()) {
                throw new \DomainException('Preview tidak tersedia atau kedaluwarsa. Impor file kembali.');
            }
            $summary = (new MastermodulesModel())->applySync($preview['source'], $preview['plan']['revision'], $scope['user_id']);
            session()->removeTempdata(self::MODULE_PREVIEW_KEY);
            return sprintf('Sinkronisasi selesai. %d modul ditambahkan, %d diperbarui, %d sesuai.', $summary['add'], $summary['update'], $summary['unchanged']);
        });
    }

    private function canSyncModules(): bool
    {
        $scope = $this->scope();
        if (!session()->get('loggedin') || in_array('', $scope, true)) return false;
        $state = (new \Modules\Hr\Models\AccessModel())->state($scope['group_id'], $scope['org_id'], $scope['user_id']);
        return \App\Libraries\ModuleAccess::routeAllowed('developer', 'os', (new \App\Models\Modelrouting())->allMenus(), $state);
    }

    public function preview()
    {
        return $this->runAction(static function (array $scope): string {
            session()->removeTempdata(self::PREVIEW_KEY);
            $cutoff = UserActivityModel::retentionCutoff();
            $count = (new UserActivityModel())->pruneExpired(true, $scope, $cutoff);
            session()->setTempdata(self::PREVIEW_KEY, compact('scope', 'cutoff', 'count'), 300);
            return 'Pemeriksaan log kedaluwarsa selesai.';
        });
    }

    public function prune()
    {
        return $this->runAction(static function (array $scope): string {
            $mode = request()->getPost('mode');
            if (request()->getPost('org_id') !== $scope['org_id']
                || !in_array($mode, ['expired', 'all'], true)) {
                throw new \DomainException('Pilihan organisasi atau penghapusan tidak valid.');
            }

            session()->removeTempdata(self::PREVIEW_KEY);
            $count = (new UserActivityModel())->deleteOrganizationLogs($scope, $mode === 'expired');
            \App\Libraries\UserActivity::setDetails([
                'Organisasi' => (string) session('orgname'),
                'ID organisasi' => $scope['org_id'],
                'Penghapusan' => $mode === 'expired' ? 'Lebih dari 30 hari' : 'Semua log',
                'Jumlah log dihapus' => $count,
                'Batas tanggal' => $mode === 'expired' ? UserActivityModel::retentionCutoff() : null,
            ]);
            return "Log aktivitas yang dihapus: $count.";
        });
    }

    private function scope(): array
    {
        return [
            'group_id' => (string) session()->get('groupid'),
            'org_id' => (string) session()->get('orgid'),
            'user_id' => (string) session()->get('userid'),
        ];
    }

    private function runAction(callable $action)
    {
        $request = request();
        if (strtoupper($request->getMethod()) !== 'POST') {
            return response()->setStatusCode(405)->setHeader('Allow', 'POST');
        }

        $scope = $this->scope();
        if (!session()->get('loggedin') || in_array('', $scope, true)) {
            return response()->setStatusCode(403);
        }

        $redirect = redirect()->to(site_url('developer/os'), 303);
        try {
            service('security')->verify($request);
            $message = $action($scope);
            return $redirect->withCookies()->with('os_success', $message);
        } catch (SecurityException $e) {
            $message = 'Sesi formulir berakhir. Silakan coba lagi.';
        } catch (\DomainException $e) {
            $message = $e->getMessage();
        } catch (\Throwable $e) {
            log_message('error', 'Operasional Sistem: pemeliharaan aktivitas gagal ({type}).', ['type' => get_class($e)]);
            $message = 'Operasi gagal. Periksa log server, lalu coba lagi.';
        }

        return $redirect->withCookies()->with('os_error', $message);
    }
}
