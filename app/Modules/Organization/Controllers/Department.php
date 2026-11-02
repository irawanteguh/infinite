<?php

namespace Modules\Organization\Controllers;

use App\Controllers\BaseController;
use Modules\Organization\Models\DepartmentModel;

class Department extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new DepartmentModel();
    }

    public function index(){
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Organization\Views\v_department')
        ]);
    }

    public function datamasterusers(){
        $result = $this->md->datamasterusers(session()->get('groupid'), session()->get('orgid'));

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

    public function activation(){
        $userid = request()->getPost('userid');
        $active = request()->getPost('active');

        $dataupdate = ['suspend'   => $active];

        if($this->md->updateuser($userid,$dataupdate)){
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = $active == "1" ? "User has been activated successfully." : "User has been deactivated successfully.";
        }else{
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to update User activation status.";
        }

        return response()->setJSON($json);
    }

    public function deleteuser(){
        $userid = request()->getPost('userid');
        $active = request()->getPost('active');

        $dataupdate = ['active'   => $active];

        if($this->md->updateuser($userid,$dataupdate)){
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = $active == "1" ? "User has been deleted successfully." : "User has been deleted successfully.";
        }else{
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to update User deleted status.";
        }

        return response()->setJSON($json);
    }

}