<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Users extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modelusers","md");
        }
        
        public function index(){
            $this->template->load("template/dashboard-light-aside","v_users");
        }

        public function adduser(){
            $userid       = generateuuid();
            $username     = $this->input->post("username-add");
            $nikrs        = $this->input->post("nikrs-add");
            $namakaryawan = $this->input->post("namakaryawan-add");
            $namaktp      = $this->input->post("namaktp-add");
            $noktp        = $this->input->post("noktp-add");
            $email        = $this->input->post("email-add");
            $file         = (object)@$_FILES['avataradd'];

            $config['upload_path']      = './assets/images/avatars/';
			$config['allowed_types']    = 'jpeg';
			$config['file_ext_tolower'] = TRUE;
			$config['file_name']        = $userid;
			$config['overwrite']        = TRUE;

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('avataradd')){
                $error_message = strip_tags($this->upload->display_errors());
				log_message('error', 'File upload error: ' . $error_message);

				$json['responDesc'] = $error_message;
            }else{
                $uploadData = $this->upload->data();
				$full_path = $uploadData['full_path'];

				$ext = strtolower(pathinfo($uploadData['file_name'], PATHINFO_EXTENSION));
				if ($ext !== 'jpeg') {
					unlink($full_path);
					$json['responDesc'] = "Hanya file .jpeg yang diizinkan!";
					echo json_encode($json);
					return;
				}

				$image = @imagecreatefromjpeg($full_path);
				if (!$image) {
					$image = @imagecreatefrompng($full_path);
				}

				if ($image) {
					$rgb_image = imagecreatetruecolor(imagesx($image), imagesy($image));
					imagecopy($rgb_image, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
					imagejpeg($rgb_image, $full_path, 95);
					imagedestroy($image);
					imagedestroy($rgb_image);
				} else {
					unlink($full_path);
					$json['responDesc'] = "File tidak valid atau rusak. Pastikan format JPEG RGB 8bit.";
					echo json_encode($json);
					return;
				}
            }

            $datainsert = [
                'group_id'   => $_SESSION['groupid'] ?? null,
                'org_id'     => $_SESSION['orgid'] ?? null,
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