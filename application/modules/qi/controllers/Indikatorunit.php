<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Indikatorunit extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modelindikatorunit","md");
        }
        
        public function index(){
            $this->template->load("template/dashboard-light-aside","v_indikatorunit");
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

    }
?>