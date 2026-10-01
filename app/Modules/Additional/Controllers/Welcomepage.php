<?php

namespace Modules\Additional\Controllers;

use App\Controllers\BaseController;

class Welcomepage extends BaseController
{
    protected $session;

    public function __construct()
    {
        $this->session = session();
    }

    public function index()
    {
        // Pastikan user sudah login
        if (!$this->session->get('loggedin')) {
            return redirect()->to(site_url('/'));
        }

        $data = [
            'name'    => $this->session->get('name'),
            'email'   => $this->session->get('email'),
            'orgname' => $this->session->get('orgname'),
        ];

        return view(
            'template/dashboard-light-aside',
            [
                'contents' => view(
                    'Modules\Additional\Views\v_welcomepage',
                    $data
                )
            ]
        );
    }
}