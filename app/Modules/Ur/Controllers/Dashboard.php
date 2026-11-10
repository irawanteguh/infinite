<?php

namespace Modules\Ur\Controllers;

use App\Controllers\BaseController;
use Modules\Ur\Models\DashboardModel;

class Dashboard extends BaseController{

    protected $md;

    public function __construct(){
        parent::__construct();
        $this->md = new DashboardModel();
    }

    public function index(){
        $data = $this->loadcombobox();

        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Ur\Views\v_dashboard',$data)
        ]);
    }

    public function dataraweklaim(){
        $periode = request()->getPost('selectperiode');
        if (!is_scalar($periode) || !preg_match('/^\d{4}$/', (string) $periode)
            || (int) $periode < 2014 || (int) $periode > (int) date('Y')) {
            return response()->setStatusCode(422)->setJSON([
                'responseCode' => '01', 'responseDesc' => 'Periode tahun tidak valid.', 'responseResult' => []
            ]);
        }
        if (!$this->session->get('koders')) {
            return response()->setStatusCode(403)->setJSON([
                'responseCode' => '01', 'responseDesc' => 'Rumah sakit pada sesi belum dipilih.', 'responseResult' => []
            ]);
        }
        $periode = (int) $periode;
        try {
            $result = $this->md->dataraweklaim($this->session->get('koders'), $periode);
        } catch (\Throwable $e) {
            log_message('error', 'UR dashboard aggregate query failed.');
            return response()->setStatusCode(500)->setJSON([
                'responseCode' => '01', 'responseDesc' => 'Ringkasan gagal dimuat. Silakan coba kembali.', 'responseResult' => []
            ]);
        }

        if (!empty($result)) {
            $json["responseCode"]   = "00";
            $json["responseHead"]   = "success";
            $json["responseDesc"]   = "Data found";
            $json["responseResult"] = $result;
        } else {
            $json["responseCode"]   = "01";
            $json["responseHead"]   = "info";
            $json["responseDesc"]   = "No data found";
        }

        return response()->setJSON($json);
    }

    private function loadcombobox(){
        $resultPeriode = $this->md->periode();

        $data['periode'] = '';
        foreach ($resultPeriode as $row) {
            $data['periode'] .= '<option value="' . $row->periode . '">' . $row->periode . '</option>';
        }

        return $data;
    }

}