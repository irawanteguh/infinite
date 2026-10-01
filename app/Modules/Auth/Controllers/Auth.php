<?php

namespace Modules\Auth\Controllers;

use CodeIgniter\Controller;
use Modules\Auth\Models\SignModel;

class Auth extends Controller
{
    protected $session;
    protected $md;

    public function __construct()
    {
        $this->session = session();
        $this->md      = new SignModel();
    }

    public function index()
    {
        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Auth\Views\v_sign')
        ]);
    }

    public function signin()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = encodedata($this->request->getPost('password'));

        if ($username === '' || $password === '') {
            return $this->response->setJSON([
                'responCode' => '01',
                'responHead' => 'error',
                'responDesc' => 'Username dan password wajib diisi'
            ]);
        }

        $checkauth = $this->md->login($username, $password);

        if (!empty($checkauth)) {

            if ($checkauth->active === '0') {

                return $this->response->setJSON([
                    'responCode' => '02',
                    'responHead' => 'failed',
                    'responDesc' => 'Your account is deactivated',
                    'url'        => site_url('additional/deactive')
                ]);

            }

            $datasession = $this->md->datasession($checkauth->user_id);

            if (empty($datasession)) {
                return $this->response->setJSON([
                    'responCode' => '01',
                    'responHead' => 'error',
                    'responDesc' => 'Data session user tidak ditemukan'
                ]);
            }

            $sessiondata = [
                'groupid'  => $datasession->group_id,
                'orgid'    => $datasession->org_id,
                'orgname'  => $datasession->organizationname,
                'website'  => $datasession->website,
                'address'  => $datasession->address,
                'emailorg' => $datasession->organizationemail,
                'pimpinan' => $datasession->pimpinan,
                'userid'   => $datasession->user_id,
                'name'     => $datasession->name,
                'email'    => $datasession->email,
                'loggedin' => true,
                'timeout'  => false
            ];

            $this->session->set($sessiondata);

            return $this->response->setJSON([
                'responCode' => '00',
                'responHead' => 'success',
                'responDesc' => 'Hey, ' . $datasession->name . '<br>Welcome Back and Have a nice day',
                'url'        => site_url('additional/welcomepage')
            ]);
        }

        return $this->response->setJSON([
            'responCode' => '01',
            'responHead' => 'error',
            'responDesc' => 'Username dan/atau password tidak sesuai'
        ]);
    }


    public function logoutsystem()
    {
        $this->session->destroy();

        return redirect()->to(site_url('/'));
    }


}
