<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Department extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modeldepartment","md");
        }
        
        public function index(){
            $this->template->load("template/dashboard-light-aside","v_department");
        }

        public function datadepartment(){
            $result = $this->md->datadepartment($_SESSION['groupid'],$_SESSION['orgid']);
            
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


        public function adddepartment(){
            $department     = $this->input->post("department-add");

            $datainsert = [
                'group_id'   => $_SESSION['groupid'] ?? null,
                'org_id'     => $_SESSION['orgid'] ?? null,
                'department' => $department ?? null,
                'created_by' => $_SESSION['userid'] ?? null
            ];


            if($this->md->insertdepartment($datainsert)){
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