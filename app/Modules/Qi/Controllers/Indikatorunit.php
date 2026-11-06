<?php

namespace Modules\Qi\Controllers;

use App\Controllers\BaseController;
use Modules\Qi\Models\IndikatorunitModel;

class Indikatorunit extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new IndikatorunitModel();
    }

    public function index(){
        $uuid = request()->getGet('uuid');
        $data = $this->loadcombobox();

        if(!empty($uuid)){
            $data['uuid'] = $uuid;

            return view('template/dashboard-light-aside', [
                'contents' => view('Modules\Qi\Views\v_indikatorunitsubmit', $data)
            ]);
        }else{
            return view('template/dashboard-light-aside', [
                'contents' => view('Modules\Qi\Views\v_indikatorunit', $data)
            ]);
        }
    }

    private function loadcombobox(){
        $resultdatamasterperiodepelaporan = $this->md->datamasterperiodepelaporan(session()->get('groupid'), session()->get('orgid'));
        $resultdatamasterdepartment       = $this->md->datamasterdepartment(session()->get('groupid'), session()->get('orgid'), session()->get('userid'));
        $resultdatauser                   = $this->md->datauser(session()->get('groupid'), session()->get('orgid'), session()->get('departmentid'));
        $resultdatamasterindikator        = $this->md->datamasterindikator(session()->get('groupid'), session()->get('orgid'));

        $data['masterperiodepelaporan'] = '';
        foreach ($resultdatamasterperiodepelaporan as $row) {
            $data['masterperiodepelaporan'] .= '<option value="' . $row->periode_id . '">' . $row->tahun . '</option>';
        }

        $data['masterdatadepartment'] = '';
        foreach ($resultdatamasterdepartment as $row) {
            $data['masterdatadepartment'] .= '<option value="' . $row->department_id . '">' . $row->department . '</option>';
        }

        $data['masterdatauser'] = '';
        foreach ($resultdatauser as $row) {
            $data['masterdatauser'] .= '<option value="' . $row->user_id . '">' . $row->name . '</option>';
        }

        $data['masterindikator'] = '';
        foreach ($resultdatamasterindikator as $row) {
            $data['masterindikator'] .= '<option value="' . $row->indikator_id . '">' . $row->indikator . '</option>';
        }

        return $data;
    }

    public function addindikatorunit(){
        $periode      = request()->getPost("modal_add_pengajuanindikatorunit_periodeid");
        $userid       = request()->getPost("modal_add_pengajuanindikatorunit_userid");
        $target       = request()->getPost("modal_add_pengajuanindikatorunit_target");
        $indikatorid  = request()->getPost("modal_add_pengajuanindikatorunit_indikatorid");
        $departmentid = request()->getPost("modal_add_pengajuanindikatorunit_departmentid");

        $datainsert = [
            'group_id'      => $_SESSION['groupid'] ?? null,
            'org_id'        => $_SESSION['orgid'] ?? null,
            'periode_id'    => $periode ?? null,
            'pic'           => $userid ?? null,
            'target'        => $target ?? null,
            'indikator_id'  => $indikatorid ?? null,
            'department_id' => $departmentid ?? null,
            'created_by'    => $_SESSION['userid'] ?? null
        ];


        if($this->md->insertindikatorunit($datainsert)){
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

    public function updateindikatorunit(){
        $uuid         = request()->getPost("modal_edit_pengajuanindikatorunit_uuid");
        $periode      = request()->getPost("modal_edit_pengajuanindikatorunit_periodeid");
        $userid       = request()->getPost("modal_edit_pengajuanindikatorunit_userid");
        $target       = request()->getPost("modal_edit_pengajuanindikatorunit_target");
        $indikatorid  = request()->getPost("modal_edit_pengajuanindikatorunit_indikatorid");
        $departmentid = request()->getPost("modal_edit_pengajuanindikatorunit_departmentid");

        $dataupdate = [
            'periode_id'       => $periode ?? null,
            'pic'              => $userid ?? null,
            'target'           => $target ?? null,
            'indikator_id'     => $indikatorid ?? null,
            'department_id'    => $departmentid ?? null,
            'last_update_by'   => $_SESSION['userid'] ?? null,
            'last_update_date' => date('Y-m-d H:i:s')
        ];

        if ($this->md->updateindikator($uuid, $dataupdate)) {
            $json['responCode'] = "00";
            $json['responHead'] = "success";
            $json['responDesc'] = "Data Updated Successfully";
        } else {
            $json['responCode'] = "01";
            $json['responHead'] = "info";
            $json['responDesc'] = "Data failed to update";
        }

        return response()->setJSON($json);
    }

    public function dataindikatorunit(){
        $result = $this->md->dataindikatorunit(session()->get('groupid'), session()->get('orgid'), session()->get('userid'));

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

    public function datateam(){
        $result = $this->md->datateam(session()->get('groupid'), session()->get('orgid'), session()->get('userid'));

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

    public function deleteindikator(){
        $transaksiid = request()->getPost('transaksiid');

        $dataupdate['active']           = "0";
        $dataupdate['last_update_by']   = session()->get('userid');
        $dataupdate['last_update_date'] = date('Y-m-d H:i:s');

        if($this->md->updateindikator($transaksiid,$dataupdate)){
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = "Indicator has been deleted successfully.";
        }else{
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to deleted Indicator.";
        }

        return response()->setJSON($json);
    }

}