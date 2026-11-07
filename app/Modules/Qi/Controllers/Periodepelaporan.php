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
        $data['teamusers'] = $this->md->datauser(session()->get('groupid'), session()->get('orgid'));
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Qi\Views\v_periodepelaporan', $data),
            'teamusers' => $data['teamusers']
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
            $json['responseCode']="00";
            $json['responseHead']="success";
            $json['responseDesc']="Data Updated Successfully";
        }else{
            $json['responseCode']="01";
            $json['responseHead']="info";
            $json['responseDesc']="Data failed to update";
        }
        

        return response()->setJSON($json);
    }

    public function editperiode(){

        $id = request()->getPost("modal_edit_periodepelaporan_id");

        $dataupdate = [
            'TAHUN'          => request()->getPost("modal_edit_periodepelaporan_periode"),
            'STATUS_ID'      => request()->getPost("modal_edit_periodepelaporan_status"),
            'USER_ID'      => request()->getPost("modal_edit_periodepelaporan_user"),
            'LAST_UPDATE_BY' => session()->get('userid')
        ];

        if($this->md->updatemasterperiode($id, $dataupdate)){
            $json['responseCode'] = "00";
            $json['responseHead'] = "success";
            $json['responseDesc'] = "Data Updated Successfully";
        }else{
            $json['responseCode'] = "01";
            $json['responseHead'] = "info";
            $json['responseDesc'] = "Data failed to update";
        }

        return response()->setJSON($json);
    }

    public function datateam(){
        $periodeid = request()->getPost('periode_id');
        if (!$this->md->periodeExists($periodeid, session()->get('groupid'), session()->get('orgid'))) {
            return response()->setJSON(['responseCode' => '01', 'responseDesc' => 'Periode tidak ditemukan.']);
        }

        return response()->setJSON([
            'responseCode' => '00',
            'responseResult' => $this->md->teamMembers($periodeid, session()->get('groupid'), session()->get('orgid'))
        ]);
    }

    public function updateteam(){
        $periodeid = request()->getPost('periode_id');
        if (!$this->md->periodeExists($periodeid, session()->get('groupid'), session()->get('orgid'))) {
            return response()->setJSON(['responseCode' => '01', 'responseHead' => 'error', 'responseDesc' => 'Periode tidak ditemukan.']);
        }

        $members = request()->getPost('members') ?? [];
        if (!is_array($members)) {
            return response()->setJSON(['responseCode' => '01', 'responseHead' => 'error', 'responseDesc' => 'Format data anggota tidak valid.']);
        }
        $validUsers = array_map(static function ($user) { return (string) $user->user_id; }, $this->md->datauser(session()->get('groupid'), session()->get('orgid')));
        $members = array_values(array_unique(array_filter(array_map('strval', $members), static function ($id) use ($validUsers) {
            return in_array($id, $validUsers, true);
        })));

        if ($this->md->replaceTeamMembers(
            $periodeid,
            $members,
            session()->get('userid'),
            session()->get('groupid'),
            session()->get('orgid')
        )) {
            return response()->setJSON(['responseCode' => '00', 'responseHead' => 'success', 'responseDesc' => 'Quality Team Members berhasil diperbarui.']);
        }

        return response()->setJSON(['responseCode' => '01', 'responseHead' => 'error', 'responseDesc' => 'Quality Team Members gagal diperbarui.']);
    }

}
