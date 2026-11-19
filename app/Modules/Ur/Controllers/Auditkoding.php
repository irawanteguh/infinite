<?php

namespace Modules\Ur\Controllers;

use App\Controllers\BaseController;
use Modules\Ur\Models\AuditkodingModel;

class Auditkoding extends BaseController
{
    protected $md;

    public function __construct()
    {
        parent::__construct();
        $this->md = new AuditkodingModel();
    }

    public function index()
    {
        return $this->renderPage('v_auditkoding');
    }

    public function detail()
    {
        return $this->renderPage('v_auditkodingdetail');
    }

    private function renderPage(string $view)
    {
        $requestedYear = request()->getGet('year');
        $selectedYear = $this->validYear($requestedYear) ? (int) $requestedYear : (int) date('Y');
        $requestedMonth = request()->getGet('month');
        $selectedMonth = is_string($requestedMonth) && preg_match('/^(?:[1-9]|1[0-2])$/', $requestedMonth)
            ? (int) $requestedMonth : 0;
        $periode = '';
        foreach ($this->md->periode() as $row) {
            $year = (int) $row->periode;
            $selected = $year === $selectedYear ? ' selected' : '';
            $periode .= '<option value="' . $year . '"' . $selected . '>' . $year . '</option>';
        }
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\\Ur\\Views\\' . $view, [
                'periode' => $periode,
                'selectedMonth' => $selectedMonth,
            ]),
        ]);
    }

    public function summary()
    {
        return $this->dataResponse('caseSummary');
    }

    public function cases()
    {
        return $this->dataResponse('cases');
    }

    private function validYear($year): bool
    {
        return is_scalar($year) && preg_match('/^\d{4}$/', (string) $year)
            && (int) $year >= 2014 && (int) $year <= (int) date('Y');
    }

    private function dataResponse(string $method)
    {
        $year = request()->getPost('selectperiode');
        if (!$this->validYear($year)) {
            return response()->setStatusCode(422)->setJSON(['responseCode' => '01', 'responseDesc' => 'Periode tahun tidak valid.', 'responseResult' => []]);
        }
        $koders = $this->session->get('koders');
        if (!$koders) {
            return response()->setStatusCode(403)->setJSON(['responseCode' => '01', 'responseDesc' => 'Rumah sakit pada sesi belum dipilih.', 'responseResult' => []]);
        }
        try {
            return response()->setJSON(['responseCode' => '00', 'responseHead' => 'success', 'responseDesc' => 'Data berhasil dimuat.', 'responseResult' => $this->md->$method($koders, (int) $year)]);
        } catch (\Throwable $e) {
            log_message('error', 'UR LOS data query failed.');
            return response()->setStatusCode(500)->setJSON(['responseCode' => '01', 'responseDesc' => 'Data auditkoding gagal dimuat. Silakan coba kembali.', 'responseResult' => []]);
        }
    }
    public function indicators()
    {
        $year = request()->getPost('selectperiode');
        if (!is_scalar($year) || !preg_match('/^\d{4}$/', (string) $year)
            || (int) $year < 2014 || (int) $year > (int) date('Y')) {
            return response()->setStatusCode(422)->setJSON(['responseCode' => '01', 'responseDesc' => 'Periode tahun tidak valid.', 'responseResult' => []]);
        }
        $koders = $this->session->get('koders');
        if (!$koders) {
            return response()->setStatusCode(403)->setJSON(['responseCode' => '01', 'responseDesc' => 'Rumah sakit pada sesi belum dipilih.', 'responseResult' => []]);
        }
        try {
            return response()->setJSON(['responseCode' => '00', 'responseHead' => 'success', 'responseDesc' => 'Data berhasil dimuat.', 'responseResult' => $this->md->summary($koders, (int) $year), 'comparison' => $this->md->comparison($koders, (int) $year), 'pendingRules' => $this->md->pendingRules($koders, (int) $year)]);
        } catch (\Throwable $e) {
            log_message('error', 'UR auditkoding summary query failed.');
            return response()->setStatusCode(500)->setJSON(['responseCode' => '01', 'responseDesc' => 'Data auditkoding gagal dimuat. Silakan coba kembali.', 'responseResult' => []]);
        }
    }

    public function sequencingDetails()
    {
        $year = request()->getPost('selectperiode');
        $care = request()->getPost('care') ?? '';
        if (!is_scalar($year) || !preg_match('/^\d{4}$/', (string) $year)
            || (int) $year < 2014 || (int) $year > (int) date('Y')
            || !in_array($care, ['', '1', '2'], true)) {
            return response()->setStatusCode(422)->setJSON(['responseDesc' => 'Filter tidak valid.']);
        }
        $koders = $this->session->get('koders');
        if (!$koders) { return response()->setStatusCode(403)->setJSON(['responseDesc' => 'Sesi rumah sakit tidak tersedia.']); }
        try {
            return response()->setJSON(['responseResult' => $this->md->sequencingDetails($koders, (int) $year, $care)]);
        } catch (\Throwable $e) {
            log_message('error', 'Audit sequencing detail query failed.');
            return response()->setStatusCode(500)->setJSON(['responseDesc' => 'Detail kasus gagal dimuat.']);
        }
    }
}
