<?php

namespace Modules\Qi\Controllers;

use App\Controllers\BaseController;
use Modules\Qi\Models\MasterjenisindikatorModel;

class Masterjenisindikator extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new MasterjenisindikatorModel();
    }

    public function index(){
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Qi\Views\v_masterjenisindikator')
        ]);
    }

    public function datamasterjenisindikator(){
        $result = $this->md->datamasterjenisindikator(session()->get('groupid'), session()->get('orgid'));

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

    public function updatejenisindikator(){
        $jenisindikatorid  = request()->getPost('jenisindikatorid');
        $active = request()->getPost('active');

        if (empty($jenisindikatorid)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Category Indikator ID is required.'
            ]);
        }

        $dataupdate['active'] = $active;

        if ($this->md->updatemasterjenisindikator($jenisindikatorid, $dataupdate)) {
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = $active == "1" ? "Indicator Category has been activated successfully." : "Indicator Category has been deactivated successfully.";
        } else {
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to update Indicator Category activation status.";
        }

        return response()->setJSON($json);
    }

}