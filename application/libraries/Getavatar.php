<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Getavatar{
    public function get($userid = null){
        $avatarDir = FCPATH . 'assets/media/avatars/';
        $avatarUrl = base_url('assets/media/avatars/');

        // Misal nama file avatar adalah USERID.jpg
        if (!empty($userid)) {

            $extensions = ['jpg', 'jpeg', 'png'];

            foreach ($extensions as $ext) {
                $filename = $userid . '.' . $ext;

                if (file_exists($avatarDir . $filename)) {
                    return $avatarUrl . $filename;
                }
            }
        }

        // Jika avatar tidak ditemukan, ambil random
        $files = glob($avatarDir . '*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);

        if (!empty($files)) {
            return $avatarUrl . basename($files[array_rand($files)]);
        }

        // Fallback
        return $avatarUrl . 'default.jpg';
    }
}