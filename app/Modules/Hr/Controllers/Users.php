<?php

namespace Modules\Hr\Controllers;

use App\Controllers\BaseController;
use Modules\Hr\Models\UsersModel;

class Users extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new UsersModel();
    }

    public function index(){
        $data = $this->loadcombobox();
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Hr\Views\v_users',$data)
        ]);
    }

    private function loadcombobox(){
        $resultdatamasterdepartment = $this->md->datamasterdepartment(session()->get('groupid'), session()->get('orgid'));

        $data['datamasterdepartment'] = '';
        foreach ($resultdatamasterdepartment as $row) {
            $data['datamasterdepartment'] .= '<option value="' . $row->department_id . '">' . $row->department . '</option>';
        }

        return $data;
    }

    public function datamasterusers(){
        $result = $this->md->datamasterusers(session()->get('groupid'), session()->get('orgid'));

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

    public function activation(){
        $userid = request()->getPost('userid');
        $active = request()->getPost('active');

        $dataupdate = ['suspend'   => $active];

        if($this->md->updateuser($userid,$dataupdate)){
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = $active == "1" ? "User has been activated successfully." : "User has been deactivated successfully.";
        }else{
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to update User activation status.";
        }

        return response()->setJSON($json);
    }

    public function deleteuser(){
        $userid = request()->getPost('userid');
        $active = request()->getPost('active');

        $dataupdate = ['active'   => $active];

        if($this->md->updateuser($userid,$dataupdate)){
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = $active == "1" ? "User has been deleted successfully." : "User has been deleted successfully.";
        }else{
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to update User deleted status.";
        }

        return response()->setJSON($json);
    }

    public function adduser(){
        $userid           = generateuuid();
        $username         = request()->getPost("modal_add_user_username");
        $nikrs            = request()->getPost("modal_add_user_nikrs");
        $department       = request()->getPost("modal_add_user_department");
        $namakaryawan     = request()->getPost("modal_add_user_name");
        $email            = request()->getPost("modal_add_user_email");
        $file             = request()->getFile('modal_add_user_avatar');
        $resultcheckemail = $this->md->checkemail($userid,$email);

        if(!empty($resultcheckemail) && $email !=""){
            $json['responCode'] = "01";
            $json['responHead'] = "error";
            $json['responDesc'] = "Email already exists.";

            return response()->setJSON($json);
        }

        if ($file && $file->isValid() && !$file->hasMoved()) {

            $uploadPath = FCPATH . 'assets/media/avatars/';
            $fileName   = $userid . '.jpg';
            $fullPath   = $uploadPath . $fileName;

            // Validasi ekstensi
            $extension = strtolower($file->getClientExtension());

            if (!in_array($extension, ['jpg', 'jpeg'])) {

                $json['responCode'] = "01";
                $json['responHead'] = "error";
                $json['responDesc'] = "Format file harus JPG atau JPEG.";

                return response()->setJSON($json);

            }

            // Validasi MIME type
            if (!in_array($file->getMimeType(), ['image/jpeg'])) {

                $json['responCode'] = "01";
                $json['responHead'] = "error";
                $json['responDesc'] = "File bukan gambar JPEG yang valid.";

                return response()->setJSON($json);

            }

            // Pastikan direktori tersedia
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Hapus file lama jika tersedia
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            // Upload file dengan nama userid.jpg
            if (!$file->move($uploadPath, $fileName)) {

                $json['responCode'] = "01";
                $json['responHead'] = "error";
                $json['responDesc'] = "Gagal mengupload avatar.";

                return response()->setJSON($json);

            }

            // Validasi dan konversi ulang JPEG menggunakan GD
            $image = @imagecreatefromjpeg($fullPath);

            if ($image !== false) {

                $rgb = imagecreatetruecolor(imagesx($image), imagesy($image));

                imagecopy(
                    $rgb,
                    $image,
                    0, 0, 0, 0,
                    imagesx($image),
                    imagesy($image)
                );

                imagejpeg($rgb, $fullPath, 95);

                imagedestroy($image);
                imagedestroy($rgb);

            } else {

                @unlink($fullPath);

                $json['responCode'] = "01";
                $json['responHead'] = "error";
                $json['responDesc'] = "File tidak valid.";

                return response()->setJSON($json);

            }

        } elseif ($file && !$file->isValid() && $file->getError() !== UPLOAD_ERR_NO_FILE) {

            $json['responCode'] = "01";
            $json['responHead'] = "error";
            $json['responDesc'] = $file->getErrorString();

            return response()->setJSON($json);
        }

        $datainsert = [
            'group_id'       => session()->get('groupid') ?? null,
            'org_id'         => session()->get('orgid') ?? null,
            'user_id'        => $userid ?? null,
            'username'       => $username ?? null,
            'department_id'  => $department ?? null,
            'nik'            => !empty($nikrs) ? $nikrs : null,
            'name'           => $namakaryawan ?? null,
            'email'          => $email ?? null,
            'created_by'     => session()->get('userid') ?? null,
            'last_update_by' => session()->get('userid') ?? null
        ];

        if($this->md->insertuser($datainsert)){
            $json['responCode']="00";
            $json['responHead']="success";
            $json['responDesc']="Data Updated Successfully";
        }else{
            $json['responCode']="01";
            $json['responHead']="info";
            $json['responDesc']="Data failed to update";
        }
        

        return response()->setJSON($json);
    }

    public function edituser(){
        $userid           = request()->getPost("modal_edit_user_userid");
        $username         = request()->getPost("modal_edit_user_username");
        $nikrs            = request()->getPost("modal_edit_user_nikrs");
        $department       = request()->getPost("modal_edit_user_department");
        $namakaryawan     = request()->getPost("modal_edit_user_name");
        $email            = request()->getPost("modal_edit_user_email");
        $file             = request()->getFile('modal_edit_user_avatar');
        $resultcheckemail = $this->md->checkemail($userid, $email);

        // Validasi email duplikat
        if(!empty($resultcheckemail) && $email !=""){
            $json['responCode'] = "01";
            $json['responHead'] = "error";
            $json['responDesc'] = "Email already exists.";

            return response()->setJSON($json);
        }

        // Upload avatar jika ada file baru
        if ($file && $file->isValid() && !$file->hasMoved()) {

            $uploadPath = FCPATH . 'assets/media/avatars/';
            $fileName   = $userid . '.jpg';
            $fullPath   = $uploadPath . $fileName;

            // Validasi ekstensi
            $extension = strtolower($file->getClientExtension());

            if (!in_array($extension, ['jpg', 'jpeg'])) {
                $json['responCode'] = "01";
                $json['responHead'] = "error";
                $json['responDesc'] = "Format file harus JPG atau JPEG.";

                return response()->setJSON($json);
            }

            // Validasi MIME type
            if ($file->getMimeType() !== 'image/jpeg') {
                $json['responCode'] = "01";
                $json['responHead'] = "error";
                $json['responDesc'] = "File bukan gambar JPEG yang valid.";

                return response()->setJSON($json);
            }

            // Pastikan direktori tersedia
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Upload dengan nama userid.jpg
            if (!$file->move($uploadPath, $fileName, true)) {
                $json['responCode'] = "01";
                $json['responHead'] = "error";
                $json['responDesc'] = "Gagal mengupload avatar.";

                return response()->setJSON($json);
            }

            // Validasi dan konversi ulang JPEG menggunakan GD
            $image = @imagecreatefromjpeg($fullPath);

            if ($image !== false) {
                $rgb = imagecreatetruecolor(imagesx($image), imagesy($image));

                imagecopy(
                    $rgb,
                    $image,
                    0, 0, 0, 0,
                    imagesx($image),
                    imagesy($image)
                );

                imagejpeg($rgb, $fullPath, 95);

                imagedestroy($image);
                imagedestroy($rgb);

            } else {
                @unlink($fullPath);

                $json['responCode'] = "01";
                $json['responHead'] = "error";
                $json['responDesc'] = "File tidak valid.";

                return response()->setJSON($json);
            }

        } elseif ($file && !$file->isValid() && $file->getError() !== UPLOAD_ERR_NO_FILE) {

            $json['responCode'] = "01";
            $json['responHead'] = "error";
            $json['responDesc'] = $file->getErrorString();

            return response()->setJSON($json);
        }

        // Data update
        $dataupdate = [
            'username'         => $username ?? null,
            'department_id'    => $department ?? null,
            'nik'              => !empty($nikrs) ? $nikrs : null,
            'name'             => $namakaryawan ?? null,
            'email'            => $email ?? null,
            'last_update_by'   => session()->get('userid') ?? null,
            'last_update_date' => date('Y-m-d H:i:s')
        ];

        if($this->md->updateuser($userid, $dataupdate)){
            $json['responCode'] = "00";
            $json['responHead'] = "success";
            $json['responDesc'] = "Data Updated Successfully";
        }else{
            $json['responCode'] = "01";
            $json['responHead'] = "info";
            $json['responDesc'] = "Data failed to update";
        }

        return response()->setJSON($json);
    }

}