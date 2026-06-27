<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Routingsystem_hook
{
    public function run(): void
    {
        if (!class_exists('Routingsystem', false)) {
            $app = &get_instance();
            $app->load->library('routingsystem');
        }

        Routingsystem::system();
    }
}
