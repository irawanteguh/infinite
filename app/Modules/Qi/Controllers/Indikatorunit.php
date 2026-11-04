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

}