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
        $periode = '';
        foreach ($this->md->periode() as $row) {
            $year = (int) $row->periode;
            $periode .= '<option value="' . $year . '">' . $year . '</option>';
        }
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Ur\Views\v_auditkoding', ['periode' => $periode]),
        ]);
    }

    public function summary()
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
