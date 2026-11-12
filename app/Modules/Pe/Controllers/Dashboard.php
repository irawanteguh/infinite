<?php

namespace Modules\Pe\Controllers;

use App\Controllers\BaseController;
use Modules\Pe\Models\DashboardModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('loggedin')) return redirect()->to(site_url('/'));
        $group = (string) session()->get('groupid');
        $org = (string) session()->get('orgid');
        $filters = ['start' => date('Y') . '-01-01', 'end' => date('Y') . '-12-31', 'service' => '', 'instrument' => ''];
        $instruments = [];
        $data = null;
        $error = '';
        try {
            if ($group === '' || $org === '') throw new \InvalidArgumentException('Sesi organisasi tidak tersedia. Silakan login kembali.');
            $model = new DashboardModel();
            $instruments = $model->instruments($group, $org);
            foreach ($filters as $key => $default) {
                $value = request()->getGet($key);
                if ($value !== null) {
                    if (!is_string($value)) throw new \InvalidArgumentException('Format filter tidak valid.');
                    $filters[$key] = trim($value);
                }
            }
            foreach (['start', 'end'] as $key) {
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $filters[$key]);
                if (!$date || $date->format('Y-m-d') !== $filters[$key]) throw new \InvalidArgumentException('Tanggal filter tidak valid.');
            }
            if ($filters['start'] > $filters['end']) throw new \InvalidArgumentException('Tanggal mulai harus sebelum atau sama dengan tanggal akhir.');
            if ((new \DateTimeImmutable($filters['start']))->diff(new \DateTimeImmutable($filters['end']))->days > 1096) {
                throw new \InvalidArgumentException('Pilih rentang periode maksimal tiga tahun.');
            }
            if (!in_array($filters['service'], ['', 'RJ', 'RI', 'IGD'], true)) throw new \InvalidArgumentException('Jenis pelayanan tidak valid.');
            if ($filters['instrument'] !== '' && !in_array($filters['instrument'], array_column($instruments, 'instrument_id'), true)) {
                throw new \InvalidArgumentException('Instrumen tidak tersedia pada organisasi ini.');
            }
            $data = $model->analytics($group, $org, $filters);
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
            response()->setStatusCode(422);
        } catch (\Throwable $e) {
            log_message('error', 'PE dashboard: {message}', ['message' => $e->getMessage()]);
            $error = 'Analitik belum dapat dimuat. Silakan coba kembali atau hubungi administrator.';
            response()->setStatusCode(503);
        }
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Pe\Views\v_dashboard', compact('filters', 'instruments', 'data', 'error')),
        ]);
    }
}