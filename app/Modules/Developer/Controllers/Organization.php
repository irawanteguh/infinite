<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;
use Modules\Developer\Models\OrganizationModel;

class Organization extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new OrganizationModel();
    }

    public function index(){
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Developer\Views\v_organization')
        ]);
    }

    public function masterorganization(){
        $result = $this->md->masterorganization();

        if (!empty($result)) {
            $json["responseCode"]   = "00";
            $json["responseHead"]   = "success";
            $json["responseDesc"]   = "Data found";
            $json["responseResult"] = $result;
        } else {
            $json["responseCode"]   = "01";
            $json["responseHead"]   = "info";
            $json["responseDesc"]   = "No data found";
        }

        return response()->setJSON($json);
    }

    public function updateActivation(){
        $orgid  = request()->getPost('orgid');
        $active = request()->getPost('active');

        if (empty($orgid)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Organization ID is required.'
            ]);
        }

        $dataupdate['active'] = $active;

        if ($this->md->updatemasterorganization($orgid, $dataupdate)) {
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = $active == "1"
                ? "Organization has been activated successfully."
                : "Organization has been deactivated successfully.";
        } else {
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to update organization activation status.";
        }

        return response()->setJSON($json);
    }

    public function headerorganizations(){
        $result = $this->md->headerorganizations();

        if (!empty($result)) {
            $json["responseCode"]   = "00";
            $json["responseHead"]   = "success";
            $json["responseDesc"]   = "Data found";
            $json["responseResult"] = $result;
        } else {
            $json["responseCode"]   = "01";
            $json["responseHead"]   = "info";
            $json["responseDesc"]   = "No data found";
        }

        return response()->setJSON($json);
    }

    public function addorganization(){
        $orgName  = trim((string) request()->getPost('modal_add_organization_name'));
        $type     = request()->getPost('modal_add_organization_type');
        $headerId = trim((string) request()->getPost('modal_add_organization_header'));
        $website  = trim((string) request()->getPost('modal_add_organization_website'));
        $email    = trim((string) request()->getPost('modal_add_organization_email'));
        $address  = trim((string) request()->getPost('modal_add_organization_address'));
        $kodeRs   = trim((string) request()->getPost('modal_add_organization_koders'));
        $kodePpk  = trim((string) request()->getPost('modal_add_organization_kodeppk'));

        if ($orgName === '' || $type === '') {
            return response()->setJSON([
                'responseCode' => '01',
                'responseHead' => 'error',
                'responseDesc' => 'Nama organization dan tipe wajib diisi.',
            ]);
        }

        if ($type === 'B' && $headerId === '') {
            return response()->setJSON([
                'responseCode' => '01',
                'responseHead' => 'error',
                'responseDesc' => 'Parent organization wajib dipilih untuk tipe Branch.',
            ]);
        }

        $holding = 'A';
        if ($type === 'B') {
            $holding = $kodeRs !== '' ? 'C' : 'B';
        }

        $orgId = generateuuid();

        $datainsert = [
            'org_id'      => $orgId,
            'header_id'   => $type === 'B' ? $headerId : null,
            'kode_rs'     => $kodeRs !== '' ? $kodeRs : null,
            'kode_ppk'    => $kodePpk !== '' ? $kodePpk : null,
            'org_name'    => $orgName,
            'website'     => $website !== '' ? $website : null,
            'email'       => $email !== '' ? $email : null,
            'address'     => $address !== '' ? $address : null,
            'holding'     => $holding,
            'trial'       => 'Y',
            'active'      => '1',
            'created_by'  => session()->get('userid'),
        ];

        if (!$this->md->insertorganization($datainsert)) {
            return response()->setJSON([
                'responseCode' => '01',
                'responseHead' => 'info',
                'responseDesc' => 'Gagal menambahkan organization.',
            ]);
        }

        $desc = 'Organization berhasil ditambahkan.';

        if ($kodeRs !== '') {
            $orgRow      = $this->md->getorganization($orgId);
            $adminResult = $this->createAdministratorForOrganization($orgRow);
            $desc       .= ' ' . $adminResult['desc'];

            if (!$adminResult['ok']) {
                return response()->setJSON([
                    'responseCode' => '01',
                    'responseHead' => 'info',
                    'responseDesc' => $desc,
                ]);
            }
        }

        return response()->setJSON([
            'responseCode' => '00',
            'responseHead' => 'success',
            'responseDesc' => $desc,
        ]);
    }

    public function addadministratoruser(){
        $orgid = request()->getPost('orgid');

        if (empty($orgid)) {
            return response()->setJSON([
                'responseCode' => '01',
                'responseHead' => 'error',
                'responseDesc' => 'Organization ID wajib diisi.',
            ]);
        }

        $org = $this->md->getorganization($orgid);

        if (empty($org)) {
            return response()->setJSON([
                'responseCode' => '01',
                'responseHead' => 'error',
                'responseDesc' => 'Organization tidak ditemukan.',
            ]);
        }

        $adminResult = $this->createAdministratorForOrganization($org);

        return response()->setJSON([
            'responseCode' => $adminResult['ok'] ? '00' : '01',
            'responseHead' => $adminResult['ok'] ? 'success' : 'info',
            'responseDesc' => $adminResult['desc'],
        ]);
    }

    private function createAdministratorForOrganization($org){
        $kodeRs = trim((string) ($org->kode_rs ?? ''));

        if ($kodeRs === '') {
            return [
                'ok'   => false,
                'desc' => 'Kode RS belum diisi. Tidak dapat membuat user administrator.',
            ];
        }

        $username = 'admin_' . $kodeRs;

        if (!empty($this->md->checkusername($username))) {
            return [
                'ok'   => false,
                'desc' => 'User administrator sudah ada dengan username ' . $username . '.',
            ];
        }

        $orgId    = $org->org_id ?? '';
        $headerId = trim((string) ($org->header_id ?? ''));
        $holding  = $org->holding ?? '';
        $groupId  = ($holding === 'C' && $headerId !== '') ? $headerId : $orgId;

        $userid = generateuuid();

        $datainsert = [
            'group_id'       => $groupId,
            'org_id'         => $orgId,
            'user_id'        => $userid,
            'username'       => $username,
            'password'       => encodedata('12345678'),
            'name'           => 'Administrator',
            'email'          => !empty($org->email) ? $org->email : null,
            'active'         => '1',
            'suspend'        => '0',
            'created_by'     => session()->get('userid'),
            'last_update_by' => session()->get('userid'),
        ];

        if (!$this->md->insertuser($datainsert)) {
            return [
                'ok'   => false,
                'desc' => 'Gagal membuat user administrator.',
            ];
        }

        return [
            'ok'       => true,
            'desc'     => 'User administrator ' . $username . ' berhasil dibuat (password default: 12345678).',
            'username' => $username,
        ];
    }
}