<?php

namespace Modules\Additional\Controllers;

use App\Controllers\BaseController;
use Modules\Additional\Models\ViewMDCModel;

class ViewMDC extends BaseController{

    protected $md;

    public function __construct(){
        parent::__construct();
        $this->md = new ViewMDCModel();
    }

    public function index(){
        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Additional\Views\v_viewmdc')
        ]);
    }

    public function datamastermdc(){
        $result = $this->md->datamastermdc();

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