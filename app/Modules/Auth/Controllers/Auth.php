<?php

namespace Modules\Auth\Controllers;

use CodeIgniter\Controller;

class Auth extends Controller
{
    public function index()
    {
        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Auth\Views\v_sign')
        ]);
    }
}