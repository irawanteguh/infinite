<?php

namespace Modules\Ur\Controllers;

use App\Controllers\BaseController;
use Modules\Ur\Models\Severity1Model;

class Severity1 extends BaseController
{
    protected $md;

    public function __construct()
    {
        parent::__construct();
        $this->md = new Severity1Model();
    }

    public function index()
    {
        $periode = '';
        foreach ($this->md->periode() as $row) {
            $year = (int) $row->periode;
            $periode .= '<option value="' . $year . '">' . $year . '</option>';
        }
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Ur\Views\v_severity1', ['periode' => $periode])
        ]);
    }

    private function dataResponse($method)
    {
        $year = request()->getPost('selectperiode');
        if (!is_scalar($year) || !preg_match('/^\d{4}$/', (string) $year)
            || (int) $year < 2014 || (int) $year > (int) date('Y')) {
            return response()->setStatusCode(422)->setJSON([
                'responseCode' => '01', 'responseDesc' => 'Periode tahun tidak valid.', 'responseResult' => []
            ]);
        }
        $koders = $this->session->get('koders');
        if (!$koders) {
            return response()->setStatusCode(403)->setJSON([
                'responseCode' => '01', 'responseDesc' => 'Rumah sakit pada sesi belum dipilih.', 'responseResult' => []
            ]);
        }
        try {
            $result = $this->md->$method($koders, (int) $year);
            return response()->setJSON([
                'responseCode' => '00', 'responseHead' => 'success',
                'responseDesc' => 'Data berhasil dimuat.', 'responseResult' => $result
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'UR severity I aggregate query failed.');
            return response()->setStatusCode(500)->setJSON([
                'responseCode' => '01', 'responseDesc' => 'Data severity I gagal dimuat. Silakan coba kembali.', 'responseResult' => []
            ]);
        }
    }

    public function summary() { return $this->dataResponse('summary'); }
    public function rawdata() { return $this->dataResponse('rawdata'); }
}
