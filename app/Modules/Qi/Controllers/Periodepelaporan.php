<?php

namespace Modules\Qi\Controllers;

use App\Controllers\BaseController;
use Modules\Qi\Models\PeriodepelaporanModel;

class Periodepelaporan extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new PeriodepelaporanModel();
    }

    public function index(){
        $data = $this->loadcombobox();
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Qi\Views\v_periodepelaporan', $data)
        ]);
    }

    private function loadcombobox(){
        $resultdatauser     = $this->md->datauser(session()->get('groupid'), session()->get('orgid'));

        $data['masterdatauser'] = '';
        foreach ($resultdatauser as $row) {
            $data['masterdatauser'] .= '<option value="' . $row->user_id . '">' . $row->name . '</option>';
        }

        return $data;
    }

    public function datamasterperiodepelaporan(){
        $result = $this->md->datamasterperiodepelaporan(session()->get('groupid'), session()->get('orgid'));

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

    public function addperiode(){

        $dataupdate = [
            'GROUP_ID'       => session()->get('groupid'),
            'ORG_ID'         => session()->get('orgid'),
            'TAHUN'          => request()->getPost("modal_add_periodepelaporan_periode"),
            'USER_ID'        => request()->getPost("modal_add_periodepelaporan_user"),
            'CREATED_BY'     => session()->get('userid'),
            'LAST_UPDATE_BY' => session()->get('userid')
        ];

        if($this->md->insertmasterperiode($dataupdate)){
            $json['responCode']="00";
            $json['responHead']="success";
            $json['responDesc']="Data Updated Successfully";
        }else{
            $json['responCode']="01";
            $json['responHead']="info";
            $json['responDesc']="Data failed to update";
        }
        

        return response()->setJSON($json);
    }

}