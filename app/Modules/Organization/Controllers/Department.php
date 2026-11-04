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
        $data = $this->loadcombobox();
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Organization\Views\v_department', $data)
        ]);
    }

    private function loadcombobox(){
        $resultdatamasteruser     = $this->md->datamasteruser(session()->get('groupid'), session()->get('orgid'));

        $data['datamasteruser'] = '';
        foreach ($resultdatamasteruser as $row) {
            $data['datamasteruser'] .= '<option value="' . $row->user_id . '">' . $row->name . '</option>';
        }

        return $data;
    }

    public function datamasterdepartment(){
        $result = $this->md->datamasterdepartment(session()->get('groupid'), session()->get('orgid'));

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

    public function updatedepartment(){
        $departmentid = request()->getPost('departmentid');
        $active       = request()->getPost('active');

        if (empty($departmentid)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Department ID is required.'
            ]);
        }

        $dataupdate['active'] = $active;

        if ($this->md->updatemasterdepartment($departmentid, $dataupdate)) {
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = $active == "1" ? "Department has been activated successfully." : "Department has been deactivated successfully.";
        } else {
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to update Department activation status.";
        }

        return response()->setJSON($json);
    }

    public function addmasterdepartment(){
        $departmentid = generateuuid();
        $userid       = request()->getPost("modal_add_department_headdepartment");

        $datainsert = [
            'GROUP_ID'       => session()->get('groupid'),
            'ORG_ID'         => session()->get('orgid'),
            'DEPARTMENT_ID'  => $departmentid,
            'DEPARTMENT'     => request()->getPost("modal_add_department_department"),
            'USER_ID'        => $userid,
            'CREATED_BY'     => session()->get('userid'),
            'LAST_UPDATE_BY' => session()->get('userid')
        ];

        if($this->md->addmasterdepartment($datainsert)){

            $dataupdateuser['department_id'] = $departmentid;
            $this->md->updatemasteruser($userid, $dataupdateuser);

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

    public function editmasterdepartment(){
        $departmentid = request()->getPost("modal_edit_masterdepartment_departmentid");
        $userid       = request()->getPost("modal_edit_masterdepartment_headdepartment");

        $dataupdate = [
            'DEPARTMENT'                => request()->getPost("modal_edit_masterdepartment_department"),
            'USER_ID'                   => $userid,
            'LAST_UPDATE_BY'            => session()->get('userid'),
            'LAST_UPDATE_DATE'          => date('Y-m-d H:i:s')
        ];

        if($this->md->updatemasterdepartment($departmentid, $dataupdate)){

            $dataupdateuser['department_id'] = $departmentid;
            $this->md->updatemasteruser($userid, $dataupdateuser);

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