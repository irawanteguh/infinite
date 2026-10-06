<?php

namespace Modules\Ur\Controllers;

use App\Controllers\BaseController;
use Modules\Ur\Models\DataeklaimModel;

class Severity3 extends BaseController{

    protected $md;

    public function __construct(){
        parent::__construct();
        $this->md = new DataeklaimModel();
    }

    public function index(){
        $data = $this->loadcombobox();

        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Ur\Views\v_severity3',$data)
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

}