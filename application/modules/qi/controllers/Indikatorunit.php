<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Indikatorunit extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modelindikatorunit","md");
        }
        
        public function index(){
            $uuid = $this->input->get('uuid', TRUE);
            $data = $this->loadcombobox();

            if(!empty($uuid)){
                $data['uuid'] = $uuid;

                $this->template->load("template/dashboard-light-aside","v_indikatorunitsubmit",$data);
            }else{
                $this->template->load("template/dashboard-light-aside","v_indikatorunit",$data);
            }
        }

        public function loadcombobox(){
            $resultdatamasterindikator        = $this->md->datamasterindikator($_SESSION['groupid'],$_SESSION['orgid']);
            $resultdatamasterperiodepelaporan = $this->md->datamasterperiodepelaporan($_SESSION['groupid'],$_SESSION['orgid']);
            $resultdatauser                   = $this->md->datauser($_SESSION['groupid'],$_SESSION['orgid']);
            $resultdatamasterdepartment       = $this->md->datamasterdepartment($_SESSION['groupid'],$_SESSION['orgid'],$_SESSION['userid']);

            $masterindikator="";
            foreach($resultdatamasterindikator as $a ){
                $masterindikator.="<option value='".$a->indikator_id."'>".$a->indikator."</option>";
            }

            $masterperiodepelaporan="";
            foreach($resultdatamasterperiodepelaporan as $a ){
                $masterperiodepelaporan.="<option value='".$a->periode_id."'>".$a->tahun."</option>";
            }

            $masterdatauser="";
            foreach($resultdatauser as $a ){
                $masterdatauser.="<option value='".$a->user_id."'>".$a->name."</option>";
            }

            $masterdatadepartment="";
            foreach($resultdatamasterdepartment as $a ){
                $masterdatadepartment.="<option value='".$a->department_id."'>".$a->department."</option>";
            }

            $data['masterindikator']        = $masterindikator;
            $data['masterperiodepelaporan'] = $masterperiodepelaporan;
            $data['masterdatauser']         = $masterdatauser;
            $data['masterdatadepartment']   = $masterdatadepartment;
            
            return $data;
		}

        public function dataindikatorunit(){
            $result = $this->md->dataindikatorunit($_SESSION['groupid'],$_SESSION['orgid'],$_SESSION['userid']);
            
			if(!empty($result)){
                $json["responCode"]="00";
                $json["responHead"]="success";
                $json["responDesc"]="Data found successfully";
				$json['responResult']=$result;
            }else{
                $json["responCode"]="01";
                $json["responHead"]="info";
                $json["responDesc"]="No data found";
            }

            echo json_encode($json);
        }

        public function datateam(){
            $result = $this->md->datateam($_SESSION['groupid'],$_SESSION['orgid'],$_SESSION['userid']);
            
			if(!empty($result)){
                $json["responCode"]="00";
                $json["responHead"]="success";
                $json["responDesc"]="Data found successfully";
				$json['responResult']=$result;
            }else{
                $json["responCode"]="01";
                $json["responHead"]="info";
                $json["responDesc"]="No data found";
            }

            echo json_encode($json);
        }
        
        public function addindikatorunit(){
            $periode      = $this->input->post("modal_add_pengajuanindikatorunit_periodeid");
            $userid       = $this->input->post("modal_add_pengajuanindikatorunit_userid");
            $target       = $this->input->post("modal_add_pengajuanindikatorunit_target");
            $indikatorid  = $this->input->post("modal_add_pengajuanindikatorunit_indikatorid");
            $departmentid = $this->input->post("modal_add_pengajuanindikatorunit_departmentid");

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
            

            echo json_encode($json);
        }

        public function inputnilaiindikator(){
            $indikator   = $this->input->post("modal_input_nilai_indikator_indikatorid");
            $bulan       = $this->input->post("modal_input_nilai_indikator_bulan");
            $numerator   = $this->input->post("modal_add_pengajuanindikatorunit_numerator");
            $denumerator = $this->input->post("modal_add_pengajuanindikatorunit_denumerator");
            $alasan      = $this->input->post("modal_add_pengajuanindikatorunit_alasan");
            $rtl         = $this->input->post("modal_add_pengajuanindikatorunit_rtl");

            $datainsert = [
                'group_id'     => $_SESSION['groupid'] ?? null,
                'org_id'       => $_SESSION['orgid'] ?? null,
                'referensi_id' => $indikator ?? null,
                'bulan'        => $bulan ?? null,
                'numerator'    => $numerator ?? 0,
                'denumerator'  => $numerator ?? 0,
                'reason'       => $alasan ?? null,
                'rtl'          => $rtl ?? null,
                'created_by'   => $_SESSION['userid'] ?? null
            ];


            if($this->md->insertnilaiindikator($datainsert)){
                $json['responCode']="00";
                $json['responHead']="success";
                $json['responDesc']="Data Updated Successfully";
            }else{
                $json['responCode']="01";
                $json['responHead']="info";
                $json['responDesc']="Data failed to update";
            }
            

            echo json_encode($json);
        }

        public function activation(){
            $transaksiid = $this->input->post("transaksiid");
            $active      = $this->input->post("active");

            $dataupdate['active'] = "0";

            if($this->md->updateindikatorunit($transaksiid,$dataupdate)){
                $json['responCode'] = "00";
                $json['responHead'] = "success";
                $json['responDesc'] = "Data Updated Successfully";
            }else{
                $json['responCode'] = "01";
                $json['responHead'] = "info";
                $json['responDesc'] = "Data failed to update";
            }

            echo json_encode($json);
        }

    }
?>