<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Routingsystem_hook{
        protected static $appInstance;
        public static $environmentSettings;

        public function run(): void{
            self::loadEnvironment();
            Routingsystem::system();
        }

        public static function loadEnvironment(){
            self::$appInstance = get_instance();
            self::$appInstance->load->model("Modelrouting");

            $groupid = "a6fae73f-cd2a-4602-b34e-325f6f953f44";
            $orgid   = "61bee2c0-6339-4201-a3f3-b6b726ce9e37";
            
            self::$environmentSettings = self::$appInstance->Modelrouting->environment($groupid,$orgid);

            if(!empty(self::$environmentSettings)){
                foreach(self::$environmentSettings as $setting){
                    if(!defined($setting['ENVIRONMENT_NAME'])){
                        define($setting['ENVIRONMENT_NAME'], $setting['VALUE']);
                    }
                }
            }else{
                log_message('error', 'No environment settings found for the specified parameters.');
            }
        }
    }
?>