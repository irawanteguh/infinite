<?php

namespace Modules\Ca\Controllers;

use App\Controllers\BaseController;
use Modules\Ca\Models\DashboardModel;
use Modules\Ca\Models\CaskriningModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('loggedin')) return redirect()->to(site_url('/'));
        response()->setHeader('Cache-Control', 'private, no-store');
        $group = (string) session()->get('groupid');
        $org = (string) session()->get('orgid');
        $filters = ['start' => date('Y') . '-01-01', 'end' => date('Y') . '-12-31', 'instrument' => '', 'domicile' => ''];
        $years = [date('Y')];
        $instruments = [];
        $data = null;
        $error = '';
        try {
            if ($group === '' || $org === '') throw new \InvalidArgumentException('Sesi organisasi tidak tersedia.');
            $model = new DashboardModel();
            $instruments = $model->instruments($group, $org);
            $year = request()->getGet('year') ?? date('Y');
            if (!is_string($year) || !preg_match('/^[1-9][0-9]{3}$/D', $year) || $year === '9999') {
                throw new \InvalidArgumentException('Tahun filter tidak valid.');
            }
            $years = array_unique(array_merge($years, $model->years($group, $org), [$year]));
            rsort($years);
            $filters['year'] = $year;
            $filters['start'] = $year . '-01-01';
            $filters['end'] = $year . '-12-31';
            foreach (['instrument', 'domicile'] as $key) {
                $value = request()->getGet($key);
                if ($value !== null) {
                    if (!is_string($value)) throw new \InvalidArgumentException('Format filter tidak valid.');
                    $filters[$key] = trim($value);
                }
            }
            if ($filters['instrument'] !== '' && !in_array($filters['instrument'], array_map('strval', array_column($instruments, 'instrument_id')), true)) {
                throw new \InvalidArgumentException('Instrumen tidak tersedia pada organisasi ini.');
            }
            if (!in_array($filters['domicile'], array_merge(['', 'UNKNOWN'], array_keys(CaskriningModel::DOMICILES)), true)) {
                throw new \InvalidArgumentException('Domisili tidak valid.');
            }
            $page = request()->getGet('page') ?? '1';
            if (!is_string($page) || !preg_match('/^[1-9][0-9]{0,8}$/D', $page)) throw new \InvalidArgumentException('Halaman tidak valid.');
            $data = $model->analytics($group, $org, $filters, (int) $page);
        } catch (\InvalidArgumentException $exception) {
            $error = $exception->getMessage();
            response()->setStatusCode(422);
        } catch (\Throwable $exception) {
            log_message('error', 'CA dashboard: {message}', ['message' => $exception->getMessage()]);
            $error = 'Dashboard belum dapat dimuat. Silakan coba kembali.';
            response()->setStatusCode(503);
        }
        return view('template/dashboard-light-aside', [
            'caYears' => $years,
            'caFilters' => $filters,
            'contents' => view('Modules\Ca\Views\v_dashboard', compact('filters', 'instruments', 'data', 'error')),
        ]);
    }
}
