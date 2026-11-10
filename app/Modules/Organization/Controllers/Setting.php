<?php

namespace Modules\Organization\Controllers;

use App\Controllers\BaseController;
use Modules\Organization\Models\SettingModel;

class Setting extends BaseController
{
    public function index()
    {
        if (!session()->get('loggedin') || !session()->get('groupid') || !session()->get('orgid')) {
            return redirect()->to(site_url('/'));
        }
        $model = new SettingModel();
        $ready = $model->ready();
        if (!session()->get('indicator_setting_token')) {
            session()->set('indicator_setting_token', bin2hex(random_bytes(32)));
        }
        return view('template/dashboard-light-aside', ['contents' => view('Modules\Organization\Views\v_setting', [
            'ready' => $ready,
            'setting' => $ready ? $model->setting() : null,
            'departments' => $ready ? $model->departments() : [],
            'grants' => $ready ? $model->grants() : [],
            'canDeactivate' => $ready && $model->supportsDeactivation(),
        ])]);
    }

    public function save()
    {
        if (!session()->get('loggedin') || !session()->get('groupid') || !session()->get('orgid')) {
            return redirect()->to(site_url('/'));
        }
        $token = (string) session()->get('indicator_setting_token');
        if (strtolower(request()->getMethod()) !== 'post' || $token === '' || !hash_equals($token, (string) request()->getPost('token'))) {
            return response()->setStatusCode(403)->setBody('Permintaan tidak valid.');
        }
        $message = 'Pengaturan gagal disimpan.';
        try {
            $model = new SettingModel();
            if (!$model->ready()) {
                $message = 'Jalankan migrasi database terlebih dahulu.';
            } elseif (request()->getPost('action') === 'deactivate') {
                $message = $model->deactivateGrant(trim((string) request()->getPost('grant_id')))
                    ? 'Izin berhasil dinonaktifkan. Riwayat tetap tersimpan.'
                    : 'Izin gagal dinonaktifkan. Pastikan izin masih aktif dan migrasi sudah dijalankan.';
            } elseif (request()->getPost('action') === 'grant') {
                $message = $model->grant(trim((string) request()->getPost('department_id')))
                    ? 'Izin pengisian diberikan selama 24 jam mulai sekarang.'
                    : 'Izin gagal diberikan. Periksa departemen atau tunggu izin aktif berakhir.';
            } else {
                $mode = request()->getPost('mode');
                $day = filter_var(request()->getPost('deadline_day'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 31]]);
                if (!in_array($mode, ['unlimited', 'limited'], true) || ($mode === 'limited' && $day === false)) {
                    $message = 'Pilih mode dan tanggal batas antara 1 sampai 31.';
                } elseif ($model->saveSetting($mode === 'unlimited' ? null : $day)) {
                    $message = 'Pengaturan batas pengisian berhasil disimpan.';
                }
            }
        } catch (\Throwable $exception) {
            log_message('error', 'Gagal menyimpan pengaturan indikator: {message}', ['message' => $exception->getMessage()]);
        }
        session()->setFlashdata('indicator_setting_message', $message);
        return redirect()->to(site_url('organization/setting'));
    }
}
