<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Users extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modelusers","md");
        }
        
        public function index(){
            $data = $this->loadcombobox();
            $this->template->load("template/dashboard-light-aside","v_users",$data);
        }

        public function loadcombobox(){
            $resultdatadataorganization = $this->md->dataorganization();
            
            $masterorganization="";
            foreach($resultdatadataorganization as $a ){
                $masterorganization.= "<option value='".$a->org_id."' data-groupid='".$a->header_id."'>".$a->org_name."</option>";
            }

            $data['masterorganization']  = $masterorganization;
            
            return $data;
		}

        public function adduser(){
            $userid       = generateuuid();
            $username     = $this->input->post("modal_add_user_username");
            $nikrs        = $this->input->post("modal_add_user_nikrs");
            $namakaryawan = $this->input->post("modal_add_user_name");
            $email        = $this->input->post("modal_add_user_email");
            $orgid        = $this->input->post("modal_add_user_orgid");
            $groupid      = $this->input->post("modal_add_user_groupid");

            if(isset($_FILES['modal_add_user_avatar']) && $_FILES['modal_add_user_avatar']['error'] != 4){

                $config['upload_path']      = './assets/media/avatars/';
                $config['allowed_types']    = 'jpg|jpeg';
                $config['file_ext_tolower'] = TRUE;
                $config['file_name']        = $userid.".jpg";
                $config['overwrite']        = TRUE;

                $this->load->library('upload');
                $this->upload->initialize($config);

                if(!$this->upload->do_upload('modal_add_user_avatar')){

                    $json['responCode'] = "01";
                    $json['responHead'] = "error";
                    $json['responDesc'] = strip_tags($this->upload->display_errors());

                    echo json_encode($json);
                    return;

                }else{

                    $uploadData = $this->upload->data();
                    $full_path  = $uploadData['full_path'];

                    $image = @imagecreatefromjpeg($full_path);

                    if($image){

                        $rgb = imagecreatetruecolor(imagesx($image), imagesy($image));
                        imagecopy($rgb, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                        imagejpeg($rgb, $full_path, 95);

                        imagedestroy($image);
                        imagedestroy($rgb);

                    }else{

                        @unlink($full_path);

                        $json['responCode'] = "01";
                        $json['responHead'] = "error";
                        $json['responDesc'] = "File tidak valid.";

                        echo json_encode($json);
                        return;
                    }
                }
            }

            $datainsert = [
                'group_id'   => $groupid ?? null,
                'org_id'     => $orgid ?? null,
                'user_id'    => $userid ?? null,
                'username'   => $username ?? null,
                'nik'        => !empty($nikrs) ? $nikrs : null,
                'name'       => $namakaryawan ?? null,
                'email'      => $email ?? null,
                'created_by' => $_SESSION['userid'] ?? null
            ];

            $resultcheckemail = $this->md->checkemail($userid,$email);

            if(empty($resultcheckemail)){
                if($this->md->insertuser($datainsert)){
                    $json['responCode']="00";
                    $json['responHead']="success";
                    $json['responDesc']="Data Updated Successfully";
                }else{
                    $json['responCode']="01";
                    $json['responHead']="info";
                    $json['responDesc']="Data failed to update";
                }
            }else{
                $json['responCode'] = "01";
                $json['responHead'] = "info";
                $json['responDesc'] = "Email is already in use";
            }
            

            echo json_encode($json);
        }

        public function edituser(){
            $userid       = $this->input->post("modal_edit_user_userid");
            $username     = $this->input->post("modal_edit_user_username");
            $nikrs        = $this->input->post("modal_edit_user_nikrs");
            $namakaryawan = $this->input->post("modal_edit_user_name");
            $email        = $this->input->post("modal_edit_user_email");

            if(isset($_FILES['modal_edit_user_avatar']) && $_FILES['modal_edit_user_avatar']['error'] != 4){

                $config['upload_path']      = './assets/media/avatars/';
                $config['allowed_types']    = 'jpg|jpeg';
                $config['file_ext_tolower'] = TRUE;
                $config['file_name']        = $userid.".jpg";
                $config['overwrite']        = TRUE;

                $this->load->library('upload');
                $this->upload->initialize($config);

                if(!$this->upload->do_upload('modal_edit_user_avatar')){

                    $json['responCode'] = "01";
                    $json['responHead'] = "error";
                    $json['responDesc'] = strip_tags($this->upload->display_errors());

                    echo json_encode($json);
                    return;

                }else{

                    $uploadData = $this->upload->data();
                    $full_path  = $uploadData['full_path'];

                    $image = @imagecreatefromjpeg($full_path);

                    if($image){

                        $rgb = imagecreatetruecolor(imagesx($image), imagesy($image));
                        imagecopy($rgb, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                        imagejpeg($rgb, $full_path, 95);

                        imagedestroy($image);
                        imagedestroy($rgb);

                    }else{

                        @unlink($full_path);

                        $json['responCode'] = "01";
                        $json['responHead'] = "error";
                        $json['responDesc'] = "File tidak valid.";

                        echo json_encode($json);
                        return;
                    }
                }
            }

            $dataupdate = [
                'username'   => $username,
                'nik'        => !empty($nikrs) ? $nikrs : null,
                'name'       => $namakaryawan,
                'email'      => $email
            ];

            $resultcheckemail = $this->md->checkemail($userid,$email);

            if(empty($resultcheckemail)){
                if($this->md->updateuser($userid,$dataupdate)){
                    $json['responCode'] = "00";
                    $json['responHead'] = "success";
                    $json['responDesc'] = "Data Updated Successfully";
                }else{
                    $json['responCode'] = "01";
                    $json['responHead'] = "info";
                    $json['responDesc'] = "Data failed to update";
                }
            }else{
                $json['responCode'] = "01";
                $json['responHead'] = "info";
                $json['responDesc'] = "Email is already in use";
            }

            echo json_encode($json);
        }

        public function datausers(){
            $result = $this->md->datausers();
            
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