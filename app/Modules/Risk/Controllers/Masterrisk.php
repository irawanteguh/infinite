<?php

namespace Modules\Risk\Controllers;

use App\Controllers\BaseController;
use Modules\Risk\Models\RegisterModel;
use Modules\Risk\Models\MasterriskModel;
use Modules\Risk\Models\RiskMatrixModel;

class Masterrisk extends BaseController
{
    private function authorize(): void
    {
        if (!session()->get('loggedin') || !session()->get('groupid') || !session()->get('orgid') || !session()->get('userid')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    public function index()
    {
        $this->authorize();
        $model = new MasterriskModel();
        $ready = $model->ready();
        $matrix = new RiskMatrixModel();
        $departments = (new RegisterModel())->departments(session()->get('groupid'), session()->get('orgid'));
        $categoriesReady = $model->categoriesReady();
        return view('template/dashboard-light-aside', [
            'riskMasterModal' => ['departments' => $departments, 'ready' => $ready, 'categoriesReady' => $categoriesReady],
            'contents' => view('Modules\\Risk\\Views\\v_masterrisk', [
                'matrixReady' => $matrix->ready(),
                'matrixCells' => $matrix->ready() ? $matrix->cells(session()->get('groupid'), session()->get('orgid')) : [],
                'categoriesReady' => $categoriesReady,
                'categories' => $model->categoriesReady() ? $model->categories(session()->get('groupid'), session()->get('orgid')) : [],
                'codes' => $ready ? $model->codes(session()->get('groupid'), session()->get('orgid')) : [],
                'departments' => $departments,
                'ready' => $ready,
            ]),
        ]);
    }

    public function save()
    {
        $this->authorize();
        if (strtolower(request()->getMethod()) !== 'post') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        service('security')->verify(request());
        $model = new MasterriskModel();
        $idInput = request()->getPost('code_id');
        $id = is_scalar($idInput) ? trim((string) $idInput) : '';
        $data = [];
        foreach (['code', 'department_id'] as $key) {
            $value = request()->getPost($key);
            $data[$key] = is_scalar($value) ? trim((string) $value) : '';
        }
        $data['code'] = strtoupper($data['code']);
        $error = '';
        if (!$model->ready()) {
            $error = 'Tabel master kode risiko belum tersedia.';
        } elseif ($id !== '' && !$model->code(session()->get('groupid'), session()->get('orgid'), $id)) {
            $error = 'Kode risiko tidak ditemukan.';
        } elseif (!preg_match('/^[A-Z0-9][A-Z0-9._\/-]{0,59}$/D', $data['code'])) {
            $error = 'Kode maksimal 60 karakter: huruf, angka, titik, garis miring, garis bawah atau tanda hubung.';
        } else {
            $units = (new RegisterModel())->departments(session()->get('groupid'), session()->get('orgid'));
            if (!in_array($data['department_id'], array_column($units, 'department_id'), true)) {
                $error = 'Pilih department yang tersedia.';
            } elseif ($model->exists(session()->get('groupid'), session()->get('orgid'), $data['code'], $id)) {
                $error = 'Kode risiko sudah terdaftar pada organisasi ini.';
            }
        }
        if ($error === '') {
            try {
                if ($id !== '') {
                    $saved = $model->updateCode(session()->get('groupid'), session()->get('orgid'), $id, $data);
                } else {
                    $data += ['code_id' => bin2hex(random_bytes(16)), 'group_id' => session()->get('groupid'), 'org_id' => session()->get('orgid'),
                        'created_by' => session()->get('userid'), 'created_date' => date('Y-m-d H:i:s')];
                    $saved = $model->add($data);
                }
                if (!$saved) { $error = 'Kode risiko gagal disimpan.'; }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                $error = 'Kode risiko gagal disimpan. Periksa apakah kode sudah terdaftar.';
            }
        }
        $redirect = redirect()->to(site_url('risk/masterrisk'))->withCookies();
        if ($error !== '') {
            session()->setFlashdata('masterrisk_error', $error);
            return $redirect->withInput();
        }
        session()->setFlashdata('masterrisk_success', $id !== '' ? 'Kode risiko berhasil diperbarui.' : 'Kode risiko berhasil ditambahkan.');
        return $redirect;
    }

    public function savecategory()
    {
        $this->authorize();
        if (strtolower(request()->getMethod()) !== 'post') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        service('security')->verify(request());
        $model = new MasterriskModel();
        $input = request()->getPost('category_name');
        $name = is_scalar($input) ? trim((string) $input) : '';
        $input = request()->getPost('category_description');
        $description = is_scalar($input) ? trim((string) $input) : '';
        $error = '';
        if (!$model->categoriesReady()) {
            $error = 'Tabel master kategori risiko belum tersedia.';
        } elseif ($name === '' || mb_strlen($name) > 100) {
            $error = 'Nama kategori wajib diisi, maksimal 100 karakter.';
        } elseif (mb_strlen($description) > 12000) {
            $error = 'Keterangan maksimal 12.000 karakter.';
        } elseif ($model->categoryExists(session()->get('groupid'), session()->get('orgid'), $name)) {
            $error = 'Kategori risiko sudah terdaftar.';
        } else {
            try {
                if (!$model->addCategory([
                    'category_id' => bin2hex(random_bytes(16)), 'group_id' => session()->get('groupid'), 'org_id' => session()->get('orgid'),
                    'name' => $name, 'description' => $description, 'created_by' => session()->get('userid'), 'created_date' => date('Y-m-d H:i:s'),
                ])) { $error = 'Kategori risiko gagal disimpan.'; }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                $error = 'Kategori risiko gagal disimpan. Periksa apakah nama sudah terdaftar.';
            }
        }
        $redirect = redirect()->to(site_url('risk/masterrisk') . '#master-categories')->withCookies();
        if ($error !== '') {
            session()->setFlashdata('masterrisk_category_error', $error);
            return $redirect->withInput();
        }
        session()->setFlashdata('masterrisk_success', 'Kategori risiko berhasil ditambahkan.');
        return $redirect;
    }
}
