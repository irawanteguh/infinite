<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;
use App\Libraries\UserActivity;
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

        $requestedDate = request()->getGet('date');
        $events = UserActivityModel::EVENTS;
        $message = '';
        $rows = [];
        $dailyActivities = [];
        $selectedDate = null;
        $moduleNames = [];
        $status = 200;

        try {
            $model = new UserActivityModel();
            $dailyActivities = $model->latestPerDay($group, $org);
            $dates = array_column($dailyActivities, 'activity_date');
            $selectedDate = in_array($requestedDate, $dates, true) ? $requestedDate : ($dates[0] ?? null);
            if ($selectedDate !== null) {
                $rows = $model->timeline($group, $org, '', null, null, $selectedDate);
            }
        } catch (\Throwable $e) {
            log_message('error', 'User Log: gagal membaca aktivitas ({type}).', ['type' => get_class($e)]);
            $message = 'Riwayat aktivitas belum tersedia.';
            $status = 503;
        }

        if ($message === '' && $dailyActivities) {
            try {
                $moduleNames = UserActivity::moduleNames();
            } catch (\Throwable $e) {
                log_message('error', 'User Log: gagal membaca nama modul ({type}).', ['type' => get_class($e)]);
            }
        }

        $activity = compact('rows', 'dailyActivities', 'selectedDate', 'moduleNames', 'events', 'message') + [
            'url' => site_url('developer/userlog'),
            'anchor' => '#userlog-title',
        ];

        return response()
            ->setStatusCode($status)
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody(view('template/dashboard-light-aside', [
                'contents' => view('Modules\Developer\Views\v_userlog', $activity),
            ]));
    }
}
