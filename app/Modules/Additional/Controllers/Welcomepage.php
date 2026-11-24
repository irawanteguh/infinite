<?php

namespace Modules\Additional\Controllers;

use App\Controllers\BaseController;
use App\Libraries\UserActivity;
use Modules\Developer\Models\UserActivityModel;

class Welcomepage extends BaseController
{
    public function index(){
        if (!$this->session->get('loggedin')) {
            return redirect()->to(site_url('/'));
        }

        $data = [
            'name'    => $this->session->get('name'),
            'email'   => $this->session->get('email'),
            'orgname' => $this->session->get('orgname'),
            'address' => $this->session->get('address'),
            'emailorg' => $this->session->get('emailorg'),
        ];

        $data['activity'] = $this->activityTimeline();

        return response()
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody(view('template/dashboard-light-aside', [
                'contents' => view('Modules\Additional\Views\v_welcomepage', $data),
            ]));
    }

    private function activityTimeline(): array
    {
        $group = (string) $this->session->get('groupid');
        $org = (string) $this->session->get('orgid');
        $user = (string) $this->session->get('userid');
        $requestedDate = request()->getGet('date');
        $dailyActivities = [];
        $selectedDate = null;
        $rows = [];
        $moduleNames = [];
        $message = '';

        if (in_array('', [$group, $org, $user], true)) {
            $message = 'Riwayat aktivitas belum tersedia.';
        } else {
            try {
                $model = new UserActivityModel();
                $dailyActivities = $model->latestPerDay($group, $org, $user);
                $dates = array_column($dailyActivities, 'activity_date');
                $selectedDate = in_array($requestedDate, $dates, true) ? $requestedDate : ($dates[0] ?? null);
                if ($selectedDate !== null) {
                    $rows = $model->ownTimeline($group, $org, $user, $selectedDate);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Welcome Page: gagal membaca aktivitas ({type}).', ['type' => get_class($e)]);
                $message = 'Riwayat aktivitas belum tersedia.';
            }
        }

        if ($message === '' && $dailyActivities) {
            try {
                $moduleNames = UserActivity::moduleNames();
            } catch (\Throwable $e) {
                log_message('error', 'Welcome Page: gagal membaca nama modul ({type}).', ['type' => get_class($e)]);
            }
        }

        return compact('rows', 'moduleNames', 'message', 'dailyActivities', 'selectedDate') + [
            'events' => UserActivityModel::EVENTS,
            'url' => site_url('additional/welcomepage'),
            'anchor' => '#welcome-activity-timeline-title',
        ];
    }
}
