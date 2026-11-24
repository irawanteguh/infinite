<?php

namespace Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Libraries\UserActivity;
use CodeIgniter\Security\Exceptions\SecurityException;
use Modules\Auth\Models\SignModel;

class Auth extends BaseController{
    protected $md;

    public function __construct(){
        parent::__construct();
        $this->md = new SignModel();
    }

    public function index(){
        if ($this->session->get('loggedin')) {
            return redirect()->to(site_url('additional/welcomepage'));
        }

        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Auth\Views\v_landing')
        ]);
    }

    public function login(){
        if ($this->session->get('loggedin')) {
            return redirect()->to(site_url('additional/welcomepage'));
        }

        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Auth\Views\v_sign')
        ]);
    }

    public function signin(){
        $username = $this->request->getPost('username');
        $username = is_string($username) ? trim($username) : '';
        $password = $this->request->getPost('password');

        if ($username === '' || !is_string($password) || $password === '') {
            return $this->response->setJSON([
                'responseCode' => '01',
                'responseHead' => 'error',
                'responseDesc' => 'Username dan password wajib diisi'
            ]);
        }

        $checkauth = $this->md->login($username, $password);

        if (!empty($checkauth)) {

            if ($checkauth->active === '0') {
                return $this->response->setJSON([
                    'responseCode' => '02',
                    'responseHead' => 'failed',
                    'responseDesc' => 'Your account is deactivated',
                    'url'        => site_url('additional/deactive')
                ]);
            }

            $datasession = $this->md->datasession($checkauth->user_id);

            if (empty($datasession)) {
                return $this->response->setJSON([
                    'responseCode' => '01',
                    'responseHead' => 'error',
                    'responseDesc' => 'Data session user tidak ditemukan'
                ]);
            }

            $sessiondata = [
                'groupid'      => $datasession->group_id,
                'orgid'        => $datasession->org_id,
                'orgname'      => $datasession->organizationname,
                'website'      => $datasession->website,
                'departmentid' => $datasession->department_id,
                'address'      => $datasession->address,
                'trial'        => $datasession->trial,
                'koders'       => $datasession->kode_rs,
                'kodeppk'      => $datasession->kode_ppk,
                'emailorg'     => $datasession->organizationemail,
                'pimpinan'     => $datasession->pimpinan,
                'userid'       => $datasession->user_id,
                'name'         => $datasession->name,
                'email'        => $datasession->email,
                'loggedin'     => true,
                'idle_session_key' => bin2hex(random_bytes(16)),
                'timeout'      => false
            ];

            $this->session->set($sessiondata);
            UserActivity::record($this->request, 'login', 200);

            return $this->response->setJSON([
                'responseCode' => '00',
                'responseHead' => 'success',
                'responseDesc' => 'Hey, ' . $datasession->name . '<br>Welcome Back and Have a nice day',
                'url'        => site_url('additional/welcomepage')
            ]);
        }

        return $this->response->setJSON([
            'responseCode' => '01',
            'responseHead' => 'error',
            'responseDesc' => 'Username dan/atau password tidak sesuai'
        ]);
    }

    public function changepassword()
    {
        $request = request();
        $response = response()->setHeader('Cache-Control', 'no-store');
        $reply = static function (int $status, string $message) use ($response) {
            return $response->setStatusCode($status)->setJSON([
                'message' => $message,
                'csrfHash' => csrf_hash(),
            ]);
        };

        if (strtoupper($request->getMethod()) !== 'POST') {
            return $reply(405, 'Metode permintaan tidak diizinkan.');
        }
        $scope = [
            'group_id' => (string) $this->session->get('groupid'),
            'org_id' => (string) $this->session->get('orgid'),
            'user_id' => (string) $this->session->get('userid'),
        ];
        if (!$this->session->get('loggedin') || in_array('', $scope, true)) {
            return $reply(401, 'Sesi berakhir. Silakan login kembali.');
        }
        try {
            service('security')->verify($request);
        } catch (SecurityException $e) {
            return $reply(403, 'Sesi formulir berakhir. Silakan coba kembali.');
        }

        $current = $request->getPost('current_password');
        $new = $request->getPost('new_password');
        $confirmation = $request->getPost('confirm_password');
        if (!is_string($current) || $current === '' || strlen($current) > 1000) {
            return $reply(422, 'Masukkan password lama yang valid.');
        }
        if (!is_string($new) || !SignModel::meetsPasswordPolicy($new)) {
            return $reply(422, 'Password baru harus terdiri dari minimal ' . SignModel::PASSWORD_MIN_LENGTH
                . ' karakter, maksimal ' . SignModel::PASSWORD_MAX_BYTES . ' byte, serta memuat huruf besar, huruf kecil, angka, dan simbol.');
        }
        if ($new !== $confirmation) {
            return $reply(422, 'Konfirmasi password baru tidak cocok.');
        }
        if ($current === $new) {
            return $reply(422, 'Gunakan password baru yang berbeda.');
        }

        try {
            if (!$this->md->changeOwnPassword($scope, $current, $new)) {
                return $reply(422, 'Password lama tidak cocok atau akun sudah berubah.');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Ganti password gagal ({type}).', ['type' => get_class($e)]);
            return $reply(503, 'Password belum dapat disimpan. Silakan coba kembali.');
        }

        return $reply(200, 'Password berhasil diganti.');
    }

    public function logoutsystem(){
        UserActivity::record($this->request, 'logout', 302);
        $this->session->destroy();
        return redirect()->to(site_url('/'));
    }

}
