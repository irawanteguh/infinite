<?php

namespace Modules\Ur\Controllers;

use App\Controllers\BaseController;
use Modules\Ur\Models\CasemixindexModel;

class Casemixindex extends BaseController{

    protected $md;

    public function __construct(){
        parent::__construct();
        $this->md = new CasemixindexModel();
    }

    public function index(){
        $data = $this->loadcombobox();

        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Ur\Views\v_casemixindex',$data)
        ]);
    }

    private function loadcombobox(){
        $resultPeriode = $this->md->periode();

        $data['periode'] = '';
        foreach ($resultPeriode as $row) {
            $data['periode'] .= '<option value="' . $row->periode . '">' . $row->periode . '</option>';
        }

        return $data;
    }

    public function datakelompokkasus(){
        $periode  = request()->getPost('selectperiode');

        $result = $this->md->datakelompokkasus($this->session->get('koders'),$periode);

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

    public function datacmg(){
        $periode  = request()->getPost('selectperiode');

        $result = $this->md->datacmg($this->session->get('koders'),$periode);

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

    public function dataseveritylevel(){
        $periode  = request()->getPost('selectperiode');

        $result = $this->md->dataseveritylevel($this->session->get('koders'),$periode);

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

}