<?php

namespace Modules\Organization\Controllers;

use App\Controllers\BaseController;
use Modules\Organization\Models\AppindikatorModel;

class Appindikator extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new AppindikatorModel();
    }

    public function index(){
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Organization\Views\v_appindikator')
        ]);
    }

    public function datamasterindikator(){
        $result = $this->md->datamasterindikator(session()->get('groupid'), session()->get('orgid'));

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

    public function updateapproval(){
        $transaksiid = request()->getPost('transaksiid');
        $status      = request()->getPost('status');

        $dataupdate['status_id']     = $status;
        $dataupdate['approval_by']   = session()->get('userid');
        $dataupdate['approval_date'] = date('Y-m-d H:i:s');

        if($this->md->updateindikator($transaksiid,$dataupdate)){
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = $status == "1" ? "Indicator has been approved successfully." : "Indicator has been declined successfully.";
        }else{
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to update Indicator status.";
        }

        return response()->setJSON($json);
    }

    

}