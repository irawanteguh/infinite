<?php

namespace Modules\Farmasi\Controllers;

use App\Controllers\BaseController;
use Modules\Farmasi\Models\MasterobatModel;

class Masterobat extends BaseController{
    protected $md;

    public function __construct(){
        $this->md = new MasterobatModel();
    }

    public function index(){
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Farmasi\Views\v_masterobat')
        ]);
    }

    public function masterobat(){
        $result = $this->md->masterobat();

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