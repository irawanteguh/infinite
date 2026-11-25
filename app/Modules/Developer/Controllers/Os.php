<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Security\Exceptions\SecurityException;
use Modules\Developer\Libraries\UserActivityMaintenance;
use Modules\Developer\Models\UserActivityModel;

class Os extends BaseController
{
    private const PREVIEW_KEY = 'os_userlog_preview';

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
