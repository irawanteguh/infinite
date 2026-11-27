<?php
namespace Modules\RestAPI\Controllers;

use App\Controllers\BaseController;

class Environment extends BaseController
{
    use \Modules\RestAPI\Libraries\WorkspaceStorage;

    public function index()
    {
        if (!session()->get('loggedin')) return redirect()->to(site_url('auth/login'));
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\RestAPI\Views\v_environment'),
        ]);
    }
}
