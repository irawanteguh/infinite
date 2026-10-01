<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;

class Testingpage extends BaseController{
    public function index()
    {
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Developer\Views\v_testingpage')
        ]);
    }
}