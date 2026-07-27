<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Organization extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modelorganization","md");
        }
        
        public function index(){
            $data = $this->loadcombobox();
            $this->template->load("template/dashboard-light-aside","v_organization",$data);
        }

        public function loadcombobox(){
            $resultdatadataorganization = $this->md->dataorganization();
            
            $masterorganization="";
            foreach($resultdatadataorganization as $a ){
                $masterorganization.="<option value='".$a->org_id."'>".$a->org_name."</option>";
            }

            $data['masterorganization']  = $masterorganization;
            
            return $data;
		}

        public function dataorganization(){
            $result = $this->md->dataorganization();
            
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

        public function addorganization(){
            $organization = trim($this->input->post("modal_add_organization_name"));
            $headerid     = $this->input->post("modal_add_organization_header");
            $type         = $this->input->post("modal_add_organization_type");
            $website      = trim($this->input->post("modal_add_organization_website"));
            $email        = trim($this->input->post("modal_add_organization_email"));
            $address      = trim($this->input->post("modal_add_organization_address"));

            if ($type == "H") {
                $headerid = null;
            }

            $datainsert = [
                'org_name'   => $organization,
                'holding'    => $type,
                'website'    => $website,
                'email'      => $email,
                'address'    => $address,
                'created_by' => $_SESSION['userid'] ?? null
            ];


            if($this->md->insertorganization($datainsert)){
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