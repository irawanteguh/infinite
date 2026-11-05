<?php

namespace Modules\Qi\Controllers;

use App\Controllers\BaseController;
use Modules\Qi\Models\IndikatorunitModel;

class Indikatorunit extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new IndikatorunitModel();
    }

    public function index(){
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Qi\Views\v_indikatorunit')
        ]);
    }

    public function dataindikatorunit(){
        $result = $this->md->dataindikatorunit(session()->get('groupid'), session()->get('orgid'), session()->get('userid'));

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