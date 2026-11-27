<?php

namespace App\Libraries;

use App\Models\Modelrouting;
use CodeIgniter\HTTP\RequestInterface;
use Modules\Developer\Models\UserActivityModel;

class UserActivity
{
    private const REQUEST_DESCRIPTIONS = [
        'POST /auth/changepassword' => 'Mengajukan perubahan password',
        'POST /restapi/collection/send' => 'Mengirim request REST API melalui Collection',
        'POST /developer/mastermodules/reorder' => 'Mengajukan perubahan urutan modul',
        'POST /developer/mastermodules/update' => 'Mengajukan perubahan detail modul',
        'POST /developer/mastermodules/addchild' => 'Mengajukan penambahan submodul',
        'POST /developer/backupdb/backup' => 'Meminta backup database',
        'POST /developer/os/setup' => 'Menjalankan penyiapan tabel aktivitas pengguna',
        'POST /developer/os/preview' => 'Memeriksa log aktivitas kedaluwarsa',
        'POST /developer/os/prune' => 'Menjalankan pembersihan log aktivitas kedaluwarsa',
        'POST /developer/os/previewmodules' => 'Memeriksa preview sinkronisasi Master Modules',
        'POST /developer/os/applymodules' => 'Menerapkan sinkronisasi Master Modules',
    ];

    private static ?array $reorderDetails = null;

    public static function setReorderDetails(array $details): void
    {
        self::$reorderDetails = $details;
    }

    public static function setDetails(array $details): void
    {
        self::$reorderDetails = $details;
    }

    public static function moduleNames(): array
    {
        $names = [];
        foreach ((new Modelrouting())->allMenus() as $menu) {
            if (ModuleAccess::hasRoute($menu)) {
                $key = strtolower(trim($menu['package']) . '/' . trim($menu['def_controller']));
                $names[$key] = (string) $menu['modules_name'];
            }
        }

        return $names;
    }

    public static function describe(array $activity, array $moduleNames): string
    {
        $route = strtolower($activity['route']);
        $methodRoute = strtoupper($activity['http_method']) . ' ' . $route;
        if ($activity['event'] === 'request' && isset(self::REQUEST_DESCRIPTIONS[$methodRoute])) {
            return self::REQUEST_DESCRIPTIONS[$methodRoute];
        }

        $moduleRoute = implode('/', array_slice(explode('/', ltrim($route, '/')), 0, 2));
        $module = $moduleNames[$moduleRoute] ?? '';
        $actions = [
            'login' => ['Masuk ke Infinite', ''],
            'logout' => ['Keluar dari Infinite', ''],
            'page' => ['Membuka halaman aplikasi', 'Membuka halaman '],
            'read' => ['Meminta data aplikasi', 'Meminta data dari '],
            'download' => ['Meminta unduhan file', 'Meminta unduhan dari '],
            'denied' => ['Mencoba mengakses halaman yang dibatasi', 'Mencoba mengakses '],
            'request' => ['Mengirim permintaan aplikasi', 'Mengirim permintaan ke '],
        ];
        [$fallback, $withModule] = $actions[$activity['event']] ?? $actions['request'];

        return $module !== '' && $withModule !== '' ? $withModule . $module : $fallback;
    }

    public static function record(RequestInterface $request, string $event, int $status): void
    {
        $session = session();
        $scope = [
            'group_id' => (string) $session->get('groupid'),
            'org_id' => (string) $session->get('orgid'),
            'user_id' => (string) $session->get('userid'),
        ];
        if (!$session->get('loggedin') || in_array('', $scope, true)) {
            return;
        }

        $router = service('router');
        [$pattern] = $router->getMatchedRoute() ?? [''];
        $route = '/' . $pattern;
        $controller = $router->controllerName();
        if ($controller instanceof \Closure && strpos($pattern, '([^/]+)') === 0) {
            // HMVC passes package, controller and action before any business record identifiers.
            $segments = array_slice($request->getUri()->getSegments(), 0, $event === 'denied' ? 2 : 3);
            $route = '/' . implode('/', array_map(static function (string $segment): string {
                return preg_match('/\A[a-z][a-z0-9_]{0,59}\z/iD', $segment) ? strtolower($segment) : '{segment}';
            }, $segments));
        } elseif (strpos($pattern, '(') !== false) {
            $route = '/' . explode('(', $pattern, 2)[0] . '{parameter}';
        }

        $details = self::$reorderDetails;
        self::$reorderDetails = null;
        $method = strtoupper($request->getMethod());

        try {
            $model = new UserActivityModel();
            $extra = [];
            if ($details === null && $event === 'page') {
                $names = self::moduleNames();
                $key = implode('/', array_slice(explode('/', ltrim($route, '/')), 0, 2));
                $details = ['Halaman' => $names[$key] ?? $key];
            }
            if ($details !== null) {
                $details['Status HTTP'] = $status;
                $extra['details'] = json_encode($details, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
            }
            $model->record($scope + [
                'user_name' => mb_substr((string) $session->get('name'), 0, 255),
                'event' => $event,
                'route' => mb_substr($route, 0, 200),
                'http_method' => $method,
                'http_status' => $status,
                'created_at' => date('Y-m-d H:i:s'),
            ] + $extra);
        } catch (\Throwable $e) {
            log_message('error', 'User Log: gagal menyimpan aktivitas ({type}).', ['type' => get_class($e)]);
            return;
        }

        // Maintenance requests must not trigger the automatic global purge.
        if ($route === '/developer/os' || strpos($route, '/developer/os/') === 0) {
            return;
        }

        try {
            $today = date('Y-m-d');
            if (cache()->get('userlog_last_prune_date') !== $today) {
                $model->pruneExpired();
                cache()->save('userlog_last_prune_date', $today, 86400);
            }
        } catch (\Throwable $e) {
            log_message('error', 'User Log: gagal membersihkan aktivitas kedaluwarsa ({type}).', ['type' => get_class($e)]);
        }
    }
}
