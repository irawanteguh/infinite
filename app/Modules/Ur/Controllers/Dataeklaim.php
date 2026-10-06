<?php

namespace Modules\Ur\Controllers;

use App\Controllers\BaseController;
use Modules\Ur\Models\DataeklaimModel;

class Dataeklaim extends BaseController{

    protected $md;

    public function __construct(){
        parent::__construct();
        $this->md = new DataeklaimModel();
    }

    public function index(){
        $data = $this->loadcombobox();

        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Ur\Views\v_dataeklaim',$data)
        ]);
    }

    public function dataraweklaim(){
        $periode  = request()->getPost('selectperiode');

        $result = $this->md->dataraweklaim($this->session->get('koders'),$periode);

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