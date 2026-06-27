<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Template{
        protected $CI;
        protected $template_data = [];

        public function __construct(){
            $this->CI =& get_instance();
        }

        public function set($name, $value){
            $this->template_data[$name] = $value;
        }

        public function load($template = '', $view = '', $view_data = [], $return = FALSE){
            $this->set('contents',$this->CI->load->view($view, $view_data, TRUE));
            return $this->CI->load->view($template,$this->template_data,$return);
        }
    }
?>