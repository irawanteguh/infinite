<?php

namespace Modules\Klaim\Controllers;

use App\Controllers\BaseController;
use Modules\Klaim\Models\PendingModel;

class Pending extends BaseController{

    protected $md;

    public function __construct(){
        parent::__construct();
        $this->md = new PendingModel();
    }

    public function index(){
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Klaim\Views\v_pending')
        ]);
    }

}