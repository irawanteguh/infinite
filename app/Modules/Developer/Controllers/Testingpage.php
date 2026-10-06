<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;
use Modules\Developer\Models\TestingpageModel;

class Testingpage extends BaseController{

    protected $md;

    public function __construct(){
        parent::__construct();
        $this->md = new TestingpageModel();
    }

    public function index(){
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Developer\Views\v_testingpage')
        ]);
    }

}