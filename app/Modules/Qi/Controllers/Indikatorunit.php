<?php

namespace Modules\Qi\Controllers;

use App\Controllers\BaseController;
use Modules\Qi\Models\IndikatorunitModel;

class Indikatorunit extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new IndikatorunitModel();
    }

    public function index(){
        $uuid = request()->getGet('uuid');
        $data = $this->loadcombobox();

        if(!empty($uuid)){
            $data['uuid'] = $uuid;

            return view('template/dashboard-light-aside', [
                'contents' => view('Modules\Qi\Views\v_indikatorunitsubmit', $data)
            ]);
        }else{
            return view('template/dashboard-light-aside', [
                'contents' => view('Modules\Qi\Views\v_indikatorunit', $data)
            ]);
        }
    }

    private function loadcombobox(){
        $resultdatamasterperiodepelaporan = $this->md->datamasterperiodepelaporan(session()->get('groupid'), session()->get('orgid'));
        $resultdatamasterdepartment       = $this->md->datamasterdepartment(session()->get('groupid'), session()->get('orgid'), session()->get('userid'));
        $resultdatauser                   = $this->md->datauser(session()->get('groupid'), session()->get('orgid'), session()->get('departmentid'));
        $resultdatamasterindikator        = $this->md->datamasterindikator(session()->get('groupid'), session()->get('orgid'));

        $data['masterperiodepelaporan'] = '';
        foreach ($resultdatamasterperiodepelaporan as $row) {
            $data['masterperiodepelaporan'] .= '<option value="' . $row->periode_id . '">' . $row->tahun . '</option>';
        }

        $data['masterdatadepartment'] = '';
        foreach ($resultdatamasterdepartment as $row) {
            $data['masterdatadepartment'] .= '<option value="' . $row->department_id . '">' . $row->department . '</option>';
        }

        $data['masterdatauser'] = '';
        foreach ($resultdatauser as $row) {
            $data['masterdatauser'] .= '<option value="' . $row->user_id . '">' . $row->name . '</option>';
        }

        $data['masterindikator'] = '';
        foreach ($resultdatamasterindikator as $row) {
            $data['masterindikator'] .= '<option value="' . $row->indikator_id . '">' . $row->indikator . '</option>';
        }

        return $data;
    }

    public function addindikatorunit(){
        $periode      = request()->getPost("modal_add_pengajuanindikatorunit_periodeid");
        $userid       = request()->getPost("modal_add_pengajuanindikatorunit_userid");
        $target       = request()->getPost("modal_add_pengajuanindikatorunit_target");
        $indikatorid  = request()->getPost("modal_add_pengajuanindikatorunit_indikatorid");
        $departmentid = request()->getPost("modal_add_pengajuanindikatorunit_departmentid");

        $datainsert = [
            'group_id'      => $_SESSION['groupid'] ?? null,
            'org_id'        => $_SESSION['orgid'] ?? null,
            'periode_id'    => $periode ?? null,
            'pic'           => $userid ?? null,
            'target'        => $target ?? null,
            'indikator_id'  => $indikatorid ?? null,
            'department_id' => $departmentid ?? null,
            'created_by'    => $_SESSION['userid'] ?? null
        ];


        if($this->md->insertindikatorunit($datainsert)){
            $json['responseCode']="00";
            $json['responseHead']="success";
            $json['responseDesc']="Data Updated Successfully";
        }else{
            $json['responseCode']="01";
            $json['responseHead']="info";
            $json['responseDesc']="Data failed to update";
        }
        

        return response()->setJSON($json);
    }

    public function updateindikatorunit(){
        $uuid         = request()->getPost("modal_edit_pengajuanindikatorunit_uuid");
        $periode      = request()->getPost("modal_edit_pengajuanindikatorunit_periodeid");
        $userid       = request()->getPost("modal_edit_pengajuanindikatorunit_userid");
        $target       = request()->getPost("modal_edit_pengajuanindikatorunit_target");
        $indikatorid  = request()->getPost("modal_edit_pengajuanindikatorunit_indikatorid");
        $departmentid = request()->getPost("modal_edit_pengajuanindikatorunit_departmentid");

        $dataupdate = [
            'periode_id'       => $periode ?? null,
            'pic'              => $userid ?? null,
            'target'           => $target ?? null,
            'indikator_id'     => $indikatorid ?? null,
            'department_id'    => $departmentid ?? null,
            'last_update_by'   => $_SESSION['userid'] ?? null,
            'last_update_date' => date('Y-m-d H:i:s')
        ];

        if ($this->md->updateindikator($uuid, $dataupdate)) {
            $json['responseCode'] = "00";
            $json['responseHead'] = "success";
            $json['responseDesc'] = "Data Updated Successfully";
        } else {
            $json['responseCode'] = "01";
            $json['responseHead'] = "info";
            $json['responseDesc'] = "Data failed to update";
        }

        return response()->setJSON($json);
    }

    public function picusers()
    {
        $indicator = $this->md->indicatorForPicChange(
            trim((string) request()->getPost('transaksi_id')),
            session()->get('groupid'),
            session()->get('orgid'),
            session()->get('userid')
        );

        if (!$indicator) {
            return response()->setStatusCode(404)->setJSON([
                'responseCode' => '01',
                'responseHead' => 'info',
                'responseDesc' => 'Indikator tidak ditemukan atau tidak dapat diubah.',
            ]);
        }

        return response()->setJSON([
            'responseCode' => '00',
            'responseResult' => [
                'currentPic' => $indicator->pic,
                'users' => $this->md->usersForDepartment(session()->get('groupid'), session()->get('orgid'), $indicator->department_id),
            ],
        ]);
    }

    public function updatepic()
    {
        $transaksiId = trim((string) request()->getPost('transaksi_id'));
        $userId = trim((string) request()->getPost('pic'));
        if ($transaksiId === '' || $userId === '') {
            return response()->setJSON([
                'responseCode' => '01',
                'responseHead' => 'warning',
                'responseDesc' => 'Indikator dan PIC wajib dipilih.',
            ]);
        }

        $updated = $this->md->changeIndicatorPic(
            $transaksiId,
            $userId,
            session()->get('groupid'),
            session()->get('orgid'),
            session()->get('userid')
        );

        return response()->setJSON([
            'responseCode' => $updated ? '00' : '01',
            'responseHead' => $updated ? 'success' : 'warning',
            'responseDesc' => $updated ? 'PIC indikator berhasil diperbarui.' : 'PIC tidak dapat diperbarui. Pastikan PIC aktif pada departemen yang sama.',
        ]);
    }

    public function dataindikatorunit(){
        $result = $this->md->dataindikatorunit(session()->get('groupid'), session()->get('orgid'), session()->get('userid'));

        $submitReference = (string) (request()->getPost('uuid') ?: request()->getGet('uuid'));
        if ($submitReference !== '') {
            foreach ($result as $row) {
                if ((string) $row->transaksi_id !== $submitReference) {
                    continue;
                }
                try {
                    $row->inputStatuses = (new \Modules\Organization\Models\SettingModel())->inputStatuses($submitReference);
                } catch (\Throwable $exception) {
                    log_message('error', 'Gagal memeriksa status pengisian indikator: {message}', ['message' => $exception->getMessage()]);
                    $row->inputStatuses = [];
                }
                for ($month = 1; $month <= 12; $month++) {
                    $monthKey = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
                    $row->{'attachments' . $monthKey} = array_map('basename', $this->attachmentFiles($submitReference, $monthKey));
                }
            }
        }

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

    public function viewattachment(){
        $referenceid = (string) request()->getGet('reference_id');
        $bulan = (string) request()->getGet('bulan');

        if (!$this->md->indikatorReferenceBelongsTo($referenceid, session()->get('groupid'), session()->get('orgid'), session()->get('userid'))) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $files = $this->attachmentFiles($referenceid, $bulan);
        if (empty($files)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $requestedFile = (string) request()->getGet('file');
        if ($requestedFile !== '') {
            $files = array_values(array_filter($files, static function ($path) use ($requestedFile) {
                return basename($path) === $requestedFile;
            }));
            if (empty($files)) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
        }

        $path = $files[0];
        $mimeType = mime_content_type($path) ?: 'application/octet-stream';

        return response()
            ->setContentType($mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($path) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody(file_get_contents($path));
    }

    private function attachmentDirectory($referenceid, $bulan){
        $safeReferenceId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $referenceid);
        $safeMonth = (string) $bulan;
        if ($safeReferenceId === '' || !preg_match('/^(0[1-9]|1[0-2])$/', $safeMonth)) {
            return null;
        }

        return WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'qi' . DIRECTORY_SEPARATOR
            . 'penilaian' . DIRECTORY_SEPARATOR . $safeReferenceId . DIRECTORY_SEPARATOR . $safeMonth;
    }

    private function attachmentFiles($referenceid, $bulan){
        $directory = $this->attachmentDirectory($referenceid, $bulan);
        if (!$directory || !is_dir($directory)) {
            return [];
        }

        $files = array_values(array_filter(glob($directory . DIRECTORY_SEPARATOR . '*') ?: [], 'is_file'));
        usort($files, static function ($left, $right) {
            return filemtime($right) <=> filemtime($left);
        });

        return $files;
    }

    public function datateam(){
        $result = $this->md->datateam(session()->get('groupid'), session()->get('orgid'), session()->get('userid'));

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

    public function deleteindikator(){
        $transaksiid = request()->getPost('transaksiid');

        $dataupdate['active']           = "0";
        $dataupdate['last_update_by']   = session()->get('userid');
        $dataupdate['last_update_date'] = date('Y-m-d H:i:s');

        if($this->md->updateindikator($transaksiid,$dataupdate)){
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = "Indicator has been deleted successfully.";
        }else{
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to deleted Indicator.";
        }

        return response()->setJSON($json);
    }

    public function inputnilaiindikator(){
        $indikator   = request()->getPost("modal_input_nilai_indikator_indikatorid");
        $referenceid = request()->getPost("modal_input_nilai_indikator_referensiid");
        $bulan       = request()->getPost("modal_input_nilai_indikator_bulan");
        $numerator   = request()->getPost("modal_add_pengajuanindikatorunit_numerator");
        $denumerator = request()->getPost("modal_add_pengajuanindikatorunit_denumerator");
        $alasan      = request()->getPost("modal_add_pengajuanindikatorunit_alasan");
        $rtl         = request()->getPost("modal_add_pengajuanindikatorunit_rtl");

        if (!preg_match('/^(0[1-9]|1[0-2])$/', (string) $bulan)
            || !$this->md->indikatorBelongsTo($referenceid, $indikator, session()->get('groupid'), session()->get('orgid'), session()->get('userid'))) {
            return response()->setJSON([
                'responseCode' => '01',
                'responseHead' => 'error',
                'responseDesc' => 'Referensi indikator atau bulan penilaian tidak valid.'
            ]);
        }

        try {
            $allowed = (new \Modules\Organization\Models\SettingModel())->allowsInput($referenceid, $bulan);
        } catch (\Throwable $exception) {
            log_message('error', 'Gagal memeriksa batas pengisian indikator: {message}', ['message' => $exception->getMessage()]);
            return response()->setJSON(['responseCode' => '01', 'responseHead' => 'error', 'responseDesc' => 'Batas pengisian tidak dapat diperiksa. Silakan coba kembali.']);
        }
        if (!$allowed) {
            return response()->setJSON([
                'responseCode' => '01', 'responseHead' => 'warning',
                'responseDesc' => 'Batas waktu pengisian bulan ini telah berakhir. Hubungi pengelola organisasi untuk izin khusus 24 jam.',
            ]);
        }

        $document = request()->getFile('supporting_document');
        $storedDocumentPath = null;

        if ($document && $document->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$document->isValid()) {
                return response()->setJSON([
                    'responseCode' => '01',
                    'responseHead' => 'error',
                    'responseDesc' => 'Dokumen gagal diunggah. Periksa ukuran file dan coba kembali.'
                ]);
            }

            if ($document->getSize() > 10 * 1024 * 1024) {
                return response()->setJSON([
                    'responseCode' => '01',
                    'responseHead' => 'error',
                    'responseDesc' => 'Ukuran dokumen maksimal 10 MB.'
                ]);
            }

            $allowedMimeTypes = [
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'image/jpeg' => 'jpg',
                'image/png' => 'png'
            ];
            $mimeType = strtolower($document->getMimeType());

            if (!isset($allowedMimeTypes[$mimeType])) {
                return response()->setJSON([
                    'responseCode' => '01',
                    'responseHead' => 'error',
                    'responseDesc' => 'Format dokumen harus PDF, Word, Excel, JPG, atau PNG.'
                ]);
            }

            $uploadDirectory = $this->attachmentDirectory($referenceid, $bulan);
            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
                return response()->setJSON([
                    'responseCode' => '01',
                    'responseHead' => 'error',
                    'responseDesc' => 'Folder penyimpanan dokumen tidak dapat dibuat.'
                ]);
            }

            try {
                $storedName = bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType];
                $document->move($uploadDirectory, $storedName);
                $storedDocumentPath = $uploadDirectory . DIRECTORY_SEPARATOR . $storedName;
            } catch (\Throwable $exception) {
                log_message('error', 'Gagal menyimpan dokumen penilaian indikator: {message}', ['message' => $exception->getMessage()]);
                return response()->setJSON([
                    'responseCode' => '01',
                    'responseHead' => 'error',
                    'responseDesc' => 'Dokumen gagal disimpan ke penyimpanan.'
                ]);
            }
        }

        $datainsert = [
            'group_id'     => session()->get('groupid') ?? null,
            'org_id'       => session()->get('orgid') ?? null,
            'referensi_id' => $referenceid ?? null,
            'indikator_id' => $indikator ?? null,
            'bulan'        => $bulan ?? null,
            'numerator'    => $numerator ?? 0,
            'denumerator'  => $denumerator ?? 0,
            'reason'       => $alasan ?? null,
            'rtl'          => $rtl ?? null,
            'created_by'   => session()->get('userid') ?? null
        ];

        try {
            $saved = $this->md->saveNilaiindikator($datainsert);
        } catch (\Throwable $exception) {
            log_message('error', 'Gagal menyimpan penilaian indikator: {message}', ['message' => $exception->getMessage()]);
            $saved = false;
        }

        if ($saved) {
            $json['responseCode']="00";
            $json['responseHead']="success";
            $json['responseDesc'] = $storedDocumentPath
                ? 'Penilaian dan dokumen pendukung berhasil disimpan.'
                : 'Penilaian berhasil disimpan.';
        }else{
            if ($storedDocumentPath && is_file($storedDocumentPath)) {
                unlink($storedDocumentPath);
            }
            $json['responseCode']="01";
            $json['responseHead']="info";
            $json['responseDesc']="Data failed to update";
        }
        

        return response()->setJSON($json);
    }

}
