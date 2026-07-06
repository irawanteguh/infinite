<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Periodepelaporan extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modelperiodepelaporan","md");
        }
        
        public function index(){
            $data = $this->loadcombobox();
            $this->template->load("template/dashboard-light-aside","v_periodepelaporan",$data);
        }

        public function loadcombobox(){
            $resultdatauser = $this->md->datauser($_SESSION['groupid'],$_SESSION['orgid']);
            
            $masterdatauser="";
            foreach($resultdatauser as $a ){
                $masterdatauser.="<option value='".$a->user_id."'>".$a->name."</option>";
            }

            $data['masterdatauser']  = $masterdatauser;
            
            return $data;
		}

        public function dataperiodepelaporan(){
            $result = $this->md->dataperiodepelaporan($_SESSION['groupid'],$_SESSION['orgid']);
            
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

        public function addperiode(){
            $periode = $this->input->post("modal_add_periodepelaporan_periode");
            $userid  = $this->input->post("modal_add_periodepelaporan_user");

            $datainsert = [
                'group_id'   => $_SESSION['groupid'] ?? null,
                'org_id'     => $_SESSION['orgid'] ?? null,
                'tahun'      => $periode ?? null,
                'user_id'    => $userid ?? null,
                'created_by' => $_SESSION['userid'] ?? null
            ];


            if($this->md->insertperiode($datainsert)){
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

    }
?>