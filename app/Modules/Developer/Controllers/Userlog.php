<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelrouting;
use Modules\Developer\Models\UserActivityModel;

class Userlog extends BaseController
{
    public function index()
    {
        if (!session()->get('loggedin')) {
            return redirect()->to(site_url('/'));
        }

        $group = (string) session()->get('groupid');
        $org = (string) session()->get('orgid');
        if ($group === '' || $org === '') {
            return response()->setStatusCode(403);
        }

        $before = request()->getGet('before');
        $before = is_string($before) && preg_match('/\A[1-9][0-9]{0,19}\z/D', $before) ? $before : '';
        $events = UserActivityModel::EVENTS;
        $message = '';
        $rows = [];
        $latestUsers = [];
        $moduleNames = [];
        $status = 200;

        try {
            $model = new UserActivityModel();
            $rows = $model->timeline($group, $org, $before, 51);
            $latestUsers = $model->latestPerUser($group, $org);
            if ($rows || $latestUsers) {
                foreach ((new Modelrouting())->allMenus() as $menu) {
                    if (\App\Libraries\ModuleAccess::hasRoute($menu)) {
                        $key = strtolower(trim($menu['package']) . '/' . trim($menu['def_controller']));
                        $moduleNames[$key] = (string) $menu['modules_name'];
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'User Log: gagal membaca aktivitas ({type}).', ['type' => get_class($e)]);
            $message = 'Riwayat aktivitas belum tersedia.';
            $status = 503;
        }

        $next = null;
        if (count($rows) > 50) {
            array_pop($rows);
            $next = (string) end($rows)['activity_id'];
        }

        return response()
            ->setStatusCode($status)
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody(view('template/dashboard-light-aside', [
                'contents' => view('Modules\Developer\Views\v_userlog', compact('rows', 'latestUsers', 'moduleNames', 'before', 'events', 'message', 'next')),
            ]));
    }
}
