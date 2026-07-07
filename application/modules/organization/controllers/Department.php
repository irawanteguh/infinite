<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Department extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modeldepartment","md");
        }
        
        public function index(){
            $data = $this->loadcombobox();
            $this->template->load("template/dashboard-light-aside","v_department",$data);
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
            $department = $this->input->post("modal_add_department_department");
            $userid     = $this->input->post("modal_add_department_headdepartment");

            $datainsert = [
                'group_id'   => $_SESSION['groupid'] ?? null,
                'org_id'     => $_SESSION['orgid'] ?? null,
                'department' => $department ?? null,
                'user_id'    => $userid ?? null,
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

        public function activation(){
            $departmentid = $this->input->post("departmentid");
            $active = $this->input->post("active");

            $dataupdate = [
                'active'   => $active
            ];

            if($this->md->updatedepartment($departmentid,$dataupdate)){
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