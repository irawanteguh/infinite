<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Sign extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modelsign","md");   
        }
        
        public function index(){
            $this->template->load("template/dashboard-light-blank","v_sign");
        }

        public function signin(){
            $username = trim((string) $this->input->post("username", true));
            $password = encodedata($this->input->post("password"));

            if ($username === "" || $password === "") {
                return $this->jsonResponse(array(
                    "responCode" => "01",
                    "responHead" => "error",
                    "responDesc" => "Username dan password wajib diisi"
                ));
            }

            $checkauth = $this->md->login($username, $password);
            
            if (!empty($checkauth)) {
                $datasession = $this->md->datasession($checkauth->user_id);

                if (empty($datasession)) {
                    return $this->jsonResponse(array(
                        "responCode" => "01",
                        "responHead" => "error",
                        "responDesc" => "Data session user tidak ditemukan"
                    ));
                }
        
                $sessiondata = array(
                    "groupid"  => $datasession->group_id,
                    "orgid"    => $datasession->org_id,
                    "userid"   => $datasession->user_id,
                    "name"     => $datasession->name,
                    "email"    => $datasession->email,
                    "loggedin" => true,
                    "timeout"  => false
                );
        
                $this->session->set_userdata($sessiondata);
        
                $json["responCode"] = "00";
                $json["responHead"] = "success";
                $json["responDesc"] = "Hey, ".$datasession->name."<br>Welcome Back and Have a nice day";
                $json["url"]        = site_url("additional/welcome");
            } else {
                $json["responCode"] = "01";
                $json["responHead"] = "error";
                $json["responDesc"] = "Username dan/atau password tidak sesuai";
            }
        
            return $this->jsonResponse($json);
        }

        private function jsonResponse(array $data){
            return $this->output
                ->set_content_type("application/json")
                ->set_output(json_encode($data));
        }

    }
?>
