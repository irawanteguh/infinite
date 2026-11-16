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

        $segments = $request->getUri()->getSegments();
        $package = (string) ($segments[0] ?? '');
        $controller = (string) ($segments[1] ?? '');
        if (\App\Libraries\ModuleAccess::isWelcome($package,$controller)) return $request;
        try {
            $model = new \Modules\Hr\Models\AccessModel();
            $state = $model->state((string)$session->get('groupid'),(string)$session->get('orgid'),(string)$session->get('userid'));
            $modules = (new \App\Models\Modelrouting())->allMenus();
            if (\App\Libraries\ModuleAccess::routeAllowed($package,$controller,$modules,$state)) return $request;
        } catch (\Throwable $e) {
            log_message('error','Module permission lookup failed: {message}',['message'=>$e->getMessage()]);
            return service('response')->setStatusCode(503)->setBody('Hak akses belum dapat diperiksa. Silakan coba kembali.');
        }
        if ($request->isAJAX() || strpos($request->getHeaderLine('Accept'),'application/json') !== false) {
            return service('response')->setStatusCode(403)->setJSON(['responseCode'=>'01','responseDesc'=>'Akses modul belum diberikan. Hubungi pengelola akses organisasi.']);
        }
        return redirect()->to(site_url('additional/welcomepage'));

    }

    public function after(RequestInterface $request,ResponseInterface $response,$arguments = null){
        // Tidak ada proses setelah request.
    }
}