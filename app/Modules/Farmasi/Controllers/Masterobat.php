<?php

namespace Modules\Farmasi\Controllers;

use App\Controllers\BaseController;

class Masterobat extends BaseController
{
    public function index()
    {
        return view('Modules\Farmasi\Views\v_masterobat');
    }
}
