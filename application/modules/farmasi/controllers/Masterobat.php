<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Masterobat extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modelmasterobat","md");
        }
        
        public function index(){
            $this->template->load("template/dashboard-light-aside","v_masterobat");
        }

        public function datamasterobat(){
            $result = $this->md->datamasterobat($_SESSION['groupid'],$_SESSION['orgid']);
            
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