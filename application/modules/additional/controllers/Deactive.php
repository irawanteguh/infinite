<?php
    defined("BASEPATH") OR exit("No direct script access allowed");
    class Deactive extends MX_Controller{ 
        public function index(){
            $this->template->load("template/dashboard-light-blank","v_deactive");
        }
    }
?>