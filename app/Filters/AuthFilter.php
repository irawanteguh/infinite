<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface{

    public function before(RequestInterface $request, $arguments = null){
        $session = session();

        if (!$session->get('loggedin')) {
            return redirect()->to(site_url('/'));
        }

        return $request;
    }

    public function after(RequestInterface $request,ResponseInterface $response,$arguments = null){
        // Tidak ada proses setelah request.
    }
}