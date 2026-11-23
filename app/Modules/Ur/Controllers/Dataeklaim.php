<?php

namespace Modules\Ur\Controllers;

use App\Controllers\BaseController;
use Modules\Ur\Models\DataeklaimModel;
use Modules\Ur\Models\InternalReviewModel;

class Dataeklaim extends BaseController
{
    protected $md;

    public function __construct()
    {
        parent::__construct();
        $this->md = new DataeklaimModel();
    }

    public function index()
    {
        $periode = '';
        foreach ($this->md->periode() as $row) $periode .= '<option value="' . $row->periode . '">' . $row->periode . '</option>';
        return view('template/dashboard-light-aside', ['periode' => $periode, 'contents' => view('Modules\Ur\Views\v_dataeklaim', compact('periode'))]);
    }

    public function review()
    {
        return $this->renderReviewPage(false);
    }

    public function reviewcharts()
    {
        return $this->renderReviewPage(true);
    }

    private function renderReviewPage(bool $isReviewCharts)
    {
        $hospital = (string) $this->session->get('koders');
        if ($hospital === '') {
            return response()->setStatusCode(403)->setBody('Sesi organisasi belum tersedia.');
        }

        $year = request()->getGet('year');
        $month = request()->getGet('month');
        $service = request()->getGet('service');
        $category = request()->getGet('category');
        $after = request()->getGet('after');
        $currentYear = (int) date('Y');
        $categories = [
            'data' => 'Kelengkapan data', 'koding' => 'Review koding', 'klinis' => 'Review klinis',
            'topup' => 'Kandidat top-up', 'klaim' => 'Riwayat verifikasi klaim',
        ];
        $filters = [
            'year' => is_string($year) && ctype_digit($year) && (int) $year >= 2014 && (int) $year <= $currentYear ? (int) $year : $currentYear,
            'month' => is_string($month) && ctype_digit($month) && (int) $month >= 1 && (int) $month <= 12 ? (int) $month : 0,
            'service' => in_array($service, ['1', '2'], true) ? $service : '',
            'category' => is_string($category) && isset($categories[$category]) ? $category : '',
            'after' => is_string($after) && strlen($after) <= 100 ? $after : '',
        ];
        if ($isReviewCharts) {
            return view('template/dashboard-light-aside', [
                'reviewFilters' => $filters,
                'currentYear' => $currentYear,
                'isReviewCharts' => true,
                'contents' => view('Modules\Ur\Views\v_dataeklaimreviewcharts', compact('filters', 'categories')),
            ]);
        }

        $error = '';
        $data = ['rows' => [], 'history' => [], 'next' => ''];
        try {
            $data = (new InternalReviewModel())->queue(
                $hospital, (string) $this->session->get('kodeppk'), $filters
            );
        } catch (\Throwable $e) {
            log_message('error', 'Antrean review E-Klaim gagal dimuat ({type}, kode {code}).', [
                'type' => get_class($e), 'code' => $e->getCode(),
            ]);
            response()->setStatusCode(503);
            $error = 'Antrean belum dapat dimuat. Silakan coba kembali.';
        }

        return view('template/dashboard-light-aside', [
            'reviewFilters' => $filters,
            'currentYear' => $currentYear,
            'contents' => view('Modules\Ur\Views\v_dataeklaimreview', $data + compact('filters', 'categories', 'error')),
        ]);
    }

    public function reviewtrend()
    {
        $hospital = (string) $this->session->get('koders');
        if ($hospital === '') {
            return response()->setStatusCode(403)->setJSON(['message' => 'Sesi organisasi belum tersedia.']);
        }

        $year = request()->getGet('year');
        $service = request()->getGet('service') ?? '';
        if (!is_string($year) || !preg_match('/^[0-9]{4}$/D', $year) || (int) $year < 2014
            || (int) $year > (int) date('Y') || !in_array($service, ['', '1', '2'], true)) {
            return response()->setStatusCode(422)->setJSON(['message' => 'Filter tren tidak valid.']);
        }

        try {
            $months = (new InternalReviewModel())->trend(
                $hospital, (string) $this->session->get('kodeppk'), (int) $year, $service
            );
            return response()->setJSON(['months' => $months, 'next' => '']);
        } catch (\Throwable $e) {
            log_message('error', 'Tren review E-Klaim gagal dimuat ({type}, kode {code}).', [
                'type' => get_class($e), 'code' => $e->getCode(),
            ]);
            return response()->setStatusCode(503)->setJSON(['message' => 'Tren belum dapat dimuat.']);
        }
    }

    public function dataraweklaim()
    {
        $draw = request()->getPost('draw');
        $draw = is_scalar($draw) && ctype_digit((string) $draw) ? (int) $draw : 0;
        try {
            $koders = $this->session->get('koders');
            if (!$koders) return response()->setStatusCode(403)->setJSON(['draw' => $draw, 'responseDesc' => 'Sesi organisasi belum tersedia. Silakan masuk kembali.']);
            $year = request()->getPost('selectperiode');
            $service = request()->getPost('service') ?? '';
            if (!is_string($service) || !in_array($service, ['', '1', '2'], true)) throw new \InvalidArgumentException('Jenis layanan tidak valid.');
            $month = request()->getPost('month') ?? '';
            if (!is_string($month) || ($month !== '' && !preg_match('/^(?:[1-9]|1[0-2])$/D', $month))) throw new \InvalidArgumentException('Pilih bulan yang valid.');
            $start = request()->getPost('start') ?? '0';
            $length = request()->getPost('length') ?? '10';
            $search = request()->getPost('search');
            $term = is_array($search) ? ($search['value'] ?? '') : '';
            if (!is_string($year) || !preg_match('/^[0-9]{4}$/D', $year) || (int) $year < 2014 || (int) $year > (int) date('Y')) throw new \InvalidArgumentException('Pilih periode tahun yang valid.');
            if (!is_scalar($start) || !ctype_digit((string) $start) || (int) $start > 10000000 || !is_scalar($length) || !ctype_digit((string) $length) || !in_array((int) $length, [10, 25, 50, 100], true)) throw new \InvalidArgumentException('Parameter halaman tidak valid.');
            if (!is_string($term) || mb_strlen($term) > 200) throw new \InvalidArgumentException('Pencarian maksimal 200 karakter.');
            $result = $this->md->page((string) $koders, (int) $year, (int) $start, (int) $length, trim($term), request()->getPost('refreshSummary') === '1', $service, (int) $month);
            return response()->setJSON(['draw' => $draw, 'responseCode' => '00'] + $result);
        } catch (\InvalidArgumentException $e) {
            return response()->setStatusCode(422)->setJSON(['draw' => $draw, 'responseDesc' => $e->getMessage()]);
        } catch (\Throwable $e) {
            log_message('error', 'Data E-Klaim: {message}', ['message' => $e->getMessage()]);
            return response()->setStatusCode(503)->setJSON(['draw' => $draw, 'responseDesc' => 'Data belum dapat dimuat dari database. Silakan muat ulang.']);
        }
    }
}
