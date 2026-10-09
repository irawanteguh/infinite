<?php

namespace Modules\Ics\Controllers;

use App\Controllers\BaseController;
use Modules\Ics\Models\MastermdcModel;

class Mastermdc extends BaseController{

    protected $md;

    public function __construct(){
        parent::__construct();
        $this->md = new MastermdcModel();
    }

    public function index(){
        $data = $this->loadcombobox();
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Ics\Views\v_mastermdcdetail',$data)
        ]);
    }

    private function loadcombobox(){
        $resultdatamastersourcedocument = $this->md->datamastersourcedocument();

        $data['masterdocument'] = '';
        foreach ($resultdatamastersourcedocument as $row) {
            $data['masterdocument'] .= '<option value="' . $row->id . '">' . $row->title . '</option>';
        }

        return $data;
    }

    public function datamastermdc(){
        $result = $this->md->datamastermdc();

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

    public function datamastersection(){
        $result = $this->md->datamastersection();

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

    public function addmastermdc(){
        $data['document_id']            = request()->getPost('DOCUMENT_ID');
        $data['mdc_code']               = request()->getPost('MDC_CODE');
        $data['name_id']                = request()->getPost('NAME_ID');
        $data['name_en']                = request()->getPost('NAME_EN');
        $data['chapter_no']             = request()->getPost('CHAPTER_NO');
        $data['page_start']             = request()->getPost('PAGE_START');
        $data['page_end']               = request()->getPost('PAGE_END');
        $data['EXPLANATION'] = request()->getPost('PERHATIAN_KHUSUS_INPUT');
        $data['EXAMPLE']  = request()->getPost('CONTOH_KASUS_ICD');
        $data['created_by']             = $this->session->get('userid');

        if($this->md->insertmastermdc($data)){
            $json['responCode']="00";
            $json['responHead']="success";
            $json['responDesc']="Data Added Successfully";
        } else {
            $json['responCode']="01";
            $json['responHead']="info";
            $json['responDesc']="Data Failed to Add";
        }

        return response()->setJSON($json);
    }

    public function updatemastermdc(){
        $id = request()->getPost('ID');

        if(empty($id)){
            $json['responCode']="01";
            $json['responHead']="info";
            $json['responDesc']="ID is required";

            return response()->setJSON($json);
        }

        $data['document_id']            = request()->getPost('DOCUMENT_ID');
        $data['mdc_code']               = request()->getPost('MDC_CODE');
        $data['name_id']                = request()->getPost('NAME_ID');
        $data['name_en']                = request()->getPost('NAME_EN');
        $data['chapter_no']             = request()->getPost('CHAPTER_NO');
        $data['page_start']             = request()->getPost('PAGE_START');
        $data['page_end']               = request()->getPost('PAGE_END');
        $data['EXPLANATION']            = request()->getPost('PERHATIAN_KHUSUS_INPUT');
        $data['EXAMPLE']                = request()->getPost('CONTOH_KASUS_ICD');
        $data['updated_by']             = $this->session->get('userid');

        if($this->md->updatemastermcd($id, $data)){
            $json['responCode']="00";
            $json['responHead']="success";
            $json['responDesc']="Data Updated Successfully";
        } else {
            $json['responCode']="01";
            $json['responHead']="info";
            $json['responDesc']="Data Failed to Update";
        }

        return response()->setJSON($json);
    }

}