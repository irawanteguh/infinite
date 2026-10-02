<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;
use Modules\Developer\Models\OrganizationModel;

class Organization extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new OrganizationModel();
    }

    public function index(){
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Developer\Views\v_organization')
        ]);
    }

    public function masterorganization(){
        $result = $this->md->masterorganization();

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

    public function updateActivation(){
        $orgid  = request()->getPost('orgid');
        $active = request()->getPost('active');

        if (empty($orgid)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Organization ID is required.'
            ]);
        }

        $dataupdate['active'] = $active;

        if ($this->md->updatemasterorganization($orgid, $dataupdate)) {
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = $active == "1"
                ? "Organization has been activated successfully."
                : "Organization has been deactivated successfully.";
        } else {
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to update organization activation status.";
        }

        return response()->setJSON($json);
    }
}