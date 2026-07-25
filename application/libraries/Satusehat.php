<?php
    class Satusehat{

        public static function oauth(){
            $body   = array("client_id"=>CLIENTID_SATUSEHAT,"client_secret"=>SECRETID_SATUSEHAT);
            $header = array("Content-Type: application/x-www-form-urlencoded");
           
            $responsecurl = curl([
                'url'     => OAUTHURL_SATUSEHAT."/accesstoken?grant_type=client_credentials",
                'method'  => "POST",
                'header'  => $header,
                'body'    => http_build_query($body),
                'savelog' => false,
                'source'  => "SATUSEHAT_TOKEN"
            ]);

            $responsecurl = json_decode($responsecurl,TRUE);
            return $responsecurl;
        }

        public static function getallproductkfa($type,$keyword){
            $oauthResponse = Satusehat::oauth();

            if (!isset($oauthResponse['access_token'])) {
                return is_array($oauthResponse) ? $oauthResponse : json_decode($oauthResponse, true);
            }

            $header = array("Content-Type: application/json","Authorization: Bearer " . $oauthResponse['access_token']);
           
            $responsecurl = curl([
                'url'     => BASEURL_SATUSEHAT."/kfa-v2/products/all?page=1&size=100&product_type=".$type."&keyword=".$keyword,
                'method'  => "GET",
                'header'  => $header,
                'body'    => "",
                'savelog' => false,
                'source'  => "SATUSEHAT_GET_PRODUCT_KFA"
            ]);

            $responsecurl = json_decode($responsecurl,TRUE);
            return $responsecurl;
        }
    }

?>