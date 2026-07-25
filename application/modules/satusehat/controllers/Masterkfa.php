<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Masterkfa extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
        }
        
        public function index(){
            $this->template->load("template/dashboard-light-aside","v_masterkfa");
        }

        public function getallproductkfa(){
            $type    = $this->input->post("type");
            $keyword = $this->input->post("keyword");

            $resultgetallproductkfa = Satusehat::getallproductkfa($type,$keyword);

            $json["responCode"]   = "00";
            $json["responHead"]   = "success";
            $json["responDesc"]   = "Data found successfully";
            $json['responResult'] = $resultgetallproductkfa['items']['data'];
            
            echo json_encode($json);

        }

    }
?>