<?php

namespace Modules\Klaim\Controllers;

use App\Controllers\BaseController;
use Modules\Klaim\Models\PendingModel;

class Pending extends BaseController{

    protected $md;

    public function __construct(){
        parent::__construct();
        $this->md = new PendingModel();
    }

    public function index(){
        $data = $this->loadcombobox();

        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Klaim\Views\v_pending',$data)
        ]);
    }

    public function dataraweklaimdetail(){
        $periode = request()->getPost('selectperiode');
        $result  = $this->md->dataraweklaimdetail($this->session->get('kodeppk'),$periode);

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

    public function dataraweklaim(){
        $periode = request()->getPost('selectperiode');
        $result  = $this->md->dataraweklaim($this->session->get('kodeppk'),$periode);

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

    public function dataalgoritmatype(){
        $periode = request()->getPost('selectperiode');
        $result  = $this->md->dataalgoritmatype($this->session->get('kodeppk'),$periode);

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

    public function dataalgoritmabreakdown(){
        $periode = request()->getPost('selectperiode');
        $result  = $this->md->dataalgoritmabreakdown($this->session->get('kodeppk'),$periode);

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