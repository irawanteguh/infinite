<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Masterindikator extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modelmasterindikator","md");
        }
        
        public function index(){
            $data = $this->loadcombobox();
            $this->template->load("template/dashboard-light-aside","v_masterindikator",$data);
        }

        public function loadcombobox(){
            $resultdatamastersatuan        = $this->md->datamastersatuan($_SESSION['groupid'],$_SESSION['orgid']);
            
            $mastersatuan="";
            foreach($resultdatamastersatuan as $a ){
                $mastersatuan.="<option value='".$a->satuan_id."'>".$a->keterangan."</option>";
            }


            $data['mastersatuan'] = $mastersatuan;
            
            return $data;
		}

        public function dataindikator(){
            $result = $this->md->dataindikator($_SESSION['groupid'],$_SESSION['orgid']);
            
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

    }
?>